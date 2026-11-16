<nav :class="{ 'glass-nav py-3 shadow-sm': scrolled, 'bg-transparent py-5': !scrolled }" class="fixed w-full z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo SD Kristen" class="w-full h-full object-contain drop-shadow-sm">
                </div>
                <div>
                    <h1 :class="scrolled ? 'text-green-800' : 'text-white'" class="font-heading font-bold text-lg leading-tight transition-colors">SD Kristen</h1>
                    <p :class="scrolled ? 'text-green-600' : 'text-green-200'" class="text-xs font-medium transition-colors">Diakui Rantai Damai</p>
                </div>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center space-x-8">
                <a href="#profil" :class="scrolled ? 'text-slate-600 hover:text-green-700' : 'text-white/90 hover:text-white'" class="font-medium text-sm transition-colors">Profil</a>
                <a href="#informasi" :class="scrolled ? 'text-slate-600 hover:text-green-700' : 'text-white/90 hover:text-white'" class="font-medium text-sm transition-colors">Informasi</a>
                <a href="#pendaftaran" :class="scrolled ? 'text-slate-600 hover:text-green-700' : 'text-white/90 hover:text-white'" class="font-medium text-sm transition-colors">Alur Pendaftaran</a>
                <a href="#contact" :class="scrolled ? 'text-slate-600 hover:text-green-700' : 'text-white/90 hover:text-white'" class="font-medium text-sm transition-colors">Kontak</a>
            </div>

            {{-- Auth Buttons (Desktop) --}}
            <div class="hidden md:flex items-center space-x-4">
                <button @click="loginModalOpen = true" :class="scrolled ? 'text-green-700 hover:bg-green-50' : 'text-white hover:bg-white/10'" class="px-4 py-2 font-medium text-sm rounded-lg transition-colors">Masuk</button>
                <button @click="registerModalOpen = true" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg font-medium text-sm shadow-sm hover:shadow-md transition-all">Daftar Sekarang</button>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" :class="scrolled ? 'text-slate-800' : 'text-white'" class="focus:outline-none p-2">
                    <i class="fas fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                    <i class="fas fa-times text-2xl" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="md:hidden absolute top-full left-0 w-full bg-white shadow-lg border-t border-slate-100" style="display: none;">
        <div class="px-4 pt-2 pb-6 space-y-1">
            <a href="#profil" @click="mobileMenuOpen = false" class="block px-3 py-3 text-base font-medium text-slate-700 hover:text-green-700 hover:bg-green-50 rounded-md">Profil</a>
            <a href="#informasi" @click="mobileMenuOpen = false" class="block px-3 py-3 text-base font-medium text-slate-700 hover:text-green-700 hover:bg-green-50 rounded-md">Informasi</a>
            <a href="#pendaftaran" @click="mobileMenuOpen = false" class="block px-3 py-3 text-base font-medium text-slate-700 hover:text-green-700 hover:bg-green-50 rounded-md">Alur Pendaftaran</a>
            <a href="#contact" @click="mobileMenuOpen = false" class="block px-3 py-3 text-base font-medium text-slate-700 hover:text-green-700 hover:bg-green-50 rounded-md">Kontak</a>
            
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col gap-3 px-3">
                <button @click="mobileMenuOpen = false; loginModalOpen = true" class="w-full border border-green-600 text-green-700 px-4 py-2.5 rounded-lg font-medium text-center">Masuk</button>
                <button @click="mobileMenuOpen = false; registerModalOpen = true" class="w-full bg-green-600 text-white px-4 py-2.5 rounded-lg font-medium text-center shadow-sm">Daftar Sekarang</button>
            </div>
        </div>
    </div>
</nav>
