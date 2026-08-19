const { RouterOSAPI } = require('node-routeros');
const Redis = require('ioredis');

const redis = new Redis({ host: process.env.REDIS_HOST || 'redis', port: 6379 });

// Konfigurasi koneksi menggunakan node-routeros murni
const ros = new RouterOSAPI({
    host: process.env.MIKROTIK_HOST || '192.168.100.1',
    user: process.env.MIKROTIK_USER || 'admin',
    password: process.env.MIKROTIK_PASS || 'password',
    keepalive: true,
    timeout: 0
});

// Menangkap error background agar sistem tidak crash
ros.on('error', (err) => {
    console.error('[Worker] RouterOS Background Error:', err.message);
});

// DAFTAR INTERFACE ISP ANDA (Sesuaikan dengan nama di Mikrotik)
// Contoh: 'ether1', 'ether2', 'pppoe-biznet', dll.
const wanInterfaces = ['ether1', 'ether2', 'ether3', 'ether4'];

async function syncData() {
    try {
        // Menggunakan perintah .write() yang 100% stabil
        const leases = await ros.write('/ip/dhcp-server/lease/print');
        const queues = await ros.write('/queue/simple/print');
        
        // Map kecepatan dari Simple Queue
        const trafficMap = {};
        for (const q of queues) {
            const targetIp = q.target ? q.target.split('/')[0] : null;
            if (targetIp && q.rate) {
                const [tx, rx] = q.rate.split('/'); 
                trafficMap[targetIp] = { tx: parseInt(tx), rx: parseInt(rx) };
            }
        }

        // Gabungkan dan simpan ke Redis
        for (const lease of leases) {
    // Tambahkan status 'waiting' agar data dummy kita bisa terbaca
    if (lease.status === 'bound' || lease.status === 'waiting') { 
        const ip = lease.address;
        const mac = lease['mac-address'];
        const traffic = trafficMap[ip] || { tx: 0, rx: 0 };
        
        // Cek apakah perangkat sudah punya comment (sudah diklaim)
        const rawComment = lease.comment ? lease.comment : '';
        const parts = rawComment.split('|');

        const data = {
            name: parts[0] ? parts[0].trim() : 'Unknown', // Jika belum diklaim, namanya 'Unknown'
            ssid: parts[1] ? parts[1].trim() : 'Unknown',
            ip: ip,
            mac: mac,
            tx_bps: traffic.tx,
            rx_bps: traffic.rx,
            timestamp: Date.now()
        };

        await redis.hset('miner:status', ip, JSON.stringify(data));
        console.log(`[Worker] Synced: ${data.name} | MAC: ${mac} | IP: ${ip}`);
    }
}

    // 2. TARIK DATA KECEPATAN ISP (BANDWIDTH)
        for (const iface of wanInterfaces) {
            try {
                const traffic = await ros.write('/interface/monitor-traffic', [
                    `=interface=${iface}`,
                    '=once='
                ]);

                if (traffic && traffic.length > 0) {
                    const rxBps = parseInt(traffic[0]['rx-bits-per-second'] || 0); // Download
                    const txBps = parseInt(traffic[0]['tx-bits-per-second'] || 0); // Upload
                    
                    // Konversi murni ke Mbps (Megabits per second)
                    const rxMbps = +(rxBps / 1000000).toFixed(2);
                    const txMbps = +(txBps / 1000000).toFixed(2);

                    //DATA PALSU
                    const fakeDownload = +(50).toFixed(2); // Angka acak 0 - 50 Mbps
                    const fakeUpload = +(Math.random() * 20).toFixed(2);   // Angka acak 0 - 20 Mbps

                    const ispData = {
                        interface: iface,
                        download_mbps: fakeDownload, // Gunakan data palsu
                        upload_mbps: fakeUpload,     // Gunakan data palsu
                        timestamp: Date.now()
                    };

                    //DATA ASLI
                   // const ispData = {
                   //     interface: iface,
                   //     download_mbps: rxMbps,
                   //     upload_mbps: txMbps,
                   //     timestamp: Date.now()
                   // };

                    // Simpan ke Redis dengan key khusus ISP
                    await redis.hset('isp:traffic', iface, JSON.stringify(ispData));
                    console.log(`[Worker] ISP ${iface} -> DL: ${rxMbps} Mbps | UL: ${txMbps} Mbps`);
                }
            } catch (err) {
                // Abaikan jika interface sedang mati atau salah nama
                console.error(`[Worker] Gagal membaca traffic ISP ${iface}:`, err.message);
            }
        }

    } catch (err) {
        console.error('[Worker] Error saat tarik data:', err.message);
    }
}

async function start() {
    console.log('[Worker] Mencoba connect ke MikroTik...');
    await ros.connect();
    console.log('[Worker] Berhasil terhubung ke MikroTik!');
    
    // Tarik data setiap 3 detik
    setInterval(syncData, 5000);
}

start().catch((error) => {
    console.error('[Worker] Fatal Error:', error.message);
    process.exit(1);
});