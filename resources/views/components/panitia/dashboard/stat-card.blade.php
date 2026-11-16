@props([
    'title',
    'value',
    'suffix' => '',
    'icon',
    'iconColor' => 'blue',
    'badgeText',
    'badgeColor' => 'slate',
    'linkText' => null,
    'linkUrl' => '#'
])

@php
    $iconBgColors = [
        'blue' => 'bg-blue-50 text-blue-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'green' => 'bg-green-50 text-green-600',
        'red' => 'bg-red-50 text-red-600',
        'amber' => 'bg-amber-50 text-amber-600',
    ];
    $iconClasses = $iconBgColors[$iconColor] ?? $iconBgColors['blue'];

    $badgeBgColors = [
        'slate' => 'bg-slate-100 text-slate-600',
        'green' => 'bg-green-100 text-green-700',
        'red' => 'bg-red-100 text-red-700',
        'blue' => 'bg-blue-100 text-blue-700',
    ];
    $badgeClasses = $badgeBgColors[$badgeColor] ?? $badgeBgColors['slate'];

    $linkColors = [
        'blue' => 'text-blue-600 hover:text-blue-700',
        'purple' => 'text-purple-600 hover:text-purple-700',
        'green' => 'text-green-600 hover:text-green-700',
        'red' => 'text-red-600 hover:text-red-700',
        'amber' => 'text-amber-600 hover:text-amber-700',
    ];
    $linkClasses = $linkColors[$iconColor] ?? $linkColors['blue'];
@endphp

<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col h-full">
    <div class="flex items-center justify-between mb-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $iconClasses }}">
            <i class="{{ $icon }}"></i>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $badgeClasses }}">{{ $badgeText }}</span>
    </div>
    <div>
        <h3 class="text-slate-500 text-sm font-medium mb-1">{{ $title }}</h3>
        <div class="flex items-end gap-2">
            <span class="text-3xl font-bold text-slate-800">{{ $value }}</span>
            @if($suffix)
                <span class="text-slate-400 text-sm font-medium mb-1">{{ $suffix }}</span>
            @endif
        </div>
    </div>
    
    @if($linkText)
    <div class="mt-auto pt-4 border-t border-slate-50">
        <a href="{{ $linkUrl }}" class="{{ $linkClasses }} text-xs font-semibold flex items-center gap-1 transition-colors">
            {{ $linkText }} <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>
    @endif
</div>
