<section class="hero-pattern min-h-[90vh] flex items-center pt-20 relative overflow-hidden">
    {{-- Decorative blobs --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-green-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 transform translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 transform -translate-x-1/2 translate-y-1/2"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full pb-16">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-center lg:text-left">
                <div class="hero-animate-1 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 text-white/90 text-sm font-medium mb-6 border border-white/20 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    PPDB Tahun Pelajaran {{ $aktivTahunAjaran->tahun_ajar ?? date('Y').'/'.(date('Y')+1) }} Dibuka
                </div>
                <h1 class="hero-animate-1 font-heading text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                    Selamat Datang di PPDB <span class="text-transparent bg-clip-text bg-linear-to-r from-amber-300 to-amber-500">SD Kristen Diakui Rantai Damai</span>
                </h1>
                <p class="hero-animate-2 text-lg text-green-50 mb-8 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Mendaftar kini lebih mudah! Lakukan pendaftaran, upload dokumen, dan pantau status penerimaan putra-putri Anda secara online kapan saja dan di mana saja.
                </p>
                <div class="hero-animate-3 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <button @click="registerModalOpen = true" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3.5 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        Daftar Sekarang <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <a href="#pendaftaran" class="bg-white/10 hover:bg-white/20 text-white border border-white/30 px-8 py-3.5 rounded-xl font-semibold text-lg backdrop-blur-sm transition-all text-center">
                        Pelajari Alur Pendaftaran
                    </a>
                </div>
            </div>
            
            <div class="hero-animate-scale hidden lg:block relative">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-white/20 transform rotate-2 hover:rotate-0 transition-transform duration-500"
                     x-data="{ 
                         images: [
                             '{{ asset('images/hero/slide1.jpeg') }}', 
                             '{{ asset('images/hero/slide2.jpeg') }}', 
                             '{{ asset('images/hero/slide3.jpeg') }}'
                         ], 
                         currentIndex: 0 
                     }" 
                     x-init="setInterval(() => { currentIndex = (currentIndex + 1) % images.length }, 5000)">
                    
                    <div style="padding-bottom: 75%;" class="bg-slate-200 relative w-full block">
                        <template x-for="(image, index) in images" :key="index">
                            <img :src="image" 
                                 alt="SD Kristen Diakui Rantai Damai" 
                                 class="absolute inset-0 object-cover w-full h-full transition-opacity duration-1000 ease-in-out"
                                 :class="currentIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'">
                        </template>
                    </div>
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-20 pointer-events-none"></div>
                    <div class="absolute bottom-6 left-6 right-6 z-30 pointer-events-none">
                        <p class="text-white font-medium text-lg drop-shadow-md">Pendidikan Karakter Berbasis Nilai Kristiani</p>
                    </div>
                </div>
                
                {{-- Floating stat card --}}
                <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl flex items-center gap-4 animate-bounce" style="animation-duration: 3s;">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800 leading-none">Akreditasi B</p>
                        <p class="text-sm text-slate-500 font-medium">Kualitas Terjamin</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Wave bottom --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" class="w-full h-auto text-slate-50 fill-current" preserveAspectRatio="none">
            <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
        </svg>
    </div>
</section>
