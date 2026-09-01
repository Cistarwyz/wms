@php
    // Ambil data rak saat ini
    $record = $getRecord();
    // Hitung total mesin yang ada di rak ini
    $totalMesin = $record->miners()->count();
@endphp

<div class="bg-slate-800 border-2 border-slate-700 hover:border-sky-500 rounded-2xl p-6 text-center cursor-pointer transition-all duration-300 shadow-lg hover:shadow-sky-500/20 flex flex-col h-full justify-center min-h-[200px]">
    
    <x-heroicon-o-server-stack class="w-16 h-16 mx-auto text-slate-400 mb-4" />
    
    <h3 class="text-2xl font-black text-white tracking-widest uppercase">{{ $record->name }}</h3>
    
    <div class="mt-4 inline-flex items-center justify-center gap-2 bg-slate-900 rounded-full py-1 px-4 border border-slate-700">
        <div class="w-2 h-2 rounded-full {{ $totalMesin > 0 ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></div>
        <span class="text-sm font-bold {{ $totalMesin > 0 ? 'text-emerald-400' : 'text-slate-500' }}">
            {{ $totalMesin }} / 90 Unit
        </span>
    </div>
</div>