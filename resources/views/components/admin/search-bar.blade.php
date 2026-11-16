@props(['action', 'placeholder' => 'Pencarian...'])

<form action="{{ $action }}" method="get" class="w-full sm:w-auto">
    <div class="relative group">
        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
            <i class="fas fa-search"></i>
        </div>
        <input type="text"
            name="search"
            class="w-full sm:w-80 rounded-xl border border-slate-200 bg-white pl-11 pr-4 py-2 text-sm focus:bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all outline-none text-slate-700 shadow-sm"
            placeholder="{{ $placeholder }}"
            value="{{ request('search') }}">
    </div>
</form>
