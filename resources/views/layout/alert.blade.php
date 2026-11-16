{{-- Alert Component — Tailwind CSS v4 --}}

@if(session('success'))
<div class="alert alert-success mb-4" role="alert" id="alert-success" x-data="{ show: true }" x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <i class="fas fa-check-circle text-green-600 mt-0.5 shrink-0"></i>
    <div class="flex-1">
        <p class="font-medium">Berhasil!</p>
        <p class="text-sm opacity-90">{{ session('success') }}</p>
    </div>
    <button @click="show = false" class="ml-auto shrink-0 text-green-600 hover:text-green-800 transition-colors">
        <i class="fas fa-times text-sm"></i>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-error mb-4" role="alert" x-data="{ show: true }" x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <i class="fas fa-exclamation-circle text-red-600 mt-0.5 shrink-0"></i>
    <div class="flex-1">
        <p class="font-medium">Terjadi Kesalahan!</p>
        <p class="text-sm opacity-90">{{ session('error') }}</p>
    </div>
    <button @click="show = false" class="ml-auto shrink-0 text-red-600 hover:text-red-800 transition-colors">
        <i class="fas fa-times text-sm"></i>
    </button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning mb-4" role="alert" x-data="{ show: true }" x-show="show">
    <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 shrink-0"></i>
    <div class="flex-1">
        <p class="font-medium">Perhatian!</p>
        <p class="text-sm opacity-90">{{ session('warning') }}</p>
    </div>
    <button @click="show = false" class="ml-auto shrink-0 text-amber-600 hover:text-amber-800 transition-colors">
        <i class="fas fa-times text-sm"></i>
    </button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info mb-4" role="alert" x-data="{ show: true }" x-show="show">
    <i class="fas fa-info-circle text-blue-600 mt-0.5 shrink-0"></i>
    <div class="flex-1">
        <p class="font-medium">Informasi</p>
        <p class="text-sm opacity-90">{{ session('info') }}</p>
    </div>
    <button @click="show = false" class="ml-auto shrink-0 text-blue-600 hover:text-blue-800 transition-colors">
        <i class="fas fa-times text-sm"></i>
    </button>
</div>
@endif

@if($errors->any())
<div class="alert alert-error mb-4" role="alert" x-data="{ show: true }" x-show="show">
    <i class="fas fa-exclamation-circle text-red-600 mt-0.5 shrink-0"></i>
    <div class="flex-1">
        <p class="font-medium">Periksa kembali data Anda:</p>
        <ul class="list-disc list-inside text-sm mt-1 space-y-0.5 opacity-90">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <button @click="show = false" class="ml-auto shrink-0 text-red-600 hover:text-red-800 transition-colors">
        <i class="fas fa-times text-sm"></i>
    </button>
</div>
@endif

{{-- Auto-dismiss after 5 seconds --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.querySelectorAll('[id^="alert-"]').forEach(function (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 500);
            });
        }, 5000);
    });
</script>