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
    }
    
    .panel-left { grid-area: left; }
    .panel-right { grid-area: right; }
    
    .panel-center { 
        grid-area: center; 
        display: flex; 
        justify-content: center;
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
                                        showModal = true; 
                                        activeMiner = { 
                                            name: '{{ $mesinAktif->name }}', 
                                            ip: '{{ $mesinAktif->ip_address ?? 'Tidak ada' }}', 
                                            mac: '{{ $mesinAktif->mac_address ?? 'Tidak ada' }}',
                                            pos: 'Level {{ $lvl }} - Urutan {{ $s }}'
                                        }
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

    <!-- MODAL POPUP (Teleport agar posisi popup selalu ada di atas layar) -->
    <template x-teleport="body">
        <div x-show="showModal" 
             style="display: none; z-index: 99999; background-color: rgba(0, 0, 0, 0.85);" 
             class="fixed inset-0 flex items-center justify-center p-4 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">
             
            <!-- Kotak Modal -->
            <div @click.away="showModal = false" 
                 style="background-color: #0f172a; box-shadow: 0 0 40px rgba(52,211,153,0.15); border-color: #334155; z-index: 100000;"
                 class="border rounded-2xl w-full max-w-md p-6 relative">
                
                <!-- Tombol Close (X) -->
                <button @click="showModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Header -->
                <div class="flex items-center gap-3 mb-6 border-b border-slate-700 pb-4">
                    <div class="p-2 rounded-lg" style="background-color: rgba(16, 185, 129, 0.2);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-emerald-400">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white leading-tight">Detail Mesin</h3>
                        <p class="text-xs text-emerald-400 font-medium" x-text="activeMiner.pos"></p>
                    </div>
                </div>
                
                <!-- Isi Data -->
                <div class="space-y-4">
                    <div class="p-3 rounded-lg border flex justify-between items-center" style="background-color: rgba(30, 41, 59, 0.5); border-color: rgba(51, 65, 85, 0.5);">
                        <span class="text-slate-400 text-sm font-medium">Nama / SN</span>
                        <strong class="text-white text-base tracking-wide" x-text="activeMiner.name"></strong>
                    </div>
                    
                    <div class="p-3 rounded-lg border flex justify-between items-center" style="background-color: rgba(30, 41, 59, 0.5); border-color: rgba(51, 65, 85, 0.5);">
                        <span class="text-slate-400 text-sm font-medium">IP Address</span>
                        <strong class="text-sky-400 font-mono text-base" x-text="activeMiner.ip"></strong>
                    </div>
                    
                    <div class="p-3 rounded-lg border flex justify-between items-center" style="background-color: rgba(30, 41, 59, 0.5); border-color: rgba(51, 65, 85, 0.5);">
                        <span class="text-slate-400 text-sm font-medium">MAC Address</span>
                        <strong class="text-amber-400 font-mono text-sm uppercase tracking-wider" x-text="activeMiner.mac"></strong>
                    </div>
                </div>
                
            </div>
        </div>
    </template>
</div>
