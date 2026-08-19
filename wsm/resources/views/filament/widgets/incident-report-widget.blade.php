<x-filament-widgets::widget wire:poll.5s>
    @php
        $offlineMiners = $this->getOfflineMiners();
        $offlineIsps = $this->getOfflineIsps();
        $hasIncident = $offlineMiners->count() > 0 || $offlineIsps->count() > 0;
        $mikrotikDown = $this->getMikrotikStatus() === 'offline';
    @endphp

    <!-- Root div wajib untuk Livewire -->
    <div style="width: 100%; font-family: sans-serif;">
        
        <!-- 1. MIKROTIK DOWN (Prioritas Utama) -->
        @if($mikrotikDown)
            <div style="background-color: #dc2626; color: white; padding: 24px; border-radius: 12px; border: 4px solid #7f1d1d; margin-bottom: 24px;">
                <h3 style="font-weight: 900; font-size: 24px; margin: 0;">⚠️ CRITICAL INCIDENT: MIKROTIK GATEWAY DOWN!</h3>
                <p style="margin-top: 8px; font-weight: bold; font-size: 16px;">Koneksi utama ke jaringan lokal terputus total (192.168.100.1). Seluruh pemantauan mesin ditangguhkan sementara waktu.</p>
            </div>
        @endif

        <!-- 2. MESIN / ISP MATI -->
        @if($hasIncident && !$mikrotikDown)
            <div style="background-color: #171717; border: 3px solid #dc2626; padding: 24px; border-radius: 12px;">
                
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                    <!-- Icon Bulat -->
                    <div style="background-color: #dc2626; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 28px; height: 28px; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 style="color: #ef4444; font-size: 24px; font-weight: 900; margin: 0; text-transform: uppercase; letter-spacing: 2px;">
                            CRITICAL INCIDENT!
                        </h2>
                        <p style="color: #f87171; font-weight: bold; margin: 0; font-size: 14px;">
                            Segera Lakukan Pengecekan Jaringan
                        </p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- LIST ISP MATI -->
                    @foreach($offlineIsps as $isp)
                        <div style="background-color: #dc2626; color: white; padding: 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
                            <span style="font-size: 16px;">ISP DOWN: {{ $isp->name }}</span>
                            <span style="background-color: white; color: #dc2626; padding: 4px 12px; border-radius: 99px; font-size: 12px;">RTO / Gateway Terputus</span>
                        </div>
                    @endforeach

                    <!-- LIST MESIN MATI -->
                    @foreach($offlineMiners as $miner)
                        <div style="background-color: #450a0a; border-left: 6px solid #dc2626; color: #fecaca; padding: 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-weight: bold;">
                            <span style="font-size: 16px;">MESIN OFFLINE: {{ $miner->name }}</span>
                            <span style="background-color: #dc2626; color: white; padding: 4px 12px; border-radius: 99px; font-size: 12px;">Rak: {{ $miner->slot_number ?? 'Unknown' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        <!-- 3. STATUS AMAN HIJAU -->
        @elseif(!$mikrotikDown)
            <div style="background-color: #171717; border: 3px solid #22c55e; padding: 24px; border-radius: 12px; display: flex; align-items: center; gap: 16px;">
                <div style="background-color: #22c55e; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 28px; height: 28px; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 style="color: #4ade80; font-size: 20px; font-weight: 900; margin: 0;">Sistem Jaringan Aman</h2>
                    <p style="color: #22c55e; font-weight: bold; margin: 0; font-size: 14px;">Seluruh ISP dan Mesin terpantau Online.</p>
                </div>
            </div>
        @endif
        
    </div>
</x-filament-widgets::widget>