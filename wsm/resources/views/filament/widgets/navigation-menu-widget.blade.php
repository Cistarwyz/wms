<x-filament-widgets::widget>
    <div class="custom-neon-menu" style="height: 100%;">
        <h3>SYSTEM NAVIGATION</h3>
        
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: space-between;">
            
            <a href="/monitor" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-cyan">
                <x-heroicon-o-squares-2x2 />
                Dashboard
            </a>

            <a href="/monitor/isps" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-blue">
                <x-heroicon-o-globe-alt />
                ISPs
            </a>

            <a href="/monitor/miners" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-green">
                <x-heroicon-o-server-stack />
                Mesin
            </a>

            <a href="/monitor/users" style="width: calc(50% - 0.375rem);" class="nav-btn nav-btn-purple">
                <x-heroicon-o-users />
                Data Miner
            </a>

            <a href="/monitor/workers" style="width: 100%;" class="nav-btn nav-btn-orange">
                <x-heroicon-o-cog-8-tooth />
                Pengaturan Worker
            </a>
        </div>
    </div>
</x-filament-widgets::widget>