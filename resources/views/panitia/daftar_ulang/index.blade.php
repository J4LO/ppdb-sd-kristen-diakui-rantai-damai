@extends('layout.tampilan')

@section('title', 'Daftar Ulang & Uang Pangkal')
@section('page-title', 'Daftar Ulang & Uang Pangkal')

@push('styles')
<style>
    /* Scoped Styles untuk Halaman Daftar Ulang & Uang Pangkal */
    .du-page,
    .du-page h1, .du-page h2, .du-page h3,
    .du-page h4, .du-page p, .du-page span,
    .du-page dt, .du-page dd, .du-page a,
    .du-page button, .du-page select, .du-page input, .du-page textarea {
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

    /* Scroll horizontal tabel: pada >= 1100px tabel muat tanpa scrollbar */
    @media (min-width: 1100px) {
        .du-table-scroll {
            overflow-x: visible !important;
            overflow-y: visible !important;
        }
    }
    @media (max-width: 1099.98px) {
        .du-table-scroll {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Grid Filter yang selalu tampil di Desktop dan dapat dilipat di Mobile */
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

    /* Breakpoint tampilan tabel vs kartu: < 800px kartu, >= 800px tabel */
    @media (max-width: 799.98px) {
        .du-table-view { display: none !important; }
        .du-cards-view { display: block !important; }
    }
    @media (min-width: 800px) {
        .du-table-view { display: block !important; }
        .du-cards-view { display: none !important; }
    }

    /* Baris dengan status Terlambat: Garis merah kiri 3px */
    .du-row-terlambat {
        border-left: 3px solid #e11d48 !important;
    }

    /* Hilangkan spinner number input pada field nominal */
    .du-no-spinner::-webkit-inner-spin-button,
    .du-no-spinner::-webkit-outer-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
    }
    .du-no-spinner {
        -moz-appearance: textfield !important;
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
         nominalInput: '',
         tglBayarInput: '{{ now()->toDateString() }}',
         catatanBayar: '',
         catatanBerkas: '',
         jenisBerkasPilihan: 'Dokumen Asli',
         statusBerkasPilihan: 'Diterima',
         nominalError: '',
         activePreset: null,
         
         getCicilan1Nominal() {
             if (!this.selectedItem) return 0;
             const min1 = Number(this.selectedItem.minCicilan1) || 500000;
             const terbayar = Number(this.selectedItem.totalTerbayar) || 0;
             const sisa = Number(this.selectedItem.sisaBiaya) || 0;
             if (terbayar >= min1 || sisa <= 0) return 0;
             return Math.min(min1 - terbayar, sisa);
         },
         
         openModalBayar(item) {
             this.selectedItem = item;
             this.catatanBayar = '';
             this.nominalError = '';
             this.tglBayarInput = '{{ now()->toDateString() }}';
             
             const cicil1Target = this.getCicilan1Nominal();
             if (cicil1Target > 0) {
                 this.nominalInput = cicil1Target;
                 this.activePreset = 'cicil1';
             } else if (item.sisaBiaya > 0) {
                 this.nominalInput = item.sisaBiaya;
                 this.activePreset = 'lunas';
             } else {
                 this.nominalInput = '';
                 this.activePreset = null;
             }
             this.validateNominal();
             
             this.modalBayar = true;
             document.body.classList.add('overflow-hidden');
             this.$nextTick(() => {
                 if (this.$refs.btnBatalBayar) this.$refs.btnBatalBayar.focus();
             });
         },
         closeModalBayar() {
             this.modalBayar = false;
             this.activePreset = null;
             document.body.classList.remove('overflow-hidden');
         },
         validateNominal() {
             const val = Number(this.nominalInput);
             const sisa = this.selectedItem ? Number(this.selectedItem.sisaBiaya) : 0;
             if (this.nominalInput === '' || this.nominalInput === null) {
                 this.nominalError = 'Nominal pembayaran wajib diisi.';
             } else if (isNaN(val) || val <= 0) {
                 this.nominalError = 'Nominal pembayaran tidak boleh nol atau negatif.';
             } else if (val < 1000) {
                 this.nominalError = 'Nominal pembayaran minimal Rp 1.000.';
             } else if (val > sisa) {
                 this.nominalError = 'Nominal pembayaran melebihi sisa tagihan (Rp ' + new Intl.NumberFormat('id-ID').format(sisa) + ').';
             } else {
                 this.nominalError = '';
             }
             
             // Sinkronisasi status activePreset
             const c1 = this.getCicilan1Nominal();
             if (c1 > 0 && val === c1) {
                 this.activePreset = 'cicil1';
             } else if (sisa > 0 && val === sisa) {
                 this.activePreset = 'lunas';
             } else {
                 this.activePreset = null;
             }
         },
         onNominalChange() {
             this.validateNominal();
         },
         isNominalValid() {
             const val = Number(this.nominalInput);
             const sisa = this.selectedItem ? Number(this.selectedItem.sisaBiaya) : 0;
             return !isNaN(val) && val >= 1000 && val <= sisa && this.nominalError === '';
         },
         setPresetNominal(type) {
             if (!this.selectedItem) return;
             if (type === 'cicil1') {
                 const cicil1Target = this.getCicilan1Nominal();
                 if (cicil1Target > 0) {
                     this.nominalInput = cicil1Target;
                     this.activePreset = 'cicil1';
                 }
             } else if (type === 'lunas') {
                 if (this.selectedItem.sisaBiaya > 0) {
                     this.nominalInput = this.selectedItem.sisaBiaya;
                     this.activePreset = 'lunas';
                 }
             }
             this.validateNominal();
         },
         formatIndonesianDate(dateStr) {
             if (!dateStr) return '';
             try {
                 const parts = dateStr.split('-');
                 if (parts.length === 3) {
                     const year = parseInt(parts[0], 10);
                     const month = parseInt(parts[1], 10) - 1;
                     const day = parseInt(parts[2], 10);
                     const d = new Date(year, month, day);
                     if (isNaN(d.getTime())) return '';
                     return new Intl.DateTimeFormat('id-ID', {
                         weekday: 'long',
                         day: 'numeric',
                         month: 'short',
                         year: 'numeric'
                     }).format(d);
                 }
                 return '';
             } catch (e) {
                 return '';
             }
         },
         trapFocus(e) {
             if (!this.modalBayar) return;
             const modalEl = document.querySelector('[role=\'alertdialog\'][aria-labelledby=\'modal-bayar-title\']');
             if (!modalEl) return;
             const focusable = modalEl.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])');
             if (!focusable.length) return;
             const first = focusable[0];
             const last = focusable[focusable.length - 1];

             if (e.shiftKey) {
                 if (document.activeElement === first) {
                     e.preventDefault();
                     last.focus();
                 }
             } else {
                 if (document.activeElement === last) {
                     e.preventDefault();
                     first.focus();
                 }
             }
         },
         
         openModalBerkas(item) {
             this.selectedItem = item;
             this.statusBerkasPilihan = item.statusBerkas === 'Diterima' ? 'Diterima' : 'Diterima';
             this.jenisBerkasPilihan = item.jenisBerkas || 'Dokumen Asli';
             this.catatanBerkas = item.catatanBerkas || '';
             this.modalBerkas = true;
             document.body.classList.add('overflow-hidden');
             this.$nextTick(() => {
                 if (this.$refs.btnBatalBerkas) this.$refs.btnBatalBerkas.focus();
             });
         },
         closeModalBerkas() {
             this.modalBerkas = false;
             document.body.classList.remove('overflow-hidden');
         }
     }"
     @keydown.escape.window="if (modalBayar) closeModalBayar(); if (modalBerkas) closeModalBerkas();">

    {{-- Alert Notifikasi --}}
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl relative flex items-center justify-between shadow-xs" role="alert" aria-live="polite">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle text-emerald-600 text-base" aria-hidden="true"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600 hover:bg-emerald-100/60 transition-colors" aria-label="Tutup pemberitahuan">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>
    </div>
    @elseif (session('error'))
    <div x-data="{ show: true }" x-show="show" class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl relative flex items-center justify-between shadow-xs" role="alert" aria-live="assertive">
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
        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 font-heading leading-tight tracking-tight">Daftar Ulang &amp; Uang Pangkal</h1>
        <p class="text-slate-600 text-sm sm:text-[15px] mt-0.5">Kelola administrasi daftar ulang, verifikasi berkas fisik, dan pemantauan setoran uang pangkal.</p>
    </div>

    {{-- Ringkasan Statistik 5 Kolom --}}
    @php
        $countLulus = (int) ($summary['totalLulus'] ?? 0);
        $nominalTagihan = (float) ($summary['totalTagihan'] ?? 0);

        $countBelumBayar = (int) ($summary['totalBelumBayar'] ?? 0);

        $countSelesai = (int) ($summary['totalSelesai'] ?? 0);
        $nominalTerbayar = (float) ($summary['totalTerbayar'] ?? 0);

        $countMenungguBerkas = (int) ($summary['totalMenungguBerkas'] ?? 0);

        $countTerlambat = (int) ($summary['totalTerlambat'] ?? 0);
        $nominalSisa = (float) ($summary['totalSisa'] ?? 0);
    @endphp
    <div class="grid grid-cols-1 min-[420px]:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 items-stretch">
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

        {{-- 2. Belum Bayar (Kartu Baru Sesuai Spesifikasi) --}}
        <div class="du-stat-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[96px]">
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block leading-4 truncate h-4" title="Belum Membayar Cicilan Pertama">Belum Bayar</span>
                <div class="mt-1 flex items-baseline gap-1.5 leading-none h-7">
                    <span class="text-2xl sm:text-[28px] font-extrabold {{ $countBelumBayar > 0 ? 'text-amber-600' : 'text-slate-400' }} leading-none tabular-nums">
                        {{ number_format($countBelumBayar) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-medium text-slate-500">siswa</span>
                </div>
                <div class="mt-1 text-xs sm:text-sm font-medium leading-5 h-5 truncate {{ $countBelumBayar > 0 ? 'text-amber-700 font-semibold' : 'text-slate-400' }}">
                    Belum cicilan awal
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countBelumBayar > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-hand-holding-usd text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 3. Daftar Ulang Selesai --}}
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

        {{-- 4. Menunggu Berkas --}}
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
                    Perlu verifikasi fisik
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $countMenungguBerkas > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 ml-3">
                <i class="fas fa-folder-open text-lg" aria-hidden="true"></i>
            </div>
        </div>

        {{-- 5. Sisa Tagihan & Terlambat --}}
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

    {{-- Filter & Pencarian Bar --}}
    @php
        $searchVal = request('search');
        $filterTaVal = request('tahun_ajaran');
        $filterKeuanganVal = request('status_keuangan');
        $filterTahap2Val = request('pelunasan_tahap2');
        $filterBerkasVal = request('status_berkas');
        $filterDuVal = request('status_daftar_ulang');

        $activeFilterCount = 0;
        if (!empty($searchVal)) $activeFilterCount++;
        if (!empty($filterTaVal)) $activeFilterCount++;
        if (!empty($filterKeuanganVal)) $activeFilterCount++;
        if (!empty($filterTahap2Val)) $activeFilterCount++;
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

            {{-- Grid Kontrol Filter (4 Kolom Sesuai Label Saat Ini) --}}
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

                {{-- Filter Status Keuangan (Termasuk Opsi Terlambat Tahap 2 yang didukung Controller) --}}
                {{-- TODO: Controller mendukung 'pelunasan_tahap2' secara langsung ($request->pelunasan_tahap2). Opsi 'Terlambat' di bawah mengirim parameter pelunasan_tahap2 agar filter jatuh tempo berjalan aman tanpa modifikasi backend controller. --}}
                <div x-data="{
                    pilihan: '{{ $filterTahap2Val === 'Terlambat' ? 'Terlambat' : ($filterKeuanganVal ?? '') }}',
                    handleKeuanganChange(e) {
                        const val = e.target.value;
                        if (val === 'Terlambat') {
                            $refs.hiddenTahap2Input.value = 'Terlambat';
                            $refs.selectKeuanganInput.name = '';
                        } else {
                            $refs.hiddenTahap2Input.value = '';
                            $refs.selectKeuanganInput.name = 'status_keuangan';
                        }
                    }
                }">
                    <input type="hidden" name="pelunasan_tahap2" x-ref="hiddenTahap2Input" value="{{ $filterTahap2Val }}">
                    <label for="filter-du-keuangan" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Keuangan</label>
                    <select id="filter-du-keuangan"
                            x-ref="selectKeuanganInput"
                            name="{{ $filterTahap2Val === 'Terlambat' ? '' : 'status_keuangan' }}"
                            x-model="pilihan"
                            @change="handleKeuanganChange($event)"
                            class="du-input-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white">
                        <option value="">Semua Status Keuangan</option>
                        <option value="Belum Bayar">Belum Bayar</option>
                        <option value="Cicilan Awal Terpenuhi">Cicilan Awal Terpenuhi</option>
                        <option value="Lunas">Lunas</option>
                        <option value="Terlambat">Tahap 2 Terlambat</option>
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

    {{-- Chip Filter Aktif --}}
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

            @if(!empty($filterTahap2Val))
                <a href="{{ route('admin.daftar_ulang.index', array_merge(request()->except(['pelunasan_tahap2', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 transition-colors font-medium">
                   <span>Tahap 2: {{ $filterTahap2Val }}</span>
                   <i class="fas fa-times text-[10px] text-rose-500 hover:text-rose-800" aria-hidden="true"></i>
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

        {{-- DESKTOP & TABLET TABLE (>= 800px) --}}
        <div class="du-table-view du-table-scroll">
            <table class="w-full text-sm text-left text-slate-700 border-collapse table-auto">
                <colgroup>
                    <col style="width: 48px; min-width: 48px;">
                    <col style="width: 14%; min-width: 135px;">
                    <col style="width: 18%; min-width: 155px;">
                    <col style="width: 20%; min-width: 175px;">
                    <col style="width: 12%; min-width: 110px;">
                    <col style="width: 10%; min-width: 95px;">
                    <col style="width: 10%; min-width: 95px;">
                    <col style="width: 16%; min-width: 170px;">
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
                        $isTerlambat = ($item->statusTahap2 === 'Terlambat');
                        $hasDuRecord = !empty($item->daftarUlang);
                        $cicilan1Terpenuhi = ($item->totalTerbayar >= $item->minCicilan1);
                        $berkasDiterima = ($item->statusBerkas === 'Diterima');
                        $isLunasSelesai = ($item->sisaBiaya <= 0 && $item->statusDaftarUlang === 'Selesai');
                    @endphp
                    <tr class="hover:bg-slate-50/75 transition-colors {{ $isTerlambat ? 'du-row-terlambat bg-rose-50/20' : '' }}">
                        {{-- NO (Dengan penanda border kiri merah 3px bila terlambat) --}}
                        <td class="pl-3 pr-1 py-3.5 align-middle text-center font-medium text-slate-500 text-xs {{ $isTerlambat ? 'border-l-[3px] border-l-rose-600' : '' }}" style="text-align: center !important;">
                            {{ $daftarUlang->firstItem() + $index }}
                        </td>

                        {{-- NO. PENDAFTARAN (Kolom Mandiri Monospace) --}}
                        <td class="px-3 py-3.5 align-middle text-left font-mono text-sm text-slate-800 whitespace-nowrap">
                            <span class="font-semibold tracking-tight" title="{{ $noReg }}">{{ $noReg }}</span>
                        </td>

                        {{-- CALON SISWA (Nama 16px semibold) --}}
                        <td class="px-3 py-3.5 align-middle">
                            <span class="font-semibold text-slate-900 text-base leading-snug">{{ $nama }}</span>
                            {{-- Nomor HP Orang Tua bila tersedia --}}
                            @if(!empty($casis->no_hp))
                                <span class="block text-xs text-slate-500 font-mono mt-0.5">
                                    <i class="fab fa-whatsapp text-emerald-600 mr-1" aria-hidden="true"></i>{{ $casis->no_hp }}
                                </span>
                            @else
                                {{-- TODO: Nomor HP orang tua tidak tersedia pada calon siswa ini --}}
                            @endif
                        </td>

                        {{-- PEMBAYARAN: Bilah Progres (8px, Hijau) + Teks Terbayar/Total + Badge Keuangan --}}
                        <td class="px-3 py-3.5 align-middle text-left">
                            <div class="space-y-1.5 min-w-[155px]">
                                {{-- Teks Nominal di atas: Rp {terbayar} / Rp {total} --}}
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-800 tabular-nums">
                                    <span>Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}</span>
                                    <span class="text-slate-400 font-normal">/ Rp {{ number_format($item->totalBiaya, 0, ',', '.') }}</span>
                                </div>
                                
                                {{-- Bilah Progres tipis (8px, hijau) --}}
                                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden" role="progressbar" aria-valuenow="{{ $item->persenTerbayar }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="bg-emerald-600 h-2 rounded-full transition-all duration-300" style="width: {{ $item->persenTerbayar }}%;"></div>
                                </div>

                                {{-- Badge Status Keuangan di bawahnya --}}
                                <div>
                                    @if($item->statusKeuangan === 'Lunas')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <i class="fas fa-check-circle text-[10px] text-emerald-600" aria-hidden="true"></i> Lunas
                                        </span>
                                    @elseif($item->statusKeuangan === 'Cicilan Awal Terpenuhi')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                            <i class="fas fa-info-circle text-[10px] text-blue-600" aria-hidden="true"></i> Cicilan Awal
                                        </span>
                                    @elseif($item->statusKeuangan === 'Sebagian')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            <i class="fas fa-hourglass-half text-[10px] text-amber-600" aria-hidden="true"></i> Sebagian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <i class="fas fa-circle-notch text-[10px] text-slate-500" aria-hidden="true"></i> Belum Bayar
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- TAHAP 2 --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            @if($item->statusTahap2 === 'Lunas')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check text-[10px] text-emerald-600" aria-hidden="true"></i> Lunas
                                </span>
                            @elseif($item->statusTahap2 === 'Terlambat')
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <i class="fas fa-exclamation-circle text-rose-600 text-[11px]" aria-hidden="true"></i> Terlambat
                                    </span>
                                    @if($item->tglJatuhTempo)
                                        <span class="block text-[11px] text-rose-600 mt-0.5 font-semibold tabular-nums">
                                            {{ \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            @elseif($item->statusTahap2 === 'Jatuh Tempo')
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock text-amber-600 text-[10px]" aria-hidden="true"></i> Hari Ini
                                    </span>
                                    @if($item->tglJatuhTempo)
                                        <span class="block text-[11px] text-amber-700 mt-0.5 font-medium tabular-nums">
                                            {{ \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            @elseif($item->statusTahap2 === 'Belum Jatuh Tempo')
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fas fa-calendar-alt text-blue-600 text-[10px]" aria-hidden="true"></i> Belum JT
                                    </span>
                                    @if($item->tglJatuhTempo)
                                        <span class="block text-[11px] text-slate-500 mt-0.5 tabular-nums">
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
                            @elseif($item->statusBerkas === 'Ditolak')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                    <i class="fas fa-times-circle text-rose-600 text-[11px]" aria-hidden="true"></i>
                                    <span>Ditolak</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fas fa-clock text-amber-600 text-[11px]" aria-hidden="true"></i>
                                    <span>Menunggu</span>
                                </span>
                            @endif
                        </td>

                        {{-- STATUS DAFTAR ULANG --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            @if($item->statusDaftarUlang === 'Selesai')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-double text-emerald-600 text-[10px]" aria-hidden="true"></i> Selesai
                                </span>
                            @elseif($item->statusDaftarUlang === 'Batal')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    <i class="fas fa-times-circle text-rose-600 text-[10px]" aria-hidden="true"></i> Batal
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-hourglass-half text-slate-500 text-[10px]" aria-hidden="true"></i> Menunggu
                                </span>
                            @endif
                        </td>

                        {{-- AKSI: Tepat 2 Tombol per baris disesuaikan kondisi service --}}
                        <td class="px-3 pr-4 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            <div class="flex items-center justify-center gap-1.5">

                                {{-- Kondisi 1: Belum ada catatan daftar ulang --}}
                                @if(!$hasDuRecord)
                                    {{-- TODO: Route backend mandiri untuk 'Mulai Daftar Ulang' tanpa pembayaran/berkas belum tersedia di controller. Saat admin mencatat setoran tunai pertama atau menerima berkas, record Daftar Ulang otomatis dibuat oleh controller (resolveCasisAndDaftarUlang). Menampilkan tombol Catat Bayar sebagai aksi utama dan Detail sebagai aksi sekunder. --}}
                                    <button type="button"
                                            @click="openModalBayar({
                                                id: '{{ $item->id }}',
                                                nama: '{{ addslashes($nama) }}',
                                                noReg: '{{ $noReg }}',
                                                totalBiaya: {{ $item->totalBiaya }},
                                                totalTerbayar: {{ $item->totalTerbayar }},
                                                sisaBiaya: {{ $item->sisaBiaya }},
                                                minCicilan1: {{ $item->minCicilan1 }}
                                            })"
                                            class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-green-700 hover:bg-green-800 text-white text-xs font-semibold transition-colors shadow-xs"
                                            title="Catat setoran pembayaran tunai kasir">
                                        <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                                        <span>Catat Bayar</span>
                                    </button>

                                    <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                                       class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                       title="Buka rincian administrasi daftar ulang {{ $nama }}">
                                        <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>

                                {{-- Kondisi 5: Lunas dan Selesai (Detail + Kuitansi) --}}
                                @elseif($isLunasSelesai)
                                    <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                                       class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                       title="Buka rincian administrasi daftar ulang {{ $nama }}">
                                        <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>

                                    <a href="{{ route('admin.daftar_ulang.kuitansi', $item->id) }}"
                                       target="_blank"
                                       class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold transition-colors shadow-xs"
                                       title="Cetak Kuitansi Pembayaran Daftar Ulang">
                                        <i class="fas fa-receipt text-xs text-emerald-600" aria-hidden="true"></i>
                                        <span>Kuitansi</span>
                                    </a>

                                {{-- Kondisi 6: Tahap 2 Terlambat & Belum Lunas (Catat Bayar Merah Tua + Detail) --}}
                                @elseif($isTerlambat && $item->sisaBiaya > 0)
                                    <button type="button"
                                            @click="openModalBayar({
                                                id: '{{ $item->id }}',
                                                nama: '{{ addslashes($nama) }}',
                                                noReg: '{{ $noReg }}',
                                                totalBiaya: {{ $item->totalBiaya }},
                                                totalTerbayar: {{ $item->totalTerbayar }},
                                                sisaBiaya: {{ $item->sisaBiaya }},
                                                minCicilan1: {{ $item->minCicilan1 }}
                                            })"
                                            class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold transition-colors shadow-xs"
                                            title="Catat setoran pelunasan tahap 2 yang terlambat">
                                        <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                                        <span>Catat Bayar</span>
                                    </button>

                                    <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                                       class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                       title="Buka rincian administrasi daftar ulang {{ $nama }}">
                                        <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>

                                {{-- Kondisi 3: Cicilan pertama terpenuhi, berkas belum diterima (Catat Bayar + Terima Berkas) --}}
                                @elseif($cicilan1Terpenuhi && !$berkasDiterima)
                                    @if($item->sisaBiaya > 0)
                                        <button type="button"
                                                @click="openModalBayar({
                                                    id: '{{ $item->id }}',
                                                    nama: '{{ addslashes($nama) }}',
                                                    noReg: '{{ $noReg }}',
                                                    totalBiaya: {{ $item->totalBiaya }},
                                                    totalTerbayar: {{ $item->totalTerbayar }},
                                                    sisaBiaya: {{ $item->sisaBiaya }},
                                                    minCicilan1: {{ $item->minCicilan1 }}
                                                })"
                                                class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-green-700 hover:bg-green-800 text-white text-xs font-semibold transition-colors shadow-xs"
                                                title="Catat setoran pembayaran tunai">
                                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                                            <span>Catat Bayar</span>
                                        </button>
                                    @else
                                        <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                                           class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                           title="Buka rincian administrasi daftar ulang {{ $nama }}">
                                            <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                            <span>Detail</span>
                                        </a>
                                    @endif

                                    <button type="button"
                                            @click="openModalBerkas({
                                                id: '{{ $item->id }}',
                                                nama: '{{ addslashes($nama) }}',
                                                noReg: '{{ $noReg }}',
                                                statusBerkas: '{{ $item->statusBerkas }}',
                                                jenisBerkas: '{{ $item->jenisBerkas ?? 'Dokumen Asli' }}',
                                                catatanBerkas: '{{ addslashes($item->daftarUlang?->catatan_berkas ?? '') }}'
                                            })"
                                            class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 text-xs font-semibold transition-colors shadow-xs"
                                            title="Verifikasi dan terima berkas fisik calon siswa">
                                        <i class="fas fa-folder-open text-xs text-amber-600" aria-hidden="true"></i>
                                        <span>Terima Berkas</span>
                                    </button>

                                {{-- Kondisi 2 & 4: Ada catatan, belum cicilan pertama ATAU cicil 1 sudah & berkas sudah tapi belum lunas (Catat Bayar + Detail) --}}
                                @else
                                    @if($item->sisaBiaya > 0)
                                        <button type="button"
                                                @click="openModalBayar({
                                                    id: '{{ $item->id }}',
                                                    nama: '{{ addslashes($nama) }}',
                                                    noReg: '{{ $noReg }}',
                                                    totalBiaya: {{ $item->totalBiaya }},
                                                    totalTerbayar: {{ $item->totalTerbayar }},
                                                    sisaBiaya: {{ $item->sisaBiaya }},
                                                    minCicilan1: {{ $item->minCicilan1 }}
                                                })"
                                                class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-green-700 hover:bg-green-800 text-white text-xs font-semibold transition-colors shadow-xs"
                                                title="Catat setoran pembayaran tunai">
                                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                                            <span>Catat Bayar</span>
                                        </button>
                                    @else
                                        <a href="{{ route('admin.daftar_ulang.kuitansi', $item->id) }}"
                                           target="_blank"
                                           class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold transition-colors shadow-xs"
                                           title="Cetak Kuitansi">
                                            <i class="fas fa-receipt text-xs text-emerald-600" aria-hidden="true"></i>
                                            <span>Kuitansi</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                                       class="du-btn-action inline-flex items-center justify-center gap-1 px-2.5 py-1.5 min-h-[38px] rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 text-xs font-semibold transition-colors shadow-xs"
                                       title="Buka rincian administrasi daftar ulang {{ $nama }}">
                                        <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>
                                @endif

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
                                @if($activeFilterCount > 0)
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

        {{-- MOBILE CARDS VIEW (< 800px) --}}
        <div class="du-cards-view divide-y divide-slate-100">
            @forelse ($daftarUlang as $index => $item)
            @php
                $casis = $item->casis;
                $nama = $casis->nama_calon_siswa;
                $noReg = $casis->no_registrasi ?: '-';
                $isTerlambat = ($item->statusTahap2 === 'Terlambat');
                $hasDuRecord = !empty($item->daftarUlang);
                $cicilan1Terpenuhi = ($item->totalTerbayar >= $item->minCicilan1);
                $berkasDiterima = ($item->statusBerkas === 'Diterima');
                $isLunasSelesai = ($item->sisaBiaya <= 0 && $item->statusDaftarUlang === 'Selesai');
            @endphp
            <div class="p-4 sm:p-5 space-y-3 {{ $isTerlambat ? 'border-l-[4px] border-l-rose-600 bg-rose-50/20' : '' }}">
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

                {{-- Detail Baris Ringkas: Pembayaran Progres, Tahap 2, Berkas --}}
                <div class="bg-slate-50/80 rounded-xl p-3 text-xs space-y-2.5 border border-slate-100">
                    {{-- Progres Pembayaran --}}
                    <div>
                        <div class="flex items-center justify-between font-semibold text-slate-800 mb-1 tabular-nums">
                            <span>Pembayaran: Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}</span>
                            <span class="text-slate-400 font-normal">/ Rp {{ number_format($item->totalBiaya, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden mb-1.5">
                            <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $item->persenTerbayar }}%;"></div>
                        </div>
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-slate-500">Status Keuangan:</span>
                            <span class="font-semibold {{ $item->statusKeuangan === 'Lunas' ? 'text-emerald-700' : ($item->statusKeuangan === 'Cicilan Awal Terpenuhi' ? 'text-blue-700' : 'text-slate-700') }}">
                                {{ $item->statusKeuangan }}
                            </span>
                        </div>
                    </div>

                    {{-- Tahap 2 --}}
                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">Pelunasan Tahap 2:</span>
                        <div class="text-right">
                            @if($isTerlambat)
                                <span class="inline-flex items-center gap-1 font-bold text-rose-700">
                                    <i class="fas fa-exclamation-circle text-xs" aria-hidden="true"></i> Terlambat
                                </span>
                            @else
                                <span class="font-medium text-slate-800">{{ $item->statusTahap2 }}</span>
                            @endif
                            @if($item->tglJatuhTempo)
                                <span class="block text-[11px] {{ $isTerlambat ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                                    {{ \Carbon\Carbon::parse($item->tglJatuhTempo)->translatedFormat('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Berkas --}}
                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">Berkas Fisik:</span>
                        <span class="font-medium {{ $item->statusBerkas === 'Diterima' ? 'text-emerald-700 font-semibold' : 'text-amber-700' }}">
                            {{ $item->statusBerkas }} {{ $item->jenisBerkas ? '(' . $item->jenisBerkas . ')' : '' }}
                        </span>
                    </div>
                </div>

                {{-- Tombol Aksi di Bawah (Penuh dan Sesuai Kondisi) --}}
                <div class="pt-1 grid grid-cols-2 gap-2">
                    @if(!$hasDuRecord)
                        <button type="button"
                                @click="openModalBayar({
                                    id: '{{ $item->id }}',
                                    nama: '{{ addslashes($nama) }}',
                                    noReg: '{{ $noReg }}',
                                    totalBiaya: {{ $item->totalBiaya }},
                                    totalTerbayar: {{ $item->totalTerbayar }},
                                    sisaBiaya: {{ $item->sisaBiaya }},
                                    minCicilan1: {{ $item->minCicilan1 }}
                                })"
                                class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-green-700 hover:bg-green-800 text-white text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                            <span>Catat Bayar</span>
                        </button>
                        <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                           class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                            <span>Detail</span>
                        </a>
                    @elseif($isLunasSelesai)
                        <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                           class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                            <span>Detail</span>
                        </a>
                        <a href="{{ route('admin.daftar_ulang.kuitansi', $item->id) }}"
                           target="_blank"
                           class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-receipt text-xs text-emerald-600" aria-hidden="true"></i>
                            <span>Kuitansi</span>
                        </a>
                    @elseif($isTerlambat && $item->sisaBiaya > 0)
                        <button type="button"
                                @click="openModalBayar({
                                    id: '{{ $item->id }}',
                                    nama: '{{ addslashes($nama) }}',
                                    noReg: '{{ $noReg }}',
                                    totalBiaya: {{ $item->totalBiaya }},
                                    totalTerbayar: {{ $item->totalTerbayar }},
                                    sisaBiaya: {{ $item->sisaBiaya }},
                                    minCicilan1: {{ $item->minCicilan1 }}
                                })"
                                class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-rose-700 hover:bg-rose-800 text-white text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                            <span>Catat Bayar</span>
                        </button>
                        <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                           class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                            <span>Detail</span>
                        </a>
                    @elseif($cicilan1Terpenuhi && !$berkasDiterima)
                        <button type="button"
                                @click="openModalBayar({
                                    id: '{{ $item->id }}',
                                    nama: '{{ addslashes($nama) }}',
                                    noReg: '{{ $noReg }}',
                                    totalBiaya: {{ $item->totalBiaya }},
                                    totalTerbayar: {{ $item->totalTerbayar }},
                                    sisaBiaya: {{ $item->sisaBiaya }},
                                    minCicilan1: {{ $item->minCicilan1 }}
                                })"
                                class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-green-700 hover:bg-green-800 text-white text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                            <span>Catat Bayar</span>
                        </button>
                        <button type="button"
                                @click="openModalBerkas({
                                    id: '{{ $item->id }}',
                                    nama: '{{ addslashes($nama) }}',
                                    noReg: '{{ $noReg }}',
                                    statusBerkas: '{{ $item->statusBerkas }}',
                                    jenisBerkas: '{{ $item->jenisBerkas ?? 'Dokumen Asli' }}',
                                    catatanBerkas: '{{ addslashes($item->daftarUlang?->catatan_berkas ?? '') }}'
                                })"
                                class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-folder-open text-xs text-amber-600" aria-hidden="true"></i>
                            <span>Terima Berkas</span>
                        </button>
                    @else
                        <button type="button"
                                @click="openModalBayar({
                                    id: '{{ $item->id }}',
                                    nama: '{{ addslashes($nama) }}',
                                    noReg: '{{ $noReg }}',
                                    totalBiaya: {{ $item->totalBiaya }},
                                    totalTerbayar: {{ $item->totalTerbayar }},
                                    sisaBiaya: {{ $item->sisaBiaya }},
                                    minCicilan1: {{ $item->minCicilan1 }}
                                })"
                                class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-green-700 hover:bg-green-800 text-white text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-money-bill-wave text-xs" aria-hidden="true"></i>
                            <span>Catat Bayar</span>
                        </button>
                        <a href="{{ route('admin.daftar_ulang.show', $item->id) }}"
                           class="du-btn-action w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition-colors min-h-[44px]">
                            <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                            <span>Detail</span>
                        </a>
                    @endif
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
                @if($activeFilterCount > 0)
                    <a href="{{ route('admin.daftar_ulang.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 underline mt-2 inline-block">
                        Atur ulang filter
                    </a>
                @endif
            </div>
            @endforelse
        </div>

        {{-- PAGINATION & INFO FOOTER --}}
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
         MODAL 1: CATAT PEMBAYARAN TUNAI (Aksesibel, Presisi, & Responsif)
         ======================================================== --}}
    <div x-show="modalBayar"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-bayar-title"
         aria-describedby="modal-bayar-desc"
         role="alertdialog"
         aria-modal="true"
         @keydown.escape.window="if (modalBayar) closeModalBayar()"
         @keydown.tab="trapFocus($event)">
        
        {{-- Backdrop gelap (rgba(0,0,0,0.5)) --}}
        <div class="fixed inset-0 bg-black/50 transition-opacity"
             aria-hidden="true"
             @click="closeModalBayar()"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-left">
            {{-- Dialog Card Modal (Lebar 92vw di HP, max-width 560px di Desktop, max-h 90vh) --}}
            <div class="relative w-full max-w-[560px] max-h-[90vh] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col z-10"
                 @click.stop>
            
            {{-- 1. Header Sticky --}}
            <div class="sticky top-0 bg-white z-10 px-5 pt-5 pb-4 border-b border-slate-200/80 flex items-center justify-between gap-3 flex-shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 min-w-[36px] min-h-[36px] flex-shrink-0 rounded-xl bg-green-700 text-white flex items-center justify-center" aria-hidden="true">
                        <i class="fas fa-money-bill-wave text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 leading-snug truncate" id="modal-bayar-title">
                            Catat Setoran Tunai
                        </h3>
                        <p class="text-sm text-slate-600 leading-snug truncate" id="modal-bayar-desc">
                            Calon Siswa: <span class="font-medium text-slate-800" x-text="selectedItem?.nama"></span>
                        </p>
                    </div>
                </div>
                <button type="button"
                        @click="closeModalBayar()"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors flex-shrink-0"
                        aria-label="Tutup dialog pencatatan setoran tunai">
                    <i class="fas fa-times text-base" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Form Wrapper --}}
            <form id="form-catat-bayar"
                  :action="'{{ url('admin/verifikasi-daftar-ulang') }}/' + (selectedItem?.id || '') + '/catat-pembayaran'"
                  method="POST"
                  class="flex flex-col min-h-0 flex-1">
                @csrf

                {{-- 2. Konten Scrollable bila melebihi 90vh --}}
                <div class="overflow-y-auto px-5 py-4 space-y-4 flex-1">
                    
                    {{-- A. Info Siswa (Satu Baris, 15px) --}}
                    <div class="text-[15px] pb-3 border-b border-slate-200/80 flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-slate-900" x-text="selectedItem?.nama"></span>
                        <span class="text-slate-400 select-none" aria-hidden="true">·</span>
                        <span class="font-mono text-slate-500" x-text="selectedItem?.noReg"></span>
                    </div>

                    {{-- B. Tiga Kotak Ringkasan (Grid 3 Kolom, Align-items: stretch) --}}
                    <div class="grid grid-cols-3 gap-1.5 sm:gap-3 items-stretch">
                        {{-- Total Tagihan --}}
                        <div class="border border-slate-200 rounded-[10px] p-2 sm:p-3 flex flex-col justify-between bg-slate-50/50 min-w-0">
                            <span class="text-[10px] sm:text-xs uppercase font-medium text-slate-600 tracking-wider truncate">
                                Total Tagihan
                            </span>
                            <span class="text-xs sm:text-lg font-semibold tabular-nums text-slate-900 mt-1 truncate">
                                Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedItem?.totalBiaya || 0)"></span>
                            </span>
                        </div>

                        {{-- Sudah Terbayar --}}
                        <div class="border border-slate-200 rounded-[10px] p-2 sm:p-3 flex flex-col justify-between bg-slate-50/50 min-w-0">
                            <span class="text-[10px] sm:text-xs uppercase font-medium text-slate-600 tracking-wider truncate">
                                Terbayar
                            </span>
                            <span class="text-xs sm:text-lg font-semibold tabular-nums text-green-800 mt-1 truncate">
                                Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedItem?.totalTerbayar || 0)"></span>
                            </span>
                        </div>

                        {{-- Sisa Tagihan --}}
                        <div class="border border-slate-200 rounded-[10px] p-2 sm:p-3 flex flex-col justify-between bg-slate-50/50 min-w-0">
                            <span class="text-[10px] sm:text-xs uppercase font-medium text-slate-600 tracking-wider truncate">
                                Sisa Tagihan
                            </span>
                            <span class="text-xs sm:text-lg font-semibold tabular-nums mt-1 truncate"
                                  :class="(selectedItem?.sisaBiaya || 0) > 0 ? 'text-amber-700' : 'text-slate-600'">
                                Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedItem?.sisaBiaya || 0)"></span>
                            </span>
                        </div>
                    </div>

                    {{-- C. Tombol Preset --}}
                    <div>
                        <span class="block text-xs font-semibold text-slate-700 mb-2">Pilihan Nominal Cepat:</span>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            {{-- Preset: Cicilan Pertama --}}
                            <button type="button"
                                    @click="setPresetNominal('cicil1')"
                                    :disabled="getCicilan1Nominal() <= 0"
                                    :class="[
                                        getCicilan1Nominal() <= 0 
                                            ? 'opacity-60 cursor-not-allowed bg-slate-50 border-slate-200' 
                                            : (activePreset === 'cicil1' 
                                                ? 'border-green-700 bg-green-50 text-green-800' 
                                                : 'border-slate-200 bg-white hover:border-green-600 hover:bg-green-50/40 text-slate-800')
                                    ]"
                                    :aria-pressed="activePreset === 'cicil1' ? 'true' : 'false'"
                                    class="border-2 rounded-xl p-2.5 sm:p-3.5 text-left transition-all duration-150 flex flex-col justify-between min-h-[64px] min-w-0"
                                    :title="getCicilan1Nominal() <= 0 ? 'Cicilan pertama sudah terpenuhi' : 'Pilih nominal cicilan pertama'">
                                <span class="text-xs sm:text-[13px] font-medium text-slate-600 truncate">Cicilan Pertama</span>
                                <span class="text-sm sm:text-base font-semibold tabular-nums mt-0.5"
                                      :class="activePreset === 'cicil1' ? 'text-green-800' : 'text-slate-900'">
                                    <template x-if="getCicilan1Nominal() > 0">
                                        <span>Rp <span x-text="new Intl.NumberFormat('id-ID').format(getCicilan1Nominal())"></span></span>
                                    </template>
                                    <template x-if="getCicilan1Nominal() <= 0">
                                        <span class="text-slate-400 font-normal text-xs">— (Sudah Terpenuhi)</span>
                                    </template>
                                </span>
                            </button>

                            {{-- Preset: Lunasi Seluruh Sisa --}}
                            <button type="button"
                                    @click="setPresetNominal('lunas')"
                                    :disabled="(selectedItem?.sisaBiaya || 0) <= 0"
                                    :class="[
                                        (selectedItem?.sisaBiaya || 0) <= 0 
                                            ? 'opacity-60 cursor-not-allowed bg-slate-50 border-slate-200' 
                                            : (activePreset === 'lunas' 
                                                ? 'border-green-700 bg-green-50 text-green-800' 
                                                : 'border-slate-200 bg-white hover:border-green-600 hover:bg-green-50/40 text-slate-800')
                                    ]"
                                    :aria-pressed="activePreset === 'lunas' ? 'true' : 'false'"
                                    class="border-2 rounded-xl p-2.5 sm:p-3.5 text-left transition-all duration-150 flex flex-col justify-between min-h-[64px] min-w-0"
                                    :title="(selectedItem?.sisaBiaya || 0) <= 0 ? 'Tagihan sudah lunas' : 'Pilih pelunasan seluruh sisa tagihan'">
                                <span class="text-xs sm:text-[13px] font-medium text-slate-600 truncate">Lunasi Seluruh Sisa</span>
                                <span class="text-sm sm:text-base font-semibold tabular-nums mt-0.5"
                                      :class="activePreset === 'lunas' ? 'text-green-800' : 'text-slate-900'">
                                    <template x-if="(selectedItem?.sisaBiaya || 0) > 0">
                                        <span>Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedItem?.sisaBiaya || 0)"></span></span>
                                    </template>
                                    <template x-if="(selectedItem?.sisaBiaya || 0) <= 0">
                                        <span class="text-slate-400 font-normal text-xs">— (Sudah Lunas)</span>
                                    </template>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- D. Field Nominal Pembayaran (type=number, no spinner, format ribuan, 52px) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="jml_bayar_du" class="block text-xs font-semibold text-slate-700">
                                Nominal Pembayaran <span class="text-rose-500">*</span>
                            </label>
                            <span x-show="nominalInput && !isNaN(Number(nominalInput)) && Number(nominalInput) > 0"
                                  class="text-xs font-bold text-green-800 tabular-nums">
                                Rp <span x-text="new Intl.NumberFormat('id-ID').format(Number(nominalInput))"></span>
                            </span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm font-bold select-none" aria-hidden="true">
                                Rp
                            </div>
                            <input type="number"
                                   name="jml_bayar_du"
                                   id="jml_bayar_du"
                                   x-model="nominalInput"
                                   @input="onNominalChange()"
                                   required
                                   min="1000"
                                   :max="selectedItem?.sisaBiaya || 10000000"
                                   placeholder="0"
                                   class="du-input-ctrl du-no-spinner w-full pl-11 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-green-700 text-base font-bold text-slate-900 h-[52px] min-h-[52px]">
                        </div>
                        <p x-show="nominalError"
                           x-cloak
                           role="alert"
                           aria-live="polite"
                           class="text-xs text-rose-600 mt-1.5 font-medium flex items-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-xs flex-shrink-0" aria-hidden="true"></i>
                            <span x-text="nominalError"></span>
                        </p>
                    </div>

                    {{-- E. Field Tanggal Pembayaran (type=date, hapus jam, teks bantu ID, 52px) --}}
                    <div>
                        <label for="tgl_bayar_du" class="block text-xs font-semibold text-slate-700 mb-1">
                            Tanggal Pembayaran <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="tgl_bayar_du"
                               id="tgl_bayar_du"
                               x-model="tglBayarInput"
                               required
                               class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-green-700 text-sm font-medium text-slate-900 h-[52px] min-h-[52px]">
                        <p class="text-[13px] text-slate-500 mt-1 font-medium"
                           x-text="formatIndonesianDate(tglBayarInput)"></p>
                    </div>

                    {{-- F. No. Kuitansi dan Catatan Kasir (Grid 2 Kolom, align-items: start, 52px) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
                        <div class="flex flex-col">
                            <label for="no_kuitansi" class="block text-xs font-semibold text-slate-700 mb-1">
                                No. Kuitansi
                            </label>
                            <input type="text"
                                   name="no_kuitansi"
                                   id="no_kuitansi"
                                   placeholder="Otomatis: KW-DU-YYYYMMDD-XXXXX"
                                   class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-green-700 text-xs sm:text-sm h-[52px] min-h-[52px]">
                            <p class="text-[13px] text-slate-500 mt-1 leading-normal">
                                Kosongkan jika ingin dibuat otomatis.
                            </p>
                        </div>
                        <div class="flex flex-col">
                            <label for="keterangan" class="block text-xs font-semibold text-slate-700 mb-1">
                                Catatan Kasir
                            </label>
                            <textarea name="keterangan"
                                      id="keterangan"
                                      maxlength="200"
                                      x-model="catatanBayar"
                                      rows="1"
                                      placeholder="Contoh: Setoran tunai di kasir sekolah"
                                      class="du-input-ctrl w-full px-3.5 py-3.5 bg-white border border-slate-300 rounded-xl focus:border-green-700 text-xs sm:text-sm h-[52px] min-h-[52px] resize-none overflow-hidden leading-normal"></textarea>
                            <div class="flex justify-between items-center text-[13px] text-slate-500 mt-1 leading-normal">
                                <span>Opsional</span>
                                <span class="tabular-nums"><span x-text="catatanBayar.length"></span>/200</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Footer Sticky (Rata Kanan, Batal Sekunder, Simpan Utama 44px) --}}
                <div class="sticky bottom-0 bg-white z-10 px-5 py-3 border-t border-slate-200/80 flex items-center justify-end gap-3 flex-shrink-0">
                    <button type="button"
                            x-ref="btnBatalBayar"
                            @click="closeModalBayar()"
                            class="du-btn-action px-5 h-[44px] min-h-[44px] border-2 border-green-700 bg-white hover:bg-green-50 text-green-700 text-sm font-semibold rounded-xl transition-colors focus-visible:outline-2 focus-visible:outline-green-700">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="!isNominalValid()"
                            class="du-btn-action px-5 h-[44px] min-h-[44px] bg-green-700 hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-xs transition-colors inline-flex items-center gap-2 focus-visible:outline-2 focus-visible:outline-green-700">
                        <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center" aria-hidden="true">
                            <i class="fas fa-save text-sm"></i>
                        </span>
                        <span>Simpan Setoran Tunai</span>
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    {{-- ========================================================
         MODAL 2: TERIMA / PERBARUI BERKAS FISIK (Aksesibel & Sesuai Spesifikasi)
         ======================================================== --}}
    <div x-show="modalBerkas"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-berkas-title"
         role="alertdialog"
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="closeModalBerkas()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-200"
                 @click.stop>
                
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center">
                            <i class="fas fa-folder-open text-lg" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800" id="modal-berkas-title">
                                Terima Berkas Fisik - <span x-text="selectedItem?.nama"></span>
                            </h3>
                            <p class="text-xs text-slate-500">Verifikasi dokumen persyaratan fisik calon siswa</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeModalBerkas()"
                            class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center"
                            aria-label="Tutup dialog penerimaan berkas">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/verifikasi-daftar-ulang') }}/' + (selectedItem?.id || '') + '/terima-berkas'"
                      method="POST"
                      class="mt-4 space-y-4">
                    @csrf

                    {{-- Informasi Calon Siswa (Read-only) --}}
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-xs text-slate-700 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Nama Calon Siswa:</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="selectedItem?.nama"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Nomor Registrasi:</span>
                            <span class="font-mono font-semibold text-slate-700" x-text="selectedItem?.noReg"></span>
                        </div>
                    </div>

                    {{-- Status Berkas --}}
                    <div>
                        <label for="status_berkas_modal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Status Berkas Fisik <span class="text-rose-500">*</span>
                        </label>
                        <select name="status_berkas"
                                id="status_berkas_modal"
                                required
                                x-model="statusBerkasPilihan"
                                class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-amber-600 text-sm font-semibold min-h-[44px]">
                            <option value="Diterima">✅ Berkas Diterima Lengkap</option>
                            <option value="Menunggu Berkas">⏳ Menunggu Berkas Diserahkan</option>
                            <option value="Ditolak">❌ Berkas Ditolak / Tidak Sesuai</option>
                        </select>
                    </div>

                    {{-- Jenis Penyerahan Dokumen (Dokumen Asli / Fotokopi / Asli & Fotokopi) --}}
                    <div x-show="statusBerkasPilihan === 'Diterima'">
                        <label for="jenis_berkas_modal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Jenis Penyerahan Dokumen
                        </label>
                        <select name="jenis_berkas"
                                id="jenis_berkas_modal"
                                x-model="jenisBerkasPilihan"
                                class="du-input-ctrl w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-amber-600 text-sm min-h-[44px]">
                            <option value="Asli">Dokumen Asli</option>
                            <option value="Fotokopi">Fotokopi Dilegalisir</option>
                            <option value="Asli &amp; Fotokopi">Asli &amp; Fotokopi Lengkap</option>
                        </select>
                    </div>

                    {{-- Catatan Berkas --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="catatan_berkas_modal" class="block text-xs font-semibold text-slate-700">
                                Catatan Panitia (Opsional)
                            </label>
                            <span class="text-[11px] text-slate-400 tabular-nums">
                                <span x-text="catatanBerkas.length"></span>/500
                            </span>
                        </div>
                        <textarea name="catatan_berkas"
                                  id="catatan_berkas_modal"
                                  rows="2"
                                  maxlength="500"
                                  x-model="catatanBerkas"
                                  placeholder="Contoh: Akta Kelahiran dan Kartu Keluarga asli telah diverifikasi langsung di kasir sekolah."
                                  class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:border-amber-600 text-sm"></textarea>
                    </div>

                    {{-- Tombol Batal & Simpan --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button"
                                x-ref="btnBatalBerkas"
                                @click="closeModalBerkas()"
                                class="du-btn-action px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl min-h-[44px] transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="du-btn-action px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors min-h-[44px] inline-flex items-center gap-2">
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
