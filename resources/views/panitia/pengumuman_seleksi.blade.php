@extends('layout.tampilanpanitia')

@section('page-title', 'Publikasi Hasil Seleksi')

@push('styles')
<style>
    .btn-action-primary {
        min-height: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        background-color: #059669;
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        border: none;
    }
    .btn-action-primary:hover {
        background-color: #047857;
    }
    .btn-action-secondary {
        min-height: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-action-secondary:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #1e293b;
    }

    /* Tampilan Responsif Handal: Desktop Table (>= 900px) vs Mobile Card (< 900px) */
    @media (min-width: 900px) {
        .view-desktop-table {
            display: block !important;
        }
        .view-mobile-cards {
            display: none !important;
        }
    }
    @media (max-width: 899.98px) {
        .view-desktop-table {
            display: none !important;
        }
        .view-mobile-cards {
            display: block !important;
        }
    }

    /* Style Khusus Kartu Nilai Mobile Bebas Garis Hitam */
    .mobile-score-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.75rem;
    }
    .mobile-score-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
        padding-bottom: 0.625rem;
        border-bottom: 1px solid #e2e8f0;
        text-align: center;
    }

    /* Grid Toolbar Pencarian Responsif */
    .search-toolbar-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 0.75rem;
        align-items: center;
    }
    @media (max-width: 639.98px) {
        .search-toolbar-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-7xl mx-auto"
     x-data="{
         showPublishModal: false,
         totalPeserta: {{ $totalPeserta ?? 0 }},
         belumDinilai: {{ $belumDinilai ?? 0 }},
         jumlahLulus: {{ $jumlahLulus ?? 0 }},
         jumlahTidakLulus: {{ $jumlahTidakLulus ?? 0 }},
         isSemuaDinilai: {{ ($isSemuaDinilai ?? false) ? 'true' : 'false' }},
         isDiumumkan: {{ ($isDiumumkan ?? false) ? 'true' : 'false' }}
     }">

    {{-- Header --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 font-heading">
            Publikasi Hasil Seleksi
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            Periksa hasil otomatis, lalu umumkan ke calon siswa.
        </p>
    </div>

    {{-- Panel Publikasi (Menggantikan Banner Biru) --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm" aria-live="polite">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            {{-- Bagian Kiri: Status, Progres, Baris Ringkas & Catatan Info --}}
            <div class="flex-1 min-w-0 space-y-3">
                {{-- Status Ikon + Teks --}}
                <div class="flex items-center gap-3">
                    @if($isDiumumkan)
                        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-emerald-950">
                                    Hasil Seleksi Resmi Telah Dipublikasikan
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    TERKUNCI
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-emerald-800 font-medium mt-0.5">
                                Dipublikasikan pada {{ $waktuPublikasi ? \Carbon\Carbon::parse($waktuPublikasi)->translatedFormat('l, d F Y - H:i') : '-' }} WITA
                                @if($panitiaPengumum)
                                    oleh <span class="font-bold">{{ $panitiaPengumum->nama_panitia }}</span>
                                @endif
                            </p>
                        </div>
                    @elseif(($belumDinilai ?? 0) > 0)
                        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-amber-950">
                                Masih {{ $belumDinilai }} peserta belum dinilai
                            </h2>
                            <p class="text-xs sm:text-sm text-amber-900 mt-0.5">
                                Lengkapi seluruh penilaian sebelum mengumumkan hasil seleksi.
                                <a href="{{ route('nilai') }}" class="font-bold text-amber-700 underline hover:text-amber-800 ml-1 inline-flex items-center gap-1">
                                    Ke daftar belum dinilai <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </p>
                        </div>
                    @else
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">
                                Semua peserta sudah dinilai ({{ $sudahDinilai }} dari {{ $totalPeserta }})
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                                Seluruh evaluasi telah selesai. Hasil seleksi siap diumumkan ke calon siswa.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Bilah Progres Tipis (6px) --}}
                @php
                    $persenSelesai = $totalPeserta > 0 ? round(($sudahDinilai / $totalPeserta) * 100) : 0;
                @endphp
                <div class="space-y-1">
                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200/60">
                        <div class="h-full rounded-full transition-all duration-300 {{ $isDiumumkan ? 'bg-emerald-600' : ($persenSelesai === 100 ? 'bg-blue-600' : 'bg-amber-500') }}"
                             style="width: {{ $persenSelesai }}%;">
                        </div>
                    </div>
                </div>

                {{-- Baris Ringkas & Catatan Info --}}
                <div class="flex flex-wrap items-center justify-between gap-y-1 text-xs text-slate-600 pt-0.5">
                    <div class="flex flex-wrap items-center gap-x-2 font-medium">
                        <span class="text-emerald-700 font-semibold">Lulus: {{ $jumlahLulus ?? 0 }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-rose-700 font-semibold">Tidak Lulus: {{ $jumlahTidakLulus ?? 0 }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-700 font-semibold">Batas lulus: total &ge; 150</span>
                    </div>
                    <span class="text-slate-500 italic">
                        Setelah dipublikasikan, hasil terlihat oleh calon siswa dan nilai terkunci.
                    </span>
                </div>
            </div>

            {{-- Bagian Kanan: Tombol Publikasi --}}
            @if(!$isDiumumkan)
            <div class="shrink-0 w-full lg:w-auto flex flex-col sm:flex-row lg:flex-col justify-end">
                @if($isSemuaDinilai)
                    <button type="button"
                            @click="showPublishModal = true"
                            class="w-full sm:w-auto min-h-[44px] h-[44px] px-6 inline-flex items-center justify-center gap-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                        <i class="fas fa-bullhorn"></i>
                        <span>Publikasikan Hasil</span>
                    </button>
                @else
                    <button type="button"
                            disabled
                            class="w-full sm:w-auto min-h-[44px] h-[44px] px-6 inline-flex items-center justify-center gap-2 text-sm font-bold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed"
                            title="Masih ada {{ $belumDinilai }} peserta yang belum dinilai. Selesaikan seluruh penilaian untuk mempublikasikan hasil.">
                        <i class="fas fa-lock text-xs"></i>
                        <span>Publikasikan Hasil (Terkunci)</span>
                    </button>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- 4 Kartu Ringkasan (Grid 4 Kolom) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 min-[1100px]:grid-cols-4 gap-4">
        {{-- Total Peserta --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-600">Total Peserta</p>
                <p class="text-[28px] font-bold text-slate-900 mt-1 leading-none tracking-tight">{{ $totalPeserta ?? 0 }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-users"></i>
            </div>
        </div>

        {{-- Belum Dinilai --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-600">Belum Dinilai</p>
                <p class="text-[28px] font-bold {{ ($belumDinilai ?? 0) > 0 ? 'text-amber-700' : 'text-slate-400' }} mt-1 leading-none tracking-tight">
                    {{ $belumDinilai ?? 0 }}
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl {{ ($belumDinilai ?? 0) > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        {{-- Lulus --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-600">Lulus</p>
                <p class="text-[28px] font-bold {{ ($jumlahLulus ?? 0) > 0 ? 'text-emerald-700' : 'text-slate-400' }} mt-1 leading-none tracking-tight">
                    {{ $jumlahLulus ?? 0 }}
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl {{ ($jumlahLulus ?? 0) > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-user-check"></i>
            </div>
        </div>

        {{-- Tidak Lulus --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-600">Tidak Lulus</p>
                <p class="text-[28px] font-bold {{ ($jumlahTidakLulus ?? 0) > 0 ? 'text-rose-700' : 'text-slate-400' }} mt-1 leading-none tracking-tight">
                    {{ $jumlahTidakLulus ?? 0 }}
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl {{ ($jumlahTidakLulus ?? 0) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-user-times"></i>
            </div>
        </div>
    </div>

    {{-- Card Tabel Hasil Seleksi & Toolbar Pencarian --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Toolbar Atas Tabel --}}
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/70">
            <form action="{{ route('panitia.pengumuman.seleksi') }}" method="get" class="w-full">
                {{-- TODO: Filter Hasil dan Status belum didukung oleh controller pengseleksi() --}}
                <div class="search-toolbar-grid">
                    <div class="relative min-w-0">
                        <input type="text"
                               name="search"
                               class="w-full min-h-[44px] h-[44px] bg-white border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 block pl-10 pr-4 transition-all"
                               placeholder="Cari nama atau no. registrasi..."
                               value="{{ request('search') }}">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                    </div>

                    <button type="submit"
                            class="min-h-[44px] h-[44px] px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-emerald-600/20 transition-all inline-flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-search text-xs"></i>
                        <span>Cari</span>
                    </button>

                    @if(!empty(request('search')))
                        <a href="{{ route('panitia.pengumuman.seleksi') }}"
                           class="min-h-[44px] h-[44px] px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-colors inline-flex items-center justify-center gap-1.5"
                           title="Hapus filter pencarian">
                            <i class="fas fa-times text-xs"></i>
                            <span>Atur ulang</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tampilan Desktop (>= 900px): TABEL Penuh 10 Kolom --}}
        <div class="view-desktop-table overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-100/90 border-b border-slate-200 text-slate-700 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th scope="col" class="px-3 py-3.5 text-center w-12">NO</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center">NO. PENDAFTARAN</th>
                        <th scope="col" class="px-4 py-3.5">CALON SISWA</th>
                        <th scope="col" class="px-3 py-3.5 text-center">MEMBACA</th>
                        <th scope="col" class="px-3 py-3.5 text-center">MENULIS</th>
                        <th scope="col" class="px-3 py-3.5 text-center">BERHITUNG</th>
                        <th scope="col" class="px-3 py-3.5 text-center">TOTAL</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center">HASIL</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center">STATUS</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $no => $value)
                    @php
                        $seleksi = $value->pembayaran?->seleksi;
                        $hasNilai = $seleksi && $seleksi->total_nilai !== null;
                        $isLulus = $seleksi && $seleksi->hasil_seleksi === 'Lulus';
                        $statusDiumumkan = $seleksi ? $seleksi->isDiumumkan() : false;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $isLulus ? 'bg-emerald-50/20' : '' }}">
                        <td class="px-3 py-3.5 text-center text-slate-500 font-medium text-sm">{{ $no + 1 }}</td>
                        <td class="px-3.5 py-3.5 text-center text-sm font-semibold text-slate-700 tracking-tight">
                            {{ $value->no_registrasi ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-base font-semibold text-slate-900 leading-snug">
                                {{ $value->nama_calon_siswa ?? $value->nama }}
                            </div>
                        </td>
                        <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                            {{ $seleksi?->nilai_baca !== null ? $seleksi->nilai_baca : '-' }}
                        </td>
                        <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                            {{ $seleksi?->nilai_tulis !== null ? $seleksi->nilai_tulis : '-' }}
                        </td>
                        <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                            {{ $seleksi?->nilai_hitung !== null ? $seleksi->nilai_hitung : '-' }}
                        </td>
                        {{-- TOTAL: Semibold netral, bukan biru --}}
                        <td class="px-3 py-3.5 text-center font-bold text-slate-900 tabular-nums text-base">
                            {{ $seleksi?->total_nilai !== null ? $seleksi->total_nilai : '-' }}
                        </td>
                        {{-- HASIL: Badge ikon + teks, tanpa garis tepi hitam --}}
                        <td class="px-3.5 py-3.5 text-center">
                            @if($hasNilai)
                                @if($isLulus)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle text-xs text-emerald-600"></i>
                                        <span>Lulus</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        <i class="fas fa-times-circle text-xs text-rose-600"></i>
                                        <span>Tidak Lulus</span>
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                    Belum Dinilai
                                </span>
                            @endif
                        </td>
                        {{-- STATUS --}}
                        <td class="px-3.5 py-3.5 text-center">
                            @if($statusDiumumkan)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                    <i class="fas fa-check-double text-xs text-emerald-600"></i>
                                    <span>Dipublikasikan</span>
                                </span>
                            @elseif($hasNilai)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    <i class="fas fa-clock text-xs text-slate-400"></i>
                                    <span>Menunggu Publikasi</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                    <span>Belum Dinilai</span>
                                </span>
                            @endif
                        </td>
                        {{-- AKSI --}}
                        <td class="px-3.5 py-3.5 text-center">
                            @if($statusDiumumkan)
                                <a href="{{ route('input', ['id' => $value->id_calon_siswa]) }}"
                                   class="min-h-[44px] h-[44px] px-4 py-2 inline-flex items-center justify-center gap-1.5 text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">
                                    <i class="fas fa-eye text-xs"></i>
                                    <span>Lihat</span>
                                </a>
                            @else
                                <a href="{{ route('input', ['id' => $value->id_calon_siswa]) }}"
                                   class="min-h-[44px] h-[44px] px-4 py-2 inline-flex items-center justify-center gap-1.5 text-xs sm:text-sm font-semibold bg-white hover:bg-slate-50 text-slate-700 rounded-xl border border-slate-300 transition-colors shadow-xs">
                                    <i class="fas fa-pen text-xs text-slate-500"></i>
                                    <span>Ubah Nilai</span>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-14 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mb-3 text-2xl">
                                    <i class="fas fa-users-slash"></i>
                                </div>
                                @if(!empty(request('search')))
                                    <h3 class="text-slate-700 font-bold text-base mb-1">Tidak ada hasil untuk pencarian ini</h3>
                                    <p class="text-slate-500 text-xs max-w-sm mb-3">
                                        Kata kunci "{{ request('search') }}" tidak cocok dengan calon siswa mana pun.
                                    </p>
                                    <a href="{{ route('panitia.pengumuman.seleksi') }}"
                                       class="min-h-[44px] h-[44px] px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold inline-flex items-center justify-center gap-1.5 shadow-sm">
                                        <i class="fas fa-redo text-[10px]"></i> Atur ulang pencarian
                                    </a>
                                @else
                                    <h3 class="text-slate-700 font-bold text-base mb-1">Belum ada peserta</h3>
                                    <p class="text-slate-500 text-xs max-w-sm">
                                        Calon siswa yang telah menyelesaikan pembayaran pendaftaran akan otomatis muncul di tabel ini.
                                    </p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tampilan Mobile (< 900px): Format Kartu Ringkas Per Baris --}}
        <div class="view-mobile-cards divide-y divide-slate-100">
            @forelse($data as $no => $value)
            @php
                $seleksi = $value->pembayaran?->seleksi;
                $hasNilai = $seleksi && $seleksi->total_nilai !== null;
                $isLulus = $seleksi && $seleksi->hasil_seleksi === 'Lulus';
                $statusDiumumkan = $seleksi ? $seleksi->isDiumumkan() : false;
            @endphp
            <div class="p-4 sm:p-5 space-y-3 hover:bg-slate-50/60 transition-colors {{ $isLulus ? 'bg-emerald-50/15' : '' }}">
                {{-- Header Kartu: Nama & Hasil --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="text-base font-semibold text-slate-900 leading-snug">
                            {{ $no + 1 }}. {{ $value->nama_calon_siswa ?? $value->nama }}
                        </div>
                        <div class="text-sm text-slate-500 mt-0.5">
                            No. Pendaftaran: <span class="font-semibold text-slate-700">{{ $value->no_registrasi ?? '-' }}</span>
                        </div>
                    </div>
                    {{-- Badge Hasil --}}
                    <div class="shrink-0">
                        @if($hasNilai)
                            @if($isLulus)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fas fa-check-circle text-[10px] text-emerald-600"></i> Lulus
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                    <i class="fas fa-times-circle text-[10px] text-rose-600"></i> Tidak Lulus
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                Belum Dinilai
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Nilai Tiga Kolom Sejajar + Total di Bawahnya (Tanpa Garis Tepi Hitam) --}}
                <div class="mobile-score-box">
                    <div class="mobile-score-grid">
                        <div>
                            <span class="text-xs text-slate-500 block mb-0.5">Membaca</span>
                            <span class="text-base font-bold text-slate-800 tabular-nums">{{ $seleksi?->nilai_baca !== null ? $seleksi->nilai_baca : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block mb-0.5">Menulis</span>
                            <span class="text-base font-bold text-slate-800 tabular-nums">{{ $seleksi?->nilai_tulis !== null ? $seleksi->nilai_tulis : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block mb-0.5">Berhitung</span>
                            <span class="text-base font-bold text-slate-800 tabular-nums">{{ $seleksi?->nilai_hitung !== null ? $seleksi->nilai_hitung : '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 px-1 text-sm">
                        <span class="text-xs font-semibold text-slate-600">Total Akumulasi:</span>
                        <span class="font-bold text-slate-900 tabular-nums">{{ $seleksi?->total_nilai !== null ? $seleksi->total_nilai . ' / 300' : '-' }}</span>
                    </div>
                </div>

                {{-- Status Publikasi --}}
                <div class="flex items-center justify-between text-xs text-slate-500 pt-0.5">
                    <span>Status:</span>
                    @if($statusDiumumkan)
                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                            <i class="fas fa-check-double text-[10px]"></i> Dipublikasikan
                        </span>
                    @elseif($hasNilai)
                        <span class="inline-flex items-center gap-1 text-slate-600 font-medium">
                            <i class="fas fa-clock text-[10px]"></i> Menunggu Publikasi
                        </span>
                    @else
                        <span class="text-slate-500">Belum Dinilai</span>
                    @endif
                </div>

                {{-- Tombol Aksi Selebar Penuh (Tinggi 44px) --}}
                <div class="pt-1">
                    @if($statusDiumumkan)
                        <a href="{{ route('input', ['id' => $value->id_calon_siswa]) }}"
                           class="w-full min-h-[44px] h-[44px] inline-flex items-center justify-center gap-2 text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">
                            <i class="fas fa-eye text-xs"></i>
                            <span>Lihat Detail</span>
                        </a>
                    @else
                        <a href="{{ route('input', ['id' => $value->id_calon_siswa]) }}"
                           class="w-full min-h-[44px] h-[44px] inline-flex items-center justify-center gap-2 text-sm font-semibold bg-white hover:bg-slate-50 text-slate-800 rounded-xl border border-slate-300 transition-colors shadow-xs">
                            <i class="fas fa-pen text-xs text-slate-600"></i>
                            <span>Ubah Nilai</span>
                        </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mb-2.5 mx-auto text-xl">
                    <i class="fas fa-users-slash"></i>
                </div>
                @if(!empty(request('search')))
                    <h3 class="text-slate-700 font-bold text-sm mb-1">Tidak ada hasil untuk pencarian ini</h3>
                    <p class="text-slate-500 text-xs mb-3">Kata kunci tidak cocok dengan calon siswa mana pun.</p>
                    <a href="{{ route('panitia.pengumuman.seleksi') }}"
                       class="min-h-[44px] h-[44px] px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold inline-flex items-center justify-center gap-1.5 shadow-sm">
                        Atur ulang
                    </a>
                @else
                    <h3 class="text-slate-700 font-bold text-sm mb-1">Belum ada peserta</h3>
                    <p class="text-slate-500 text-xs">Calon siswa yang lunas bayar akan muncul di sini.</p>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Konfirmasi Publikasi Pengumuman (role="alertdialog") --}}
    <div x-show="showPublishModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
         @keydown.escape.window="showPublishModal = false"
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="modal-publish-title"
         aria-describedby="modal-publish-desc">
        
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5"
             style="max-width: 440px;"
             @click.away="showPublishModal = false">
            
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl shrink-0">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                    <h3 id="modal-publish-title" class="text-lg font-bold text-slate-900">Publikasikan Hasil Seleksi</h3>
                    <p id="modal-publish-desc" class="text-sm text-slate-500 mt-0.5">
                        Konfirmasi pengumuman hasil seleksi resmi untuk TA {{ $aktivTahunAjaran->tahun_ajaran ?? ($aktivTahunAjaran->tahun_ajar ?? 'Aktif') }}.
                    </p>
                </div>
            </div>

            {{-- Ringkasan Hasil --}}
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-2 text-sm">
                <div class="flex justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">Total Peserta Seleksi:</span>
                    <span class="font-bold text-slate-900">{{ $totalPeserta ?? 0 }} siswa</span>
                </div>
                <div class="flex justify-between text-emerald-700 font-semibold">
                    <span>Jumlah Dinyatakan Lulus:</span>
                    <span class="font-black text-base">{{ $jumlahLulus ?? 0 }} siswa</span>
                </div>
                <div class="flex justify-between text-rose-700 font-semibold">
                    <span>Jumlah Tidak Lulus:</span>
                    <span class="font-black text-base">{{ $jumlahTidakLulus ?? 0 }} siswa</span>
                </div>
            </div>

            {{-- Peringatan Kunci Nilai --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 space-y-1.5 leading-relaxed">
                <div class="font-bold flex items-center gap-1.5 text-amber-950 text-sm">
                    <i class="fas fa-lock text-amber-600"></i> Peringatan Penguncian Nilai:
                </div>
                <p>
                    Setelah dipublikasikan, hasil seleksi akan <strong>langsung terlihat oleh seluruh calon siswa</strong> di portal orang tua dan seluruh nilai seleksi akan <strong>terkunci secara permanen</strong>.
                </p>
            </div>

            {{-- Form Submit Publikasi --}}
            <form action="{{ route('panitia.pengumuman.publikasikan') }}" method="POST">
                @csrf
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button"
                            x-ref="btnBatalPublikasi"
                            @click="showPublishModal = false"
                            class="btn-action-secondary">
                        Batal
                    </button>
                    <button type="submit"
                            class="btn-action-primary">
                        <i class="fas fa-check"></i>
                        <span>Ya, Publikasikan Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
