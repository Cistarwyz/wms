<x-filament-widgets::widget class="col-span-full">
    <x-filament::section wire:poll="10s">
    @php
        $offlineMiners = $this->getOfflineMiners();
        $offlineIsps = $this->getOfflineIsps();
        $hasCritical = $offlineMiners->isNotEmpty() || $offlineIsps->isNotEmpty() || $this->getMikrotikStatus() !== 'online';
    @endphp

    @if($hasCritical)
        <div class="custom-neon-red">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <x-heroicon-o-exclamation-triangle style="width: 40px; height: 40px; color: #ef4444;" />
                <div>
                    <h2 class="incident-header-text">CRITICAL INCIDENT!</h2>
                    <p style="color: #f87171; margin: 0; font-size: 0.875rem; font-weight: bold;">Segera Lakukan Pengecekan Jaringan</p>
                </div>
            </div>
            
            <div style="margin-top: 1rem;">
                @foreach($offlineMiners as $miner)
                    <div class="incident-row">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; margin-right: 12px; display: inline-block;"></span>
                        MESIN OFFLINE: {{ $miner->name ?? 'SPX' }}
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="custom-neon-safe" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <x-heroicon-o-check-circle style="width: 40px; height: 40px; color: #22c55e;" />
                <div>
                    <h2 class="safe-header-text">SYSTEM SECURE</h2>
                    <p style="color: #4ade80; margin: 0; font-size: 0.875rem; font-weight: bold;">Semua Mesin dan ISP Berjalan Normal</p>
                </div>
            </div>
            <span style="color: #22c55e; font-weight: bold; padding: 0.5rem 1rem; background: rgba(34,197,94,0.1); border-radius: 20px;">Status: Online</span>
        </div>
    @endif
    </x-filament::section>
</x-filament-widgets::widget>