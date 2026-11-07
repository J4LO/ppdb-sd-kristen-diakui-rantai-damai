@extends('layout.tampilan')

@section('title', 'Pembayaran Pendaftaran')
@section('page-title', 'Pembayaran Pendaftaran')

@push('styles')
<style>
    /* Scoped Styles untuk Halaman Pembayaran Pendaftaran */
    .pay-page,
    .pay-page h1, .pay-page h2, .pay-page h3,
    .pay-page h4, .pay-page p, .pay-page span,
    .pay-page dt, .pay-page dd, .pay-page a,
    .pay-page button, .pay-page select, .pay-page input {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    /* Focus visible dengan outline kontras tinggi (tema hijau/emerald) */
    .pay-btn-action:focus-visible,
    .pay-input-ctrl:focus-visible {
        outline: 2px solid #15803d !important;
        outline-offset: 2px !important;
    }
    .pay-btn-action:focus:not(:focus-visible),
    .pay-input-ctrl:focus:not(:focus-visible) {
        outline: none !important;
        box-shadow: none !important;
    }

    /* Scroll horizontal tabel: pada >= 1100px tabel muat tanpa scrollbar */
    @media (min-width: 1100px) {
        .pay-table-scroll {
            overflow-x: visible !important;
            overflow-y: visible !important;
        }
    }
    @media (max-width: 1099.98px) {
        .pay-table-scroll {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Grid Filter yang selalu tampil di Desktop dan dapat dilipat di Mobile - Selaras dengan Menu Calon Siswa */
    .pay-filter-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }
    @media (min-width: 640px) {
        .pay-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .pay-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            max-width: 672px;
        }
    }
    @media (max-width: 767.98px) {
        .pay-filter-grid:not(.is-open) {
            display: none !important;
        }
    }

    /* Breakpoint tampilan tabel vs kartu: < 900px kartu, >= 900px tabel */
    @media (max-width: 899.98px) {
        .pay-table-view { display: none !important; }
        .pay-cards-view { display: block !important; }
    }
    @media (min-width: 900px) {
        .pay-table-view { display: block !important; }
        .pay-cards-view { display: none !important; }
    }

    /* Responsif Kolom Order ID: >= 1440px kolom sendiri, < 1440px di bawah Calon Siswa */
    @media (min-width: 1440px) {
        .pay-order-col { display: table-cell !important; }
        col.pay-order-col { display: table-column !important; }
        .pay-order-inline { display: none !important; }
    }
    @media (max-width: 1439.98px) {
        .pay-order-col { display: none !important; }
        col.pay-order-col { display: none !important; }
        .pay-order-inline { display: flex !important; }
    }

    /* Prefer reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .pay-page,
        .pay-page * {
            transition: none !important;
            animation: none !important;
            transform: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="pay-page space-y-5 sm:space-y-6"
     x-data="{
         filterOpenMobile: false,
         detailOpen: false,
         selectedData: null,
         copiedNotice: false,
         copiedText: '',
         copyText(text, label = 'Teks') {
             if (!text || text === '-') return;
             navigator.clipboard.writeText(text).then(() => {
                 this.copiedText = label;
                 this.copiedNotice = true;
                 setTimeout(() => { this.copiedNotice = false; }, 2000);
             });
         },
         openDetail(item) {
             this.selectedData = item;
             this.detailOpen = true;
             this.$nextTick(() => {
                 this.$refs.closeDetailBtn?.focus();
             });
         },
         closeDetail() {
             this.detailOpen = false;
         }
     }"
     @keydown.escape.window="if (detailOpen) closeDetail()">

    {{-- Toast Notifikasi Salin --}}
    <div x-show="copiedNotice"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2"
         role="status"
         aria-live="polite"
         style="display: none;">
        <i class="fas fa-check-circle text-emerald-400" aria-hidden="true"></i>
        <span><span x-text="copiedText"></span> berhasil disalin ke papan klip</span>
    </div>

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

    {{-- Kartu Judul Halaman --}}
    <div class="bg-white rounded-2xl px-5 py-4 sm:px-6 sm:py-5 border border-slate-200/80 shadow-xs">
        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 font-heading leading-tight tracking-tight">Pembayaran Pendaftaran</h1>
        <p class="text-slate-600 text-sm sm:text-[15px] mt-0.5">Pantau status pembayaran pendaftaran dari payment gateway.</p>
    </div>

    {{-- Ringkasan Statistik Pembayaran (Satu Query Agregat) --}}
    @php
        $countLunas = (int) ($stats->total_lunas ?? 0);
        $nominalLunas = (float) ($stats->nominal_lunas ?? 0);

        $countMenunggu = (int) ($stats->total_menunggu ?? 0);
        $nominalMenunggu = (float) ($stats->nominal_menunggu ?? 0);

        $countGagal = (int) ($stats->total_gagal ?? 0);
        $nominalGagal = (float) ($stats->nominal_gagal ?? 0);

        $countPerhatian = (int) ($stats->total_perlu_perhatian ?? 0);
    @endphp
    <div class="grid grid-cols-1 min-[420px]:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-stretch">
        {{-- 1. Lunas --}}
        <div class="pay-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Lunas">Lunas</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countLunas > 0 ? 'text-slate-900' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countLunas) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">transaksi</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countLunas > 0 ? 'text-emerald-700 font-semibold' : 'text-slate-400 font-medium' }}">
                    Rp {{ number_format($nominalLunas, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countLunas > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-check-circle text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 2. Menunggu Pembayaran --}}
        <div class="pay-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Menunggu Pembayaran">Menunggu Pembayaran</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countMenunggu > 0 ? 'text-slate-900' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countMenunggu) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">transaksi</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countMenunggu > 0 ? 'text-slate-700' : 'text-slate-400' }}">
                    Rp {{ number_format($nominalMenunggu, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countMenunggu > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-clock text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 3. Gagal / Kedaluwarsa --}}
        <div class="pay-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Gagal / Kedaluwarsa">Gagal / Kedaluwarsa</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countGagal > 0 ? 'text-slate-900' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countGagal) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">transaksi</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countGagal > 0 ? 'text-slate-700' : 'text-slate-400' }}">
                    Rp {{ number_format($nominalGagal, 0, ',', '.') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countGagal > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-times-circle text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 4. Perlu Perhatian --}}
        <div class="pay-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Perlu Perhatian">Perlu Perhatian</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countPerhatian > 0 ? 'text-amber-800' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countPerhatian) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">transaksi</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countPerhatian > 0 ? 'text-amber-800' : 'text-slate-400' }}">
                    {{ $countPerhatian > 0 ? 'Menunggu > 24 jam / gagal' : 'Tidak ada kendala' }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countPerhatian > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-exclamation-triangle text-lg" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian Bar (Selaras dengan Menu Calon Siswa) --}}
    @php
        $activeFilterCount = 0;
        if (!empty($search)) $activeFilterCount++;
        if (!empty($statusFilter)) $activeFilterCount++;
        if (!empty($tahunAjaranFilter)) $activeFilterCount++;

        $statusMap = [
            'Lunas'       => 'Lunas',
            'Pending'     => 'Menunggu Pembayaran',
            'Kadaluarsa'  => 'Kedaluwarsa',
            'Gagal'       => 'Gagal',
        ];
        $selectedStatusLabel = !empty($statusFilter) ? ($statusMap[$statusFilter] ?? $statusFilter) : null;

        $selectedTaName = null;
        if (!empty($tahunAjaranFilter)) {
            $foundTa = $tahunAjaranList->firstWhere('id_tahun_ajaran', $tahunAjaranFilter);
            $selectedTaName = $foundTa ? $foundTa->tahun_ajaran : "ID {$tahunAjaranFilter}";
        }
    @endphp
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <form action="{{ route('index') }}" method="GET" class="space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                {{-- Kolom Pencarian (?search=) & Tombol Cari di sampingnya --}}
                <div class="flex items-center gap-2 flex-1 min-w-0 max-w-2xl">
                    <div class="relative flex-1 min-w-0">
                        <label for="pay-search-input" class="sr-only">Cari Pembayaran</label>
                        <input type="text"
                               id="pay-search-input"
                               name="search"
                               class="pay-input-ctrl w-full min-w-0 bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl pl-10 pr-9 h-11 min-h-[44px] transition-colors focus:bg-white"
                               placeholder="Cari nama, no. registrasi, atau order ID..."
                               value="{{ $search ?? '' }}">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400" aria-hidden="true">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        @if(!empty($search))
                        <a href="{{ route('index', array_merge(request()->except(['search', 'page']))) }}"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                           title="Hapus kata kunci pencarian"
                           aria-label="Hapus kata kunci pencarian">
                            <i class="fas fa-times-circle text-sm" aria-hidden="true"></i>
                        </a>
                        @endif
                    </div>

                    {{-- Tombol Cari & Filter persis di samping input --}}
                    <button type="submit"
                            class="pay-btn-action px-3.5 sm:px-5 h-11 min-h-[44px] text-xs sm:text-sm font-semibold rounded-xl bg-green-700 hover:bg-green-800 text-white shadow-xs flex items-center gap-2 transition-colors shrink-0">
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
                            aria-controls="pay-filter-panel">
                        <i class="fas fa-filter text-xs" aria-hidden="true"></i>
                        <span>Filter</span>
                        @if($activeFilterCount > 0)
                            <span class="w-5 h-5 rounded-full bg-green-700 text-white text-[10px] flex items-center justify-center font-bold">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('index') }}"
                           class="pay-btn-action px-3.5 h-11 min-h-[44px] text-xs sm:text-sm font-medium rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 transition-colors flex items-center gap-1.5"
                           title="Atur ulang seluruh filter">
                            <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                            <span>Atur Ulang</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Grid Kontrol Filter (Selaras dengan Menu Calon Siswa) --}}
            <div id="pay-filter-panel" class="pay-filter-grid" :class="{ 'is-open': filterOpenMobile }">
                {{-- Filter Tahun Ajaran --}}
                <div>
                    <label for="filter-ta" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Tahun Ajaran</label>
                    <select id="filter-ta" name="tahun_ajaran" class="pay-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjaranList as $ta)
                            <option value="{{ $ta->id_tahun_ajaran }}" {{ ($tahunAjaranFilter ?? '') == $ta->id_tahun_ajaran ? 'selected' : '' }}>
                                {{ $ta->tahun_ajaran }} {{ $ta->status === 'Berlangsung' ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status Pembayaran --}}
                <div>
                    <label for="filter-status-bayar" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Pembayaran</label>
                    <select id="filter-status-bayar" name="status" class="pay-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="Lunas" {{ ($statusFilter ?? '') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="Pending" {{ ($statusFilter ?? '') === 'Pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                        <option value="Kadaluarsa" {{ ($statusFilter ?? '') === 'Kadaluarsa' ? 'selected' : '' }}>Kedaluwarsa</option>
                        <option value="Gagal" {{ ($statusFilter ?? '') === 'Gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Chip Filter Aktif (Selaras dengan Menu Calon Siswa) --}}
    @if($activeFilterCount > 0)
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-semibold text-slate-600 mr-1">Filter Aktif:</span>

            @if(!empty($search))
                <a href="{{ route('index', array_merge(request()->except(['search', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Cari: "{{ Str::limit($search, 20) }}"</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedTaName)
                <a href="{{ route('index', array_merge(request()->except(['tahun_ajaran', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Tahun Ajaran: {{ $selectedTaName }}</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedStatusLabel)
                <a href="{{ route('index', array_merge(request()->except(['status', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Status: {{ $selectedStatusLabel }}</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            <a href="{{ route('index') }}"
               class="text-xs text-rose-600 hover:text-rose-800 hover:underline font-medium ml-1">
                Hapus Semua
            </a>
        </div>
    @endif

    {{-- Wadah Tabel Pembayaran --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">

        {{-- DESKTOP & TABLET TABLE (>= 900px) --}}
        <div class="pay-table-view pay-table-scroll">
            <table class="w-full text-sm text-left text-slate-700 border-collapse table-auto">
                <colgroup>
                    <col style="width: 48px; min-width: 48px;">
                    <col style="width: 15%; min-width: 125px;">
                    <col style="width: 18%; min-width: 135px;">
                    <col class="pay-order-col" style="width: 13%; min-width: 120px;">
                    <col style="width: 15%; min-width: 135px;">
                    <col style="width: 12%; min-width: 110px;">
                    <col style="width: 11%; min-width: 105px;">
                    <col style="width: 11%; min-width: 115px;">
                    <col style="width: 120px; min-width: 120px;">
                </colgroup>
                <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="w-[48px] pl-3 pr-1 py-3.5 font-bold text-center text-slate-600" style="text-align: center !important;">NO</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">NO. REGISTRASI</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700">CALON SISWA</th>
                        <th scope="col" class="pay-order-col px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">ORDER ID</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">WAKTU BAYAR</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-left text-slate-700 whitespace-nowrap">METODE</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-right text-slate-700 whitespace-nowrap">NOMINAL</th>
                        <th scope="col" class="px-3 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">STATUS</th>
                        <th scope="col" class="px-3 pr-4 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $no => $value)
                    @php
                        $casis = $value->calonSiswa ?? $value->casis;
                        $namaSiswa = $casis?->nama_calon_siswa ?? ($casis?->nama ?? ('Calon Siswa #' . $value->id_calon_siswa));
                        $noReg = $casis?->no_registrasi ?: '-';

                        $rawStatus = $value->status_pembayaran;
                        $isLunas = in_array($rawStatus, ['Lunas', 'Diterima']);
                        $isMenunggu = in_array($rawStatus, ['Menunggu', 'Pending']);
                        $isKadaluarsa = in_array($rawStatus, ['Kadaluarsa']);
                        $isGagal = in_array($rawStatus, ['Gagal', 'Ditolak']);

                        // Pemetaan Istilah Status Tampilan
                        if ($isLunas) {
                            $statusLabel = 'Lunas';
                            $statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            $statusIcon = 'fa-check-circle text-emerald-600';
                        } elseif ($isMenunggu) {
                            $statusLabel = 'Menunggu Pembayaran';
                            $statusClass = 'bg-amber-50 text-amber-800 border-amber-200';
                            $statusIcon = 'fa-clock text-amber-600';
                        } elseif ($isKadaluarsa) {
                            $statusLabel = 'Kedaluwarsa';
                            $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                            $statusIcon = 'fa-hourglass-end text-slate-500';
                        } elseif ($isGagal) {
                            $statusLabel = 'Gagal';
                            $statusClass = 'bg-rose-50 text-rose-800 border-rose-200';
                            $statusIcon = 'fa-times-circle text-rose-600';
                        } else {
                            $statusLabel = $rawStatus ?: 'Pending';
                            $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                            $statusIcon = 'fa-info-circle text-slate-500';
                        }

                        // Penanda "Perlu Perhatian": Menunggu > 24 jam atau Gagal
                        $hoursSinceCreated = $value->tgl_pembayaran ? \Carbon\Carbon::parse($value->tgl_pembayaran)->diffInHours(now()) : 0;
                        $isMenungguOver24h = $isMenunggu && ($hoursSinceCreated >= 24);
                        $isPerluPerhatian = $isMenungguOver24h || $isGagal;

                        // Waktu Bayar vs Waktu Dibuat
                        // Format: "31 Okt 2026, 13.38 WITA"
                        $waktuBayarFormatted = ($isLunas && $value->waktu_lunas)
                            ? format_school_time($value->waktu_lunas, 'd M Y, H.i')
                            : null;
                        $waktuDibuatFormatted = $value->tgl_pembayaran
                            ? format_school_time($value->tgl_pembayaran, 'd M Y, H.i')
                            : null;

                        // Metode Pembayaran Label Ramah
                        $rawMetode = $value->metode_pembayaran;
                        $vaNumber = $value->va_number;
                        $metodeLabel = null;
                        $metodeIcon = 'fa-credit-card';

                        if (!empty($rawMetode)) {
                            $lowerMetode = strtolower($rawMetode);
                            if (str_contains($lowerMetode, 'bank_transfer') || str_contains($lowerMetode, 'va') || str_contains($lowerMetode, 'echannel')) {
                                $bankName = '';
                                if ($vaNumber) {
                                    $parts = explode('-', $vaNumber);
                                    $bankName = strtoupper(trim($parts[0]));
                                }
                                $metodeLabel = $bankName ? "Virtual Account {$bankName}" : 'Virtual Account';
                                $metodeIcon = 'fa-university';
                            } elseif (str_contains($lowerMetode, 'qris')) {
                                $metodeLabel = 'QRIS';
                                $metodeIcon = 'fa-qrcode';
                            } elseif (str_contains($lowerMetode, 'gopay') || str_contains($lowerMetode, 'shopeepay') || str_contains($lowerMetode, 'dana') || str_contains($lowerMetode, 'ovo')) {
                                $metodeLabel = 'E-wallet';
                                $metodeIcon = 'fa-wallet';
                            } elseif (str_contains($lowerMetode, 'cstore') || str_contains($lowerMetode, 'indomaret') || str_contains($lowerMetode, 'alfamart')) {
                                $metodeLabel = 'Gerai Tunai';
                                $metodeIcon = 'fa-store';
                            } elseif (str_contains($lowerMetode, 'card') || str_contains($lowerMetode, 'credit')) {
                                $metodeLabel = 'Kartu Kredit/Debit';
                                $metodeIcon = 'fa-credit-card';
                            } elseif (str_contains($lowerMetode, 'transfer')) {
                                $metodeLabel = 'Transfer Bank';
                                $metodeIcon = 'fa-money-check-alt';
                            } else {
                                $metodeLabel = Str::title($rawMetode);
                                $metodeIcon = 'fa-money-bill-wave';
                            }
                        }

                        $rawOrderId = $value->order_id ?: '-';
                        $orderIdShort = (strlen($rawOrderId) > 16)
                            ? (substr($rawOrderId, 0, 8) . '...' . substr($rawOrderId, -4))
                            : $rawOrderId;

                        // Payload JSON untuk Modal Detail
                        $detailPayload = [
                            'id_pembayaran' => $value->id_pembayaran,
                            'nama_siswa' => $namaSiswa,
                            'no_registrasi' => $noReg,
                            'order_id' => $rawOrderId,
                            'transaction_id' => $value->transaction_id ?: '-',
                            'nominal' => 'Rp ' . number_format($value->jml_pembayaran ?? 0, 0, ',', '.'),
                            'status' => $statusLabel,
                            'status_mentah' => $rawStatus ?: '-',
                            'metode' => $metodeLabel ?: '-',
                            'va_number' => $vaNumber ?: '-',
                            'waktu_bayar' => $waktuBayarFormatted ?: 'Belum dibayar',
                            'waktu_dibuat' => $waktuDibuatFormatted ?: '-',
                            'is_perlu_perhatian' => $isPerluPerhatian,
                            'perhatian_alasan' => $isMenungguOver24h ? 'Menunggu pembayaran lebih dari 24 jam' : ($isGagal ? 'Transaksi pembayaran berstatus gagal/ditolak' : '')
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors align-middle {{ $isPerluPerhatian ? ($isGagal ? 'border-l-[3px] border-l-rose-500 bg-rose-50/15' : 'border-l-[3px] border-l-amber-500 bg-amber-50/15') : '' }}">
                        {{-- NO --}}
                        <td class="w-[48px] pl-3 pr-1 py-3 text-center font-medium text-slate-600 text-sm align-middle" style="text-align: center !important;">
                            {{ $no + 1 }}
                        </td>

                        {{-- NO. REGISTRASI --}}
                        <td class="px-3 py-3 align-middle text-left whitespace-nowrap">
                            <div class="inline-flex items-center gap-1 font-mono text-[14px] font-semibold text-slate-800">
                                <span>{{ $noReg }}</span>
                                @if($noReg !== '-')
                                    <button type="button"
                                            @click="copyText('{{ $noReg }}', 'Nomor Registrasi')"
                                            class="pay-btn-action text-slate-400 hover:text-slate-700 p-1 rounded-md transition-colors"
                                            title="Salin nomor registrasi"
                                            aria-label="Salin nomor registrasi">
                                        <i class="fas fa-copy text-xs" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- CALON SISWA --}}
                        <td class="px-3 py-3 align-middle text-left">
                            <div class="flex items-center gap-1.5">
                                @if($isPerluPerhatian)
                                    <i class="fas {{ $isGagal ? 'fa-times-circle text-rose-500' : 'fa-exclamation-triangle text-amber-500' }} text-xs shrink-0"
                                       title="{{ $isGagal ? 'Perlu perhatian: Pembayaran gagal atau ditolak' : 'Perlu perhatian: Menunggu pembayaran lebih dari 24 jam' }}"
                                       aria-label="Perlu perhatian"></i>
                                @endif
                                <span class="font-semibold text-slate-900 text-base leading-snug" title="{{ $namaSiswa }}">
                                    {{ $namaSiswa }}
                                </span>
                            </div>
                            {{-- Pada rentang < 1440px (termasuk 1100-1440px), ORDER ID dipindah ke bawah sel CALON SISWA --}}
                            <div class="pay-order-inline mt-1 text-xs text-slate-500 font-mono items-center gap-1.5 flex-wrap">
                                <span class="text-slate-400 font-sans">Order:</span>
                                <span class="font-medium text-slate-700" title="{{ $rawOrderId }}">{{ $orderIdShort }}</span>
                                @if($value->order_id)
                                    <button type="button"
                                            @click="copyText('{{ $rawOrderId }}', 'Order ID')"
                                            class="pay-btn-action text-slate-400 hover:text-slate-700 p-0.5"
                                            title="Salin Order ID"
                                            aria-label="Salin Order ID">
                                        <i class="fas fa-copy text-[10px]" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- ORDER ID (Tampil di kolom sendiri hanya pada >= 1440px) --}}
                        <td class="pay-order-col px-3 py-3 align-middle text-left whitespace-nowrap">
                            <div class="inline-flex items-center gap-1 font-mono text-sm text-slate-700">
                                <span class="font-medium" title="{{ $rawOrderId }}">{{ $orderIdShort }}</span>
                                @if($value->order_id)
                                    <button type="button"
                                            @click="copyText('{{ $rawOrderId }}', 'Order ID')"
                                            class="pay-btn-action text-slate-400 hover:text-slate-700 p-1 rounded-md transition-colors"
                                            title="Salin Order ID"
                                            aria-label="Salin Order ID">
                                        <i class="fas fa-copy text-xs" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- WAKTU BAYAR --}}
                        <td class="px-3 py-3 align-middle text-left whitespace-nowrap text-sm">
                            @if($waktuBayarFormatted)
                                <span class="font-medium text-slate-800" title="Waktu Pembayaran Berhasil">
                                    {{ $waktuBayarFormatted }}
                                </span>
                            @else
                                <div>
                                    <span class="font-medium text-slate-500 block">Belum dibayar</span>
                                    @if($waktuDibuatFormatted)
                                        <span class="block text-xs text-slate-400 mt-0.5" title="Waktu Transaksi Dibuat">
                                            Dibuat {{ $waktuDibuatFormatted }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>

                        {{-- METODE --}}
                        <td class="px-3 py-3 align-middle text-left whitespace-nowrap text-sm">
                            @if($metodeLabel)
                                <div class="inline-flex items-center gap-1.5 text-slate-700">
                                    <span class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 text-[11px]" aria-hidden="true">
                                        <i class="fas {{ $metodeIcon }}"></i>
                                    </span>
                                    <span class="font-medium">{{ $metodeLabel }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 italic font-medium" title="Metode pembayaran belum tercatat">-</span>
                            @endif
                        </td>

                        {{-- NOMINAL --}}
                        <td class="px-3 py-3 align-middle text-right font-semibold text-slate-900 tabular-nums text-[15px] whitespace-nowrap">
                            Rp {{ number_format($value->jml_pembayaran ?? 0, 0, ',', '.') }}
                        </td>

                        {{-- STATUS --}}
                        <td class="px-3 py-3 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border {{ $statusClass }}">
                                <i class="fas {{ $statusIcon }} text-[11px]" aria-hidden="true"></i>
                                <span>{{ $statusLabel }}</span>
                            </span>
                        </td>

                        {{-- AKSI --}}
                        <td class="px-3 pr-4 py-3 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Tombol Detail (Tinggi 44px) --}}
                                <button type="button"
                                        @click="openDetail({{ json_encode($detailPayload) }})"
                                        class="pay-btn-action inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[44px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                        title="Lihat Rincian Pembayaran {{ $namaSiswa }}"
                                        aria-label="Lihat Rincian Pembayaran {{ $namaSiswa }}">
                                    <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                    <span>Detail</span>
                                </button>

                                {{-- Tombol Cek Status Midtrans (Hanya untuk pembayaran yang belum Lunas) --}}
                                @if(!$isLunas && !empty($value->order_id))
                                    <form action="{{ route('pembayaran.check_status', $value->id_pembayaran) }}" method="POST" class="inline-block" onsubmit="return confirm('Periksa status transaksi ke Midtrans untuk Order ID {{ $value->order_id }}?')">
                                        @csrf
                                        <button type="submit"
                                                class="pay-btn-action inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold transition-colors shadow-xs"
                                                title="Cek Status Transaksi ke Midtrans"
                                                aria-label="Cek Status Transaksi ke Midtrans">
                                            <i class="fas fa-sync-alt text-xs" aria-hidden="true"></i>
                                            <span>Cek Status</span>
                                        </button>
                                    </form>
                                @endif

                                {{-- Tombol Hapus: Sembunyikan dari tampilan tabel, form dan route tetap utuh --}}
                                <form action="{{ route('delete', $value->id_pembayaran) }}" method="POST" class="hidden" aria-hidden="true">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" tabindex="-1" class="hidden">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mb-3 border border-slate-200">
                                    <i class="fas {{ !empty($search) ? 'fa-search' : 'fa-file-invoice-dollar' }} text-xl" aria-hidden="true"></i>
                                </div>
                                <h3 class="text-base font-bold text-slate-800 mb-1">
                                    {{ !empty($search) ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada pembayaran.' }}
                                </h3>
                                @if(!empty($search))
                                    <p class="text-slate-500 text-sm mb-4">Tidak ditemukan data pembayaran dengan kata kunci "{{ $search }}".</p>
                                    <a href="{{ route('index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors border border-emerald-200">
                                        <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                                        <span>Hapus pencarian</span>
                                    </a>
                                @else
                                    <p class="text-slate-500 text-sm">Belum ada transaksi pembayaran formulir pendaftaran yang tercatat.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS VIEW (< 900px) --}}
        <div class="pay-cards-view divide-y divide-slate-100">
            @forelse($data as $no => $value)
            @php
                $casis = $value->calonSiswa ?? $value->casis;
                $namaSiswa = $casis?->nama_calon_siswa ?? ($casis?->nama ?? ('Calon Siswa #' . $value->id_calon_siswa));
                $noReg = $casis?->no_registrasi ?: '-';

                $rawStatus = $value->status_pembayaran;
                $isLunas = in_array($rawStatus, ['Lunas', 'Diterima']);
                $isMenunggu = in_array($rawStatus, ['Menunggu', 'Pending']);
                $isKadaluarsa = in_array($rawStatus, ['Kadaluarsa']);
                $isGagal = in_array($rawStatus, ['Gagal', 'Ditolak']);

                if ($isLunas) {
                    $statusLabel = 'Lunas';
                    $statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                    $statusIcon = 'fa-check-circle text-emerald-600';
                } elseif ($isMenunggu) {
                    $statusLabel = 'Menunggu Pembayaran';
                    $statusClass = 'bg-amber-50 text-amber-800 border-amber-200';
                    $statusIcon = 'fa-clock text-amber-600';
                } elseif ($isKadaluarsa) {
                    $statusLabel = 'Kedaluwarsa';
                    $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                    $statusIcon = 'fa-hourglass-end text-slate-500';
                } elseif ($isGagal) {
                    $statusLabel = 'Gagal';
                    $statusClass = 'bg-rose-50 text-rose-800 border-rose-200';
                    $statusIcon = 'fa-times-circle text-rose-600';
                } else {
                    $statusLabel = $rawStatus ?: 'Pending';
                    $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                    $statusIcon = 'fa-info-circle text-slate-500';
                }

                $hoursSinceCreated = $value->tgl_pembayaran ? \Carbon\Carbon::parse($value->tgl_pembayaran)->diffInHours(now()) : 0;
                $isMenungguOver24h = $isMenunggu && ($hoursSinceCreated >= 24);
                $isPerluPerhatian = $isMenungguOver24h || $isGagal;

                $waktuBayarFormatted = ($isLunas && $value->waktu_lunas)
                    ? format_school_time($value->waktu_lunas, 'd M Y, H.i')
                    : null;
                $waktuDibuatFormatted = $value->tgl_pembayaran
                    ? format_school_time($value->tgl_pembayaran, 'd M Y, H.i')
                    : null;

                $rawMetode = $value->metode_pembayaran;
                $vaNumber = $value->va_number;
                $metodeLabel = null;
                $metodeIcon = 'fa-credit-card';

                if (!empty($rawMetode)) {
                    $lowerMetode = strtolower($rawMetode);
                    if (str_contains($lowerMetode, 'bank_transfer') || str_contains($lowerMetode, 'va') || str_contains($lowerMetode, 'echannel')) {
                        $bankName = '';
                        if ($vaNumber) {
                            $parts = explode('-', $vaNumber);
                            $bankName = strtoupper(trim($parts[0]));
                        }
                        $metodeLabel = $bankName ? "Virtual Account {$bankName}" : 'Virtual Account';
                        $metodeIcon = 'fa-university';
                    } elseif (str_contains($lowerMetode, 'qris')) {
                        $metodeLabel = 'QRIS';
                        $metodeIcon = 'fa-qrcode';
                    } elseif (str_contains($lowerMetode, 'gopay') || str_contains($lowerMetode, 'shopeepay') || str_contains($lowerMetode, 'dana') || str_contains($lowerMetode, 'ovo')) {
                        $metodeLabel = 'E-wallet';
                        $metodeIcon = 'fa-wallet';
                    } elseif (str_contains($lowerMetode, 'cstore') || str_contains($lowerMetode, 'indomaret') || str_contains($lowerMetode, 'alfamart')) {
                        $metodeLabel = 'Gerai Tunai';
                        $metodeIcon = 'fa-store';
                    } elseif (str_contains($lowerMetode, 'card') || str_contains($lowerMetode, 'credit')) {
                        $metodeLabel = 'Kartu Kredit/Debit';
                        $metodeIcon = 'fa-credit-card';
                    } elseif (str_contains($lowerMetode, 'transfer')) {
                        $metodeLabel = 'Transfer Bank';
                        $metodeIcon = 'fa-money-check-alt';
                    } else {
                        $metodeLabel = Str::title($rawMetode);
                        $metodeIcon = 'fa-money-bill-wave';
                    }
                }

                $rawOrderId = $value->order_id ?: '-';
                $orderIdShort = (strlen($rawOrderId) > 16)
                    ? (substr($rawOrderId, 0, 8) . '...' . substr($rawOrderId, -4))
                    : $rawOrderId;

                $detailPayloadMobile = [
                    'id_pembayaran' => $value->id_pembayaran,
                    'nama_siswa' => $namaSiswa,
                    'no_registrasi' => $noReg,
                    'order_id' => $rawOrderId,
                    'transaction_id' => $value->transaction_id ?: '-',
                    'nominal' => 'Rp ' . number_format($value->jml_pembayaran ?? 0, 0, ',', '.'),
                    'status' => $statusLabel,
                    'status_mentah' => $rawStatus ?: '-',
                    'metode' => $metodeLabel ?: '-',
                    'va_number' => $vaNumber ?: '-',
                    'waktu_bayar' => $waktuBayarFormatted ?: 'Belum dibayar',
                    'waktu_dibuat' => $waktuDibuatFormatted ?: '-',
                    'is_perlu_perhatian' => $isPerluPerhatian,
                    'perhatian_alasan' => $isMenungguOver24h ? 'Menunggu pembayaran lebih dari 24 jam' : ($isGagal ? 'Transaksi pembayaran berstatus gagal/ditolak' : '')
                ];
            @endphp
            <div class="p-4 sm:p-5 space-y-3 {{ $isPerluPerhatian ? ($isGagal ? 'border-l-4 border-l-rose-500 bg-rose-50/15' : 'border-l-4 border-l-amber-500 bg-amber-50/15') : '' }}">
                {{-- Header Kartu: Nama & Status di Atas --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            @if($isPerluPerhatian)
                                <i class="fas {{ $isGagal ? 'fa-times-circle text-rose-500' : 'fa-exclamation-triangle text-amber-500' }} text-xs shrink-0"
                                   title="{{ $isGagal ? 'Perlu perhatian: Pembayaran gagal atau ditolak' : 'Perlu perhatian: Menunggu pembayaran lebih dari 24 jam' }}"
                                   aria-label="Perlu perhatian"></i>
                            @endif
                            <h2 class="font-bold text-slate-900 text-base leading-snug truncate">{{ $namaSiswa }}</h2>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-mono mt-0.5">
                            <span class="text-slate-400 font-sans">No. Reg:</span>
                            <span class="font-semibold text-slate-800">{{ $noReg }}</span>
                            @if($noReg !== '-')
                                <button type="button" @click="copyText('{{ $noReg }}', 'Nomor Registrasi')" class="text-slate-400 hover:text-slate-700 p-0.5" aria-label="Salin nomor registrasi">
                                    <i class="fas fa-copy text-[10px]" aria-hidden="true"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold border shrink-0 {{ $statusClass }}">
                        <i class="fas {{ $statusIcon }} text-[10px]" aria-hidden="true"></i>
                        <span>{{ $statusLabel }}</span>
                    </span>
                </div>

                {{-- Detail Baris Ringkas: Waktu Bayar, Metode, Nominal, Order ID --}}
                <div class="bg-slate-50/80 rounded-xl p-3 text-xs space-y-2 border border-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Nominal:</span>
                        <span class="font-bold text-slate-900 text-sm tabular-nums">
                            Rp {{ number_format($value->jml_pembayaran ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Waktu Bayar:</span>
                        <span class="font-semibold text-slate-800 text-right">
                            @if($waktuBayarFormatted)
                                {{ $waktuBayarFormatted }}
                            @else
                                <span class="text-slate-600 font-medium">Belum dibayar</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Metode:</span>
                        <span class="font-medium text-slate-800 text-right">
                            {{ $metodeLabel ?: '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Order ID:</span>
                        <div class="flex items-center gap-1 font-mono text-slate-700">
                            <span class="font-medium" title="{{ $rawOrderId }}">{{ $orderIdShort }}</span>
                            @if($value->order_id)
                                <button type="button" @click="copyText('{{ $rawOrderId }}', 'Order ID')" class="text-slate-400 hover:text-slate-700 p-1 rounded-md" aria-label="Salin Order ID" title="Salin Order ID">
                                    <i class="fas fa-copy text-[11px]" aria-hidden="true"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi di Bawah (Area Sentuh Minimum 44px) --}}
                <div class="pt-1 flex items-center justify-end gap-2 flex-wrap sm:flex-nowrap">
                    @if(!$isLunas && !empty($value->order_id))
                        <form action="{{ route('pembayaran.check_status', $value->id_pembayaran) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Periksa status transaksi ke Midtrans untuk Order ID {{ $value->order_id }}?')">
                            @csrf
                            <button type="submit"
                                    class="pay-btn-action w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold transition-colors min-h-[44px]">
                                <i class="fas fa-sync-alt text-xs" aria-hidden="true"></i>
                                <span>Cek Status</span>
                            </button>
                        </form>
                    @endif
                    <button type="button"
                            @click="openDetail({{ json_encode($detailPayloadMobile) }})"
                            class="pay-btn-action w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition-colors min-h-[44px]"
                            aria-label="Lihat detail pembayaran {{ $namaSiswa }}">
                        <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                        <span>Lihat Detail</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mx-auto mb-3 border border-slate-200">
                    <i class="fas {{ !empty($search) ? 'fa-search' : 'fa-file-invoice-dollar' }} text-xl" aria-hidden="true"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-1">
                    {{ !empty($search) ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada pembayaran.' }}
                </h3>
                @if(!empty($search))
                    <a href="{{ route('index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 underline mt-2 inline-block">
                        Hapus pencarian
                    </a>
                @endif
            </div>
            @endforelse
        </div>

        {{-- PAGINATION & INFO FOOTER --}}
        <div class="px-5 py-4 border-t border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-600">
            <div>
                Menampilkan <span class="font-semibold text-slate-800">{{ $data->firstItem() ?? 0 }}</span> - <span class="font-semibold text-slate-800">{{ $data->lastItem() ?? 0 }}</span> dari total <span class="font-semibold text-slate-800">{{ $data->total() }}</span> pembayaran
            </div>
            @if($data->hasPages())
                <div>
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL DIALOG DETAIL PEMBAYARAN (Aksesibel & Sesuai Standar) --}}
    <div x-show="detailOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-detail-title"
         style="display: none;">

        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all"
             style="max-width: 520px;"
             @click.away="closeDetail()">

            {{-- Header Modal --}}
            <div class="px-5 py-4 sm:px-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <i class="fas fa-receipt text-sm" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 id="modal-detail-title" class="text-base sm:text-lg font-bold text-slate-900 font-heading leading-tight">
                            Rincian Pembayaran
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Informasi transaksi payment gateway pendaftaran.</p>
                    </div>
                </div>
                <button type="button"
                        x-ref="closeDetailBtn"
                        @click="closeDetail()"
                        class="pay-btn-action w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors min-h-[44px] min-w-[44px]"
                        aria-label="Tutup dialog rincian pembayaran">
                    <i class="fas fa-times text-sm" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Konten Modal --}}
            <div class="p-5 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <template x-if="selectedData">
                    <div class="space-y-4 text-xs sm:text-sm">
                        {{-- Banner Perlu Perhatian --}}
                        <template x-if="selectedData.is_perlu_perhatian">
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-amber-900">
                                <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 text-sm shrink-0" aria-hidden="true"></i>
                                <div>
                                    <span class="font-bold block">Perlu Perhatian</span>
                                    <span class="text-xs" x-text="selectedData.perhatian_alasan"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Rincian Field Transaksi --}}
                        <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 border border-slate-100">
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Calon Siswa:</span>
                                <span class="font-bold text-slate-900 text-right" x-text="selectedData.nama_siswa"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">No. Registrasi:</span>
                                <span class="font-mono font-semibold text-slate-800" x-text="selectedData.no_registrasi"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Order ID:</span>
                                <div class="flex items-center gap-1">
                                    <span class="font-mono font-semibold text-slate-800" x-text="selectedData.order_id"></span>
                                    <button type="button" @click="copyText(selectedData.order_id, 'Order ID')" class="text-slate-400 hover:text-slate-700 p-1" aria-label="Salin Order ID">
                                        <i class="fas fa-copy text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">ID Transaksi (Midtrans):</span>
                                <div class="flex items-center gap-1">
                                    <span class="font-mono text-slate-700" x-text="selectedData.transaction_id"></span>
                                    <button type="button" x-show="selectedData.transaction_id !== '-'" @click="copyText(selectedData.transaction_id, 'ID Transaksi')" class="text-slate-400 hover:text-slate-700 p-1" aria-label="Salin ID Transaksi">
                                        <i class="fas fa-copy text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Status Tampilan:</span>
                                <span class="font-semibold text-slate-800" x-text="selectedData.status"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Status Mentah Database:</span>
                                <span class="font-mono text-slate-600 text-xs" x-text="selectedData.status_mentah"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Metode Pembayaran:</span>
                                <span class="font-semibold text-slate-800" x-text="selectedData.metode"></span>
                            </div>
                            <template x-if="selectedData.va_number && selectedData.va_number !== '-'">
                                <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                    <span class="text-slate-500 font-medium">Nomor Virtual Account:</span>
                                    <div class="flex items-center gap-1">
                                        <span class="font-mono font-bold text-emerald-800" x-text="selectedData.va_number"></span>
                                        <button type="button" @click="copyText(selectedData.va_number, 'Nomor VA')" class="text-slate-400 hover:text-slate-700 p-1" aria-label="Salin nomor VA">
                                            <i class="fas fa-copy text-xs" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Waktu Transaksi Dibuat:</span>
                                <span class="text-slate-700" x-text="selectedData.waktu_dibuat"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500 font-medium">Waktu Bayar (Lunas):</span>
                                <span class="font-semibold text-slate-800" x-text="selectedData.waktu_bayar"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 pt-2">
                                <span class="text-slate-700 font-bold">Total Nominal:</span>
                                <span class="font-bold text-emerald-700 text-base tabular-nums" x-text="selectedData.nominal"></span>
                            </div>
                        </div>

                        {{-- Tombol Cek Status Midtrans di dalam Modal (Hanya jika belum Lunas) --}}
                        <template x-if="selectedData && selectedData.status !== 'Lunas' && selectedData.order_id !== '-'">
                            <form :action="'/admin/pembayaran/check-status/' + selectedData.id_pembayaran" method="POST" class="pt-2" onsubmit="return confirm('Periksa status transaksi ke Midtrans untuk Order ID ' + selectedData.order_id + '?')">
                                @csrf
                                <button type="submit"
                                        class="pay-btn-action w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                                    <i class="fas fa-sync-alt text-xs" aria-hidden="true"></i>
                                    <span>Cek Status Terkini ke Midtrans</span>
                                </button>
                            </form>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Footer Modal --}}
            <div class="px-5 py-3 sm:px-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button"
                        @click="closeDetail()"
                        class="pay-btn-action px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl text-xs sm:text-sm font-semibold transition-colors min-h-[44px]">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
