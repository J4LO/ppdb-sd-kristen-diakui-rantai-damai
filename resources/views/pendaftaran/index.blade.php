@extends('layout.tampilan')

@section('title', 'Verifikasi Dokumen')
@section('page-title', 'Verifikasi Dokumen')

@push('styles')
<style>
    /* Scoped Styles untuk Verifikasi Dokumen - Selaras dengan menu Data Calon Siswa */
    .verif-page,
    .verif-page h1, .verif-page h2, .verif-page h3,
    .verif-page h4, .verif-page p, .verif-page span,
    .verif-page dt, .verif-page dd, .verif-page a,
    .verif-page button, .verif-page select, .verif-page input {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    .verif-btn-action:focus-visible,
    .verif-filter-ctrl:focus-visible,
    .verif-tab-card:focus-visible {
        outline: 2px solid #15803d !important;
        outline-offset: 2px !important;
    }
    .verif-btn-action:focus:not(:focus-visible),
    .verif-filter-ctrl:focus:not(:focus-visible),
    .verif-tab-card:focus:not(:focus-visible) {
        outline: none !important;
        box-shadow: none !important;
    }

    .verif-filter-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .verif-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .verif-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 240px));
        }
    }
</style>
@endpush

@section('content')
@php
    $activeFilterCount = 0;
    if (!empty($q)) $activeFilterCount++;
    if (!empty($filterTa)) $activeFilterCount++;
    if (!empty($tab) && $tab !== 'menunggu') $activeFilterCount++;
@endphp

<div class="verif-page space-y-5 sm:space-y-6">

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

    {{-- Kartu Judul Ringkas (Sesuai Gaya Data Calon Siswa) --}}
    <div class="bg-white rounded-2xl px-5 py-4 sm:px-6 sm:py-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3.5">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 font-heading leading-tight tracking-tight">Verifikasi Dokumen</h1>
            <p class="text-slate-600 text-sm sm:text-[15px] mt-0.5">Periksa keabsahan berkas calon siswa per dokumen untuk menentukan status pendaftaran.</p>
        </div>
    </div>

    {{-- Ringkasan Kartu Angka (4 Kolom, Gaya Persis Menu Calon Siswa) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        {{-- Menunggu Verifikasi --}}
        <a href="{{ route('datapendaftaran', array_merge(request()->query(), ['tab' => 'menunggu', 'page' => null])) }}"
           class="verif-tab-card bg-white rounded-2xl p-4 sm:p-5 border transition-all flex items-center gap-3.5 group {{ $tab === 'menunggu' ? 'border-amber-400 ring-2 ring-amber-400/30 bg-amber-50/20 shadow-xs' : 'border-slate-200/80 shadow-xs hover:border-slate-300' }}"
           title="Tampilkan calon siswa menunggu verifikasi">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i class="fas fa-clock text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-slate-500 truncate">Menunggu Verifikasi</p>
                <div class="flex items-baseline justify-between gap-1.5 mt-0.5">
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading">{{ number_format($summary->menunggu) }}</p>
                    @if($tab === 'menunggu')
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded uppercase tracking-wider">Aktif</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Perlu Perbaikan --}}
        <a href="{{ route('datapendaftaran', array_merge(request()->query(), ['tab' => 'perlu_perbaikan', 'page' => null])) }}"
           class="verif-tab-card bg-white rounded-2xl p-4 sm:p-5 border transition-all flex items-center gap-3.5 group {{ $tab === 'perlu_perbaikan' ? 'border-rose-400 ring-2 ring-rose-400/30 bg-rose-50/20 shadow-xs' : 'border-slate-200/80 shadow-xs hover:border-slate-300' }}"
           title="Tampilkan calon siswa perlu perbaikan berkas">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i class="fas fa-exclamation-triangle text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-slate-500 truncate">Perlu Perbaikan</p>
                <div class="flex items-baseline justify-between gap-1.5 mt-0.5">
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading">{{ number_format($summary->perlu_perbaikan) }}</p>
                    @if($tab === 'perlu_perbaikan')
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded uppercase tracking-wider">Aktif</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Data Terverifikasi --}}
        <a href="{{ route('datapendaftaran', array_merge(request()->query(), ['tab' => 'terverifikasi', 'page' => null])) }}"
           class="verif-tab-card bg-white rounded-2xl p-4 sm:p-5 border transition-all flex items-center gap-3.5 group {{ $tab === 'terverifikasi' ? 'border-emerald-400 ring-2 ring-emerald-400/30 bg-emerald-50/20 shadow-xs' : 'border-slate-200/80 shadow-xs hover:border-slate-300' }}"
           title="Tampilkan calon siswa data terverifikasi">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i class="fas fa-check-circle text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-slate-500 truncate">Terverifikasi</p>
                <div class="flex items-baseline justify-between gap-1.5 mt-0.5">
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading">{{ number_format($summary->terverifikasi) }}</p>
                    @if($tab === 'terverifikasi')
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded uppercase tracking-wider">Aktif</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Semua Pendaftar --}}
        <a href="{{ route('datapendaftaran', array_merge(request()->query(), ['tab' => 'semua', 'page' => null])) }}"
           class="verif-tab-card bg-white rounded-2xl p-4 sm:p-5 border transition-all flex items-center gap-3.5 group {{ $tab === 'semua' ? 'border-slate-500 ring-2 ring-slate-400/30 bg-slate-50 shadow-xs' : 'border-slate-200/80 shadow-xs hover:border-slate-300' }}"
           title="Tampilkan semua data calon siswa">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i class="fas fa-users text-lg" aria-hidden="true"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-slate-500 truncate">Semua Pendaftar</p>
                <div class="flex items-baseline justify-between gap-1.5 mt-0.5">
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 font-heading">{{ number_format($summary->semua) }}</p>
                    @if($tab === 'semua')
                        <span class="text-[10px] font-bold text-slate-700 bg-slate-200 px-1.5 py-0.5 rounded uppercase tracking-wider">Aktif</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Filter & Pencarian Bar (Persis Gaya Menu Calon Siswa) --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs" x-data="{ filterOpenMobile: false }">
        <form action="{{ route('datapendaftaran') }}" method="get" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                {{-- Kolom Pencarian & Tombol Cari di sampingnya --}}
                <div class="flex items-center gap-2 flex-1 max-w-2xl">
                    <div class="relative flex-1">
                        <label for="search-input" class="sr-only">Cari Calon Siswa</label>
                        <input type="text"
                               id="search-input"
                               name="q"
                               class="verif-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl pl-10 pr-9 h-11 min-h-[44px] transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-600/20 focus:border-green-700"
                               placeholder="Cari nama calon siswa atau nomor registrasi..."
                               value="{{ $q ?? '' }}">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400" aria-hidden="true">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        @if(!empty($q))
                        <a href="{{ route('datapendaftaran', array_merge(request()->except(['q', 'page']))) }}"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                           title="Hapus kata kunci pencarian"
                           aria-label="Hapus kata kunci pencarian">
                            <i class="fas fa-times-circle text-sm" aria-hidden="true"></i>
                        </a>
                        @endif
                    </div>

                    {{-- Tombol Hijau: Cari & Filter langsung di samping input --}}
                    <button type="submit"
                            class="verif-btn-action px-4 sm:px-5 h-11 min-h-[44px] text-xs sm:text-sm font-semibold rounded-xl bg-green-700 hover:bg-green-800 text-white shadow-xs flex items-center gap-2 transition-colors shrink-0">
                        <i class="fas fa-search text-xs" aria-hidden="true"></i>
                        <span>Cari &amp; Filter</span>
                    </button>
                </div>

                {{-- Tombol Aksi di Baris yang Sama, Rata Kanan --}}
                <div class="flex items-center gap-2 self-end md:self-auto flex-wrap">
                    {{-- Tombol Toggle Filter di Layar Kecil --}}
                    <button type="button"
                            @click="filterOpenMobile = !filterOpenMobile"
                            class="md:hidden px-3.5 h-11 min-h-[44px] text-xs font-semibold rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 transition-colors">
                        <i class="fas fa-filter text-xs" aria-hidden="true"></i>
                        <span>Filter</span>
                        @if($activeFilterCount > 0)
                            <span class="w-5 h-5 rounded-full bg-green-700 text-white text-[10px] flex items-center justify-center font-bold">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('datapendaftaran') }}"
                           class="verif-btn-action px-3.5 h-11 min-h-[44px] text-xs sm:text-sm font-medium rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 transition-colors flex items-center gap-1.5"
                           title="Atur ulang seluruh filter">
                            <i class="fas fa-undo text-xs" aria-hidden="true"></i>
                            <span>Atur Ulang</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Grid Kontrol Filter Tambahan (Tahun Ajaran & Status Antrean) --}}
            <div class="verif-filter-grid pt-1" :class="{ 'block': filterOpenMobile, 'hidden md:grid': !filterOpenMobile }">
                {{-- Filter Tahun Ajaran --}}
                <div>
                    <label for="filter-ta" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Tahun Ajaran</label>
                    <select id="filter-ta" name="ta" class="verif-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-600/20 focus:border-green-700">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjaranList as $itemTa)
                            <option value="{{ $itemTa->id_tahun_ajaran }}" {{ $filterTa == $itemTa->id_tahun_ajaran ? 'selected' : '' }}>
                                {{ $itemTa->tahun_ajaran }} {{ $itemTa->status === 'Berlangsung' ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tab Antrean Dokumen --}}
                <div>
                    <label for="filter-tab" class="block text-xs sm:text-[13px] font-semibold text-slate-700 mb-1.5">Status Antrean Dokumen</label>
                    <select id="filter-tab" name="tab" class="verif-filter-ctrl w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs sm:text-sm rounded-xl px-3 h-11 min-h-[44px] transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-600/20 focus:border-green-700">
                        <option value="menunggu" {{ $tab === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi ({{ $summary->menunggu }})</option>
                        <option value="perlu_perbaikan" {{ $tab === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan ({{ $summary->perlu_perbaikan }})</option>
                        <option value="terverifikasi" {{ $tab === 'terverifikasi' ? 'selected' : '' }}>Data Terverifikasi ({{ $summary->terverifikasi }})</option>
                        <option value="semua" {{ $tab === 'semua' ? 'selected' : '' }}>Semua Data ({{ $summary->semua }})</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Data Antrean Verifikasi --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="w-16 px-4 py-3.5 font-bold text-center text-slate-600" style="text-align: center !important;">No</th>
                        <th class="px-5 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">No Registrasi</th>
                        <th class="px-5 py-3.5 font-bold text-center text-slate-700" style="text-align: center !important;">Calon Siswa</th>
                        <th class="px-5 py-3.5 font-bold text-center text-slate-700 whitespace-nowrap" style="text-align: center !important;">Tgl Daftar</th>
                        <th class="px-5 py-3.5 font-bold text-center text-slate-700" style="text-align: center !important;">Kelengkapan Berkas</th>
                        <th class="px-5 py-3.5 font-bold text-center text-slate-700" style="text-align: center !important;">Status Pendaftaran</th>
                        <th class="px-5 py-3.5 text-center w-36 font-bold text-slate-700" style="text-align: center !important;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($data as $index => $item)
                        @php
                            $rowNum = $data->firstItem() + $index;
                            $aktaDoc = $item->dokumen->firstWhere('jenis_dokumen', 'Akta Kelahiran');
                            $kkDoc   = $item->dokumen->firstWhere('jenis_dokumen', 'Kartu Keluarga');
                            $fotoDoc = $item->dokumen->firstWhere('jenis_dokumen', 'Pas Foto');
                            $kpsDoc  = $item->dokumen->firstWhere('jenis_dokumen', 'KPS');

                            $hasPendingDoc = $item->dokumen->contains('status_verifikasi', 'Menunggu');
                        @endphp
                        <tr class="hover:bg-slate-50/70 border-b border-gray-100 transition-colors align-middle {{ $hasPendingDoc ? 'bg-amber-50/20' : '' }}">
                            {{-- No --}}
                            <td class="w-16 px-4 py-3.5 text-center font-medium text-slate-700 text-xs align-middle" style="text-align: center !important;">
                                {{ $rowNum }}
                            </td>

                            {{-- No Registrasi --}}
                            <td class="px-5 py-3.5 whitespace-nowrap align-middle text-center" style="text-align: center !important;">
                                <span class="font-mono text-xs font-semibold text-slate-800">
                                    {{ $item->no_registrasi ?? '-' }}
                                </span>
                            </td>

                            {{-- Calon Siswa --}}
                            <td class="px-5 py-3.5 align-middle text-center" style="text-align: center !important;">
                                <span class="font-bold text-slate-900 text-sm">
                                    {{ $item->nama_calon_siswa }}
                                </span>
                            </td>

                            {{-- Tgl Daftar --}}
                            <td class="px-5 py-3.5 align-middle text-center whitespace-nowrap text-xs text-slate-700 font-medium" style="text-align: center !important;" title="Tanggal Pendaftaran: {{ $item->tgl_pendaftaran ? \Carbon\Carbon::parse($item->tgl_pendaftaran)->translatedFormat('d M Y') : '-' }}">
                                {{ $item->tgl_pendaftaran ? \Carbon\Carbon::parse($item->tgl_pendaftaran)->translatedFormat('d M Y') : '-' }}
                            </td>

                            {{-- Status Dokumen Per-Item --}}
                            <td class="px-5 py-3.5 align-middle text-center" style="text-align: center !important;">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    {{-- Akta --}}
                                    @if($aktaDoc)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $aktaDoc->status_verifikasi === 'Valid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($aktaDoc->status_verifikasi === 'Tidak Valid' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}"
                                            title="Akta Kelahiran: {{ $aktaDoc->status_verifikasi }}{{ $aktaDoc->catatan ? ' (' . $aktaDoc->catatan . ')' : '' }}">
                                            <i class="fas {{ $aktaDoc->status_verifikasi === 'Valid' ? 'fa-check' : ($aktaDoc->status_verifikasi === 'Tidak Valid' ? 'fa-times' : 'fa-clock') }} text-[10px]"></i>
                                            Akta: {{ $aktaDoc->status_verifikasi }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200" title="Akta Kelahiran belum diunggah">
                                            Akta: -
                                        </span>
                                    @endif

                                    {{-- KK --}}
                                    @if($kkDoc)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $kkDoc->status_verifikasi === 'Valid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($kkDoc->status_verifikasi === 'Tidak Valid' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}"
                                            title="Kartu Keluarga: {{ $kkDoc->status_verifikasi }}{{ $kkDoc->catatan ? ' (' . $kkDoc->catatan . ')' : '' }}">
                                            <i class="fas {{ $kkDoc->status_verifikasi === 'Valid' ? 'fa-check' : ($kkDoc->status_verifikasi === 'Tidak Valid' ? 'fa-times' : 'fa-clock') }} text-[10px]"></i>
                                            KK: {{ $kkDoc->status_verifikasi }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200" title="Kartu Keluarga belum diunggah">
                                            KK: -
                                        </span>
                                    @endif

                                    {{-- Foto --}}
                                    @if($fotoDoc)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $fotoDoc->status_verifikasi === 'Valid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($fotoDoc->status_verifikasi === 'Tidak Valid' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}"
                                            title="Pas Foto: {{ $fotoDoc->status_verifikasi }}{{ $fotoDoc->catatan ? ' (' . $fotoDoc->catatan . ')' : '' }}">
                                            <i class="fas {{ $fotoDoc->status_verifikasi === 'Valid' ? 'fa-check' : ($fotoDoc->status_verifikasi === 'Tidak Valid' ? 'fa-times' : 'fa-clock') }} text-[10px]"></i>
                                            Foto: {{ $fotoDoc->status_verifikasi }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200" title="Pas Foto belum diunggah">
                                            Foto: -
                                        </span>
                                    @endif

                                    {{-- KPS jika ada --}}
                                    @if($kpsDoc)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold
                                            {{ $kpsDoc->status_verifikasi === 'Valid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($kpsDoc->status_verifikasi === 'Tidak Valid' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}"
                                            title="KPS: {{ $kpsDoc->status_verifikasi }}{{ $kpsDoc->catatan ? ' (' . $kpsDoc->catatan . ')' : '' }}">
                                            KPS: {{ $kpsDoc->status_verifikasi }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Pendaftaran --}}
                            <td class="px-5 py-3.5 align-middle whitespace-nowrap text-center" style="text-align: center !important;">
                                @if($item->status_pendaftaran === 'Diterima')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-check-circle"></i>
                                        Data Terverifikasi
                                    </span>
                                @elseif($item->status_pendaftaran === 'Ditolak')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200" title="{{ $item->catatan_pendaftaran }}">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Perlu Perbaikan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock"></i>
                                        Menunggu Verifikasi
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-3.5 align-middle text-center whitespace-nowrap">
                                <a href="{{ route('periksadokumen', $item->id_calon_siswa) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white text-xs font-semibold rounded-xl shadow-xs hover:shadow transition-all">
                                    <i class="fas fa-clipboard-check text-xs"></i>
                                    <span>Periksa Berkas</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-3">
                                        <i class="fas fa-folder-open text-2xl"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Tidak ada data pendaftaran</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        @if(!empty($q))
                                            Tidak ditemukan data calon siswa yang sesuai dengan kata kunci pencarian.
                                        @else
                                            Belum ada calon siswa dalam antrean verifikasi ini.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($data->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $data->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
