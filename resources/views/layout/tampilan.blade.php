<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin') — PPDB SD Kristen</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('template/plugins/fontawesome-free/css/all.min.css') }}">

    {{-- Vite (Tailwind) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Scoped styling untuk Sidebar Admin Datar dan Aksesibel */
        .admin-sidebar {
            width: 16rem;
            background-color: #0f4c2a;
            color: #d1fae5;
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 50;
            box-shadow: 4px 0 16px -2px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Sinkronisasi margin konten utama terhadap sidebar pada breakpoint lg (1024px) */
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem !important;
            }
        }
        @media (max-width: 1023.98px) {
            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Label Grup Teks (Datar, Tanpa Akordion) */
        .admin-group-label {
            padding: 0.35rem 0.75rem 0.15rem 0.75rem;
            font-size: 0.6875rem; /* 11px */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #86efac; /* Green-300: rasio kontras > 8:1 terhadap #0f4c2a */
            user-select: none;
        }

        /* Item Navigasi Tunggal */
        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 38px;
            padding: 0.45rem 0.75rem;
            font-size: 0.875rem; /* 14px */
            font-weight: 500;
            line-height: 1.25;
            border-radius: 8px;
            color: #d1fae5;
            border-left: 3px solid transparent;
            text-decoration: none;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .admin-nav-item:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        /* Item Aktif */
        .admin-nav-item.active {
            background-color: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            font-weight: 600;
            border-left-color: #34d399; /* emerald-400 */
        }

        /* Focus Visible Khusus (Kontras Tinggi Tanpa Garis Tepi Hitam Bawaan) */
        .admin-nav-item:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px #34d399, 0 0 0 4px #0f4c2a;
        }

        /* Wadah Ikon Ukuran Tetap */
        .admin-nav-icon {
            width: 20px;
            height: 20px;
            min-width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.95rem;
            text-align: center;
        }

        /* Custom Scrollbar Tipis */
        .custom-sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-sidebar {
                transition: none !important;
            }
            .admin-nav-item {
                transition: none !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="h-full bg-slate-50 font-sans"
      x-data="{ 
          sidebarOpen: false,
          openSidebar() {
              this.sidebarOpen = true;
              document.body.classList.add('overflow-hidden');
              this.$nextTick(() => {
                  this.$refs.closeButton?.focus();
              });
          },
          closeSidebar() {
              this.sidebarOpen = false;
              document.body.classList.remove('overflow-hidden');
              this.$nextTick(() => {
                  this.$refs.menuButton?.focus();
              });
          }
      }"
      @keydown.escape="if (sidebarOpen) closeSidebar()">

    {{-- Sidebar Overlay (Mobile Off-canvas) --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden"
         @click="closeSidebar()"
         aria-hidden="true"
         style="display:none;">
    </div>

    {{-- Sidebar Admin --}}
    <aside id="sidebar"
           class="admin-sidebar sidebar-nav fixed inset-y-0 left-0 z-50 flex flex-col w-64 max-w-[86vw] bg-[#0f4c2a] text-[#d1fae5] shadow-xl -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
           :class="sidebarOpen ? '!translate-x-0' : ''"
           aria-label="Menu navigasi admin">

        {{-- Brand & Tombol Tutup Mobile --}}
        <div class="sidebar-brand flex items-center justify-between px-4 py-2.5 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo SD Kristen Diakui Rantai Damai" class="w-full h-full object-contain drop-shadow-sm">
                </div>
                <div class="min-w-0">
                    <p class="text-white font-bold text-sm leading-tight truncate">PPDB SD Kristen</p>
                    <p class="text-emerald-300/80 text-[11px] truncate">Diakui Rantai Damai</p>
                </div>
            </div>
            {{-- Tombol Tutup Sidebar (<1024px) --}}
            <button type="button"
                    x-ref="closeButton"
                    @click="closeSidebar()"
                    class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg text-emerald-200 hover:text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f4c2a] transition-colors"
                    aria-label="Tutup menu navigasi">
                <i class="fas fa-times text-base" aria-hidden="true"></i>
            </button>
        </div>

        {{-- User Info --}}
        <div class="px-3 py-2 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-2 bg-white/5 rounded-lg px-2.5 py-1.5">
                <div class="w-7 h-7 rounded-full bg-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-user-shield text-white text-[11px]" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                    @php $currentUser = session('user') ?? auth()->user(); @endphp
                    <p class="text-white text-xs font-semibold truncate">{{ $currentUser->nama ?? $currentUser->name ?? 'Admin' }}</p>
                    <p class="text-emerald-300/80 text-[11px] leading-none">Administrator</p>
                </div>
            </div>
        </div>

        {{-- Navigation List (Scrollable Area) --}}
        <nav class="flex-1 min-h-0 px-3 py-2 overflow-y-auto space-y-1.5 custom-sidebar-scroll"
             x-init="$nextTick(() => {
                 const active = $el.querySelector('.admin-nav-item.active');
                 if (active) {
                     active.scrollIntoView({ block: 'nearest' });
                 }
             })"
             aria-label="Menu navigasi admin">

            {{-- 1. Dashboard (Mandiri di atas, tanpa judul grup) --}}
            @php
                $isDashboardActive = Request::is('admin/dashboard') || Request::routeIs('admin.dashboard');
            @endphp
            <div>
                <a href="/admin/dashboard"
                   @click="if (window.innerWidth < 1024) closeSidebar()"
                   title="Dashboard Ringkasan Admin"
                   class="admin-nav-item {{ $isDashboardActive ? 'active' : '' }}"
                   {!! $isDashboardActive ? 'aria-current="page"' : '' !!}>
                    <span class="admin-nav-icon"><i class="fas fa-tachometer-alt" aria-hidden="true"></i></span>
                    <span class="truncate">Dashboard</span>
                </a>
            </div>

            {{-- 2. Grup: PENDAFTARAN --}}
            <div>
                <div class="admin-group-label" id="group-pendaftaran">PENDAFTARAN</div>
                <ul role="list" aria-labelledby="group-pendaftaran" class="space-y-1">
                    {{-- Calon Siswa --}}
                    @php
                        $isCasisActive = Request::is('admin/data/casis*') || Request::routeIs('indexcasis', 'editdata', 'detailcasis', 'formpendaftaran');
                    @endphp
                    <li>
                        <a href="/admin/data/casis"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Data Calon Siswa & Arsip"
                           class="admin-nav-item {{ $isCasisActive ? 'active' : '' }}"
                           {!! $isCasisActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
                            <span class="truncate">Calon Siswa</span>
                        </a>
                    </li>

                    {{-- Verifikasi Dokumen --}}
                    @php
                        $isPendaftaranActive = (Request::is('admin/pendaftaran*') && !Request::routeIs('formpendaftaran')) || Request::routeIs('datapendaftaran', 'periksadokumen', 'tampildata');
                    @endphp
                    <li>
                        <a href="/admin/pendaftaran"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Verifikasi Dokumen Persyaratan Calon Siswa"
                           class="admin-nav-item {{ $isPendaftaranActive ? 'active' : '' }}"
                           {!! $isPendaftaranActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                            <span class="truncate">Verifikasi Dokumen</span>
                            @if(!empty($antreanDokumenMenunggu) && $antreanDokumenMenunggu > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-bold leading-none text-white bg-amber-500 rounded-full shadow-xs" title="{{ $antreanDokumenMenunggu }} siswa dalam antrean verifikasi">
                                    {{ $antreanDokumenMenunggu }}
                                </span>
                            @endif
                        </a>
                    </li>

                    {{-- Pembayaran Pendaftaran --}}
                    @php
                        $isPembayaranActive = Request::is('admin/pembayaran*') || Request::routeIs('index', 'tagihan');
                    @endphp
                    <li>
                        <a href="/admin/pembayaran"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Pembayaran Pendaftaran"
                           class="admin-nav-item {{ $isPembayaranActive ? 'active' : '' }}"
                           {!! $isPembayaranActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
                            <span class="truncate">Pembayaran</span>
                            @if (!empty($pembayaranPerluPerhatian) && $pembayaranPerluPerhatian > 0)
                                <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-400 text-amber-950"
                                      title="{{ $pembayaranPerluPerhatian }} pembayaran perlu perhatian"
                                      aria-label="{{ $pembayaranPerluPerhatian }} pembayaran perlu perhatian">
                                    {{ $pembayaranPerluPerhatian }}
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 3. Grup: DAFTAR ULANG --}}
            <div>
                <div class="admin-group-label" id="group-daftar-ulang">DAFTAR ULANG</div>
                <ul role="list" aria-labelledby="group-daftar-ulang" class="space-y-1">
                    {{-- Daftar Ulang & Uang Pangkal --}}
                    @php
                        $isDuActive = Request::is('admin/verifikasi-daftar-ulang*') || Request::routeIs('admin.daftar_ulang.*');
                    @endphp
                    <li>
                        <a href="{{ route('admin.daftar_ulang.index') }}"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Daftar Ulang & Uang Pangkal"
                           class="admin-nav-item {{ $isDuActive ? 'active' : '' }}"
                           {!! $isDuActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-user-check" aria-hidden="true"></i></span>
                            <span class="truncate">Daftar Ulang</span>
                            {{-- TODO: Tampilkan badge angka bila variabel antrean daftar ulang belum lunas sudah tersedia secara global di layout --}}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 4. Grup: LAPORAN --}}
            <div>
                <div class="admin-group-label" id="group-laporan">LAPORAN</div>
                <ul role="list" aria-labelledby="group-laporan" class="space-y-1">
                    {{-- Laporan Pendaftaran --}}
                    @php
                        $isLapPendaftaranActive = Request::is('admin/laporan/pendaftaran*') || Request::routeIs('admin.pendaftaran');
                    @endphp
                    <li>
                        <a href="/admin/laporan/pendaftaran"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Rekap Pendaftaran"
                           aria-label="Rekap Pendaftaran"
                           class="admin-nav-item {{ $isLapPendaftaranActive ? 'active' : '' }}"
                           {!! $isLapPendaftaranActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                            <span class="truncate">Rekap Pendaftaran</span>
                        </a>
                    </li>

                    {{-- Laporan Pembayaran --}}
                    @php
                        $isLapPembayaranActive = Request::is('admin/laporan/pembayaran*') || Request::routeIs('admin.pembayaran');
                    @endphp
                    <li>
                        <a href="/admin/laporan/pembayaran"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Rekap Pembayaran"
                           aria-label="Rekap Pembayaran"
                           class="admin-nav-item {{ $isLapPembayaranActive ? 'active' : '' }}"
                           {!! $isLapPembayaranActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></span>
                            <span class="truncate">Rekap Pembayaran</span>
                        </a>
                    </li>

                    {{-- Laporan Daftar Ulang --}}
                    @php
                        $isLapDuActive = Request::is('admin/laporan/daftar-ulang*') || Request::routeIs('admin.laporan.daftar-ulang.*');
                    @endphp
                    <li>
                        <a href="{{ route('admin.laporan.daftar-ulang.index') }}"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Rekap Daftar Ulang"
                           aria-label="Rekap Daftar Ulang"
                           class="admin-nav-item {{ $isLapDuActive ? 'active' : '' }}"
                           {!! $isLapDuActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-file-signature" aria-hidden="true"></i></span>
                            <span class="truncate">Rekap Daftar Ulang</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 5. Grup: MASTER DATA --}}
            <div>
                <div class="admin-group-label" id="group-master-data">MASTER DATA</div>
                <ul role="list" aria-labelledby="group-master-data" class="space-y-1">
                    {{-- Tahun Ajaran & Biaya --}}
                    @php
                        $isTahunActive = Request::is('admin/data/tahun-ajaran*') || Request::is('admin/tahun-ajaran*') || Request::is('admin/biaya-daftar-ulang*') || Request::routeIs('beranda.tahun', 'tahun.*', 'biaya-daftar-ulang.*');
                    @endphp
                    <li>
                        <a href="/admin/data/tahun-ajaran"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Tahun Ajaran & Pengaturan Biaya"
                           class="admin-nav-item {{ $isTahunActive ? 'active' : '' }}"
                           {!! $isTahunActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                            <span class="truncate">Tahun Ajaran & Biaya</span>
                        </a>
                    </li>

                    {{-- Data Panitia --}}
                    @php
                        $isPanitiaActive = Request::is('admin/data/panitia*') || Request::routeIs('datapanitia', 'addpanitia', 'editpanitia');
                    @endphp
                    <li>
                        <a href="/admin/data/panitia"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Data Panitia PPDB"
                           class="admin-nav-item {{ $isPanitiaActive ? 'active' : '' }}"
                           {!! $isPanitiaActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-users-cog" aria-hidden="true"></i></span>
                            <span class="truncate">Data Panitia</span>
                        </a>
                    </li>

                    {{-- Data Pengguna --}}
                    @php
                        $isUserActive = Request::is('admin/data/user*') || Request::routeIs('beranda.datauser');
                    @endphp
                    <li>
                        <a href="/admin/data/user"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Data Akun Pengguna"
                           class="admin-nav-item {{ $isUserActive ? 'active' : '' }}"
                           {!! $isUserActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
                            <span class="truncate">Data Pengguna</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 6. Grup: KONTEN WEBSITE --}}
            <div>
                <div class="admin-group-label" id="group-konten">KONTEN WEBSITE</div>
                <ul role="list" aria-labelledby="group-konten" class="space-y-1">
                    {{-- Landing Page --}}
                    @php
                        $isHeroActive = Request::is('admin/data/landing-page*') || Request::routeIs('photo.pengaturan');
                    @endphp
                    <li>
                        <a href="/admin/data/landing-page"
                           @click="if (window.innerWidth < 1024) closeSidebar()"
                           title="Pengaturan Konten Landing Page & Hero Banner"
                           class="admin-nav-item {{ $isHeroActive ? 'active' : '' }}"
                           {!! $isHeroActive ? 'aria-current="page"' : '' !!}>
                            <span class="admin-nav-icon"><i class="fas fa-images" aria-hidden="true"></i></span>
                            <span class="truncate">Landing Page</span>
                        </a>
                    </li>
                </ul>
            </div>

        </nav>

        {{-- 7. Grup: AKUN --}}
        <div class="shrink-0 px-3 pt-2 pb-2.5 border-t border-white/10 bg-[#0c3c21]" style="padding-bottom: max(0.625rem, env(safe-area-inset-bottom, 0.625rem));">
            <div class="admin-group-label" id="group-akun" style="padding-top: 0 !important; padding-bottom: 0.15rem !important;">AKUN</div>
            <ul role="list" aria-labelledby="group-akun" class="space-y-1">
                @php
                    $isProfilActive = Request::is('admin/profil*') || Request::routeIs('admin.profil');
                @endphp
                <li>
                    <a href="/admin/profil"
                       @click="if (window.innerWidth < 1024) closeSidebar()"
                       title="Profil Saya"
                       class="admin-nav-item {{ $isProfilActive ? 'active' : '' }}"
                       {!! $isProfilActive ? 'aria-current="page"' : '' !!}>
                        <span class="admin-nav-icon"><i class="fas fa-user-circle" aria-hidden="true"></i></span>
                        <span class="truncate">Profil Saya</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('logout') }}"
                       onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
                       title="Keluar dari Sistem PPDB"
                       aria-label="Keluar"
                       class="admin-nav-item min-h-[44px] text-red-300 hover:text-white hover:bg-red-500/20">
                        <span class="admin-nav-icon text-red-400"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></span>
                        <span class="truncate">Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="main-content" id="main-content">

        {{-- Top Navbar --}}
        <header class="top-navbar">
            <div class="flex items-center gap-3">
                {{-- Mobile Menu Toggle (Area Sentuh Minimum 44x44px) --}}
                <button type="button"
                        x-ref="menuButton"
                        @click="openSidebar()"
                        class="lg:hidden w-11 h-11 flex items-center justify-center rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition-colors"
                        aria-label="Buka menu navigasi"
                        :aria-expanded="sidebarOpen.toString()"
                        aria-controls="sidebar">
                    <i class="fas fa-bars text-lg" aria-hidden="true"></i>
                </button>

                {{-- Page Breadcrumb --}}
                <nav class="hidden sm:flex breadcrumb">
                    <span>PPDB SD Kristen</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                    <span class="breadcrumb-item active">@yield('page-title', 'Dashboard')</span>
                </nav>
            </div>

            {{-- Right Nav --}}
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

        {{-- Page Content --}}
        <main class="flex-1 p-5 lg:p-6">
            @include('layout.alert')
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="main-footer">
            <div class="flex items-center justify-between gap-4">
                <span><strong>PPDB SD Kristen Diakui Rantai Damai</strong> &mdash; Sistem Penerimaan Peserta Didik Baru</span>
            </div>
        </footer>
    </div>

    {{-- Alpine.js (Core) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- jQuery (needed for some features) --}}
    <script src="{{ asset('template/plugins/jquery/jquery.min.js') }}"></script>

    @stack('scripts')

    {{-- Auto-close mobile sidebar on desktop resize --}}
    <script>
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                document.body.classList.remove('overflow-hidden');
            }
        });
    </script>
</body>

</html>