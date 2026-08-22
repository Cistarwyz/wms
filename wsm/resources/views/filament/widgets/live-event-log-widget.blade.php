<x-filament-widgets::widget>
    <div class="w-full rounded-xl bg-[#0b0f19] border border-fuchsia-600 shadow-[0_0_15px_rgba(192,38,211,0.2)] p-6 mb-4">
        
        <!-- Header & Tombol Paginasi -->
        <div class="flex items-center justify-between mb-4 border-b border-slate-700 pb-3">
            <h3 class="text-xl font-bold text-slate-200 tracking-wider">
                CATATAN MESIN DOWN <span class="text-xs text-red-400 ml-2 animate-pulse">(LIVE)</span>
            </h3>
            
            <!-- Tombol Navigasi Next/Prev via Livewire -->
            <div class="flex gap-2">
                <button wire:click="prevPage" @if($this->page <= 1) disabled @endif class="px-3 py-1 bg-slate-800 text-white rounded hover:bg-slate-700 disabled:opacity-50 transition">
                    &laquo; Prev
                </button>
                <span class="px-3 py-1 text-slate-400 font-bold">Hal {{ $this->page }}</span>
                <button wire:click="nextPage" @if(!$hasMore) disabled @endif class="px-3 py-1 bg-slate-800 text-white rounded hover:bg-slate-700 disabled:opacity-50 transition">
                    Next &raquo;
                </button>
            </div>
        </div>

        <!-- Tabel Data -->
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="text-slate-400 border-b border-slate-700">
                    <th class="pb-3">Priority</th>
                    <th class="pb-3">Device</th>
                    <th class="pb-3">Location</th>
                    <th class="pb-3">Description</th>
                    <th class="pb-3 text-right">Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $miner)
                    <tr class="border-b border-fuchsia-900/30 hover:bg-fuchsia-900/10 transition">
                        <td class="py-3 text-red-500 font-semibold tracking-wide">Critical</td>
                        <td class="py-3 text-slate-300">{{ $miner->name ?? 'Unknown' }}</td>
                        <td class="py-3 text-slate-400">Rak: {{ $miner->rak ?? '-' }}</td>
                        <td class="py-3 text-slate-400">Mesin Tidak Merespon (Offline)</td>
                        <td class="py-3 text-slate-400 text-right">{{ $miner->updated_at ? $miner->updated_at->format('H:i:s') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 italic">
                            Semua sistem berjalan normal. Tidak ada mesin down di halaman ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-widgets::widget>