<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Panitia') — PPDB SD Kristen</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('template/plugins/fontawesome-free/css/all.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="h-full bg-slate-50 font-sans" x-data="{ sidebarOpen: false }" @keydown.escape="sidebarOpen = false">

    {{-- Overlay Mobile --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"
         @click="sidebarOpen = false"
         style="display:none;">
    </div>

    {{-- Sidebar Panitia --}}
    <aside class="sidebar-nav -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
           :class="sidebarOpen ? '!translate-x-0' : ''">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo SD Kristen Diakui Rantai Damai" class="w-full h-full object-contain drop-shadow-sm">
            </div>
            <div class="min-w-0">
                <p class="text-white font-bold text-sm leading-tight truncate">PPDB SD Kristen</p>
                <p class="text-green-300 text-xs">Portal Panitia</p>
            </div>
        </div>

        {{-- User Info --}}
        <div class="px-3 py-3 border-b border-white/10">
            <div class="flex items-center gap-2.5 bg-white/5 rounded-lg px-3 py-2.5">
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center shrink-0">
                    <i class="fas fa-user text-white text-xs"></i>
                </div>
                <div class="min-w-0">
                    @php $currentUser = session('user') ?? auth()->user(); @endphp
                    <p class="text-white text-sm font-medium truncate">{{ $currentUser->nama ?? $currentUser->name ?? 'Panitia' }}</p>
                    <p class="text-green-300 text-xs">Panitia PPDB</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-2 py-3 space-y-1 overflow-y-auto flex flex-col justify-between">
            <div class="space-y-1">
                {{-- Dashboard --}}
                <div>
                    <a href="/beranda/panitia"
                       class="sidebar-nav-item min-h-[44px] {{ Request::is('beranda/panitia') ? 'active' : '' }}"
                       title="Dashboard"
                       aria-label="Dashboard">
                        <span class="w-5 flex items-center justify-center shrink-0 text-center">
                            <i class="fas fa-home text-base"></i>
                        </span>
                        <span>Dashboard</span>
                    </a>
                </div>

                {{-- Grup SELEKSI --}}
                <div>
                    <div class="sidebar-group-title select-none">SELEKSI</div>
                    <div class="space-y-0.5">
                        <a href="/panitia/evaluasi-seleksi"
                           class="sidebar-nav-item min-h-[44px] {{ Request::is('panitia/evaluasi-seleksi') || Request::is('panitia/input_nilai/*') ? 'active' : '' }}"
                           title="Penilaian Seleksi"
                           aria-label="Penilaian Seleksi">
                            <span class="w-5 flex items-center justify-center shrink-0 text-center">
                                <i class="fas fa-clipboard-list text-base"></i>
                            </span>
                            <span>Penilaian Seleksi</span>
                        </a>

                        <a href="{{ route('panitia.pengumuman.seleksi') }}"
                           class="sidebar-nav-item min-h-[44px] {{ Request::routeIs('panitia.pengumuman.seleksi') ? 'active' : '' }}"
                           title="Publikasi Hasil"
                           aria-label="Publikasi Hasil">
                            <span class="w-5 flex items-center justify-center shrink-0 text-center">
                                <i class="fas fa-bullhorn text-base"></i>
                            </span>
                            <span>Publikasi Hasil</span>
                        </a>
                    </div>
                </div>

                {{-- Grup LAPORAN --}}
                <div>
                    <div class="sidebar-group-title select-none">LAPORAN</div>
                    <div class="space-y-0.5">
                        <a href="/laporan/hasil casis"
                           class="sidebar-nav-item min-h-[44px] {{ Request::is('laporan/hasil casis') ? 'active' : '' }}"
                           title="Rekap Hasil Seleksi"
                           aria-label="Rekap Hasil Seleksi">
                            <span class="w-5 flex items-center justify-center shrink-0 text-center">
                                <i class="fas fa-chart-pie text-base"></i>
                            </span>
                            <span>Rekap Hasil Seleksi</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Grup AKUN (di bawah dengan jarak aman) --}}
            <div class="pt-4 border-t border-white/10 mt-auto space-y-0.5">
                <div class="sidebar-group-title select-none">AKUN</div>

                <a href="/beranda/profil panitia"
                   class="sidebar-nav-item min-h-[44px] {{ Request::is('beranda/profil panitia') ? 'active' : '' }}"
                   title="Profil Saya"
                   aria-label="Profil Saya">
                    <span class="w-5 flex items-center justify-center shrink-0 text-center">
                        <i class="fas fa-user-circle text-base"></i>
                    </span>
                    <span>Profil Saya</span>
                </a>

                <a href="{{ route('logout') }}"
                   class="sidebar-nav-item min-h-[44px] text-red-300 hover:text-white hover:bg-red-500/20"
                   title="Keluar"
                   aria-label="Keluar">
                    <span class="w-5 flex items-center justify-center shrink-0 text-center">
                        <i class="fas fa-sign-out-alt text-base"></i>
                    </span>
                    <span>Keluar</span>
                </a>
            </div>
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Top Navbar --}}
        <header class="top-navbar">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <nav class="hidden sm:flex breadcrumb items-center gap-2 text-sm text-slate-500">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <span>Portal Panitia</span>
                        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                        <span class="breadcrumb-item active">@yield('page-title', 'Dashboard')</span>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <span class="hidden sm:flex items-center gap-1.5 text-sm text-slate-600">
                    <i class="fas fa-calendar-alt text-green-600 text-xs"></i>
                    TA {{ $aktivTahunAjaran->tahun_ajar ?? '-' }}
                </span>
                <div class="w-px h-5 bg-slate-200 hidden sm:block"></div>
                <a href="/logout" class="flex items-center gap-1.5 text-sm text-slate-600 hover:text-red-600 transition-colors px-2 py-1 rounded-lg hover:bg-red-50">
                    <i class="fas fa-sign-out-alt text-xs"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-5 lg:p-6">
            @include('layout.alert')
            @yield('content')
        </main>

        <footer class="main-footer">
            <span><strong>PPDB SD Kristen Diakui Rantai Damai</strong> &mdash; Portal Panitia PPDB</span>
        </footer>
    </div>

    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('template/plugins/jquery/jquery.min.js') }}"></script>
    @stack('scripts')
</body>
</html>