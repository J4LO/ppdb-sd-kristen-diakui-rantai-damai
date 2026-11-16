@extends(auth()->user()?->role === 'Panitia' ? 'layout.tampilanpanitia' : 'layout.tampilan')

@section('title', 'Rekap Administrasi Daftar Ulang Calon Siswa')

{{-- Breadcrumb untuk Layout Admin --}}
@section('page-title')
    <span class="text-slate-500">Laporan</span>
    <i class="fas fa-chevron-right text-[10px] text-slate-400 mx-2" aria-hidden="true"></i>
    <span class="text-emerald-700 font-bold">Rekap Daftar Ulang</span>
@endsection

{{-- Breadcrumb untuk Layout Panitia --}}
@section('breadcrumb')
    <span>Portal Panitia</span>
    <i class="fas fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
    <span class="text-slate-500">Laporan</span>
    <i class="fas fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
    <span class="font-semibold text-slate-800">Rekap Daftar Ulang</span>
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
        display: block;
    }
    .filter-input-control:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
    }
    .filter-input-control.has-icon-left {
        padding-left: 2.5rem !important;
    }

    /* Grid Filter Identik Rekap Hasil Seleksi */
    .filter-grid-row-1 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .filter-grid-row-1 {
            grid-template-columns: minmax(0, 6fr) minmax(0, 3fr) minmax(0, 3fr);
        }
    }
    .filter-grid-row-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        align-items: flex-end;
    }
    @media (min-width: 768px) and (max-width: 1023.98px) {
        .filter-grid-row-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .filter-grid-row-2 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
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
@php
    $tahunAjaranList = \App\Models\TahunAjaran::orderBy('id_tahun_ajaran', 'desc')->get();

    // Filter Pencarian Teks, Status & Tahun Ajaran di Sisi Koleksi
    $searchKeyword = trim(request('search', ''));
    $filterStatus = request('status', '');
    $filterTa = request('id_tahun_ajaran', '');
    $displayData = $daftarUlang;

    if ($searchKeyword !== '') {
        $kw = strtolower($searchKeyword);
        $displayData = $displayData->filter(function($item) use ($kw) {
            $casis = $item->calonSiswa ?? ($item->pendaftaran?->casis ?? ($item->seleksi?->pembayaran?->calonSiswa ?? null));
            $nama = strtolower($casis->nama_calon_siswa ?? ($casis->nama ?? ''));
            $reg = strtolower($casis->no_registrasi ?? '');
            return str_contains($nama, $kw) || str_contains($reg, $kw);
        });
    }

    if ($filterStatus !== '') {
        $displayData = $displayData->filter(function($item) use ($filterStatus) {
            $st = $item->status_bayar ?? ($item->status_du ?? '');
            if ($filterStatus === 'Berhasil') {
                return in_array($st, ['Berhasil', 'Lunas', 'Selesai']);
            } elseif ($filterStatus === 'Menunggu Konfirmasi') {
                return in_array($st, ['Menunggu Konfirmasi', 'Menunggu', 'Pending']);
            } elseif ($filterStatus === 'Gagal') {
                return in_array($st, ['Gagal', 'Ditolak', 'Batal']);
            }
            return true;
        });
    }

    if ($filterTa !== '') {
        $displayData = $displayData->filter(function($item) use ($filterTa) {
            $casis = $item->calonSiswa ?? ($item->pendaftaran?->casis ?? ($item->seleksi?->pembayaran?->calonSiswa ?? null));
            return ($casis->id_tahun_ajaran ?? null) == $filterTa;
        });
    }

    $filterStatusBerkas = request('status_berkas', '');
    if ($filterStatusBerkas !== '') {
        $displayData = $displayData->filter(function($item) use ($filterStatusBerkas) {
            $sb = $item->status_berkas ?? 'Menunggu';
            if ($filterStatusBerkas === 'Diterima') {
                return in_array($sb, ['Diterima', 'Sudah Diterima', 'Lengkap']);
            } elseif ($filterStatusBerkas === 'Menunggu') {
                return !in_array($sb, ['Diterima', 'Sudah Diterima', 'Lengkap']);
            }
            return true;
        });
    }

    // Statistik Ringkasan Data
    $totalSiswa = $daftarUlang->count();
    $selesaiItems = $daftarUlang->filter(function($d) {
        $st = $d->status_bayar ?? ($d->status_du ?? '');
        return in_array($st, ['Berhasil', 'Lunas', 'Selesai']);
    });
    $totalSelesai = $selesaiItems->count();
    $totalNominalMasuk = $selesaiItems->sum(function($d) {
        return (float) ($d->jumlah_bayar ?? 0);
    });
    $totalMenunggu = $daftarUlang->filter(function($d) {
        $st = $d->status_bayar ?? ($d->status_du ?? '');
        return in_array($st, ['Menunggu Konfirmasi', 'Menunggu', 'Pending']);
    })->count();

    $hasActiveFilter = request()->filled('start_date') || request()->filled('end_date') || request()->filled('status') || request()->filled('status_berkas') || request()->filled('id_tahun_ajaran') || request()->filled('search');

    // Tautan Unduh PDF
    $downloadRoute = auth()->user()?->role === 'Admin' ? 'admin.laporan.daftar-ulang.cetak' : 'laporan.daftar-ulang.cetak';
    $downloadUrl = Route::has($downloadRoute) ? route($downloadRoute, request()->query()) : route('laporan.daftar-ulang.cetak', request()->query());
@endphp

<div class="space-y-6 max-w-7xl mx-auto">

    {{-- 1. Header Halaman & Aksi Cetak --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                Rekap Administrasi Daftar Ulang Calon Siswa
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Laporan pemantauan pembayaran uang pangkal dan penyerahan berkas fisik daftar ulang.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ $downloadUrl }}"
               target="_blank"
               class="btn-action-primary"
               title="Unduh dan Cetak Laporan PDF Sesuai Filter">
                <i class="fas fa-print text-sm" aria-hidden="true"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    {{-- 2. Ringkasan Statistik (KPI Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Calon Siswa --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Daftar Ulang</p>
                <p class="text-2xl font-bold text-slate-900 mt-1 leading-none tracking-tight tabular-nums">
                    {{ $totalSiswa }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Siswa lulus berhak daftar ulang</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-user-graduate" aria-hidden="true"></i>
            </div>
        </div>

        {{-- Selesai / Terverifikasi --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Selesai / Lunas</p>
                <p class="text-2xl font-bold text-emerald-700 mt-1 leading-none tracking-tight tabular-nums">
                    {{ $totalSelesai }}
                </p>
                <p class="text-xs text-emerald-600/80 mt-1">Cicilan terpenuhi &amp; berkas sah</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-check-double" aria-hidden="true"></i>
            </div>
        </div>

        {{-- Total Setoran Masuk --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Setoran Masuk</p>
                <p class="text-xl sm:text-2xl font-bold text-blue-700 mt-1 leading-none tracking-tight tabular-nums truncate" title="Rp {{ number_format($totalNominalMasuk, 0, ',', '.') }}">
                    Rp {{ number_format($totalNominalMasuk, 0, ',', '.') }}
                </p>
                <p class="text-xs text-blue-600/80 mt-1">Uang pangkal terverifikasi</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-money-bill-wave" aria-hidden="true"></i>
            </div>
        </div>

        {{-- Menunggu Berkas / Konfirmasi --}}
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between min-h-[88px]">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Menunggu</p>
                <p class="text-2xl font-bold text-amber-700 mt-1 leading-none tracking-tight tabular-nums">
                    {{ $totalMenunggu }}
                </p>
                <p class="text-xs text-amber-700/80 mt-1">Menunggu berkas atau setoran</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-hourglass-half" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    {{-- 3. Filter Laporan (Tata Letak Identik Rekap Hasil Seleksi) --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-semibold">
                    <i class="fas fa-filter" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Filter Data Laporan</h2>
                    <p class="text-xs text-slate-500">Saring calon siswa berdasarkan nama, nomor registrasi, status daftar ulang, atau rentang tanggal.</p>
                </div>
            </div>

            @if($hasActiveFilter)
                <a href="{{ request()->url() }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors inline-flex items-center gap-1">
                    <i class="fas fa-times-circle" aria-hidden="true"></i>
                    <span>Hapus Semua Filter</span>
                </a>
            @endif
        </div>

        <form action="{{ request()->url() }}" method="get" class="space-y-4">
            {{-- Baris 1: Pencarian, Status, dan Tahun Ajaran (6fr 3fr 3fr) --}}
            <div class="filter-grid-row-1">
                {{-- Pencarian Kata Kunci --}}
                <div>
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Pencarian Calon Siswa
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Nama lengkap calon siswa atau no. registrasi..."
                               class="filter-input-control has-icon-left">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                {{-- Status Daftar Ulang --}}
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Status Daftar Ulang
                    </label>
                    <select id="status" name="status" class="filter-input-control">
                        <option value="">Semua Status</option>
                        <option value="Berhasil" {{ request('status') === 'Berhasil' ? 'selected' : '' }}>Selesai / Lunas</option>
                        <option value="Menunggu Konfirmasi" {{ request('status') === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Gagal" {{ request('status') === 'Gagal' ? 'selected' : '' }}>Gagal / Batal</option>
                    </select>
                </div>

                {{-- Tahun Ajaran --}}
                <div>
                    <label for="id_tahun_ajaran" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tahun Ajaran
                    </label>
                    <select id="id_tahun_ajaran" name="id_tahun_ajaran" class="filter-input-control">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunAjaranList ?? [] as $ta)
                            <option value="{{ $ta->id_tahun_ajaran }}" {{ request('id_tahun_ajaran') == $ta->id_tahun_ajaran ? 'selected' : '' }}>
                                TA {{ $ta->tahun_ajaran ?? $ta->tahun_ajar }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Baris 2: Rentang Tanggal Daftar Ulang, Status Berkas & Tombol Aksi --}}
            <div class="filter-grid-row-2">
                {{-- Tanggal Mulai --}}
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Mulai
                    </label>
                    <input type="date"
                           id="start_date"
                           name="start_date"
                           value="{{ request('start_date') }}"
                           class="filter-input-control">
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Selesai
                    </label>
                    <input type="date"
                           id="end_date"
                           name="end_date"
                           value="{{ request('end_date') }}"
                           class="filter-input-control">
                </div>

                {{-- Status Berkas --}}
                <div>
                    <label for="status_berkas" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Status Berkas
                    </label>
                    <select id="status_berkas" name="status_berkas" class="filter-input-control">
                        <option value="">Semua Status Berkas</option>
                        <option value="Diterima" {{ request('status_berkas') === 'Diterima' ? 'selected' : '' }}>Diterima (Lengkap)</option>
                        <option value="Menunggu" {{ request('status_berkas') === 'Menunggu' ? 'selected' : '' }}>Menunggu / Belum</option>
                    </select>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <a href="{{ request()->url() }}"
                       class="btn-action-secondary text-xs sm:text-sm">
                        <i class="fas fa-undo-alt text-xs" aria-hidden="true"></i>
                        <span>Reset</span>
                    </a>
                    <button type="submit"
                            class="btn-action-primary text-xs sm:text-sm">
                        <i class="fas fa-search text-xs" aria-hidden="true"></i>
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
                            <i class="fas fa-search text-[10px] text-slate-400" aria-hidden="true"></i>
                            <span>"{{ request('search') }}"</span>
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 {{ request('status') === 'Berhasil' ? 'bg-emerald-50 text-emerald-700' : (request('status') === 'Gagal' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }} rounded-lg font-semibold">
                            <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i>
                            <span>Status: {{ request('status') }}</span>
                        </span>
                    @endif
                    @if(request('status_berkas'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 {{ request('status_berkas') === 'Diterima' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} rounded-lg font-semibold">
                            <i class="fas fa-file-alt text-[10px]" aria-hidden="true"></i>
                            <span>Berkas: {{ request('status_berkas') }}</span>
                        </span>
                    @endif
                    @if(request('id_tahun_ajaran'))
                        @php
                            $selectedTa = ($tahunAjaranList ?? collect())->firstWhere('id_tahun_ajaran', request('id_tahun_ajaran'));
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg">
                            <i class="fas fa-calendar-alt text-[10px]" aria-hidden="true"></i>
                            <span>TA {{ $selectedTa->tahun_ajaran ?? ($selectedTa->tahun_ajar ?? request('id_tahun_ajaran')) }}</span>
                        </span>
                    @endif
                    @if(request('start_date') || request('end_date'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg">
                            <i class="fas fa-calendar-week text-[10px]" aria-hidden="true"></i>
                            <span>
                                {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') : 'Awal' }}
                                &mdash;
                                {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') : 'Akhir' }}
                            </span>
                        </span>
                    @endif
                    <span class="text-slate-400 ml-auto">
                        Ditemukan <strong class="text-slate-800">{{ $displayData->count() }}</strong> data
                    </span>
                </div>
            @endif
        </form>
    </div>

    {{-- 4. Tabel Hasil Laporan Daftar Ulang (Desktop >= 900px) --}}
    <div class="report-desktop-view bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
                <thead class="bg-slate-100/90 border-b border-slate-200 text-slate-700 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th scope="col" class="px-3.5 py-3.5 text-center w-12">NO</th>
                        <th scope="col" class="px-4 py-3.5 text-center min-w-[130px]">NO. PENDAFTARAN</th>
                        <th scope="col" class="px-4 py-3.5 text-left min-w-[220px]">CALON SISWA</th>
                        <th scope="col" class="px-4 py-3.5 text-center">TGL DAFTAR ULANG</th>
                        <th scope="col" class="px-4 py-3.5 text-right min-w-[130px]">SETORAN TERBAYAR</th>
                        <th scope="col" class="px-4 py-3.5 text-center">METODE</th>
                        <th scope="col" class="px-4 py-3.5 text-center">STATUS BERKAS</th>
                        <th scope="col" class="px-4 py-3.5 text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($displayData as $no => $item)
                        @php
                            $casis = $item->calonSiswa ?? ($item->pendaftaran?->casis ?? ($item->seleksi?->pembayaran?->calonSiswa ?? null));
                            $namaCasis = $casis->nama_calon_siswa ?? ($casis->nama ?? '-');
                            $jk = $casis->jenis_kelamin ?? null;
                            $ta = $casis->tahunAjaran?->tahun_ajaran ?? ($casis->tahunAjaran?->tahun_ajar ?? null);
                            $jumlahBayar = (float) ($item->jumlah_bayar ?? 0);
                            $metode = $item->metode_pembayaran ?? 'Gateway';
                            $status = $item->status_bayar ?? ($item->status_du ?? 'Berhasil');
                            $statusBerkas = $item->status_berkas ?? 'Menunggu';
                            $isBerkasDiterima = in_array($statusBerkas, ['Diterima', 'Sudah Diterima', 'Lengkap']);
                            $isBerhasil = in_array($status, ['Berhasil', 'Lunas', 'Selesai']);
                            $isGagal = in_array($status, ['Gagal', 'Ditolak', 'Batal']);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isBerhasil ? 'bg-emerald-50/20' : '' }}">
                            {{-- No --}}
                            <td class="px-3.5 py-3.5 text-center text-slate-500 font-medium text-sm">
                                {{ $loop->iteration }}
                            </td>

                            {{-- No. Pendaftaran --}}
                            <td class="px-4 py-3.5 text-center text-sm font-semibold text-slate-700 tracking-tight">
                                {{ $casis->no_registrasi ?? '-' }}
                            </td>

                            {{-- Calon Siswa --}}
                            <td class="px-4 py-3.5">
                                <div class="text-base font-semibold text-slate-900 leading-snug">
                                    {{ $namaCasis }}
                                </div>
                            </td>

                            {{-- Tanggal Daftar Ulang --}}
                            <td class="px-4 py-3.5 text-center text-slate-600 text-sm">
                                {{ $item->tgl_daftar_ulang ? \Carbon\Carbon::parse($item->tgl_daftar_ulang)->format('d/m/Y') : '-' }}
                            </td>

                            {{-- Setoran Terbayar --}}
                            <td class="px-4 py-3.5 text-right font-bold text-sm whitespace-nowrap {{ $jumlahBayar > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                                Rp {{ number_format($jumlahBayar, 0, ',', '.') }}
                            </td>

                            {{-- Metode Pembayaran --}}
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ strcasecmp($metode, 'tunai') === 0 ? 'bg-amber-100 text-amber-900' : 'bg-blue-50 text-blue-900' }}">
                                    {{ $metode }}
                                </span>
                            </td>

                            {{-- Status Berkas Fisik --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($isBerkasDiterima)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-check-circle text-xs text-emerald-600" aria-hidden="true"></i>
                                        <span>Diterima</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock text-xs text-amber-600" aria-hidden="true"></i>
                                        <span>Menunggu</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Status Pembayaran / Daftar Ulang --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($isBerhasil)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle text-xs text-emerald-600" aria-hidden="true"></i>
                                        <span>{{ $status }}</span>
                                    </span>
                                @elseif($isGagal)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        <i class="fas fa-times-circle text-xs text-rose-600" aria-hidden="true"></i>
                                        <span>{{ $status }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <i class="fas fa-clock text-xs text-amber-600" aria-hidden="true"></i>
                                        <span>{{ $status }}</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                                        <i class="fas fa-folder-open" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Tidak ada data daftar ulang</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau rentang tanggal di atas.</p>
                                    <a href="{{ request()->url() }}" class="btn-action-secondary mt-3 !min-h-[38px] !h-[38px] !px-3.5 text-xs inline-flex items-center gap-1.5">
                                        <i class="fas fa-undo-alt text-xs" aria-hidden="true"></i>
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

    {{-- 5. Tampilan Kartu Daftar Ulang (Mobile < 900px) --}}
    <div class="report-mobile-view space-y-3">
        @forelse($displayData as $no => $item)
            @php
                $casis = $item->calonSiswa ?? ($item->pendaftaran?->casis ?? ($item->seleksi?->pembayaran?->calonSiswa ?? null));
                $namaCasis = $casis->nama_calon_siswa ?? ($casis->nama ?? '-');
                $jumlahBayar = (float) ($item->jumlah_bayar ?? 0);
                $status = $item->status_bayar ?? ($item->status_du ?? 'Berhasil');
                $statusBerkas = $item->status_berkas ?? 'Menunggu';
                $isBerkasDiterima = in_array($statusBerkas, ['Diterima', 'Sudah Diterima', 'Lengkap']);
                $isBerhasil = in_array($status, ['Berhasil', 'Lunas', 'Selesai']);
                $isGagal = in_array($status, ['Gagal', 'Ditolak', 'Batal']);
                $metode = $item->metode_pembayaran ?? 'Gateway';
                $words = explode(' ', trim($namaCasis));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
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
                                {{ $namaCasis }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                Reg: {{ $casis->no_registrasi ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($isBerhasil)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i>
                                <span>{{ $status }}</span>
                            </span>
                        @elseif($isGagal)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                <i class="fas fa-times-circle text-[10px]" aria-hidden="true"></i>
                                <span>{{ $status }}</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                <i class="fas fa-clock text-[10px]" aria-hidden="true"></i>
                                <span>{{ $status }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Detail Info Daftar Ulang --}}
                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-slate-400 block uppercase text-[10px]">Setoran Terbayar</span>
                        <span class="font-bold text-emerald-700 text-sm mt-0.5 block">
                            Rp {{ number_format($jumlahBayar, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block uppercase text-[10px]">Metode / Tgl Daftar Ulang</span>
                        <span class="font-medium text-slate-800 mt-0.5 block">
                            {{ $metode }} &bull; {{ $item->tgl_daftar_ulang ? \Carbon\Carbon::parse($item->tgl_daftar_ulang)->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <div class="col-span-2 pt-1 border-t border-dashed border-slate-100 flex items-center justify-between">
                        <span class="text-slate-400 uppercase text-[10px]">Status Berkas Fisik</span>
                        @if($isBerkasDiterima)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <i class="fas fa-check-circle text-[10px] text-emerald-600" aria-hidden="true"></i>
                                <span>Diterima</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <i class="fas fa-clock text-[10px] text-amber-600" aria-hidden="true"></i>
                                <span>Menunggu</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-6 text-center border border-slate-200/90 text-slate-500 text-sm">
                Tidak ada data daftar ulang yang ditemukan.
            </div>
        @endforelse
    </div>

</div>
@endsection