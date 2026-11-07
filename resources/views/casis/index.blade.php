@extends('layout.tampilan')

@section('title', 'Data Calon Siswa')
@section('page-title', 'Data Calon Siswa')

@push('styles')
<style>
    /* Scoped Styles untuk Data Calon Siswa */
    .casis-page,
    .casis-page h1, .casis-page h2, .casis-page h3,
    .casis-page h4, .casis-page p, .casis-page span,
    .casis-page dt, .casis-page dd, .casis-page a,
    .casis-page button, .casis-page select, .casis-page input {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    /* Focus visible dengan ring hijau tema (offset 2px), tanpa garis tepi hitam saat diklik mouse */
    .casis-btn-action:focus-visible,
    .casis-filter-ctrl:focus-visible,
    .casis-tab-btn:focus-visible,
    .casis-menu-item:focus-visible {
        outline: 2px solid #15803d !important;
        outline-offset: 2px !important;
    }
    .casis-btn-action:focus:not(:focus-visible),
    .casis-filter-ctrl:focus:not(:focus-visible),
    .casis-tab-btn:focus:not(:focus-visible),
    .casis-menu-item:focus:not(:focus-visible) {
        outline: none !important;
        box-shadow: none !important;
    }

    /* Wadah tabel bergulir horizontal tanpa batas tinggi vertikal (tidak ada scrollbar vertikal bersarang) */
    .casis-table-scroll {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    /* Tabel Berbobot Proporsional & Bebas dari Overflow Desktop */
    @media (min-width: 1280px) {
        .casis-table-scroll {
            overflow-x: hidden;
        }
        .casis-fixed-table {
            table-layout: fixed;
            width: 100%;
        }
    }
    @media (max-width: 1279.98px) {
        .casis-fixed-table {
            table-layout: auto;
            min-width: 880px;
            width: 100%;
        }
    }

    /* Breakpoint tampilan tabel vs kartu */
    @media (max-width: 699.98px) {
        .casis-table-view { display: none !important; }
        .casis-cards-view { display: block !important; }
    }
    @media (min-width: 700px) {
        .casis-table-view { display: block !important; }
        .casis-cards-view { display: none !important; }
    }


    /* Grid Filter yang selalu tampil di Desktop dan dapat dilipat di Mobile */
    .casis-filter-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }
    @media (min-width: 640px) {
        .casis-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .casis-filter-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 767.98px) {
        .casis-filter-grid:not(.is-open) {
            display: none !important;
        }
    }

    /* Prefer reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .casis-page,
        .casis-page * {
            transition: none !important;
            animation: none !important;
            transform: none !important;
        }
    }
</style>
@endpush

@section('content')
@php
    // Helper nama filter aktif untuk chip
    $selectedTaName = null;
    if ($filterTa) {
        $foundTa = $tahunAjaranList->firstWhere('id_tahun_ajaran', $filterTa);
        $selectedTaName = $foundTa ? $foundTa->tahun_ajaran : "ID {$filterTa}";
    }

    $daftarLabelMap = [
        'Menunggu' => 'Menunggu Verifikasi',
        'Diterima' => 'Data Terverifikasi',
        'Ditolak'  => 'Perlu Perbaikan',
    ];
    $selectedDaftarLabel = $filterStatusDaftar ? ($daftarLabelMap[$filterStatusDaftar] ?? $filterStatusDaftar) : null;

    $bayarLabelMap = [
        'Lunas'       => 'Lunas / Diterima',
        'Menunggu'    => 'Menunggu Pembayaran',
        'Belum Bayar' => 'Belum Bayar',
        'Gagal'       => 'Gagal / Kedaluwarsa',
    ];
    $selectedBayarLabel = $filterStatusBayar ? ($bayarLabelMap[$filterStatusBayar] ?? $filterStatusBayar) : null;

    $arsipLabelMap = [
        'aktif'    => 'Data Aktif',
        'terarsip' => 'Data Terarsip',
        'semua'    => 'Semua Data',
    ];
    $selectedArsipLabel = ($filterStatusArsip && $filterStatusArsip !== 'aktif') ? ($arsipLabelMap[$filterStatusArsip] ?? $filterStatusArsip) : null;

    // Hitung jumlah filter aktif (selain default)
    $activeFilterCount = 0;
    if (!empty($q)) $activeFilterCount++;
    if ($filterTa) $activeFilterCount++;
    if ($filterStatusDaftar) $activeFilterCount++;
    if ($filterStatusBayar) $activeFilterCount++;
    if ($filterStatusArsip && $filterStatusArsip !== 'aktif') $activeFilterCount++;
@endphp

<div class="casis-page space-y-5 sm:space-y-6" x-data="{
    modalMode: '', // 'archive', 'force_delete', 'restore'
    modalOpen: false,
    formAction: '',
    studentName: '',
    studentReg: '',
    hasRelatedData: false,
    filterOpenMobile: false,

    // Floating Popover Menu (Fixed Position - Bebas dari pemotongan overflow tabel)
    activeMenu: null,
    openMenu(event, id, name, reg, hasRelated, isArchived) {
        const btn = event.currentTarget;
        const rect = btn.getBoundingClientRect();
        const menuWidth = 210;
        const menuHeight = isArchived ? 52 : (hasRelated ? 52 : 106);
        const spaceBelow = window.innerHeight - rect.bottom;
        const openUp = spaceBelow < (menuHeight + 20) && rect.top > menuHeight;

        let left = rect.right - menuWidth;
        if (left < 12) left = 12;
        if (left + menuWidth > window.innerWidth - 12) {
            left = window.innerWidth - menuWidth - 12;
        }

        let top = openUp ? (rect.top - menuHeight - 6) : (rect.bottom + 6);

        this.activeMenu = {
            btnEl: btn,
            id,
            name,
            reg,
            hasRelated,
            isArchived,
            top: Math.round(top),
            left: Math.round(left),
            openUp
        };

        this.$nextTick(() => {
            const firstItem = document.querySelector('#floating-action-menu [role=\'menuitem\']');
            if (firstItem) firstItem.focus();
        });
    },
    closeMenu() {
        if (this.activeMenu && this.activeMenu.btnEl) {
            this.activeMenu.btnEl.focus();
        }
        this.activeMenu = null;
    },
    focusNextMenuItem(direction) {
        const items = Array.from(document.querySelectorAll('#floating-action-menu [role=\'menuitem\']'));
        if (!items.length) return;
        const curIdx = items.indexOf(document.activeElement);
        let nextIdx = curIdx + direction;
        if (nextIdx < 0) nextIdx = items.length - 1;
        if (nextIdx >= items.length) nextIdx = 0;
        items[nextIdx].focus();
    },
    openModal(mode, actionUrl, name, reg, hasRelated) {
        this.closeMenu();
        this.modalMode = mode;
        this.formAction = actionUrl;
        this.studentName = name;
        this.studentReg = reg;
        this.hasRelatedData = !!hasRelated;
        this.modalOpen = true;
    }
}">

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

    {{-- Kartu Judul Ringkas (Tanpa Breadcrumb Ganda, Tinggi ~90-110px) --}}
    <div class="bg-white rounded-2xl px-5 py-4 sm:px-6 sm:py-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3.5">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 font-heading leading-tight tracking-tight">Data Calon Siswa</h1>
            <p class="text-slate-600 text-sm sm:text-[15px] mt-0.5">Kelola data pendaftaran dan orang tua siswa baru.</p>
        </div>

        {{-- Tab Cepat: Data Aktif vs Data Terarsip --}}
        <div class="flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200 self-start md:self-auto">
            <a href="{{ route('indexcasis', array_merge(request()->query(), ['status_arsip' => 'aktif', 'page' => null])) }}"
               class="casis-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $filterStatusArsip !== 'terarsip' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}"
               aria-label="Tampilkan data calon siswa aktif">
                <i class="fas fa-users text-[11px]" aria-hidden="true"></i>
                <span>Data Aktif</span>
            </a>

            <a href="{{ route('indexcasis', array_merge(request()->query(), ['status_arsip' => 'terarsip', 'page' => null])) }}"
               class="casis-tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $filterStatusArsip === 'terarsip' ? 'bg-amber-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}"
               aria-label="Tampilkan data calon siswa terarsip">
                <i class="fas fa-archive text-[11px]" aria-hidden="true"></i>
                <span>Data Terarsip</span>
                @if($summary->terarsip > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $filterStatusArsip === 'terarsip' ? 'bg-amber-800 text-amber-100' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        {{ $summary->terarsip }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    {{-- Ringkasan Kartu Angka (1 Query Agregat, Kartu Statis) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        {{-- Total Pendaftar --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <i class="fas fa-user-graduate text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 truncate">Total Pendaftar</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading mt-0.5">{{ number_format($summary->total) }}</p>
            </div>
        </div>

        {{-- Menunggu Verifikasi --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0">
                <i class="fas fa-clock text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 truncate">Menunggu Verifikasi</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading mt-0.5">{{ number_format($summary->menunggu) }}</p>
            </div>
        </div>

        {{-- Terverifikasi --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center shrink-0">
                <i class="fas fa-check-circle text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 truncate">Terverifikasi</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading mt-0.5">{{ number_format($summary->terverifikasi) }}</p>
            </div>
        </div>

        {{-- Belum Bayar --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center shrink-0">
                <i class="fas fa-money-bill-wave text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500 truncate">Belum Bayar</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading mt-0.5">{{ number_format($summary->belum_bayar) }}</p>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian Bar (Kontrol Terlihat Jelas) --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <form action="{{ route('indexcasis') }}" method="get" class="space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                {{-- Kolom Pencarian (?q=) & Tombol Cari di sampingnya --}}
                <div class="flex items-center gap-2 flex-1 max-w-2xl">
                    <div class="relative flex-1">
                        <label for="search-input" class="sr-only">Cari Calon Siswa</label>
                        <input type="text"
                               id="search-input"
                               name="q"
                               class="casis-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl pl-10 pr-9 h-11 min-h-[44px] transition-colors"
                               placeholder="Cari nama calon siswa, NIK, atau nomor registrasi..."
                               value="{{ $q ?? '' }}">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400" aria-hidden="true">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        @if(!empty($q))
                        <a href="{{ route('indexcasis', array_merge(request()->except(['q', 'search', 'page']))) }}"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                           title="Hapus kata kunci pencarian"
                           aria-label="Hapus kata kunci pencarian">
                            <i class="fas fa-times-circle text-sm" aria-hidden="true"></i>
                        </a>
                        @endif
                    </div>

                    {{-- Tombol Cari & Filter persis di samping input --}}
                    <button type="submit"
                            class="casis-btn-action px-4 sm:px-5 h-11 min-h-[44px] text-xs sm:text-sm font-semibold rounded-xl bg-green-700 hover:bg-green-800 text-white shadow-xs flex items-center gap-2 transition-colors shrink-0">
                        <i class="fas fa-search text-xs" aria-hidden="true"></i>
                        <span>Cari &amp; Filter</span>
                    </button>
                </div>

                {{-- Tombol Aksi Sisi Kanan: Tambah Siswa & Atur Ulang --}}
                <div class="flex items-center gap-2 self-end md:self-auto flex-wrap">
                    {{-- Toggle Filter Mobile (< 768px) --}}
                    <button type="button"
                            @click="filterOpenMobile = !filterOpenMobile"
                            class="md:hidden px-3.5 h-11 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 transition-colors"
                            :aria-expanded="filterOpenMobile"
                            aria-controls="casis-filter-panel">
                        <i class="fas fa-filter text-xs" aria-hidden="true"></i>
                        <span>Filter</span>
                        @if($activeFilterCount > 0)
                            <span class="w-5 h-5 rounded-full bg-green-700 text-white text-[10px] flex items-center justify-center font-bold">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>

                    {{-- Tombol Tambah Siswa (Sejajar dengan Cari & Filter) --}}
                    <a href="{{ route('formpendaftaran') }}"
                       class="casis-btn-action px-4 h-11 min-h-[44px] text-xs sm:text-sm font-semibold rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs flex items-center gap-2 transition-colors shrink-0"
                       title="Tambah Pendaftaran Calon Siswa Baru">
                        <i class="fas fa-user-plus text-xs" aria-hidden="true"></i>
                        <span>Tambah Siswa</span>
                    </a>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('indexcasis') }}"
                           class="casis-btn-action px-3.5 h-11 min-h-[44px] text-xs sm:text-sm font-medium rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 transition-colors flex items-center gap-1.5"
                           title="Atur ulang seluruh filter">
                            <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                            <span>Atur Ulang</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Grid Kontrol Filter (4 Kolom dengan Label Jelas & Tinggi 44px) --}}
            <div id="casis-filter-panel" class="casis-filter-grid" :class="{ 'is-open': filterOpenMobile }">
                {{-- Filter Tahun Ajaran --}}
                <div>
                    <label for="filter-ta" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Tahun Ajaran</label>
                    <select id="filter-ta" name="ta" class="casis-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjaranList as $itemTa)
                            <option value="{{ $itemTa->id_tahun_ajaran }}" {{ $filterTa == $itemTa->id_tahun_ajaran ? 'selected' : '' }}>
                                {{ $itemTa->tahun_ajaran }} {{ $itemTa->status === 'Berlangsung' ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status Pendaftaran --}}
                <div>
                    <label for="filter-status-daftar" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Pendaftaran</label>
                    <select id="filter-status-daftar" name="status_pendaftaran" class="casis-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors">
                        <option value="">Semua Status Pendaftaran</option>
                        <option value="Menunggu" {{ $filterStatusDaftar === 'Menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="Diterima" {{ $filterStatusDaftar === 'Diterima' ? 'selected' : '' }}>Data Terverifikasi</option>
                        <option value="Ditolak" {{ $filterStatusDaftar === 'Ditolak' ? 'selected' : '' }}>Perlu Perbaikan</option>
                    </select>
                </div>

                {{-- Filter Status Pembayaran --}}
                <div>
                    <label for="filter-status-bayar" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Pembayaran</label>
                    <select id="filter-status-bayar" name="status_pembayaran" class="casis-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="Lunas" {{ $filterStatusBayar === 'Lunas' ? 'selected' : '' }}>Lunas / Diterima</option>
                        <option value="Menunggu" {{ $filterStatusBayar === 'Menunggu' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                        <option value="Belum Bayar" {{ $filterStatusBayar === 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="Gagal" {{ $filterStatusBayar === 'Gagal' ? 'selected' : '' }}>Gagal / Kedaluwarsa</option>
                    </select>
                </div>

                {{-- Filter Status Arsip Data --}}
                <div>
                    <label for="filter-status-arsip" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Arsip Data</label>
                    <select id="filter-status-arsip" name="status_arsip" class="casis-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors">
                        <option value="aktif" {{ $filterStatusArsip === 'aktif' ? 'selected' : '' }}>Data Aktif (Standar)</option>
                        <option value="terarsip" {{ $filterStatusArsip === 'terarsip' ? 'selected' : '' }}>Data Terarsip (Arsip Lunak)</option>
                        <option value="semua" {{ $filterStatusArsip === 'semua' ? 'selected' : '' }}>Semua Data (Aktif &amp; Arsip)</option>
                    </select>
                </div>

                {{-- Preserved sort & per_page as hidden fields --}}
                <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
            </div>
        </form>
    </div>

    {{-- Chip Filter Aktif (Memudahkan Menghapus Satu Filter) --}}
    @if($activeFilterCount > 0)
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-semibold text-slate-600 mr-1">Filter Aktif:</span>

            @if(!empty($q))
                <a href="{{ route('indexcasis', array_merge(request()->except(['q', 'search', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Cari: "{{ Str::limit($q, 20) }}"</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedTaName)
                <a href="{{ route('indexcasis', array_merge(request()->except(['ta', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Tahun Ajaran: {{ $selectedTaName }}</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedDaftarLabel)
                <a href="{{ route('indexcasis', array_merge(request()->except(['status_pendaftaran', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Pendaftaran: {{ $selectedDaftarLabel }}</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedBayarLabel)
                <a href="{{ route('indexcasis', array_merge(request()->except(['status_pembayaran', 'page']))) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-colors font-medium">
                    <span>Pembayaran: {{ $selectedBayarLabel }}</span>
                    <i class="fas fa-times text-[10px] text-slate-500 hover:text-slate-800" aria-hidden="true"></i>
                </a>
            @endif

            @if($selectedArsipLabel)
                <a href="{{ route('indexcasis', array_merge(request()->except(['status_arsip', 'page']), ['status_arsip' => 'aktif'])) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 transition-colors font-semibold">
                    <span>Status Arsip: {{ $selectedArsipLabel }}</span>
                    <i class="fas fa-times text-[10px] text-amber-700" aria-hidden="true"></i>
                </a>
            @endif

            <a href="{{ route('indexcasis') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800 underline ml-1">
                Atur Ulang Semua
            </a>
        </div>
    @endif

    {{-- Wadah Tabel (Bebas dari Scroll Vertikal Bersarang) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        {{-- Banner Status Arsip Aktif --}}
        @if($filterStatusArsip === 'terarsip')
            <div class="bg-amber-50 border-b border-amber-200 text-amber-900 px-5 py-3 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fas fa-archive text-amber-700 text-sm" aria-hidden="true"></i>
                    <span>Menampilkan <strong>Data Calon Siswa Terarsip</strong>. Data ini disembunyikan dari proses aktif sekolah tetapi dapat dipulihkan kapan saja oleh Admin.</span>
                </div>
                <a href="{{ route('indexcasis', array_merge(request()->query(), ['status_arsip' => 'aktif', 'page' => null])) }}"
                   class="font-semibold text-emerald-800 hover:text-emerald-950 underline shrink-0">
                    Kembali ke Data Aktif
                </a>
            </div>
        @endif

        {{-- DESKTOP & TABLET TABLE (>= 700px) --}}
        <div class="casis-table-view casis-table-scroll">
            <table class="casis-fixed-table text-sm text-left text-slate-700 border-collapse">
                <colgroup>
                    <col style="width: 4%; min-width: 35px;">
                    <col style="width: 11%; min-width: 100px;">
                    <col style="width: 14%; min-width: 110px;">
                    <col style="width: 12%; min-width: 100px;">
                    <col style="width: 13%; min-width: 115px;">
                    <col style="width: 9%; min-width: 85px;">
                    <col style="width: 14%; min-width: 125px;">
                    <col style="width: 13%; min-width: 105px;">
                    <col style="width: 10%; min-width: 95px;">
                </colgroup>
                <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-600" style="text-align: center !important;">NO</th>

                        {{-- Sort No Registrasi --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">
                            @php
                                $nextDirReg = ($sortBy === 'no_registrasi' && $sortDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('indexcasis', array_merge(request()->query(), ['sort_by' => 'no_registrasi', 'sort_dir' => $nextDirReg])) }}"
                               class="inline-flex items-center justify-center gap-1 hover:text-green-800 transition-colors group"
                               title="Urutkan berdasarkan Nomor Registrasi">
                                <span>NO. REGISTRASI</span>
                                <i class="fas {{ $sortBy === 'no_registrasi' ? ($sortDir === 'asc' ? 'fa-sort-up text-green-700' : 'fa-sort-down text-green-700') : 'fa-sort text-slate-400 group-hover:text-slate-600' }} text-xs" aria-hidden="true"></i>
                            </a>
                        </th>
                        
                        {{-- Sort Calon Siswa --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700" style="text-align: center !important;">
                            @php
                                $nextDirName = ($sortBy === 'nama_calon_siswa' && $sortDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('indexcasis', array_merge(request()->query(), ['sort_by' => 'nama_calon_siswa', 'sort_dir' => $nextDirName])) }}"
                               class="inline-flex items-center justify-center gap-1 hover:text-green-800 transition-colors group"
                               title="Urutkan berdasarkan Nama Siswa">
                                <span>CALON SISWA</span>
                                <i class="fas {{ $sortBy === 'nama_calon_siswa' ? ($sortDir === 'asc' ? 'fa-sort-up text-green-700' : 'fa-sort-down text-green-700') : 'fa-sort text-slate-400 group-hover:text-slate-600' }} text-xs" aria-hidden="true"></i>
                            </a>
                        </th>

                        {{-- ORANG TUA / WALI (Ayah Saja) --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700" style="text-align: center !important;">ORANG TUA / WALI</th>

                        {{-- NO HP / WHATSAPP --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">NO HP / WHATSAPP</th>

                        {{-- Kolom Tanggal Pendaftaran --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">
                            @php
                                $nextDirDate = ($sortBy === 'tgl_pendaftaran' && $sortDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('indexcasis', array_merge(request()->query(), ['sort_by' => 'tgl_pendaftaran', 'sort_dir' => $nextDirDate])) }}"
                               class="inline-flex items-center justify-center gap-1 hover:text-green-800 transition-colors group"
                               title="Urutkan berdasarkan Tanggal Pendaftaran">
                                <span>TGL DAFTAR</span>
                                <i class="fas {{ $sortBy === 'tgl_pendaftaran' ? ($sortDir === 'asc' ? 'fa-sort-up text-green-700' : 'fa-sort-down text-green-700') : 'fa-sort text-slate-400 group-hover:text-slate-600' }} text-xs" aria-hidden="true"></i>
                            </a>
                        </th>

                        {{-- Sort Status Pendaftaran --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">
                            @php
                                $nextDirStatus = ($sortBy === 'status_pendaftaran' && $sortDir === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('indexcasis', array_merge(request()->query(), ['sort_by' => 'status_pendaftaran', 'sort_dir' => $nextDirStatus])) }}"
                               class="inline-flex items-center justify-center gap-1 hover:text-green-800 transition-colors group"
                               title="Urutkan berdasarkan Status Pendaftaran">
                                <span>STATUS PENDAFTARAN</span>
                                <i class="fas {{ $sortBy === 'status_pendaftaran' ? ($sortDir === 'asc' ? 'fa-sort-up text-green-700' : 'fa-sort-down text-green-700') : 'fa-sort text-slate-400 group-hover:text-slate-600' }} text-xs" aria-hidden="true"></i>
                            </a>
                        </th>

                        {{-- Status Pembayaran --}}
                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">
                            <span>STATUS PEMBAYARAN</span>
                        </th>

                        <th scope="col" class="px-2 py-3.5 font-bold text-center text-slate-700" style="text-align: center !important;">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $value)
                    @php
                        $isArchived = $value->trashed();
                        $namaSiswa = $value->nama_calon_siswa ?: $value->nama;
                        $noReg = $value->no_registrasi ?: '-';
                        $rawNik = $value->nik_calon_siswa ?: $value->nik;
                        $maskedNik = (strlen($rawNik) >= 8)
                            ? substr($rawNik, 0, 4) . '••••••••' . substr($rawNik, -4)
                            : ($rawNik ?: '-');
                        $tglDaftar = $value->tgl_pendaftaran ? \Carbon\Carbon::parse($value->tgl_pendaftaran)->translatedFormat('d M Y') : '-';

                        // Kapitalisasi nama orang tua agar konsisten
                        $namaAyah = $value->nama_ayah ? Str::title($value->nama_ayah) : '-';
                        $namaIbu = $value->nama_ibu ? Str::title($value->nama_ibu) : '-';
                        $phone = $value->no_hp_orang_tua ?: $value->user?->no_hp;
                        $waDigits = '';
                        $formattedPhone = '';
                        if ($phone) {
                            $cleanDigits = preg_replace('/\D/', '', $phone);
                            if (str_starts_with($cleanDigits, '0')) {
                                $waDigits = '62' . substr($cleanDigits, 1);
                            } elseif (str_starts_with($cleanDigits, '62')) {
                                $waDigits = $cleanDigits;
                            } else {
                                $waDigits = '62' . $cleanDigits;
                            }
                            if (strlen($cleanDigits) >= 10 && strlen($cleanDigits) <= 13 && str_starts_with($cleanDigits, '08')) {
                                $formattedPhone = substr($cleanDigits, 0, 4) . '-' . substr($cleanDigits, 4, 4) . '-' . substr($cleanDigits, 8);
                            } else {
                                $formattedPhone = $phone;
                            }
                        }

                        // Berkas Persyaratan
                        $docTypes = [
                            ['type' => 'Akta Kelahiran', 'label' => 'Akte', 'required' => true],
                            ['type' => 'Kartu Keluarga', 'label' => 'KK', 'required' => true],
                            ['type' => 'Pas Foto', 'label' => 'Foto', 'required' => true],
                            ['type' => 'KPS / KIP / PKH', 'label' => 'KPS', 'required' => false],
                        ];

                        $validCount = 0;
                        $menungguCount = 0;
                        $tidakValidCount = 0;
                        $uploadedCount = 0;

                        foreach(['Akta Kelahiran', 'Kartu Keluarga', 'Pas Foto'] as $reqType) {
                            $d = $value->dokumen?->firstWhere('jenis_dokumen', $reqType);
                            if ($d && !empty($d->file_path)) {
                                $uploadedCount++;
                                if ($d->status_verifikasi === 'Valid') {
                                    $validCount++;
                                } elseif ($d->status_verifikasi === 'Tidak Valid') {
                                    $tidakValidCount++;
                                } else {
                                    $menungguCount++;
                                }
                            }
                        }

                        $statusDaftar = $value->status_pendaftaran;
                        $pembayaran = $value->pembayaran;
                        $statusBayar = $pembayaran?->status_pembayaran;

                        // Periksa relasi anak untuk validasi hapus aman
                        $hasRelatedData = ($pembayaran !== null);
                    @endphp
                    <tr class="hover:bg-slate-50/70 border-b border-gray-100 transition-colors align-middle {{ $isArchived ? 'bg-amber-50/40' : '' }}">
                        {{-- NO --}}
                        <td class="px-3 py-3.5 text-center font-medium text-gray-700 text-xs align-middle" style="text-align: center !important;">
                            {{ $loop->iteration + $data->firstItem() - 1 }}
                        </td>

                        {{-- NO REGISTRASI --}}
                        <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            <span class="font-mono text-xs font-semibold text-gray-800" title="{{ $noReg }}">
                                {{ $noReg }}
                            </span>
                        </td>

                        {{-- CALON SISWA --}}
                        <td class="px-4 py-3.5 align-middle text-center min-w-0" style="text-align: center !important;">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm leading-snug break-words" title="{{ $namaSiswa }}">{{ $namaSiswa }}</span>
                                @if($isArchived)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-900 border border-amber-300 shrink-0">
                                        <i class="fas fa-archive text-[9px]" aria-hidden="true"></i> Terarsip
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- ORANG TUA / WALI (Hanya Nama Ayah) --}}
                        <td class="px-3 py-3.5 align-middle text-center min-w-0" style="text-align: center !important;">
                            <span class="font-medium text-gray-800 text-xs sm:text-sm truncate block" title="{{ $namaAyah }}">
                                {{ $namaAyah }}
                            </span>
                        </td>

                        {{-- NO HP / WHATSAPP (Tanpa Bubble) --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap" style="text-align: center !important;">
                            @if($phone)
                                <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener noreferrer"
                                   title="Hubungi WhatsApp {{ $formattedPhone }}"
                                   class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-gray-800 hover:text-emerald-700 hover:underline transition-colors group">
                                    <i class="fab fa-whatsapp text-emerald-600 text-xs" aria-hidden="true"></i>
                                    <span class="font-mono">{{ $formattedPhone }}</span>
                                </a>
                            @else
                                <span class="text-xs text-gray-400 italic">
                                    -
                                </span>
                            @endif
                        </td>

                        {{-- TANGGAL DAFTAR --}}
                        <td class="px-3 py-3.5 align-middle text-center whitespace-nowrap text-xs text-gray-700 font-medium" style="text-align: center !important;" title="Tanggal Pendaftaran: {{ $tglDaftar }}">
                            {{ $tglDaftar }}
                        </td>

                        {{-- STATUS PENDAFTARAN (Tanpa Bubble) --}}
                        <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap min-w-0" style="text-align: center !important;">
                            @if($statusDaftar == 'Diterima')
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-700 whitespace-nowrap" title="Status Pendaftaran: Data Terverifikasi">
                                    <i class="fas fa-check-circle text-emerald-600 text-xs" aria-hidden="true"></i>
                                    <span>Data Terverifikasi</span>
                                </span>
                            @elseif($statusDaftar == 'Ditolak')
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-rose-600 whitespace-nowrap" title="Status Pendaftaran: Perlu Perbaikan">
                                    <i class="fas fa-exclamation-circle text-rose-500 text-xs" aria-hidden="true"></i>
                                    <span>Perlu Perbaikan</span>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-amber-600 whitespace-nowrap" title="Status Pendaftaran: Menunggu Verifikasi">
                                    <i class="fas fa-clock text-amber-500 text-xs" aria-hidden="true"></i>
                                    <span>Menunggu</span>
                                </span>
                            @endif
                        </td>

                        {{-- STATUS PEMBAYARAN (Tanpa Bubble) --}}
                        <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap min-w-0" style="text-align: center !important;">
                            @if(in_array($statusBayar, ['Lunas', 'Diterima']))
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-700 whitespace-nowrap" title="Status Pembayaran: Lunas">
                                    <i class="fas fa-check-circle text-emerald-600 text-xs" aria-hidden="true"></i>
                                    <span>Lunas</span>
                                </span>
                            @elseif($statusBayar && in_array($statusBayar, ['Menunggu', 'Pending', 'Menunggu Konfirmasi', 'Menunggu Pembayaran']))
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-amber-600 whitespace-nowrap" title="Status Pembayaran: Menunggu Pembayaran">
                                    <i class="fas fa-clock text-amber-500 text-xs" aria-hidden="true"></i>
                                    <span>Menunggu Bayar</span>
                                </span>
                            @elseif($statusBayar && in_array($statusBayar, ['Gagal', 'Kadaluarsa', 'Ditolak']))
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-rose-600 whitespace-nowrap" title="Status Pembayaran: Gagal / Kedaluwarsa">
                                    <i class="fas fa-times-circle text-rose-500 text-xs" aria-hidden="true"></i>
                                    <span>Gagal</span>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-slate-500 whitespace-nowrap" title="Status Pembayaran: Belum Bayar">
                                    <i class="fas fa-minus-circle text-slate-400 text-xs" aria-hidden="true"></i>
                                    <span>Belum Bayar</span>
                                </span>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td class="px-4 py-3.5 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($isArchived)
                                    {{-- Tombol Pulihkan --}}
                                    <button type="button"
                                            @click="openModal('restore', '{{ route('restorecasis', $value->id_calon_siswa) }}', '{{ addslashes($namaSiswa) }}', '{{ addslashes($noReg) }}', {{ $hasRelatedData ? 'true' : 'false' }})"
                                            class="casis-btn-action px-2.5 py-1.5 h-8 rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-600 hover:text-white border border-emerald-300 text-xs font-semibold transition-colors flex items-center gap-1 shadow-xs"
                                            title="Pulihkan data calon siswa">
                                        <i class="fas fa-undo-alt text-xs" aria-hidden="true"></i>
                                        <span>Pulihkan</span>
                                    </button>
                                @else
                                    {{-- Tombol Lihat --}}
                                    <a href="{{ route('detailcasis', $value->id_calon_siswa) }}"
                                       class="casis-btn-action w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 hover:bg-gray-100 text-gray-500 hover:text-gray-700 border border-slate-200/60 transition-colors shrink-0"
                                       title="Lihat Detail {{ $namaSiswa }}"
                                       aria-label="Lihat Detail {{ $namaSiswa }}">
                                       <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                                    </a>

                                    {{-- Tombol Ubah --}}
                                    <a href="{{ route('editdata', $value->id_calon_siswa) }}"
                                       class="casis-btn-action w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 hover:bg-gray-100 text-gray-500 hover:text-gray-700 border border-slate-200/60 transition-colors shrink-0"
                                       title="Ubah Data {{ $namaSiswa }}"
                                       aria-label="Ubah Data {{ $namaSiswa }}">
                                       <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    </a>

                                    {{-- Tombol Titik Tiga (⋯) dengan Popover Fixed Position --}}
                                    <button type="button"
                                            @click="openMenu($event, {{ $value->id_calon_siswa }}, '{{ addslashes($namaSiswa) }}', '{{ addslashes($noReg) }}', {{ $hasRelatedData ? 'true' : 'false' }}, false)"
                                            class="casis-btn-action w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 hover:bg-gray-100 text-gray-500 hover:text-gray-700 border border-slate-200/60 transition-colors shrink-0"
                                            aria-haspopup="menu"
                                            :aria-expanded="activeMenu?.id === {{ $value->id_calon_siswa }}"
                                            aria-label="Menu Aksi Lainnya {{ $namaSiswa }}"
                                            title="Menu Aksi Lainnya">
                                        <i class="fas fa-ellipsis-v text-xs" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            @if(!empty($q) || $filterTa || $filterStatusDaftar || $filterStatusBayar || $filterStatusArsip !== 'aktif')
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mb-3 border border-slate-200">
                                        <i class="fas fa-search text-xl" aria-hidden="true"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 mb-1">Tidak ada hasil yang cocok</h3>
                                    <p class="text-slate-500 text-sm mb-4">Tidak ditemukan data calon siswa yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
                                    <a href="{{ route('indexcasis') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-xl transition-colors border border-green-200">
                                        <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                                        <span>Reset Pencarian &amp; Filter</span>
                                    </a>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mb-3 border border-slate-100">
                                        <i class="fas fa-folder-open text-xl" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-slate-500 font-medium text-sm">Belum ada data calon siswa.</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD LIST (< 700px) --}}
        <div class="casis-cards-view divide-y divide-slate-100">
            @forelse($data as $value)
            @php
                $isArchived = $value->trashed();
                $namaSiswa = $value->nama_calon_siswa ?: $value->nama;
                $noReg = $value->no_registrasi ?: '-';
                $rawNik = $value->nik_calon_siswa ?: $value->nik;
                $maskedNik = (strlen($rawNik) >= 8)
                    ? substr($rawNik, 0, 4) . '••••••••' . substr($rawNik, -4)
                    : ($rawNik ?: '-');
                $tglDaftar = $value->tgl_pendaftaran ? \Carbon\Carbon::parse($value->tgl_pendaftaran)->translatedFormat('d M Y') : '-';

                $namaAyah = $value->nama_ayah ? Str::title($value->nama_ayah) : '-';
                $namaIbu = $value->nama_ibu ? Str::title($value->nama_ibu) : '-';
                $phone = $value->no_hp_orang_tua ?: $value->user?->no_hp;
                $waDigits = '';
                $formattedPhone = '';
                if ($phone) {
                    $cleanDigits = preg_replace('/\D/', '', $phone);
                    if (str_starts_with($cleanDigits, '0')) {
                        $waDigits = '62' . substr($cleanDigits, 1);
                    } elseif (str_starts_with($cleanDigits, '62')) {
                        $waDigits = $cleanDigits;
                    } else {
                        $waDigits = '62' . $cleanDigits;
                    }
                    if (strlen($cleanDigits) >= 10 && strlen($cleanDigits) <= 13 && str_starts_with($cleanDigits, '08')) {
                        $formattedPhone = substr($cleanDigits, 0, 4) . '-' . substr($cleanDigits, 4, 4) . '-' . substr($cleanDigits, 8);
                    } else {
                        $formattedPhone = $phone;
                    }
                }

                $docTypes = [
                    ['type' => 'Akta Kelahiran', 'label' => 'Akte', 'required' => true],
                    ['type' => 'Kartu Keluarga', 'label' => 'KK', 'required' => true],
                    ['type' => 'Pas Foto', 'label' => 'Foto', 'required' => true],
                    ['type' => 'KPS / KIP / PKH', 'label' => 'KPS', 'required' => false],
                ];
                $validCount = 0;
                $menungguCount = 0;
                $tidakValidCount = 0;
                $uploadedCount = 0;

                foreach(['Akta Kelahiran', 'Kartu Keluarga', 'Pas Foto'] as $reqType) {
                    $d = $value->dokumen?->firstWhere('jenis_dokumen', $reqType);
                    if ($d && !empty($d->file_path)) {
                        $uploadedCount++;
                        if ($d->status_verifikasi === 'Valid') {
                            $validCount++;
                        } elseif ($d->status_verifikasi === 'Tidak Valid') {
                            $tidakValidCount++;
                        } else {
                            $menungguCount++;
                        }
                    }
                }

                $statusDaftar = $value->status_pendaftaran;
                $pembayaran = $value->pembayaran;
                $statusBayar = $pembayaran?->status_pembayaran;
                $hasRelatedData = ($pembayaran !== null);
            @endphp
            <div class="p-4 sm:p-5 space-y-3.5 {{ $isArchived ? 'bg-amber-50/40' : '' }}">
                {{-- Header Kartu: Nama & Gender --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-slate-900 text-base leading-snug">{{ $namaSiswa }}</h3>
                            @if($value->jenis_kelamin == 'Laki-Laki')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-800 border border-sky-200">
                                    <i class="fas fa-mars text-[10px] text-sky-600" aria-hidden="true"></i> Laki-laki
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-50 text-pink-800 border border-pink-200">
                                    <i class="fas fa-venus text-[10px] text-pink-600" aria-hidden="true"></i> Perempuan
                                </span>
                            @endif

                            @if($isArchived)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                                    <i class="fas fa-archive text-[10px]" aria-hidden="true"></i> Terarsip
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-600 mt-1">
                            <span class="font-mono text-sm font-semibold text-slate-800">{{ $noReg }}</span>
                            <span class="text-slate-300" aria-hidden="true">·</span>
                            <span class="font-mono text-sm text-slate-600">{{ $maskedNik }}</span>
                        </div>
                    </div>
                </div>

                {{-- Status Pendaftaran & Pembayaran --}}
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Pendaftaran</span>
                        @if($statusDaftar == 'Diterima')
                            <span class="font-semibold text-emerald-800 flex items-center gap-1">
                                <i class="fas fa-check-circle text-emerald-600" aria-hidden="true"></i> Data Terverifikasi
                            </span>
                        @elseif($statusDaftar == 'Ditolak')
                            <span class="font-semibold text-rose-800 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-rose-600" aria-hidden="true"></i> Perlu Perbaikan
                            </span>
                        @else
                            <span class="font-semibold text-amber-800 flex items-center gap-1">
                                <i class="fas fa-clock text-amber-600" aria-hidden="true"></i> Menunggu
                            </span>
                        @endif
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Pembayaran</span>
                        @if(in_array($statusBayar, ['Lunas', 'Diterima']))
                            <span class="font-semibold text-emerald-800 flex items-center gap-1">
                                <i class="fas fa-check-circle text-emerald-600" aria-hidden="true"></i> Lunas
                            </span>
                        @elseif($statusBayar && in_array($statusBayar, ['Menunggu', 'Pending', 'Menunggu Konfirmasi', 'Menunggu Pembayaran']))
                            <span class="font-semibold text-amber-800 flex items-center gap-1">
                                <i class="fas fa-clock text-amber-600" aria-hidden="true"></i> Menunggu Bayar
                            </span>
                        @elseif($statusBayar && in_array($statusBayar, ['Gagal', 'Kadaluarsa', 'Ditolak']))
                            <span class="font-semibold text-rose-800 flex items-center gap-1">
                                <i class="fas fa-times-circle text-rose-600" aria-hidden="true"></i> Gagal
                            </span>
                        @else
                            <span class="font-semibold text-slate-700 flex items-center gap-1">
                                <i class="fas fa-minus-circle text-slate-400" aria-hidden="true"></i> Belum Bayar
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Detail Orang Tua & Berkas --}}
                <div class="bg-slate-50/70 rounded-xl p-3 space-y-2.5 text-xs text-slate-700 border border-slate-100">
                    <div>
                        <span class="text-slate-500 font-medium">Orang Tua:</span>
                        <div class="mt-0.5 space-y-0.5 font-medium text-slate-800 text-sm">
                            <div>Ayah: {{ $namaAyah }}</div>
                            <div>Ibu: {{ $namaIbu }}</div>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium">No. HP / WhatsApp:</span>
                        <div class="mt-1">
                            @if($phone)
                                <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener noreferrer"
                                   aria-label="Hubungi lewat WhatsApp {{ $formattedPhone }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-900 transition-colors">
                                    <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                        <i class="fab fa-whatsapp text-xs" aria-hidden="true"></i>
                                    </span>
                                    <span>{{ $formattedPhone }}</span>
                                </a>
                            @else
                                <span class="text-xs text-slate-500 italic">Belum diisi</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="text-slate-500 font-medium">Berkas Dokumen:</span>
                            @if($validCount === 3)
                                <span class="text-[11px] font-semibold text-emerald-800">3/3 Valid</span>
                            @elseif($tidakValidCount > 0)
                                <span class="text-[11px] font-semibold text-rose-800">{{ $validCount }}/3 Valid</span>
                            @elseif($menungguCount > 0)
                                <span class="text-[11px] font-semibold text-amber-800">{{ $validCount }}/3 Valid</span>
                            @else
                                <span class="text-[11px] font-medium text-slate-500">{{ $validCount }}/3 Valid</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($docTypes as $docItem)
                                @php
                                    $doc = $value->dokumen?->firstWhere('jenis_dokumen', $docItem['type']);
                                    $hasFile = $doc && !empty($doc->file_path);
                                    $status = $hasFile ? ($doc->status_verifikasi ?: 'Menunggu') : 'Belum diunggah';
                                    $statusClass = match($status) {
                                        'Valid' => 'bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100',
                                        'Tidak Valid' => 'bg-rose-50 text-rose-800 border-rose-300 hover:bg-rose-100',
                                        'Menunggu' => 'bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100',
                                        default => 'bg-slate-100 text-slate-600 border-dashed border-slate-300 cursor-default',
                                    };
                                @endphp
                                @if($hasFile)
                                    <a href="{{ route('dokumen.show', $doc->id_dokumen) }}" target="_blank" rel="noopener noreferrer"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold border transition-colors {{ $statusClass }}"
                                       title="{{ $docItem['label'] }}: {{ $status }}"
                                       aria-label="{{ $docItem['label'] }}: {{ $status }}">
                                        {{ $docItem['label'] }}
                                    </a>
                                @else
                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold border {{ $statusClass }}"
                                          title="{{ $docItem['label'] }}: Belum diunggah{{ !$docItem['required'] ? ' (opsional)' : '' }}"
                                          aria-label="{{ $docItem['label'] }}: Belum diunggah{{ !$docItem['required'] ? ' (opsional)' : '' }}">
                                        {{ $docItem['label'] }}
                                    </span>
                                @endif
                            @endforeach
                            <span class="text-slate-500 text-[11px]">· KPS: Opsional</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons (Mobile Touch Target >= 44px) --}}
                <div class="flex items-center gap-2 pt-1">
                    @if($isArchived)
                        <button type="button"
                                @click="openModal('restore', '{{ route('restorecasis', $value->id_calon_siswa) }}', '{{ addslashes($namaSiswa) }}', '{{ addslashes($noReg) }}', {{ $hasRelatedData ? 'true' : 'false' }})"
                                class="casis-btn-action w-full min-h-[44px] px-4 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 flex items-center justify-center gap-2 text-xs font-semibold transition-colors shadow-xs">
                            <i class="fas fa-undo-alt text-xs" aria-hidden="true"></i>
                            <span>Pulihkan Data Calon Siswa</span>
                        </button>
                    @else
                        <a href="{{ route('detailcasis', $value->id_calon_siswa) }}"
                           class="casis-btn-action flex-1 min-h-[44px] px-3 rounded-xl bg-slate-100 text-slate-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-center gap-1.5 text-xs font-semibold transition-colors">
                            <i class="fas fa-eye text-xs" aria-hidden="true"></i>
                            <span>Lihat</span>
                        </a>
                        <a href="{{ route('editdata', $value->id_calon_siswa) }}"
                           class="casis-btn-action flex-1 min-h-[44px] px-3 rounded-xl bg-slate-100 text-slate-700 hover:bg-amber-50 hover:text-amber-700 flex items-center justify-center gap-1.5 text-xs font-semibold transition-colors">
                            <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                            <span>Ubah</span>
                        </a>
                        <button type="button"
                                @click="openMenu($event, {{ $value->id_calon_siswa }}, '{{ addslashes($namaSiswa) }}', '{{ addslashes($noReg) }}', {{ $hasRelatedData ? 'true' : 'false' }}, false)"
                                class="casis-btn-action min-h-[44px] min-w-[44px] px-3 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center gap-1.5 text-xs font-semibold transition-colors"
                                aria-haspopup="menu"
                                :aria-expanded="activeMenu?.id === {{ $value->id_calon_siswa }}"
                                aria-label="Menu Aksi {{ $namaSiswa }}"
                                title="Menu Aksi">
                            <i class="fas fa-ellipsis-v text-xs" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                @if(!empty($q) || $filterTa || $filterStatusDaftar || $filterStatusBayar || $filterStatusArsip !== 'aktif')
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mb-3 border border-slate-200">
                            <i class="fas fa-search text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Tidak ada hasil yang cocok</h3>
                        <p class="text-slate-500 text-xs mb-4">Tidak ditemukan data calon siswa yang sesuai dengan filter atau pencarian Anda.</p>
                        <a href="{{ route('indexcasis') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-xl transition-colors border border-green-200">
                            <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                            <span>Reset Pencarian &amp; Filter</span>
                        </a>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mb-3 border border-slate-100">
                            <i class="fas fa-folder-open text-xl" aria-hidden="true"></i>
                        </div>
                        <p class="text-slate-500 font-medium text-sm">Belum ada data calon siswa.</p>
                    </div>
                @endif
            </div>
            @endforelse
        </div>

        {{-- Pagination & Rows Per Page --}}
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
            <div class="flex items-center gap-2">
                <span>Tampilkan:</span>
                <form action="{{ route('indexcasis') }}" method="get" class="inline">
                    @foreach(request()->except(['per_page', 'page']) as $k => $v)
                        @if($v !== null && $v !== '')
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endforeach
                    <select name="per_page" onchange="this.form.submit()" class="casis-filter-ctrl bg-white border border-slate-300 text-slate-700 text-xs rounded-lg py-1 px-2">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 data</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 data</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 data</option>
                    </select>
                </form>
                <span>dari total {{ number_format($data->total()) }} data</span>
            </div>

            @if($data->hasPages())
                <div>
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- FLOATING POPOVER ACTION MENU (Fixed Position - Tidak Terpotong Oleh Overflow Tabel) --}}
    <div x-show="activeMenu !== null"
         x-cloak
         id="floating-action-menu"
         @click.outside="closeMenu()"
         @keydown.escape.window="closeMenu()"
         @keydown.arrow-down.prevent="focusNextMenuItem(1)"
         @keydown.arrow-up.prevent="focusNextMenuItem(-1)"
         :style="`position: fixed; top: ${activeMenu?.top}px; left: ${activeMenu?.left}px; width: 210px; z-index: 9999;`"
         class="bg-white rounded-xl shadow-2xl border border-slate-200 py-1.5 focus:outline-none"
         style="display: none;"
         role="menu"
         aria-orientation="vertical">

        {{-- Mode Arsip: Tombol Pulihkan --}}
        <button type="button"
                x-show="activeMenu?.isArchived"
                @click="openModal('restore', `/admin/data/casis/restore/${activeMenu.id}`, activeMenu.name, activeMenu.reg, activeMenu.hasRelated)"
                class="casis-menu-item w-full text-left px-4 py-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 flex items-center gap-2.5 transition-colors min-h-[44px]"
                role="menuitem" tabindex="0">
            <i class="fas fa-undo-alt text-xs text-emerald-600" aria-hidden="true"></i>
            <span>Pulihkan Data</span>
        </button>

        {{-- Mode Aktif: Opsi Arsipkan --}}
        <button type="button"
                x-show="!activeMenu?.isArchived"
                @click="openModal('archive', `/admin/data/casis/delete/${activeMenu.id}`, activeMenu.name, activeMenu.reg, activeMenu.hasRelated)"
                class="casis-menu-item w-full text-left px-4 py-3 text-xs font-semibold text-amber-800 hover:bg-amber-50 flex items-center gap-2.5 transition-colors min-h-[44px]"
                role="menuitem" tabindex="0">
            <i class="fas fa-archive text-xs text-amber-600" aria-hidden="true"></i>
            <span>Arsipkan Data</span>
        </button>

        {{-- Mode Aktif: Opsi Hapus Permanen (Hanya jika belum ada pembayaran / seleksi / daftar ulang) --}}
        <div x-show="!activeMenu?.isArchived && !activeMenu?.hasRelated" class="border-t border-slate-100 my-1"></div>
        <button type="button"
                x-show="!activeMenu?.isArchived && !activeMenu?.hasRelated"
                @click="openModal('force_delete', `/admin/data/casis/delete/${activeMenu.id}`, activeMenu.name, activeMenu.reg, false)"
                class="casis-menu-item w-full text-left px-4 py-3 text-xs font-semibold text-rose-700 hover:bg-rose-50 flex items-center gap-2.5 transition-colors min-h-[44px]"
                role="menuitem" tabindex="0">
            <i class="fas fa-trash-alt text-xs text-rose-600" aria-hidden="true"></i>
            <span>Hapus Permanen</span>
        </button>
    </div>

    {{-- MODAL AKSI (Arsipkan / Hapus Permanen / Pulihkan) --}}
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="action-modal-title"
         aria-describedby="action-modal-description"
         style="display: none;"
         @keydown.escape.window="modalOpen = false">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="modalOpen = false"
             x-show="modalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             aria-hidden="true"></div>

        {{-- Modal Box --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-100"
                 x-show="modalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="modalOpen = false">
                
                {{-- Header Modal --}}
                <div class="flex items-start gap-4">
                    {{-- Icon Berdasarkan Mode --}}
                    <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl sm:mx-0"
                         :class="{
                             'bg-amber-100 text-amber-700': modalMode === 'archive',
                             'bg-rose-100 text-rose-600': modalMode === 'force_delete',
                             'bg-emerald-100 text-emerald-700': modalMode === 'restore'
                         }">
                        <template x-if="modalMode === 'archive'">
                            <i class="fas fa-archive text-lg" aria-hidden="true"></i>
                        </template>
                        <template x-if="modalMode === 'force_delete'">
                            <i class="fas fa-trash-alt text-lg" aria-hidden="true"></i>
                        </template>
                        <template x-if="modalMode === 'restore'">
                            <i class="fas fa-undo-alt text-lg" aria-hidden="true"></i>
                        </template>
                    </div>

                    <div class="flex-1 mt-0.5">
                        <h3 class="text-lg font-bold text-slate-800 font-heading" id="action-modal-title">
                            <span x-show="modalMode === 'archive'">Arsipkan Data Calon Siswa</span>
                            <span x-show="modalMode === 'force_delete'">Hapus Permanen Data Calon Siswa</span>
                            <span x-show="modalMode === 'restore'">Pulihkan Data Calon Siswa</span>
                        </h3>
                        <div class="mt-2 text-sm text-slate-600 space-y-2.5" id="action-modal-description">
                            <p>
                                Calon Siswa: <strong class="text-slate-900 font-semibold" x-text="studentName"></strong> 
                                (<span class="font-mono text-xs font-semibold text-slate-700" x-text="studentReg"></span>)
                            </p>

                            {{-- Pesan Khusus Mode Arsip --}}
                            <template x-if="modalMode === 'archive'">
                                <div class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200 leading-relaxed">
                                    <p class="font-semibold mb-1"><i class="fas fa-info-circle mr-1" aria-hidden="true"></i> Informasi Pengarsipan:</p>
                                    <p>Data calon siswa akan disembunyikan dari daftar pendaftar aktif dan seleksi. Seluruh riwayat pembayaran keuangan dan berkas dokumen tetap terjaga aman. Anda dapat memulihkan data ini kapan saja melalui tab atau filter arsip.</p>
                                </div>
                            </template>

                            {{-- Pesan Khusus Mode Hapus Permanen --}}
                            <template x-if="modalMode === 'force_delete'">
                                <div class="text-xs text-rose-800 bg-rose-50 p-3 rounded-xl border border-rose-200 leading-relaxed">
                                    <p class="font-semibold mb-1"><i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i> Tindakan Ini Permanen:</p>
                                    <p>Data calon siswa dan seluruh berkas persyaratan yang terunggah di server akan dihapus secara fisik dan tidak dapat dipulihkan. Akun pengguna orang tua tetap dipertahankan.</p>
                                </div>
                            </template>

                            {{-- Pesan Khusus Mode Pulihkan --}}
                            <template x-if="modalMode === 'restore'">
                                <div class="text-xs text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-200 leading-relaxed">
                                    <p class="font-semibold mb-1"><i class="fas fa-check-circle mr-1" aria-hidden="true"></i> Pemulihan Data:</p>
                                    <p>Data calon siswa ini akan dikembalikan ke daftar pendaftar aktif dan dapat diproses kembali seperti biasa.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Form Konfirmasi --}}
                <form :action="formAction" method="POST" class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    @csrf
                    <template x-if="modalMode !== 'restore'">
                        <input type="hidden" name="_method" value="DELETE">
                    </template>
                    <input type="hidden" name="action" :value="modalMode">

                    <button type="button"
                            @click="modalOpen = false"
                            class="casis-btn-action w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors min-h-[44px]">
                        Batal
                    </button>

                    {{-- Tombol Submit Berdasarkan Mode --}}
                    <button type="submit"
                            x-show="modalMode === 'archive'"
                            class="casis-btn-action w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-colors shadow-xs min-h-[44px] flex items-center justify-center gap-2">
                        <i class="fas fa-archive text-xs" aria-hidden="true"></i>
                        <span>Ya, Arsipkan Data</span>
                    </button>

                    <button type="submit"
                            x-show="modalMode === 'force_delete'"
                            class="casis-btn-action w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-xs min-h-[44px] flex items-center justify-center gap-2">
                        <i class="fas fa-trash-alt text-xs" aria-hidden="true"></i>
                        <span>Ya, Hapus Permanen</span>
                    </button>

                    <button type="submit"
                            x-show="modalMode === 'restore'"
                            class="casis-btn-action w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-xs min-h-[44px] flex items-center justify-center gap-2">
                        <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                        <span>Ya, Pulihkan Data</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
