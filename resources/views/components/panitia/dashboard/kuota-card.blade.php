@props([
    'terisi',
    'total',
    'tersisa'
])

@php
    $percentage = $total > 0 ? min(100, round(($terisi / $total) * 100)) : 0;
@endphp

<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col h-full">
    <div class="flex items-center justify-between mb-4">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
            <i class="fas fa-chart-pie"></i>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">Kuota</span>
    </div>
    <div>
        <h3 class="text-slate-500 text-sm font-medium mb-1">Keterisian Kuota</h3>
        <div class="flex items-end gap-2">
            <span class="text-3xl font-bold text-slate-800">{{ $terisi }}</span>
            <span class="text-slate-400 text-lg font-medium mb-1">/ {{ $total }}</span>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-slate-50 flex justify-between text-xs">
        <span class="text-slate-500">Tersisa: <strong class="text-slate-700">{{ $tersisa }}</strong></span>
        <span class="{{ $terisi >= $total ? 'text-red-500 font-semibold' : 'text-green-500' }}">
            {{ $percentage }}% Terisi
        </span>
    </div>
    {{-- Progress bar --}}
    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
    </div>
</div>
