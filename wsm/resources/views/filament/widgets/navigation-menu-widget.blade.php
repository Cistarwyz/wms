<x-filament-widgets::widget>
    <div class="custom-neon-menu" style="height: 100%; display: flex; flex-direction: column; gap: 1rem;">
        
        <h3>SYSTEM NAVIGATION</h3>

        <!-- BUNGKUS UTAMA DENGAN GRID FLEKSIBEL (Lebar penuh) -->
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1rem; align-items: stretch;">
            
            <!-- KOLOM KIRI: TOMBOL NAVIGASI -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-content: flex-start;">
                
                <a href="/monitor" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-cyan">
                    <x-heroicon-o-squares-2x2 style="width: 1.25rem; height: 1.25rem;"/>
                    Dashboard
                </a>

                <a href="/monitor/isps" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-blue">
                    <x-heroicon-o-globe-alt style="width: 1.25rem; height: 1.25rem;"/>
                    ISPs
                </a>

                <a href="/monitor/miners" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-green">
                    <x-heroicon-o-server-stack style="width: 1.25rem; height: 1.25rem;"/>
                    Daftar Mesin
                </a>

                <a href="/monitor/owners" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-purple">
                    <x-heroicon-o-users style="width: 1.25rem; height: 1.25rem;"/>
                    Data Miner
                </a>

                <a href="/monitor/shelves" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-orange">
                    <x-clarity-rack-server-solid style="width: 1.25rem; height: 1.25rem;"/>
                    Data Rak
                </a>
                <a href="/monitor/users" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-purple">
                    <x-heroicon-s-building-office style="width: 1.25rem; height: 1.25rem;"/>
                    Data Pos
                </a>

            </div>

            <!-- KOLOM KANAN: LOG IMPORT (Melebar penuh mengisi ruang kosong) -->
            <div style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.75rem; display: flex; flex-direction: column; height: 100%;">
                <h4 style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                    <x-heroicon-o-clipboard-document-list style="width: 1rem; height: 1rem; color: #a855f7;" />
                    Log Import Terbaru
                </h4>
                
                <!-- Area Scrollable untuk Log -->
                <div style="flex: 1; min-height: 160px; max-height: 220px; overflow-y: auto; font-family: monospace; font-size: 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; padding-right: 0.3rem;">
                    
                    @forelse($logs ?? [] as $log)
                        <div style="background: rgba(255,255,255,0.05); padding: 0.4rem; border-radius: 4px; border-left: 2px solid #a855f7;">
                            <span style="color: #6ee7b7;">[{{ $log->created_at->format('H:i:s') }}]</span> 
                            <span style="color: #cbd5e1;">{{ $log->message }}</span>
                        </div>
                    @empty
                        <div style="opacity: 0.5; text-align: center; padding: 2rem 0; color: #cbd5e1;">
                            Belum ada data log import...
                        </div>
                    @endforelse

                </div>
            </div>

        </div>

    </div>
</x-filament-widgets::widget>