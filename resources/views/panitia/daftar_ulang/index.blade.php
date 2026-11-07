@extends('layout.tampilan')

@section('title', 'Daftar Ulang & Uang Pangkal')
@section('page-title', 'Daftar Ulang & Uang Pangkal')

@push('styles')
<style>
    /* Scoped Styles untuk Halaman Daftar Ulang & Uang Pangkal - Selaras dengan Menu Pembayaran */
    .du-page,
    .du-page h1, .du-page h2, .du-page h3,
    .du-page h4, .du-page p, .du-page span,
    .du-page dt, .du-page dd, .du-page a,
    .du-page button, .du-page select, .du-page input {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    /* Focus visible dengan outline kontras tinggi (tema hijau/emerald) */
    .du-btn-action:focus-visible,
    .du-input-ctrl:focus-visible {
        outline: 2px solid #15803d !important;
        outline-offset: 2px !important;
    }
    .du-btn-action:focus:not(:focus-visible),
    .du-input-ctrl:focus:not(:focus-visible) {
        outline: none !important;
        box-shadow: none !important;
    }

    /* Scroll horizontal tabel: pada >= 1200px tabel muat tanpa scrollbar */
    @media (min-width: 1200px) {
        .du-table-scroll {
            overflow-x: visible !important;
            overflow-y: visible !important;
        }
    }
    @media (max-width: 1199.98px) {
        .du-table-scroll {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Grid Filter yang selalu tampil di Desktop dan dapat dilipat di Mobile - Selaras dengan Menu Calon Siswa */
    .du-filter-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }
    @media (min-width: 640px) {
        .du-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .du-filter-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 767.98px) {
        .du-filter-grid:not(.is-open) {
            display: none !important;
        }
    }

    /* Breakpoint tampilan tabel vs kartu: < 900px kartu, >= 900px tabel */
    @media (max-width: 899.98px) {
        .du-table-view { display: none !important; }
        .du-cards-view { display: block !important; }
    }
    @media (min-width: 900px) {
        .du-table-view { display: block !important; }
        .du-cards-view { display: none !important; }
    }

    /* Prefer reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .du-page,
        .du-page * {
            transition: none !important;
            animation: none !important;
            transform: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="du-page space-y-5 sm:space-y-6"
     x-data="{
         filterOpenMobile: false,
         modalBayar: false,
         modalBerkas: false,
         selectedItem: null,
         openModalBayar(item) {
             this.selectedItem = item;
             this.modalBayar = true;
         },
         openModalBerkas(item) {
             this.selectedItem = item;
             this.modalBerkas = true;
         }
     }"
     @keydown.escape.window="if (modalBayar) modalBayar = false; if (modalBerkas) modalBerkas = false;">

    {{-- Alert Notifikasi --}}
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl relative flex items-center justify-between shadow-xs" role="alert">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle text-emerald-600 text-base" aria-hidden="true"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600 hover:bg-emerald-100/60 transition-colors" aria-label="Tutup pemberitahuan">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>
    </div>
    @elseif (session('error'))
    <div x-data="{ show: true }" x-show="show" class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl relative flex items-center justify-between shadow-xs" role="alert">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-exclamation-circle text-rose-600 text-base" aria-hidden="true"></i>
            <span class="font-medium text-sm">{{ session('error') }}</span>
        </div>
        <button type="button" @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-600 hover:bg-rose-100/60 transition-colors" aria-label="Tutup pemberitahuan">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>
    </div>
    @endif

    {{-- Kartu Judul Halaman (Selaras dengan Menu Pembayaran) --}}
    <div class="bg-white rounded-2xl px-5 py-4 sm:px-6 sm:py-5 border border-slate-200/80 shadow-xs">
        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 font-heading leading-tight tracking-tight">Daftar Ulang &amp; Uang Pangkal</h1>
        <p class="text-slate-600 text-sm sm:text-[15px] mt-0.5">Kelola administrasi daftar ulang, verifikasi berkas fisik, dan pemantauan setoran uang pangkal.</p>
    </div>

    {{-- Ringkasan Statistik 4 Kolom (Persis Pola Menu Pembayaran) --}}
    @php
        $countLulus = (int) ($summary['totalLulus'] ?? 0);
        $nominalTagihan = (float) ($summary['totalTagihan'] ?? 0);

        $countSelesai = (int) ($summary['totalSelesai'] ?? 0);
        $nominalTerbayar = (float) ($summary['totalTerbayar'] ?? 0);

        $countMenungguBerkas = (int) ($summary['totalMenungguBerkas'] ?? 0);

        $countTerlambat = (int) ($summary['totalTerlambat'] ?? 0);
        $nominalSisa = (float) ($summary['totalSisa'] ?? 0);
    @endphp
    <div class="grid grid-cols-1 min-[420px]:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-stretch">
        {{-- 1. Lulus Seleksi --}}
        <div class="du-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Lulus Seleksi">Lulus Seleksi</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countLulus > 0 ? 'text-slate-900' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countLulus) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">siswa</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate text-slate-600">
                    Tagihan: Rp {{ number_format($nominalTagihan, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-user-graduate text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 2. Selesai (Cicilan 1 & Berkas) --}}
        <div class="du-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Daftar Ulang Selesai">Daftar Ulang Selesai</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countSelesai > 0 ? 'text-slate-900' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countSelesai) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">siswa</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countSelesai > 0 ? 'text-emerald-700 font-semibold' : 'text-slate-400 font-medium' }}">
                    Masuk: Rp {{ number_format($nominalTerbayar, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countSelesai > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-check-double text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 3. Menunggu Berkas --}}
        <div class="du-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Menunggu Berkas Fisik">Menunggu Berkas</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countMenungguBerkas > 0 ? 'text-slate-900' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countMenungguBerkas) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">siswa</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countMenungguBerkas > 0 ? 'text-amber-700 font-semibold' : 'text-slate-400' }}">
                    Perlu verifikasi dokumen fisik
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countMenungguBerkas > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-folder-open text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 4. Sisa & Tahap 2 Terlambat --}}
        <div class="du-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Sisa Kewajiban & Terlambat">Sisa Tagihan</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countTerlambat > 0 ? 'text-rose-600' : 'text-slate-900' }} leading-none tabular-nums">
                        {{ number_format($countTerlambat) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">terlambat</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate text-slate-700">
                    Sisa: Rp {{ number_format($nominalSisa, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countTerlambat > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-clock text-lg" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian Bar (Selaras dengan Menu Calon Siswa) --}}
    @php
        $searchVal = request('search');
        $filterTaVal = request('tahun_ajaran');
        $filterKeuanganVal = request('status_keuangan');
        $filterBerkasVal = request('status_berkas');
        $filterDuVal = request('status_daftar_ulang');

        $activeFilterCount = 0;
        if (!empty($searchVal)) $activeFilterCount++;
        if (!empty($filterTaVal)) $activeFilterCount++;
        if (!empty($filterKeuanganVal)) $activeFilterCount++;
        if (!empty($filterBerkasVal)) $activeFilterCount++;
        if (!empty($filterDuVal)) $activeFilterCount++;

        $selectedTaName = null;
        if (!empty($filterTaVal)) {
            $foundTa = $tahunAjaranList->firstWhere('id_tahun_ajaran', $filterTaVal);
            $selectedTaName = $foundTa ? $foundTa->tahun_ajaran : "ID {$filterTaVal}";
        }
    @endphp
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.daftar_ulang.index') }}" method="GET" class="space-y-4">
            @if(request()->filled('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                {{-- Kolom Pencarian (?search=) & Tombol Cari di sampingnya --}}
                <div class="flex items-center gap-2 flex-1 min-w-0 max-w-2xl">
                    <div class="relative flex-1 min-w-0">
                        <label for="du-search-input" class="sr-only">Cari Calon Siswa</label>
                        <input type="text"
                               id="du-search-input"
                               name="search"
                               class="du-input-ctrl w-full min-w-0 bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl pl-10 pr-9 h-11 min-h-[44px] transition-colors focus:bg-white"
                               placeholder="Cari nama atau no. pendaftaran..."
                               value="{{ $searchVal }}">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400" aria-hidden="true">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        @if(!empty($searchVal))
                        <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['search', 'page']))) }}"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                           title="Hapus kata kunci pencarian"
                           aria-label="Hapus kata kunci pencarian">
                            <i class="fas fa-times-circle text-sm" aria-hidden="true"></i>
                        </a>
                        @endif
                    </div>

                    {{-- Tombol Cari & Filter persis di samping input --}}
                    <button type="submit"
                            class="du-btn-action px-3.5 sm:px-5 h-11 min-h-[44px] text-xs sm:text-sm font-semibold rounded-xl bg-green-700 hover:bg-green-800 text-white shadow-xs flex items-center gap-2 transition-colors shrink-0">
                        <i class="fas fa-search text-xs" aria-hidden="true"></i>
                        <span>Cari &amp; Filter</span>
                    </button>
                </div>

                {{-- Tombol Aksi Sisi Kanan: Mobile Filter Toggle & Atur Ulang --}}
                <div class="flex items-center gap-2 self-start sm:self-end md:self-auto flex-wrap">
                    {{-- Toggle Filter Mobile (< 768px) --}}
                    <button type="button"
                            @click="filterOpenMobile = !filterOpenMobile"
                            class="md:hidden px-3.5 h-11 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 transition-colors"
                            :aria-expanded="filterOpenMobile"
                            aria-controls="du-filter-panel">
                        <i class="fas fa-filter text-xs" aria-hidden="true"></i>
                        <span>Filter</span>
                        @if($activeFilterCount > 0)
                            <span class="w-5 h-5 rounded-full bg-green-700 text-white text-[10px] flex items-center justify-center font-bold">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('admin.daftar_ulang.index') }}"
                           class="du-btn-action px-3.5 h-11 min-h-[44px] text-xs sm:text-sm font-medium rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 transition-colors flex items-center gap-1.5"
                           title="Atur ulang seluruh filter">
                            <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                            <span>Atur Ulang</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Grid Kontrol Filter (4 Kolom dengan Label Jelas - Selaras dengan Menu Calon Siswa) --}}
            <div id="du-filter-panel" class="du-filter-grid" :class="{ 'is-open': filterOpenMobile }">
                {{-- Filter Tahun Ajaran --}}
                <div>
                    <label for="filter-du-ta" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Tahun Ajaran</label>
                    <select id="filter-du-ta" name="tahun_ajaran" class="du-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjaranList as $ta)
                            <option value="{{ $ta->id_tahun_ajaran }}" {{ $filterTaVal == $ta->id_tahun_ajaran ? 'selected' : '' }}>
                                {{ $ta->tahun_ajaran }} {{ $ta->status === 'Berlangsung' ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status Keuangan --}}
                <div>
                    <label for="filter-du-keuangan" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Keuangan</label>
                    <select id="filter-du-keuangan" name="status_keuangan" class="du-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Status Keuangan</option>
                        <option value="Belum Bayar" {{ $filterKeuanganVal === 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="Cicilan Awal Terpenuhi" {{ $filterKeuanganVal === 'Cicilan Awal Terpenuhi' ? 'selected' : '' }}>Cicilan Awal Terpenuhi</option>
                        <option value="Lunas" {{ $filterKeuanganVal === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>

                {{-- Filter Status Berkas --}}
                <div>
                    <label for="filter-du-berkas" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Berkas</label>
                    <select id="filter-du-berkas" name="status_berkas" class="du-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Status Berkas</option>
                        <option value="Menunggu Berkas" {{ $filterBerkasVal === 'Menunggu Berkas' ? 'selected' : '' }}>Menunggu Berkas</option>
                        <option value="Diterima" {{ $filterBerkasVal === 'Diterima' ? 'selected' : '' }}>Berkas Diterima</option>
                    </select>
                </div>

                {{-- Filter Status Daftar Ulang --}}
                <div>
                    <label for="filter-du-status" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Daftar Ulang</label>
                    <select id="filter-du-status" name="status_daftar_ulang" class="du-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Status Daftar Ulang</option>
                        <option value="Menunggu Berkas" {{ $filterDuVal === 'Menunggu Berkas' ? 'selected' : '' }}>Menunggu Berkas</option>
                        <option value="Selesai" {{ $filterDuVal === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Batal" {{ $filterDuVal === 'Batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Chip Filter Aktif (Selaras dengan Menu Calon Siswa) --}}
    @if($activeFilterCount > 0)
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-semibold text-slate-600 mr-1">Filter Aktif:</span>

            @if(!empty($searchVal))
                <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['search', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Cari: "{{ Str::limit($searchVal, 20) }}"</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedTaName)
                <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['tahun_ajaran', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                   <span>Tahun Ajaran: {{ $selectedTaName }}</span>
                   <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if(!empty($filterKeuanganVal))
                <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['status_keuangan', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                   <span>Keuangan: {{ $filterKeuanganVal }}</span>
                   <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if(!empty($filterBerkasVal))
                <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['status_berkas', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                   <span>Berkas: {{ $filterBerkasVal }}</span>
                   <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if(!empty($filterDuVal))
                <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['status_daftar_ulang', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                   <span>Status DU: {{ $filterDuVal }}</span>
                   <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            <a href="{{ route('admin.daftar_ulang.index') }}"
               class="text-xs text-rose-600 hover:text-rose-800 hover:underline font-medium ml-1">
                Hapus Semua
            </a>
        </div>
    @endif

    {{-- Wadah Tabel Daftar Ulang & Uang Pangkal --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">

        {{-- DESKTOP & TABLET TABLE (>= 900px) --}}
        <div class="du-table-view du-table-scroll">
            <table class="w-full text-sm text-left text-slate-700 border-collapse table-auto">
                <colgroup>
                    <col style="width: 48px; min-width: 48px;">
                    <col style="width: 15%; min-width: 135px;">
                    <col style="width: 20%; min-width: 165px;">
                    <col style="width: 14%; min-width: 125px;">
                    <col style="width: 12%; min-width: 105px;">
                    <col style="width: 12%; min-width: 105px;">
                    <col style="width: 11%; min-width: 95px;">
                    <col style="width: 16%; min-width: 165px;">
                </colgroup>
                <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="w-[48px] pl-3 pr-1 py-3.5 font-bold text-center text-slate-600" style="text-align: center !important;">NO</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">NO. PENDAFTARAN</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">CALON SISWA</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">PEMBAYARAN</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">TAHAP 2</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">BERKAS</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">STATUS</th>
                        <th scope="col" class="px-3 pr-4 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarUlang as $index => $item)
                    @php
                        $casis = $item->casis;
                        $nama = $casis->nama_calon_siswa;
                        $noReg = $casis->no_registrasi ?: '-';
                    @endphp
                    <tr class="hover:bg-slate-50/75 transition-colors">
                        {{-- NO --}}
                        <td class="pl-3 pr-1 py-3.5 align-middle text-center font-medium text-slate-500 text-xs" style="text-align: center !important;">
                            {{ $daftarUlang->firstItem() + $index }}
                        </td>

                        {{-- NO. PENDAFTARAN (Kolom Mandiri Seperti Menu Pembayaran) --}}
                        <td class="px-3 py-3.5 align-middle text-left font-mono text-sm text-slate-800 whitespace-nowrap">
                            <span class="font-semibold" title="{{ $noReg }}">{{ $noReg }}</span>
                        </td>

                        {{-- CALON SISWA (Hanya Nama, NIK Dihapus) --}}
                        <td class="px-3 py-3.5 align-middle">
                            <span class="font-bold text-slate-900 text-sm leading-snug">{{ $nama }}</span>
                        </td>

                        {{-- PEMBAYARAN (Hanya Nominal yang Sudah Dibayar) --}}
                        <td class="px-3 py-3.5 align-middle text-left font-semibold text-slate-900 tabular-nums text-sm whitespace-nowrap">
                            Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}
                        </td>

                        {{-- TAHAP 2 --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            @if($item->statusTahap2 === 'Lunas')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check text-[10px]" aria-hidden="true"></i> Lunas
                                </span>
                            @elseif($item->statusTahap2 === 'Terlambat')
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <i class="fas fa-exclamation-circle text-[10px]" aria-hidden="true"></i> Terlambat
                                    </span>
                                    @if($item->tglJatuhTempo)
                                        <span class="block text-[11px] text-rose-600 mt-0.5 font-medium">
                                            {{ \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            @elseif($item->statusTahap2 === 'Jatuh Tempo')
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock text-[10px]" aria-hidden="true"></i> Hari Ini
                                    </span>
                                    @if($item->tglJatuhTempo)
                                        <span class="block text-[11px] text-amber-700 mt-0.5 font-medium">
                                            {{ \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            @elseif($item->statusTahap2 === 'Belum Jatuh Tempo')
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fas fa-calendar-alt text-[10px]" aria-hidden="true"></i> Belum JT
                                    </span>
                                    @if($item->tglJatuhTempo)
                                        <span class="block text-[11px] text-slate-500 mt-0.5">
                                            {{ \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 font-mono">-</span>
                            @endif
                        </td>

                        {{-- BERKAS --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            @if($item->statusBerkas === 'Diterima')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-circle text-emerald-600 text-[11px]" aria-hidden="true"></i>
                                    <span>Diterima</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fas fa-clock text-amber-600 text-[11px]" aria-hidden="true"></i>
                                    <span>Menunggu</span>
                                </span>
                            @endif
                        </td>

                        {{-- STATUS --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            @if($item->statusDaftarUlang === 'Selesai')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-double text-[10px]" aria-hidden="true"></i> Selesai
                                </span>
                            @elseif($item->statusDaftarUlang === 'Batal')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    <i class="fas fa-times-circle text-[10px]" aria-hidden="true"></i> Batal
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-hourglass-half text-[10px]" aria-hidden="true"></i> Menunggu
                                </span>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td class="px-3 pr-4 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Tombol Detail --}}
                                <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                                   class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                   title="Buka rincian administrasi daftar ulang {{ $nama }}">
                                    <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                    <span>Detail</span>
                                </a>

                                {{-- Tombol Catat Bayar --}}
                                @if($item->sisaBiaya > 0)
                                    <button type="button"
                                            @click="openModalBayar({
                                                id: '{{ $item->id }}',
                                                nama: '{{ addslashes($nama) }}',
                                                noReg: '{{ $noReg }}',
                                                totalBiaya: {{ $item->totalBiaya }},
                                                sisaBiaya: {{ $item->sisaBiaya }}
                                            })"
                                            class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-semibold transition-colors shadow-xs"
                                            title="Catat setoran pembayaran tunai kasir">
                                        <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                                        <span>Catat</span>
                                    </button>
                                @endif

                                {{-- Tombol Terima Berkas --}}
                                <button type="button"
                                        @click="openModalBerkas({
                                            id: '{{ $item->id }}',
                                            nama: '{{ addslashes($nama) }}',
                                            noReg: '{{ $noReg }}',
                                            statusBerkas: '{{ $item->statusBerkas }}',
                                            jenisBerkas: '{{ $item->jenisBerkas ?? 'Asli' }}',
                                            catatan: '{{ addslashes($item->daftarUlang?->catatan_berkas ?? '') }}'
                                        })"
                                        class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl {{ $item->statusBerkas === 'Diterima' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }} text-xs font-semibold transition-colors shadow-xs"
                                        title="Verifikasi penerimaan berkas fisik calon siswa">
                                    <i class="fas {{ $item->statusBerkas === 'Diterima' ? 'fa-check text-emerald-600' : 'fa-folder-open text-amber-600' }} text-xs" aria-hidden="true"></i>
                                    <span>Berkas</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mb-3 border border-slate-200">
                                    <i class="fas {{ request('search') ? 'fa-search' : 'fa-user-graduate' }} text-xl" aria-hidden="true"></i>
                                </div>
                                <h3 class="text-base font-bold text-slate-800 mb-1">
                                    {{ request('search') ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada data calon siswa.' }}
                                </h3>
                                @if($hasActiveFilter)
                                    <p class="text-slate-500 text-sm mb-4">Tidak ditemukan data calon siswa dengan kriteria filter saat ini.</p>
                                    <a href="{{ route('admin.daftar_ulang.index') }}" class="du-btn-action inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors border border-emerald-200">
                                        <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                                        <span>Atur ulang filter</span>
                                    </a>
                                @else
                                    <p class="text-slate-500 text-sm">Belum ada calon siswa lulus seleksi yang tercatat di sistem.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS VIEW (< 900px) --}}
        <div class="du-cards-view divide-y divide-slate-100">
            @forelse ($daftarUlang as $index => $item)
            @php
                $casis = $item->casis;
                $nama = $casis->nama_calon_siswa;
                $noReg = $casis->no_registrasi ?: '-';
            @endphp
            <div class="p-4 sm:p-5 space-y-3">
                {{-- Header Kartu: Nama & Status DU --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 text-base leading-snug truncate">{{ $nama }}</h2>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-mono mt-0.5">
                            <span class="text-slate-400 font-sans">No. Pendaftaran:</span>
                            <span class="font-semibold text-slate-800">{{ $noReg }}</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold border shrink-0 {{ $item->statusDaftarUlang === 'Selesai' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($item->statusDaftarUlang === 'Batal' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                        <i class="fas {{ $item->statusDaftarUlang === 'Selesai' ? 'fa-check-double text-emerald-600' : ($item->statusDaftarUlang === 'Batal' ? 'fa-times-circle text-rose-600' : 'fa-hourglass-half text-slate-500') }} text-[10px]" aria-hidden="true"></i>
                        <span>{{ $item->statusDaftarUlang }}</span>
                    </span>
                </div>

                {{-- Detail Baris Ringkas: Pembayaran, Tahap 2, Berkas --}}
                <div class="bg-slate-50/80 rounded-xl p-3 text-xs space-y-2 border border-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Pembayaran:</span>
                        <span class="font-bold text-slate-900 text-sm tabular-nums">
                            Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Pelunasan Tahap 2:</span>
                        <span class="font-medium text-slate-800">
                            {{ $item->statusTahap2 }} {{ $item->tglJatuhTempo ? '(' . \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') . ')' : '' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Berkas Fisik:</span>
                        <span class="font-medium {{ $item->statusBerkas === 'Diterima' ? 'text-emerald-700 font-semibold' : 'text-amber-700' }}">
                            {{ $item->statusBerkas }} {{ $item->jenisBerkas ? '(' . $item->jenisBerkas . ')' : '' }}
                        </span>
                    </div>
                </div>

                {{-- Tombol Aksi di Bawah (Area Sentuh Minimum 44px) --}}
                <div class="pt-1 flex items-center justify-end gap-2 flex-wrap sm:flex-nowrap">
                    <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                       class="du-btn-action w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition-colors min-h-[44px]">
                        <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                        <span>Detail</span>
                    </a>

                    @if($item->sisaBiaya > 0)
                        <button type="button"
                                @click="openModalBayar({
                                    id: '{{ $item->id }}',
                                    nama: '{{ addslashes($nama) }}',
                                    noReg: '{{ $noReg }}',
                                    totalBiaya: {{ $item->totalBiaya }},
                                    sisaBiaya: {{ $item->sisaBiaya }}
                                })"
                                class="du-btn-action w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                            <span>Catat Bayar</span>
                        </button>
                    @endif

                    <button type="button"
                            @click="openModalBerkas({
                                id: '{{ $item->id }}',
                                nama: '{{ addslashes($nama) }}',
                                noReg: '{{ $noReg }}',
                                statusBerkas: '{{ $item->statusBerkas }}',
                                jenisBerkas: '{{ $item->jenisBerkas ?? 'Asli' }}',
                                catatan: '{{ addslashes($item->daftarUlang?->catatan_berkas ?? '') }}'
                            })"
                            class="du-btn-action w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl {{ $item->statusBerkas === 'Diterima' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }} text-xs font-semibold transition-colors min-h-[44px]">
                        <i class="fas {{ $item->statusBerkas === 'Diterima' ? 'fa-check' : 'fa-folder-open' }} text-xs" aria-hidden="true"></i>
                        <span>{{ $item->statusBerkas === 'Diterima' ? 'Ubah Berkas' : 'Terima Berkas' }}</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mx-auto mb-3 border border-slate-200">
                    <i class="fas {{ request('search') ? 'fa-search' : 'fa-user-graduate' }} text-xl" aria-hidden="true"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">
                    {{ request('search') ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada calon siswa.' }}
                </h3>
                @if($hasActiveFilter)
                    <a href="{{ route('admin.daftar_ulang.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 underline mt-2 inline-block">
                        Atur ulang filter
                    </a>
                @endif
            </div>
            @endforelse
        </div>

        {{-- PAGINATION & INFO FOOTER (Persis Pola Menu Pembayaran) --}}
        <div class="px-5 py-4 border-t border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-600">
            <div>
                Menampilkan <span class="font-semibold text-slate-800">{{ $daftarUlang->firstItem() ?? 0 }}</span> - <span class="font-semibold text-slate-800">{{ $daftarUlang->lastItem() ?? 0 }}</span> dari total <span class="font-semibold text-slate-800">{{ $daftarUlang->total() }}</span> calon siswa
            </div>
            @if($daftarUlang->hasPages())
                <div>
                    {{ $daftarUlang->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================================
         MODAL 1: CATAT PEMBAYARAN TUNAI (Aksesibel & Sesuai Standar)
         ======================================================== --}}
    <div x-show="modalBayar"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-bayar-title"
         role="dialog"
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="modalBayar = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 border border-slate-200"
                 @click.stop>
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-lg" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800" id="modal-bayar-title">Catat Pembayaran Tunai</h3>
                            <p class="text-xs text-slate-500">Pencatatan setoran langsung di kasir sekolah</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="modalBayar = false"
                            class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center"
                            aria-label="Tutup modal pencatatan pembayaran">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/verifikasi-daftar-ulang') }}/' + (selectedItem?.id || '') + '/catat-pembayaran'"
                      method="POST"
                      class="mt-4 space-y-4">
                    @csrf

                    {{-- Informasi Singkat Casis --}}
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-xs text-slate-700 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Calon Siswa:</span>
                            <span class="font-bold text-slate-800" x-text="selectedItem?.nama"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Registrasi:</span>
                            <span class="font-mono font-semibold text-slate-700" x-text="selectedItem?.noReg"></span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200">
                            <span class="text-slate-500">Sisa Tagihan:</span>
                            <span class="font-bold text-amber-700 text-sm">
                                Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedItem?.sisaBiaya || 0)"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Input Nominal Bayar --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="jml_bayar_du" class="block text-xs font-semibold text-slate-700">
                                Nominal Pembayaran (Rp) <span class="text-red-500">*</span>
                            </label>
                            <button type="button"
                                    @click="$refs.inputNominal.value = selectedItem?.sisaBiaya || 0"
                                    class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">
                                Bayar Penuh Sisa
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-bold">
                                Rp
                            </div>
                            <input type="number"
                                   name="jml_bayar_du"
                                   id="jml_bayar_du"
                                   x-ref="inputNominal"
                                   required
                                   min="1000"
                                   :max="selectedItem?.sisaBiaya || 10000000"
                                   class="du-input-ctrl w-full pl-11 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-blue-600 text-sm font-bold text-slate-800 min-h-[44px]"
                                   :placeholder="'Maksimal ' + (selectedItem?.sisaBiaya || 0)">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Nominal pembayaran tunai tidak boleh melebihi sisa tagihan.</p>
                    </div>

                    {{-- Tanggal Bayar --}}
                    <div>
                        <label for="tgl_bayar_du" class="block text-xs font-semibold text-slate-700 mb-1">
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local"
                               name="tgl_bayar_du"
                               id="tgl_bayar_du"
                               required
                               value="{{ now()->format('Y-m-d\TH:i') }}"
                               class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-blue-600 text-sm min-h-[44px]">
                    </div>

                    {{-- Nomor Kuitansi & Keterangan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="no_kuitansi" class="block text-xs font-semibold text-slate-700 mb-1">
                                No. Kuitansi (Opsional)
                            </label>
                            <input type="text"
                                   name="no_kuitansi"
                                   id="no_kuitansi"
                                   placeholder="Otomatis jika kosong"
                                   class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-blue-600 text-sm min-h-[44px]">
                        </div>
                        <div>
                            <label for="keterangan" class="block text-xs font-semibold text-slate-700 mb-1">
                                Keterangan Setoran
                            </label>
                            <input type="text"
                                   name="keterangan"
                                   id="keterangan"
                                   placeholder="Contoh: Setoran tunai di kasir sekolah"
                                   class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-blue-600 text-sm min-h-[44px]">
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalBayar = false"
                                class="du-btn-action px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl min-h-[44px] transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="du-btn-action px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] inline-flex items-center gap-2">
                            <i class="fas fa-save text-xs" aria-hidden="true"></i>
                            <span>Simpan Pembayaran Tunai</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================
         MODAL 2: TERIMA / PERBARUI BERKAS FISIK (Aksesibel & Sesuai Standar)
         ======================================================== --}}
    <div x-show="modalBerkas"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-berkas-title"
         role="dialog"
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="modalBerkas = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-200"
                 @click.stop>
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <i class="fas fa-folder-open text-lg" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800" id="modal-berkas-title">Penerimaan Berkas Fisik</h3>
                            <p class="text-xs text-slate-500">Verifikasi dokumen persyaratan daftar ulang</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="modalBerkas = false"
                            class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center"
                            aria-label="Tutup modal penerimaan berkas">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/verifikasi-daftar-ulang') }}/' + (selectedItem?.id || '') + '/terima-berkas'"
                      method="POST"
                      class="mt-4 space-y-4">
                    @csrf

                    {{-- Informasi Calon Siswa --}}
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-xs text-slate-700 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Calon Siswa:</span>
                            <span class="font-bold text-slate-800" x-text="selectedItem?.nama"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Registrasi:</span>
                            <span class="font-mono font-semibold text-slate-700" x-text="selectedItem?.noReg"></span>
                        </div>
                    </div>

                    {{-- Status Berkas --}}
                    <div>
                        <label for="status_berkas_modal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Status Berkas Fisik <span class="text-red-500">*</span>
                        </label>
                        <select name="status_berkas"
                                id="status_berkas_modal"
                                required
                                x-model="selectedItem.statusBerkas"
                                class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-emerald-600 text-sm font-semibold min-h-[44px]">
                            <option value="Menunggu Berkas">⏳ Menunggu Berkas Diserahkan</option>
                            <option value="Diterima">✅ Berkas Diterima Lengkap</option>
                            <option value="Ditolak">❌ Berkas Ditolak / Tidak Sesuai</option>
                        </select>
                    </div>

                    {{-- Jenis Berkas --}}
                    <div x-show="selectedItem?.statusBerkas === 'Diterima'">
                        <label for="jenis_berkas_modal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Jenis Penyerahan Dokumen
                        </label>
                        <select name="jenis_berkas"
                                id="jenis_berkas_modal"
                                x-model="selectedItem.jenisBerkas"
                                class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-emerald-600 text-sm min-h-[44px]">
                            <option value="Asli">Dokumen Asli</option>
                            <option value="Fotokopi">Fotokopi Dilegalisir</option>
                            <option value="Asli &amp; Fotokopi">Asli &amp; Fotokopi Lengkap</option>
                        </select>
                    </div>

                    {{-- Catatan Berkas --}}
                    <div>
                        <label for="catatan_berkas_modal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Catatan Panitia (Opsional)
                        </label>
                        <textarea name="catatan_berkas"
                                  id="catatan_berkas_modal"
                                  rows="2"
                                  x-model="selectedItem.catatan"
                                  placeholder="Contoh: Akta dan Kartu Keluarga asli telah diperiksa langsung."
                                  class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:border-emerald-600 text-sm"></textarea>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalBerkas = false"
                                class="du-btn-action px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl min-h-[44px] transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="du-btn-action px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] inline-flex items-center gap-2">
                            <i class="fas fa-check text-xs" aria-hidden="true"></i>
                            <span>Simpan Status Berkas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
