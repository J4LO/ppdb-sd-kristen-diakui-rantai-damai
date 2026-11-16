@if (session('success'))
<div x-data="{ show: true }"
    x-show="show"
    class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative flex items-center justify-between"
    role="alert">

    <div class="flex items-center gap-2">
        <i class="fas fa-check-circle"></i>

        <span class="block sm:inline font-medium">
            {{ session('success') }}
        </span>
    </div>

    <button @click="show = false"
        class="text-green-500 hover:text-green-700">

        <i class="fas fa-times"></i>
    </button>
</div>

@elseif (session('error'))
<div x-data="{ show: true }"
    x-show="show"
    class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative flex items-center justify-between"
    role="alert">

    <div class="flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i>

        <span class="block sm:inline font-medium">
            {{ session('error') }}
        </span>
    </div>

    <button @click="show = false"
        class="text-red-500 hover:text-red-700">

        <i class="fas fa-times"></i>
    </button>
</div>
@endif