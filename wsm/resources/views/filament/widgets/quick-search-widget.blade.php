<x-filament-widgets::widget>
    <!-- Container untuk membatasi ukuran agar di tengah dan proporsional -->
    <div class="w-full max-w-4xl mx-auto pt-2 pb-4 custom-neon-search">
        {{ $this->form }}
    </div>

    <!-- Modals untuk Pop-Up -->
    <x-filament-actions::modals />

    <!-- Injeksi CSS Khusus untuk widget ini saja -->
    <style>
        /* 1. Mengubah background dan bentuk kotak utama Filament */
        .custom-neon-search .fi-input-wrp {
            background-color: #151e32 !important; /* Warna dasar sama dengan card statistik */
            border: 1px solid #3b82f6 !important; /* Garis biru */
            border-radius: 12px !important; /* Ujung melengkung */
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.15) !important; /* Efek glow halus */
            padding: 4px 8px !important;
            transition: all 0.3s ease-in-out !important;
        }

        /* 2. Efek menyala saat sedang diketik (Focus) */
        .custom-neon-search .fi-input-wrp:focus-within {
            border-color: #06b6d4 !important; /* Berubah jadi Cyan */
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4) !important; /* Glow makin terang */
        }

        /* 3. Mewarnai ikon agar serasi */
        .custom-neon-search .fi-input-wrp svg {
            color: #3b82f6 !important;
            width: 22px !important;
            height: 22px !important;
        }

        /* 4. Menyesuaikan teks input */
        .custom-neon-search .fi-input-wrp input, 
        .custom-neon-search .fi-input-wrp .fi-select-input {
            color: white !important;
            font-size: 15px !important;
            background: transparent !important;
        }
    </style>
</x-filament-widgets::widget>