<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Calon Siswa') — PPDB SD Kristen</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('template/plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        /* Standar Wadah Bersama Portal Calon Siswa (Sesuai Halaman Pembayaran) */
        .casis-main-area {
            padding-top: 1.5rem;
            padding-bottom: 2.5rem;
            padding-left: clamp(16px, 2.5vw, 24px);
            padding-right: clamp(16px, 2.5vw, 24px);
            width: 100%;
            box-sizing: border-box;
        }

        .page-container {
            width: 100%;
            max-width: 1152px; /* 72rem = max-w-6xl */
            margin-left: auto;
            margin-right: auto;
            box-sizing: border-box;
        }

        @media (max-width: 899.98px) {
            .casis-main-area {
                padding-left: 16px !important;
                padding-right: 16px !important;
                padding-top: 1rem;
                padding-bottom: 2rem;
            }
        }
    </style>

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

    {{-- Sidebar Calon Siswa --}}
    <aside class="sidebar-nav"
           :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full lg:translate-x-0': !sidebarOpen }">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo SD Kristen Diakui Rantai Damai" class="w-full h-full object-contain drop-shadow-sm">
            </div>
            <div class="min-w-0">
                <p class="text-white font-bold text-sm leading-tight truncate">PPDB SD Kristen</p>
                <p class="text-green-300 text-xs">Portal Calon Siswa</p>
            </div>
        </div>

        {{-- User Info --}}
        <div class="px-3 py-3 border-b border-white/10">
            <div class="flex items-center gap-2.5 bg-white/5 rounded-lg px-3 py-2.5">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center shrink-0">
                    <i class="fas fa-user text-white text-xs"></i>
                </div>
                <div class="min-w-0">
                    @php $currentUser = session('user') ?? auth()->user(); @endphp
                    <p class="text-white text-sm font-medium truncate">{{ $currentUser->nama ?? $currentUser->name ?? 'Orang Tua' }}</p>
                    <p class="text-green-300 text-xs">{{ $currentUser->role ?? 'Orang Tua' }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
            <div class="sidebar-group-title">Menu Utama</div>

            <a href="{{ route('berandacasis') }}" class="sidebar-nav-item {{ Request::routeIs('berandacasis') ? 'active' : '' }}">
                <i class="fas fa-home w-4 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('formcasis') }}" class="sidebar-nav-item {{ Request::routeIs('formcasis') || Request::routeIs('daftarcasis') ? 'active' : '' }}">
                <i class="fas fa-address-card w-4 text-center"></i>
                <span>Pendaftaran</span>
            </a>

            <a href="{{ route('pembayaran') }}" class="sidebar-nav-item {{ Request::routeIs('pembayaran') ? 'active' : '' }}">
                <i class="fas fa-credit-card w-4 text-center"></i>
                <span>Pembayaran</span>
            </a>

            <a href="{{ route('pengumuman') }}" class="sidebar-nav-item {{ Request::routeIs('pengumuman') ? 'active' : '' }}">
                <i class="fas fa-bullhorn w-4 text-center"></i>
                <span>Pengumuman Seleksi</span>
            </a>

            <a href="{{ route('calon_siswa.daftar_ulang.show') }}" class="sidebar-nav-item {{ Request::routeIs('calon_siswa.daftar_ulang.*') ? 'active' : '' }}">
                <i class="fas fa-redo-alt w-4 text-center"></i>
                <span>Daftar Ulang</span>
            </a>

            <div class="sidebar-group-title">Akun</div>

            <a href="/beranda/profil casis" class="sidebar-nav-item {{ Request::is('beranda/profil casis') ? 'active' : '' }}">
                <i class="fas fa-user-circle w-4 text-center"></i>
                <span>Profil Saya</span>
            </a>

            <a href="{{ route('logout') }}" class="sidebar-nav-item hover:bg-red-800/50">
                <i class="fas fa-sign-out-alt w-4 text-center"></i>
                <span>Logout</span>
            </a>
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
                <nav class="hidden sm:flex breadcrumb">
                    <span>Portal Calon Siswa</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                    <span class="breadcrumb-item active">@yield('page-title', 'Dashboard')</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <span class="hidden sm:flex items-center gap-1.5 text-sm text-slate-600">
                    <i class="fas fa-calendar-alt text-green-600 text-xs"></i>
                    TA {{ $aktivTahunAjaran->tahun_ajar ?? '-' }}
                </span>
                <div class="w-px h-5 bg-slate-200 hidden sm:block"></div>
                <a href="{{ route('logout') }}" class="flex items-center gap-1.5 text-sm text-slate-600 hover:text-red-600 transition-colors px-2 py-1 rounded-lg hover:bg-red-50">
                    <i class="fas fa-sign-out-alt text-xs"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </header>

        <main class="flex-1 casis-main-area">
            <div class="page-container">
                @include('layout.alert')
                @yield('content')
            </div>
        </main>

        <footer class="main-footer">
            <div class="flex items-center justify-between gap-4">
                <span><strong>PPDB SD Kristen Diakui Rantai Damai</strong> &mdash; Portal Calon Siswa</span>
            </div>
        </footer>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('template/plugins/jquery/jquery.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
