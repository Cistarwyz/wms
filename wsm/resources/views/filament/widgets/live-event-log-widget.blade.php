<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-command-line class="w-5 h-5 text-gray-400" />
                <span>Live Event Log</span>
            </div>
        </x-slot>

        <!-- Kotak hitam ala Terminal -->
        <div class="p-4 bg-gray-900 dark:bg-black rounded-lg font-mono text-xs sm:text-sm h-[300px] overflow-y-auto border border-gray-800">
            @forelse($this->getLogs() as $log)
                <div class="mb-2 text-gray-300 border-b border-gray-800 pb-1">
                    {!! $log !!}
                </div>
            @empty
                <div class="text-gray-500 italic flex items-center h-full justify-center">
                    Menunggu aktivitas jaringan...
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>