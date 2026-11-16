@props(['title', 'description', 'icon' => 'fas fa-folder'])

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-blue-600 text-xl shrink-0">
            <i class="{{ $icon }}"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                {{ $title }}
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                {{ $description }}
            </p>
        </div>
    </div>
    
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>
