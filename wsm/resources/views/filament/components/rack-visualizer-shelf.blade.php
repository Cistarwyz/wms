@php
    $shelf = $getRecord(); 
    $miners = $shelf ? $shelf->miners : collect();
    $displayRack = $shelf ? $shelf->name : '-';
    $totalTerisi = $miners->count();
    $kapasitas = 90; 
@endphp

<style>
   .dc-container { 
        display: grid; 
        grid-template-areas: "left center right";
        grid-template-columns: 200px auto 200px; 
        gap: 1.5rem; background: #0f172a; padding: 2rem; border-radius: 1rem; border: 1px solid #1e293b; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        
        /* Tambahan baru: */
        isolation: isolate; 
        position: relative;
        z-index: 1;
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
    .text-rack { color: #38bdf8; font-size: 2.5rem; text-shadow: 0 0 15px rgba(56,189,248,0.4); }
    .text-level { color: #a78bfa; font-size: 1.5rem; text-shadow: 0 0 15px rgba(167,139,250,0.4); }
    .text-slot { color: #10b981; font-size: 2.5rem; text-shadow: 0 0 20px rgba(16,185,129,0.5); }

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
    
    .shelf-row { 
        display: flex; justify-content: space-between; align-items: flex-end; 
        height: 55px; border-bottom: 8px solid #475569; margin-bottom: 15px; position: relative; gap: 6px;
    }
    
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
        animation: pulse-glow 2.5s infinite ease-in-out; 
    }
    /* Tambahan efek hover untuk kotak yang bisa diklik */
    .machine-box.active:hover {
        transform: scale(1.15) translateY(-5px);
        box-shadow: 0 0 25px rgba(52, 211, 153, 1);
        cursor: pointer;
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
        .panel-center { justify-content: flex-start; }
    }

    @keyframes pulse-glow {
        0% { box-shadow: 0 0 10px rgba(52, 211, 153, 0.4); transform: scale(1) translateY(0); }
        50% { box-shadow: 0 0 20px rgba(52, 211, 153, 0.8), 0 0 10px rgba(52, 211, 153, 0.5) inset; transform: scale(1.05) translateY(-2px); }
        100% { box-shadow: 0 0 10px rgba(52, 211, 153, 0.4); transform: scale(1) translateY(0); }
    }
</style>

<!-- Bungkus container dengan x-data untuk Alpine.js Modal -->
<div x-data="{ showModal: false, activeMiner: {} }">
    <div class="dc-container">
        
        <!-- KOLOM KIRI -->
        <div class="dc-panel panel-left">
            <div class="dc-label">Posisi Rak</div>
            <div class="neon-text text-rack">#{{ $displayRack }}</div>
            <div class="dc-label" style="margin-top: 1rem; color: #475569;">Kapasitas: {{ $kapasitas }} Unit</div>
        </div>

        <!-- KOLOM TENGAH (VISUAL RAK) -->
        <div class="panel-center">
            <div class="supermarket-rack">
                
                @for ($lvl = 1; $lvl <= 5; $lvl++)
                    <div class="shelf-row">
                        @for ($s = 1; $s <= 18; $s++) 
                            @php
                                $mesinAktif = $miners->first(function($m) use ($lvl, $s) {
                                    return $m->shelf_level == $lvl && $m->slot_number == $s;
                                });
                                $isActive = $mesinAktif ? true : false;
                            @endphp
                            
                            <!-- Bikin bisa diklik pakai @click -->
                           <div class="machine-box {{ $isActive ? 'active' : '' }}" 
                                title="{{ $isActive ? 'Klik untuk lihat detail ' . $mesinAktif->name : 'Kosong' }}"
                                @if($isActive)
                                    @click="
                                        activeMiner = { 
                                            name: '{{ $mesinAktif->name ?? '-' }}', 
                                            rak: '{{ $displayRack }}',
                                            tingkat: '{{ $lvl }}',
                                            urutan: '{{ $s }}',
                                            pemilik: '{{ $mesinAktif->owner_name->name ?? 'Tidak diketahui' }}', 
                                            ip: '{{ $mesinAktif->ip_address ?? 'Tidak ada' }}', 
                                            mac: '{{ $mesinAktif->mac_address ?? 'Tidak ada' }}'
                                        };
                                        $dispatch('open-modal', { id: 'modal-detail-mesin' });
                                    "
                                @endif
                            >
                                <div class="blade-led {{ $isActive ? 'active' : '' }}"></div>
                                <span class="blade-text">{{ $s }}</span>
                            </div>
                        @endfor
                    </div>
                @endfor
                
            </div>
        </div>

        <!-- KOLOM KANAN (Summary Rak) -->
        <div class="dc-panel panel-right">
            <div class="dc-label">Terisi</div>
            <div class="neon-text text-level" style="color: #34d399;">{{ $totalTerisi }} Unit</div>
            
            <div style="height: 1px; background: #334155; margin: 1rem 0;"></div>
            
            <div class="dc-label">Sisa Slot</div>
            <div class="neon-text text-slot" style="color: #f87171; text-shadow: 0 0 20px rgba(248,113,113,0.5);">{{ $kapasitas - $totalTerisi }}</div>
        </div>
    </div>

  <!-- MODAL NATIVE FILAMENT (UI NOC PREMIUM - ANTI GEPENG) -->
    <x-filament::modal id="modal-detail-mesin" width="4xl">
        
        <x-slot name="heading">
            Detail Mesin
        </x-slot>

        <!-- Pembungkus Utama (Grid 2 Kolom) -->
        <div class="flex flex-col w-full pt-2" style="gap: 12px;">
            

            <!-- KOLOM KANAN: URUTAN DATA (Anti Sesak) -->
            <div class="col-span-1 flex flex-col" style="gap: 12px;">
                
                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">Nomor Mesin</span>
                    <strong class="text-base tracking-wider" style="color: #ffffff;" x-text="activeMiner.name"></strong>
                </div>

                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">Rak Nomor</span>
                    <strong class="text-base font-bold uppercase" style="color: #38bdf8;" x-text="'#' + activeMiner.rak"></strong>
                </div>

                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">Tingkat</span>
                    <strong class="text-base font-bold" style="color: #a78bfa;" x-text="'Tingkat ' + activeMiner.tingkat"></strong>
                </div>

                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">Urutan / Slot</span>
                    <strong class="text-base font-bold" style="color: #34d399;" x-text="activeMiner.urutan"></strong>
                </div>

                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">Pemilik</span>
                    <strong class="text-base" style="color: #fbbc04;" x-text="activeMiner.pemilik"></strong>
                </div>

                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">IP Address</span>
                    <strong class="text-base font-mono" style="color: #e2e8f0;" x-text="activeMiner.ip"></strong>
                </div>

                <div class="flex justify-between items-center rounded-xl border" style="padding: 12px 20px; background-color: #1e293b; border-color: #334155;">
                    <span class="text-sm font-medium" style="color: #94a3b8;">MAC Address</span>
                    <strong class="text-sm font-mono uppercase tracking-widest" style="color: #94a3b8;" x-text="activeMiner.mac"></strong>
                </div>

            </div>
        </div>
        
        <!-- Tombol Tutup -->
        <x-slot name="footer">
            <div class="flex justify-end w-full">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'modal-detail-mesin' })">
                    Tutup
                </x-filament::button>
            </div>
        </x-slot>
        
    </x-filament::modal>
</div> <!-- Penutup x-data -->
