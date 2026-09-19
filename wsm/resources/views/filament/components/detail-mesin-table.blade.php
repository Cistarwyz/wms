@php
    // Gunakan helper bawaan Filament jika variabel $mesin tidak dipass secara eksplisit
   $mesin = $mesin ?? $getRecord();

    // Panggil nama pemilik melalui relasi (pastikan model Miner punya fungsi owner())
    $pemilik = $mesin->owner->name ?? 'Tidak diketahui';
    
    // Amankan data posisi dengan memanggil dari object $mesin
    $namaRak = $mesin->shelf->name ?? '#A'; 
    $tingkat = $mesin->shelf_level ?? '-';
    $urutan = $mesin->slot_number ?? '-';
    
    // FIX: Ambil nilainya dari properti $mesin, bukan dari variabel yang belum didefinisikan
    $displayRack = $mesin->shelf_number ?? '-';
    $levelNum = $mesin->shelf_level ?? '-';
@endphp

<style>
    .dc-container { 
        display: grid; 
        grid-template-areas: "left center right";
        grid-template-columns: 200px auto 200px; 
        gap: 1.5rem; background: #0f172a; padding: 2rem; border-radius: 1rem; border: 1px solid #1e293b; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    }
    
    .panel-left { grid-area: left; }
    .panel-right { grid-area: right; }
    
    .panel-center { 
        grid-area: center; 
        display: flex; 
        justify-content: flex-start;
        width: 100%; 
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 1rem; 
    }

    .dc-panel { 
        background: #1e293b; border: 1px solid #334155; border-radius: 0.75rem; 
        padding: 1.5rem; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
        display: flex; flex-direction: column; justify-content: center;
    }
    .dc-label { color: #94a3b8; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.5rem; }
    
    .neon-text { font-weight: 900; text-transform: uppercase; }
    .text-rack { color: #1eff00; font-size: 2.3rem; text-shadow: 0 0 15px rgba(56,189,248,0.4); }
    .text-level { color: #a78bfa; font-size: 2.3rem; text-shadow: 0 0 15px rgba(167,139,250,0.4); }
    .text-slot { color: #ff9500; font-size: 2.3rem; text-shadow: 0 0 20px rgba(16,185,129,0.5); }
    .text-nomor { color: #ffae00; font-size: 2rem; text-shadow: 0 0 20px rgba(255, 170, 0, 0.915); }

    /* BINGKAI RAK */
    .supermarket-rack { 
        background: #020617; 
        border-left: 10px solid #334155; border-right: 10px solid #334155; border-top: 6px solid #334155;
        border-radius: 6px 6px 0 0; 
        display: flex; flex-direction: column; 
        padding: 10px 15px 0 15px;
        box-shadow: inset 0 0 30px rgba(0,0,0,1);
        min-width: 650px; 
        margin-bottom: 5px;
        margin-left: auto;
       margin-right: auto;
    }
    
    /* PAPAN RAK TIAP TINGKAT */
    .shelf-row { 
        display: flex; justify-content: space-between; align-items: flex-end; 
        height: 55px; border-bottom: 8px solid #475569; margin-bottom: 15px; position: relative; gap: 6px;
    }
    
    /* FISIK MESIN MINI */
    .machine-box { 
        position: relative; z-index: 10; 
        width: 100%; max-width: 30px; height: 45px; 
        background: linear-gradient(180deg, #1e293b, #0f172a); 
        border: 1px solid #334155; border-radius: 4px; 
        display: flex; flex-direction: column; align-items: center; justify-content: center; 
        transition: all 0.3s ease;
    }
    
    .machine-box.active { 
        background: linear-gradient(180deg, #064e3b, #022c22); 
        border-color: #34d399; 
        z-index: 20; 
        /* Panggil animasi pulse-glow di sini */
        animation: pulse-glow 1.5s infinite ease-in-out; 
    }
    
    .blade-led { width: 6px; height: 6px; border-radius: 50%; background: #475569; margin-bottom: 4px;}
    .blade-led.active { background: #34d399; box-shadow: 0 0 8px #34d399; }
    
    .blade-text { color: #64748b; font-size: 0.55rem; font-weight: bold; font-family: monospace; letter-spacing: -0.5px;}
    .machine-box.active .blade-text { color: #ffffff; }

    @media (max-width: 1024px) {
        .dc-container { 
            grid-template-areas: "left right" "center center"; 
            grid-template-columns: 1fr 1fr; 
        }
        .panel-center {
            justify-content: flex-start; 
        }
    }

    @keyframes pulse-glow {
        0% {
            box-shadow: 0 0 15px rgba(52, 211, 153, 0.6);
            transform: scale(1.15) translateY(-4px);
        }
        50% {
            /* Cahaya membesar dan elemen sedikit lebih membesar */
            box-shadow: 0 0 30px rgba(52, 211, 153, 1), 0 0 10px rgba(52, 211, 153, 0.5) inset;
            transform: scale(1.20) translateY(-5px);
        }
        100% {
            box-shadow: 0 0 15px rgba(52, 211, 153, 0.6);
            transform: scale(1.15) translateY(-4px);
        }
    }

    
</style>

<div class="dc-container">
    
    <!-- KOLOM KIRI -->
    <div class="dc-panel panel-left">
        <div class="dc-label">Nomor Mesin</div>
        <div class="neon-text text-nomor">{{  $mesin->name ?? '-'  }}</div>
        
        <div style="height: 1px; background: #334155; margin: 1rem 0;"></div>
        <div class="text-sm font-mono font-bold" style="color: #e2e8f0;">{{ $pemilik }}</div>
            
    </div>

    <!-- KOLOM TENGAH (VISUAL RAK) -->
    <div class="panel-center" x-data x-init="
        setTimeout(() => {
            const activeMachine = $el.querySelector('.machine-box.active');
            if(activeMachine) {
                // Geser otomatis ke posisi tengah secara halus
                activeMachine.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        }, 300); // Diberi jeda 300ms agar menunggu animasi pop-up selesai terbuka
    ">
        <div class="supermarket-rack">
            
            @for ($lvl = 1; $lvl <= 5; $lvl++)
                <div class="shelf-row">
                    @for ($s = 1; $s <= 18; $s++) 
                        @php
                            // FIX: Gunakan variabel $tingkat dan $urutan yang sudah diambil dari $mesin di atas
                            $isActive = ((int)$tingkat === $lvl && (int)$urutan === $s);
                        @endphp
                        
                        <div class="machine-box {{ $isActive ? 'active' : '' }}" title="Level {{ $lvl }} - Urutan {{ $s }}">
                            <div class="blade-led {{ $isActive ? 'active' : '' }}"></div>
                            <!-- LANGSUNG TAMPILKAN VARIABEL $s UNTUK ANGKA 1-18 -->
                            <span class="blade-text">{{ $s }}</span>
                        </div>
                    @endfor
                </div>
            @endfor
            
        </div>
    </div>

    <!-- KOLOM KANAN -->
    <div class="dc-panel panel-right">
        
        <div class="dc-label">Nomor Rak</div>
        <div class="neon-text text-rack">{{ $namaRak }}</div>

        <div style="height: 1px; background: #334155; margin: 1rem 0;"></div>
        
        <div class="dc-label">Tingkat</div>
        <div class="neon-text text-level">{{ $tingkat }}</div>
            
        <div style="height: 1px; background: #334155; margin: 1rem 0;"></div>
            
        <div class="dc-label">Posisi Urut</div>
        <div class="neon-text text-slot">{{ $urutan }}</div>   
    </div>
</div>
