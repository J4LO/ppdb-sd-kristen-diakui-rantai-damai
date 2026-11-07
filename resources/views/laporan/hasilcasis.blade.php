@extends('layout.tampilanpanitia')

@section('page-title', 'Rekap Hasil Seleksi')

@section('breadcrumb')
    <span>Portal Panitia</span>
    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
    <span class="text-slate-500">Laporan</span>
    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
    <span class="font-semibold text-slate-800">Rekap Hasil Seleksi</span>
@endsection

@push('styles')
<style>
    .btn-action-primary {
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
        background-color: #059669;
        color: #ffffff;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-action-primary:hover {
        background-color: #047857;
        color: #ffffff;
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

    /* Filter Form Input Container */
    .filter-input-control {
        min-height: 44px;
        height: 44px;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #1e293b;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        transition: all 0.15s ease-in-out;
        width: 100%;
    }
    .filter-input-control:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
    }
    .filter-input-control.has-icon-left {
        padding-left: 2.5rem !important;
    }

    /* Responsive View: Desktop Table (>= 900px) vs Mobile Cards (< 900px) */
    @media (min-width: 900px) {
        .report-desktop-view {
            display: block !important;
        }
        .report-mobile-view {
            display: none !important;
        }
    }
    @media (max-width: 899.98px) {
        .report-desktop-view {
            display: none !important;
        }
        .report-mobile-view {
            display: block !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    {{-- 1. Header Halaman & Aksi Cetak --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                Rekap Hasil Seleksi Calon Siswa
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Laporan evaluasi observasi kemampuan membaca, menulis, dan berhitung calon siswa baru.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('unduhhasil', request()->query()) }}"
               target="_blank"
               class="btn-action-primary"
               title="Unduh dan Cetak Laporan PDF Sesuai Filter">
                <i class="fas fa-print text-sm"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    {{-- 2. Ringkasan Statistik (KPI Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Peserta --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Peserta</p>
                <p class="text-2xl font-bold text-slate-900 mt-1 leading-none tracking-tight">
                    {{ $totalPeserta ?? 0 }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Telah dinilai panitia</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-users"></i>
            </div>
        </div>

        {{-- Lulus --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Lulus Seleksi</p>
                <p class="text-2xl font-bold text-emerald-700 mt-1 leading-none tracking-tight">
                    {{ $totalLulus ?? 0 }}
                </p>
                <p class="text-xs text-emerald-600/80 mt-1">Total nilai &ge; 150</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-user-check"></i>
            </div>
        </div>

        {{-- Tidak Lulus --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Tidak Lulus</p>
                <p class="text-2xl font-bold text-rose-700 mt-1 leading-none tracking-tight">
                    {{ $totalTidakLulus ?? 0 }}
                </p>
                <p class="text-xs text-rose-600/80 mt-1">Total nilai &lt; 150</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-user-times"></i>
            </div>
        </div>

        {{-- Rata-rata Skor --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Rata-rata Skor</p>
                <p class="text-2xl font-bold text-amber-700 mt-1 leading-none tracking-tight">
                    {{ $rataRataNilai ?? 0 }}
                </p>
                <p class="text-xs text-amber-700/80 mt-1">Akumulasi 3 mata uji</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>

    {{-- 3. Filter Laporan --}}
    @php
        $hasActiveFilter = request()->filled('start_date') || request()->filled('end_date') || request()->filled('status') || request()->filled('id_tahun_ajaran') || request()->filled('search');
    @endphp
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-semibold">
                    <i class="fas fa-filter"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Filter Data Laporan</h2>
                    <p class="text-xs text-slate-500">Saring calon siswa berdasarkan nama, status kelulusan, tahun ajaran, atau rentang tanggal observasi.</p>
                </div>
            </div>

            @if($hasActiveFilter)
                <a href="{{ route('hasilcasis') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors inline-flex items-center gap-1">
                    <i class="fas fa-times-circle"></i>
                    <span>Hapus Semua Filter</span>
                </a>
            @endif
        </div>

        <form action="{{ route('hasilcasis') }}" method="get" class="space-y-4">
            {{-- Baris 1: Pencarian, Status, dan Tahun Ajaran --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4">
                {{-- Pencarian Kata Kunci (Span 6) --}}
                <div class="md:col-span-6">
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Pencarian Calon Siswa
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Nama lengkap, no. registrasi, atau NIK..."
                               class="filter-input-control has-icon-left">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Status Hasil Seleksi (Span 3) --}}
                <div class="md:col-span-3">
                    <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Status Hasil
                    </label>
                    <select id="status" name="status" class="filter-input-control">
                        <option value="">Semua Status</option>
                        <option value="Lulus" {{ request('status') === 'Lulus' ? 'selected' : '' }}>Lulus (≥ 150)</option>
                        <option value="Tidak Lulus" {{ request('status') === 'Tidak Lulus' ? 'selected' : '' }}>Tidak Lulus (< 150)</option>
                    </select>
                </div>

                {{-- Tahun Ajaran (Span 3) --}}
                <div class="md:col-span-3">
                    <label for="id_tahun_ajaran" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tahun Ajaran
                    </label>
                    <select id="id_tahun_ajaran" name="id_tahun_ajaran" class="filter-input-control">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjaranList ?? [] as $ta)
                            <option value="{{ $ta->id_tahun_ajaran }}" {{ request('id_tahun_ajaran') == $ta->id_tahun_ajaran ? 'selected' : '' }}>
                                TA {{ $ta->tahun_ajaran }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Baris 2: Rentang Tanggal Observasi & Tombol Aksi --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-end">
                {{-- Tanggal Mulai (Span 3) --}}
                <div class="md:col-span-3">
                    <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Observasi Mulai
                    </label>
                    <input type="date"
                           id="start_date"
                           name="start_date"
                           value="{{ request('start_date') }}"
                           class="filter-input-control">
                </div>

                {{-- Tanggal Selesai (Span 3) --}}
                <div class="md:col-span-3">
                    <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Observasi Selesai
                    </label>
                    <input type="date"
                           id="end_date"
                           name="end_date"
                           value="{{ request('end_date') }}"
                           class="filter-input-control">
                </div>

                {{-- Tombol Aksi (Span 6) --}}
                <div class="md:col-span-6 flex items-center justify-end gap-2.5 pt-1">
                    <a href="{{ route('hasilcasis') }}"
                       class="btn-action-secondary text-xs sm:text-sm">
                        <i class="fas fa-undo-alt text-xs"></i>
                        <span>Reset</span>
                    </a>
                    <button type="submit"
                            class="btn-action-primary text-xs sm:text-sm">
                        <i class="fas fa-search text-xs"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>

            {{-- Ringkasan Tag Filter Aktif --}}
            @if($hasActiveFilter)
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-500 font-medium">Filter aktif:</span>
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg">
                            <i class="fas fa-search text-[10px] text-slate-400"></i>
                            <span>"{{ request('search') }}"</span>
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 {{ request('status') === 'Lulus' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} rounded-lg font-semibold">
                            <i class="fas fa-check-circle text-[10px]"></i>
                            <span>Status: {{ request('status') }}</span>
                        </span>
                    @endif
                    @if(request('id_tahun_ajaran'))
                        @php
                            $selectedTa = ($tahunAjaranList ?? collect())->firstWhere('id_tahun_ajaran', request('id_tahun_ajaran'));
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg">
                            <i class="fas fa-calendar-alt text-[10px]"></i>
                            <span>TA {{ $selectedTa->tahun_ajaran ?? request('id_tahun_ajaran') }}</span>
                        </span>
                    @endif
                    @if(request('start_date') || request('end_date'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg">
                            <i class="fas fa-calendar-week text-[10px]"></i>
                            <span>
                                {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') : 'Awal' }}
                                &mdash;
                                {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') : 'Akhir' }}
                            </span>
                        </span>
                    @endif
                    <span class="text-slate-400 ml-auto">
                        Ditemukan <strong class="text-slate-800">{{ $data->count() }}</strong> data
                    </span>
                </div>
            @endif
        </form>
    </div>

    {{-- 4. Tabel Hasil Seleksi (Desktop >= 900px) --}}
    <div class="report-desktop-view bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-100/90 border-b border-slate-200 text-slate-700 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th scope="col" class="px-3 py-3.5 text-center w-12">NO</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center min-w-[130px]">NO. PENDAFTARAN</th>
                        <th scope="col" class="px-4 py-3.5 text-left min-w-[200px]">CALON SISWA</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center min-w-[120px]">TGL OBSERVASI</th>
                        <th scope="col" class="px-3 py-3.5 text-center">MEMBACA</th>
                        <th scope="col" class="px-3 py-3.5 text-center">MENULIS</th>
                        <th scope="col" class="px-3 py-3.5 text-center">BERHITUNG</th>
                        <th scope="col" class="px-3 py-3.5 text-center">TOTAL</th>
                        <th scope="col" class="px-3 py-3.5 text-center">RATA-RATA</th>
                        <th scope="col" class="px-3.5 py-3.5 text-center">HASIL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($data as $no => $value)
                        @php
                            $casis = $value->casis;
                            $isLulus = in_array($value->hasil_seleksi, ['Lolos', 'Lulus']);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isLulus ? 'bg-emerald-50/20' : '' }}">
                            {{-- No --}}
                            <td class="px-3 py-3.5 text-center text-slate-500 font-medium text-sm">
                                {{ $no + 1 }}
                            </td>

                            {{-- No. Pendaftaran / Registrasi --}}
                            <td class="px-3.5 py-3.5 text-center text-sm font-semibold text-slate-700 tracking-tight">
                                {{ $casis->no_registrasi ?? '-' }}
                            </td>

                            {{-- Calon Siswa (Tanpa NIK) --}}
                            <td class="px-4 py-3.5">
                                <div class="text-base font-semibold text-slate-900 leading-snug">
                                    {{ $casis->nama_calon_siswa ?? $casis->nama ?? '-' }}
                                </div>
                            </td>

                            {{-- Tanggal Observasi --}}
                            <td class="px-3.5 py-3.5 text-center text-slate-600 text-sm font-medium">
                                {{ $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->translatedFormat('d M Y') : '-' }}
                            </td>

                            {{-- Nilai Baca --}}
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                                {{ $value->nilai_baca ?? 0 }}
                            </td>

                            {{-- Nilai Tulis --}}
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                                {{ $value->nilai_tulis ?? 0 }}
                            </td>

                            {{-- Nilai Hitung --}}
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                                {{ $value->nilai_hitung ?? 0 }}
                            </td>

                            {{-- Total Nilai (Langsung 140, tanpa /300) --}}
                            <td class="px-3 py-3.5 text-center font-bold text-slate-900 tabular-nums text-base">
                                {{ $value->total_nilai ?? 0 }}
                            </td>

                            {{-- Rata-rata --}}
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700 tabular-nums text-base">
                                {{ number_format($value->nilai_akhir ?? 0, 2) }}
                            </td>

                            {{-- Status Hasil --}}
                            <td class="px-3.5 py-3.5 text-center">
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
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Tidak ada data hasil seleksi</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau rentang tanggal di atas.</p>
                                    <a href="{{ route('hasilcasis') }}" class="btn-action-secondary mt-3 !min-h-[38px] !h-[38px] !px-3.5 text-xs inline-flex items-center gap-1.5">
                                        <i class="fas fa-undo-alt text-xs"></i>
                                        <span>Reset Filter</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 5. Tampilan Kartu Hasil Seleksi (Mobile < 900px) --}}
    <div class="report-mobile-view space-y-3">
        @forelse($data as $no => $value)
            @php
                $casis = $value->casis;
                $isLulus = in_array($value->hasil_seleksi, ['Lolos', 'Lulus']);
                $initials = '';
                if ($casis && $casis->nama) {
                    $words = explode(' ', trim($casis->nama));
                    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                }
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm space-y-3">
                {{-- Baris Atas: Profil & Status --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 border border-slate-200/80 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ $initials ?: 'CS' }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900 text-base truncate leading-snug">
                                {{ $casis->nama_calon_siswa ?? $casis->nama ?? 'Calon Siswa' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                No. Reg: {{ $casis->no_registrasi ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
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
                    </div>
                </div>

                {{-- Tanggal Observasi --}}
                <div class="text-xs text-slate-500 flex items-center gap-1.5 pt-1 border-t border-slate-100">
                    <i class="fas fa-calendar-alt text-slate-400 text-xs"></i>
                    <span>Tgl Observasi: <strong class="text-slate-700">{{ $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->translatedFormat('l, d M Y') : '-' }}</strong></span>
                </div>

                {{-- Rincian 3 Komponen Nilai --}}
                <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 text-center">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 uppercase block">Membaca</span>
                        <span class="font-mono font-bold text-slate-900 text-base">{{ $value->nilai_baca ?? 0 }}</span>
                    </div>
                    <div class="border-x border-slate-200">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase block">Menulis</span>
                        <span class="font-mono font-bold text-slate-900 text-base">{{ $value->nilai_tulis ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 uppercase block">Berhitung</span>
                        <span class="font-mono font-bold text-slate-900 text-base">{{ $value->nilai_hitung ?? 0 }}</span>
                    </div>
                </div>

                {{-- Baris Bawah: Total Nilai & Rata-rata --}}
                <div class="pt-1">
                    <span class="text-xs text-slate-500 block">Total Nilai:</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl font-bold font-mono text-slate-900">{{ $value->total_nilai ?? 0 }}</span>
                        <span class="text-xs text-slate-500 font-medium">(Rata-rata: {{ number_format($value->nilai_akhir ?? 0, 2) }})</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 border border-slate-200/90 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                    <i class="fas fa-folder-open"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">Tidak ada data hasil seleksi</p>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau rentang tanggal.</p>
                <a href="{{ route('hasilcasis') }}" class="btn-action-secondary mt-3 !min-h-[36px] !h-[36px] !px-3 text-xs inline-flex">
                    <i class="fas fa-undo-alt text-xs"></i>
                    <span>Reset Filter</span>
                </a>
            </div>
        @endforelse
    </div>

</div>
@endsection
