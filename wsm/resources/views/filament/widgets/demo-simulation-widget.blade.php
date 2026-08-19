<div>
    <style>
        .noc-overlay { position: fixed; inset: 0; z-index: 100000; background: rgba(15, 23, 42, 0.90); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-y: auto; padding: 20px;}
        .noc-card { background: #0f172a; border: 2px solid rgba(239, 68, 68, 0.6); border-radius: 1.25rem; box-shadow: 0 0 60px rgba(220, 38, 38, 0.2); width: 100%; max-width: 1100px; padding: 2.5rem; color: white; display: flex; flex-direction: column; gap: 2rem; margin: auto;}
        
        /* ==========================================
           UI RAK MESIN (DIAMBIL DARI DESAIN ANDA)
           ========================================== */
        .dc-container { display: grid; grid-template-areas: "left center right"; grid-template-columns: 200px auto 200px; gap: 1.5rem; background: #020617; padding: 2rem; border-radius: 1rem; border: 1px solid #1e293b; }
        .panel-left { grid-area: left; } .panel-right { grid-area: right; }
        .panel-center { grid-area: center; display: flex; justify-content: center; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 1rem; }
        .panel-center::-webkit-scrollbar { height: 8px; }
        .panel-center::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        
        .dc-panel { background: #1e293b; border: 1px solid #334155; border-radius: 0.75rem; padding: 1.5rem; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: flex; flex-direction: column; justify-content: center; }
        .dc-label { color: #94a3b8; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.5rem; }
        .neon-text { font-weight: 900; text-transform: uppercase; }
        .text-rack { color: #38bdf8; font-size: 2.5rem; text-shadow: 0 0 15px rgba(56,189,248,0.4); }
        .text-level { color: #a78bfa; font-size: 1.5rem; text-shadow: 0 0 15px rgba(167,139,250,0.4); }
        .text-slot { color: #ef4444; font-size: 2.5rem; text-shadow: 0 0 20px rgba(239, 68, 68, 0.5); } /* Alert: Merah */

        .supermarket-rack { background: #020617; border-left: 10px solid #334155; border-right: 10px solid #334155; border-top: 6px solid #334155; border-radius: 6px 6px 0 0; display: flex; flex-direction: column; padding: 10px 15px 0 15px; box-shadow: inset 0 0 30px rgba(0,0,0,1); min-width: 600px; margin-bottom: 5px; }
        .shelf-row { display: flex; justify-content: space-between; align-items: flex-end; height: 55px; border-bottom: 8px solid #475569; margin-bottom: 15px; position: relative; gap: 6px; }
        .machine-box { position: relative; z-index: 10; width: 100%; max-width: 30px; height: 45px; background: linear-gradient(180deg, #1e293b, #0f172a); border: 1px solid #334155; border-radius: 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.3s ease; }
        
        /* STATE ALERT / OFFLINE (MERAH BERKEDIP) */
        .machine-box.active { background: linear-gradient(180deg, #7f1d1d, #450a0a); border-color: #ef4444; transform: scale(1.15) translateY(-4px); box-shadow: 0 0 15px rgba(239, 68, 68, 0.8); z-index: 20; animation: pulse-red 1.5s infinite; }
        .blade-led { width: 6px; height: 6px; border-radius: 50%; background: #475569; margin-bottom: 4px;}
        .blade-led.active { background: #ef4444; box-shadow: 0 0 8px #ef4444; }
        .blade-text { color: #64748b; font-size: 0.55rem; font-weight: bold; font-family: monospace; letter-spacing: -0.5px;}
        .machine-box.active .blade-text { color: #ffffff; }

        @keyframes pulse-red { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; transform: scale(1.10) translateY(-4px); box-shadow: 0 0 25px rgba(239, 68, 68, 1); } }

        /* RESPONSIVE ANTI-TERPOTONG */
        @media (max-width: 1024px) {
            .dc-container { grid-template-areas: "left right" "center center"; grid-template-columns: 1fr 1fr; }
            .panel-center { justify-content: flex-start; }
            .noc-card { padding: 1.5rem; }
        }
    </style>

    <!-- State Alpine JS -->
    <div x-data="{ 
            open: false, alertAktif: false, tipe: '', judul: '', pesan: '',
            namaMesin: '', macMesin: '', rak: 0, tingkat: 0, urutan: 0, slotGlobal: 0
         }" 
         @tampilkan-alert.window="
            let data = $event.detail[0] || $event.detail;
            alertAktif = true; open = false;
            tipe = data.tipe; judul = data.judul; pesan = data.pesan || '';
            namaMesin = data.namaMesin || ''; macMesin = data.macMesin || '';
            rak = data.rak || 0; tingkat = data.tingkat || 0; urutan = data.urutan || 0;
            slotGlobal = data.slotGlobal || 0;
            
            let audio = document.getElementById('sirineNoc');
            audio.currentTime = 0; audio.play().catch(e => console.log(e));
         "
         style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column-reverse; align-items: flex-end; gap: 12px;">
        
        <!-- BUBBLE TOMBOL -->
        <button @click="open = !open" style="background-color: #dc2626; color: white; border-radius: 50%; padding: 16px; cursor: pointer; border: 4px solid rgba(239, 68, 68, 0.3);">
            <x-heroicon-o-bug-ant style="width: 28px; height: 28px;" />
        </button>

        <!-- MENU -->
        <div x-show="open" style="display: none; display: flex; flex-direction: column; gap: 10px;">
            <button wire:click="simulasikanMesinMati" style="background: #111827; color: white; padding: 12px 20px; border-radius: 99px; cursor: pointer; border: 1px solid #374151; display: flex; gap: 10px;">
                <span>Matikan Mesin Acak</span> <x-heroicon-m-cpu-chip style="width: 20px; color: #ef4444;" />
            </button>
            <button wire:click="simulasikanIspMati" style="background: #111827; color: white; padding: 12px 20px; border-radius: 99px; cursor: pointer; border: 1px solid #374151; display: flex; gap: 10px;">
                <span>Matikan ISP 1</span> <x-heroicon-m-wifi style="width: 20px; color: #eab308;" />
            </button>
        </div>

        <!-- ========================================== -->
        <!-- POP-UP UI MODERN (NOC INVESTIGATION PANEL) -->
        <!-- ========================================== -->
        <div x-show="alertAktif" class="noc-overlay" style="display: none;">
            <div class="noc-card">
                
                <!-- HEADER Peringatan -->
                <div style="display: flex; align-items: center; gap: 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 1.5rem;">
                    <x-heroicon-o-exclamation-triangle style="width: 60px; height: 60px; color: #ef4444; animation: pulse-red 1.5s infinite;" />
                    <div>
                        <h1 style="font-size: 2rem; font-weight: 900; color: #ef4444; margin: 0;" x-text="judul"></h1>
                        <p style="color: #94a3b8; font-size: 1.1rem; margin-top: 0.25rem;" x-show="tipe === 'mesin'">
                            Koneksi terputus ke Mesin <strong style="color: white;" x-text="namaMesin"></strong> 
                            (MAC: <span x-text="macMesin"></span>)
                        </p>
                        <p style="color: #facc15; font-size: 1.1rem; margin-top: 0.25rem;" x-show="tipe === 'isp'" x-text="pesan"></p>
                    </div>
                </div>

                <!-- TAMPILAN KHUSUS JIKA MESIN MATI -->
                <template x-if="tipe === 'mesin'">
                    <div class="dc-container">
                        <!-- KOLOM KIRI -->
                        <div class="dc-panel panel-left">
                            <div class="dc-label">Posisi Rak</div>
                            <div class="neon-text text-rack">#<span x-text="rak"></span></div>
                            <div class="dc-label" style="margin-top: 1rem; color: #475569;">Kapasitas: 64 Unit/Rak</div>
                        </div>

                        <!-- KOLOM TENGAH (VISUAL RAK DARI ALPINE JS) -->
                        <div class="panel-center">
                            <div class="supermarket-rack">
                                <template x-for="lvl in 4" :key="lvl">
                                    <div class="shelf-row">
                                        <template x-for="s in 16" :key="s">
                                            <!-- Rumus Posisi Global untuk setiap box -->
                                            <div class="machine-box" 
                                                 :class="((rak - 1) * 64 + (lvl - 1) * 16 + s) === slotGlobal ? 'active' : ''"
                                                 :title="'Mesin #' + ((rak - 1) * 64 + (lvl - 1) * 16 + s)">
                                                
                                                <div class="blade-led" :class="((rak - 1) * 64 + (lvl - 1) * 16 + s) === slotGlobal ? 'active' : ''"></div>
                                                
                                                <span class="blade-text" x-text="((rak - 1) * 64 + (lvl - 1) * 16 + s)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="dc-panel panel-right">
                            <div class="dc-label">Tingkat</div>
                            <div class="neon-text text-level">Lv. <span x-text="tingkat"></span></div>
                            
                            <div style="height: 1px; background: #334155; margin: 1rem 0;"></div>
                            
                            <div class="dc-label">Posisi Urut Mati</div>
                            <div class="neon-text text-slot" x-text="urutan"></div>
                        </div>
                    </div>
                </template>

                <!-- TOMBOL TUTUP -->
                <button style="background: #ef4444; color: white; font-weight: bold; padding: 1rem 3rem; border-radius: 99px; border: none; cursor: pointer; font-size: 1rem; text-transform: uppercase; align-self: center; margin-top: 1rem;" 
                        @click="alertAktif = false; document.getElementById('sirineNoc').pause();">
                    Dalam Pemeriksaan
                </button>
            </div>
        </div>

        <audio id="sirineNoc" src="https://assets.mixkit.co/active_storage/sfx/940/940-preview.mp3" preload="auto" loop></audio>
    </div>
</div>