@extends('layout.tampilancasis')

@section('page-title', 'Daftar Ulang')

@section('content')
@php
    // -------------------------------------------------------------
    // DATA DARI SERVICE / CONTROLLER
    // Catatan: Tidak ada aturan bisnis yang dihitung di view.
    // -------------------------------------------------------------
    $totalBiaya = (float)($totalBiaya ?? 0);
    $totalSudahDibayar = (float)($totalSudahDibayar ?? 0);
    $sisaTagihan = (float)($sisaTagihan ?? $sisaBiaya ?? max(0, $totalBiaya - $totalSudahDibayar));
    $minDp = (float)($minDp ?? min(500000, $sisaTagihan > 0 ? $sisaTagihan : 500000));
    $statusDaftarUlang = $daftarUlang->status_daftar_ulang ?? 'Menunggu';
    $isSelesai = ($statusDaftarUlang === 'Selesai');
    $isLunas = ($sisaTagihan <= 0);
    $isCicilanPertamaLunas = ($totalSudahDibayar >= $minDp);

    // Ambil jenis kelamin calon siswa untuk penyesuaian label seragam
    $jk = strtolower($casis->jenis_kelamin ?? '');
    $genderSeragam = in_array($jk, ['laki-laki', 'pria', 'l']) ? ' (Putra)' : (in_array($jk, ['perempuan', 'wanita', 'p']) ? ' (Putri)' : '');

    // Evaluasi 6 Kondisi Tampilan Sesuai Server
    $isArchived = !empty($isArchived);
    $diumumkanStatus = false;
    if (!empty($seleksi)) {
        if (is_object($seleksi) && method_exists($seleksi, 'isDiumumkan')) {
            $diumumkanStatus = $seleksi->isDiumumkan();
        } else {
            $diumumkanStatus = !empty($seleksi->diumumkan_pada) || (isset($seleksi->is_diumumkan) && $seleksi->is_diumumkan);
        }
    }
    $isLulus = (!empty($seleksi) && $diumumkanStatus && in_array($seleksi->hasil_seleksi ?? null, ['Lulus', 'Lolos']));

    if ($isArchived) {
        $kondisi = 6; // Terarsip
        $subjudul = 'Data pendaftaran diarsipkan. Silakan hubungi panitia PPDB.';
        $currentStep = 1;
    } elseif (!$isLulus || isset($error)) {
        $kondisi = 1; // Belum diumumkan / Tidak Lulus
        $subjudul = 'Informasi kelulusan dan persyaratan daftar ulang calon siswa.';
        $currentStep = 1;
    } elseif ($isSelesai && $isLunas) {
        $kondisi = 5; // Lunas dan Selesai
        $subjudul = 'Seluruh tahapan daftar ulang dan pembayaran biaya pendidikan telah tuntas.';
        $currentStep = 3;
    } elseif ($isSelesai && !$isLunas) {
        $kondisi = 4; // Daftar ulang Selesai, sisa belum lunas
        $subjudul = 'Pendaftaran ulang Anda telah diverifikasi oleh sekolah. Sisa uang pembangunan dilunasi sesuai tanggal jatuh tempo.';
        $currentStep = 3;
    } elseif ($isCicilanPertamaLunas) {
        $kondisi = 3; // Cicilan pertama terpenuhi, berkas belum diterima
        $subjudul = 'Cicilan pertama telah diterima. Silakan serahkan berkas fisik ke sekolah untuk verifikasi.';
        $currentStep = 2;
    } else {
        $kondisi = 2; // Lulus, belum membayar
        $subjudul = 'Selesaikan pembayaran cicilan pertama untuk melanjutkan tahap daftar ulang.';
        $currentStep = 1;
    }

    // Tanggal Jatuh Tempo Tahap 2 dari Service / Pengaturan
    // Jika belum tersedia dari backend, bernilai null / "Belum ditentukan"
    $rawJatuhTempo = $tglJatuhTempo ?? $jatuhTempoTahap2 ?? null;
    $jatuhTempoTampil = !empty($rawJatuhTempo) ? $rawJatuhTempo : 'Belum ditentukan';

    // Status Jatuh Tempo Tahap 2: Lunas, Belum Jatuh Tempo, Jatuh Tempo, Terlambat
    // Dihitung saat dibaca dari tanggal server pada zona waktu sekolah (school_timezone)
    if (!empty($statusJatuhTempoTahap2)) {
        $statusTahap2 = $statusJatuhTempoTahap2;
    } elseif ($isLunas) {
        $statusTahap2 = 'Lunas';
    } elseif (empty($rawJatuhTempo) || $jatuhTempoTampil === 'Belum ditentukan') {
        $statusTahap2 = 'Belum Jatuh Tempo';
    } else {
        try {
            $tz = function_exists('school_timezone') ? school_timezone() : 'Asia/Makassar';
            $today = \Carbon\Carbon::now($tz)->startOfDay();
            $due = \Carbon\Carbon::parse($rawJatuhTempo, $tz)->startOfDay();
            if ($today->lt($due)) {
                $statusTahap2 = 'Belum Jatuh Tempo';
            } elseif ($today->eq($due)) {
                $statusTahap2 = 'Jatuh Tempo';
            } else {
                $statusTahap2 = 'Terlambat';
            }
        } catch (\Exception $e) {
            $statusTahap2 = 'Belum Jatuh Tempo';
        }
    }

    // Keterangan sisa hari bila tersedia dari service
    $sisaHariTeks = $sisaHariJatuhTempo ?? null;
    if (!$sisaHariTeks && !empty($rawJatuhTempo) && $jatuhTempoTampil !== 'Belum ditentukan') {
        try {
            $tz = function_exists('school_timezone') ? school_timezone() : 'Asia/Makassar';
            $today = \Carbon\Carbon::now($tz)->startOfDay();
            $due = \Carbon\Carbon::parse($rawJatuhTempo, $tz)->startOfDay();
            $diffDays = $today->diffInDays($due, false);
            if ($diffDays > 0) {
                $sisaHariTeks = "Sisa {$diffDays} hari lagi";
            } elseif ($diffDays === 0) {
                $sisaHariTeks = "Batas pelunasan hari ini";
            } else {
                $sisaHariTeks = "Lewat " . abs($diffDays) . " hari";
            }
        } catch (\Exception $e) {
            $sisaHariTeks = null;
        }
    }
@endphp

{{-- Load Midtrans Snap JS jika tersedia --}}
@if(!empty($snapJsUrl) && !empty($clientKey))
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
@endif

{{-- Scoped CSS untuk Halaman Daftar Ulang --}}
<style>
    .du-page,
    .du-page h1, .du-page h2, .du-page h3,
    .du-page h4, .du-page h5, .du-page p, .du-page span,
    .du-page strong, .du-page a, .du-page button, .du-page table {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    /* Kartu Standar */
    .du-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
        overflow: hidden;
        transform: none !important;
        transition: none !important;
        animation: none !important;
    }
    .du-card:hover {
        transform: none !important;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05) !important;
    }

    /* Layout Dua Kolom >= 900px, Satu Kolom < 900px */
    .du-layout-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (min-width: 900px) {
        .du-layout-grid {
            grid-template-columns: 1.35fr 1fr;
        }
    }
    @media (min-width: 1200px) {
        .du-layout-grid {
            grid-template-columns: 1.45fr 1fr;
        }
    }
    @media (max-width: 899.98px) {
        .du-col-right {
            order: -1;
        }
    }

    /* Stepper Responsif (Pelunasan Sisa BUKAN langkah stepper karena bukan syarat selesai) */
    .du-stepper-desktop {
        display: none;
    }
    .du-stepper-mobile {
        display: block;
    }
    @media (min-width: 640px) {
        .du-stepper-desktop {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .du-stepper-mobile {
            display: none;
        }
    }

    .du-step-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        min-height: 44px;
        box-sizing: border-box;
    }
    .du-step-item.is-active {
        background: #eff6ff;
        border-color: #3b82f6;
    }
    .du-step-item.is-done {
        background: #f0fdf4;
        border-color: #86efac;
    }

    .du-step-icon {
        width: 32px;
        height: 32px;
        min-width: 32px;
        min-height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Tombol Bayar Sticky di Mobile (< 600px) */
    .du-mobile-pay-bar {
        display: none;
    }
    @media (max-width: 599.98px) {
        .du-mobile-pay-bar {
            display: block;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid #cbd5e1;
            padding: 12px 16px;
            padding-bottom: max(12px, env(safe-area-inset-bottom));
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }
    }

    /* Target Sentuh Minimal 44px */
    .du-touch-target {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
    }

    /* Focus visible */
    a:focus-visible, button:focus-visible, input:focus-visible {
        outline: 2px solid #2563eb !important;
        outline-offset: 2px !important;
    }

    @media (prefers-reduced-motion: reduce) {
        .du-page, .du-page * {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>

<div class="du-page space-y-6 pb-24 sm:pb-16">

    {{-- =========================================================
         HEADER HALAMAN & SUBJUDUL SESUAI KONDISI
         ========================================================= --}}
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl sm:text-3xl text-slate-900 font-extrabold tracking-tight">Daftar Ulang</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-1.5 leading-relaxed">
            {{ $subjudul }}
        </p>
    </div>

    @include('layout.alert')

    {{-- =========================================================
         KONDISI 6: DATA DIARSIPKAN
         ========================================================= --}}
    @if($kondisi === 6)
        <div class="du-card p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0" aria-hidden="true">
                    <i class="fas fa-archive text-xl"></i>
                </div>
                <div class="space-y-3">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Data Pendaftaran Diarsipkan</h2>
                    <p class="text-slate-700 text-sm sm:text-base leading-relaxed">
                        Data pendaftaran calon siswa <strong>{{ $archivedCasis?->nama_calon_siswa ?? '' }}</strong> sedang dalam status diarsipkan. Silakan hubungi panitia PPDB sekolah untuk proses administrasi lebih lanjut.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-3">
                        <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Panitia PPDB SD Kristen Rantai Damai, saya ingin menanyakan perihal status arsip data calon siswa: ' . ($archivedCasis?->nama_calon_siswa ?? '')) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="du-touch-target px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm gap-2">
                            <i class="fab fa-whatsapp text-base" aria-hidden="true"></i>
                            <span>Hubungi Panitia PPDB via WhatsApp</span>
                        </a>
                        <a href="{{ route('berandacasis') }}" class="du-touch-target px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-xl text-sm gap-2">
                            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                            <span>Kembali ke Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    {{-- =========================================================
         KONDISI 1: BELUM DIUMUMKAN / TIDAK LULUS
         (Pesan singkat sopan, tanpa tagihan & tanpa tombol bayar, tautan ke pengumuman)
         ========================================================= --}}
    @elseif($kondisi === 1)
        <div class="du-card p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0" aria-hidden="true">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div class="space-y-3 flex-1">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Tahap Daftar Ulang Belum Terbuka</h2>
                    <p class="text-slate-700 text-sm sm:text-base leading-relaxed">
                        Pendaftaran ulang calon siswa baru hanya dapat dilakukan oleh calon siswa yang telah dinyatakan <strong>Lulus Seleksi</strong> dan hasil seleksi telah resmi dipublikasikan oleh sekolah.
                    </p>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Silakan periksa status dan jadwal pengumuman seleksi Anda melalui tautan di bawah ini.
                    </p>
                    <div class="pt-3 flex flex-wrap gap-3">
                        <a href="{{ route('pengumuman') }}" class="du-touch-target px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm gap-2">
                            <i class="fas fa-bullhorn text-xs" aria-hidden="true"></i>
                            <span>Lihat Pengumuman Seleksi</span>
                        </a>
                        <a href="{{ route('berandacasis') }}" class="du-touch-target px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-xl text-sm gap-2">
                            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                            <span>Kembali ke Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    {{-- =========================================================
         KONDISI 2, 3, 4, 5: CALON SISWA DINYATAKAN LULUS
         ========================================================= --}}
    @else

        {{-- STEPPER TIGA LANGKAH --}}
        {{-- Desktop / Tablet (>= 640px) --}}
        <div class="du-stepper-desktop" aria-label="Tahapan Daftar Ulang">
            {{-- Langkah 1: Bayar cicilan pertama --}}
            <div class="du-step-item {{ $isCicilanPertamaLunas ? 'is-done' : 'is-active' }}">
                <div class="du-step-icon {{ $isCicilanPertamaLunas ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }}" aria-hidden="true">
                    @if($isCicilanPertamaLunas)
                        <i class="fas fa-check text-xs"></i>
                    @else
                        1
                    @endif
                </div>
                <div>
                    <span class="sr-only">Langkah 1:</span>
                    <p class="text-xs font-bold uppercase tracking-wider {{ $isCicilanPertamaLunas ? 'text-emerald-700' : 'text-blue-700' }}">
                        {{ $isCicilanPertamaLunas ? 'Selesai' : 'Langkah 1' }}
                    </p>
                    <p class="text-sm font-semibold text-slate-900 leading-tight">Bayar cicilan pertama</p>
                </div>
            </div>

            {{-- Langkah 2: Serahkan berkas ke sekolah --}}
            <div class="du-step-item {{ $isSelesai ? 'is-done' : ($isCicilanPertamaLunas ? 'is-active' : '') }}">
                <div class="du-step-icon {{ $isSelesai ? 'bg-emerald-600 text-white' : ($isCicilanPertamaLunas ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600') }}" aria-hidden="true">
                    @if($isSelesai)
                        <i class="fas fa-check text-xs"></i>
                    @else
                        2
                    @endif
                </div>
                <div>
                    <span class="sr-only">Langkah 2:</span>
                    <p class="text-xs font-bold uppercase tracking-wider {{ $isSelesai ? 'text-emerald-700' : ($isCicilanPertamaLunas ? 'text-blue-700' : 'text-slate-500') }}">
                        {{ $isSelesai ? 'Selesai' : ($isCicilanPertamaLunas ? 'Aktif' : 'Menunggu') }}
                    </p>
                    <p class="text-sm font-semibold text-slate-900 leading-tight">Serahkan berkas ke sekolah</p>
                </div>
            </div>

            {{-- Langkah 3: Selesai (Pelunasan sisa bukan syarat selesai) --}}
            <div class="du-step-item {{ $isSelesai ? 'is-done' : '' }}">
                <div class="du-step-icon {{ $isSelesai ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}" aria-hidden="true">
                    @if($isSelesai)
                        <i class="fas fa-check text-xs"></i>
                    @else
                        3
                    @endif
                </div>
                <div>
                    <span class="sr-only">Langkah 3:</span>
                    <p class="text-xs font-bold uppercase tracking-wider {{ $isSelesai ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $isSelesai ? 'Selesai' : 'Tahap Akhir' }}
                    </p>
                    <p class="text-sm font-semibold text-slate-900 leading-tight">Selesai</p>
                </div>
            </div>
        </div>

        {{-- Mobile (< 640px) --}}
        <div class="du-stepper-mobile du-card p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">
                    Langkah {{ $currentStep }} dari 3
                </span>
                <span class="text-xs font-semibold text-slate-600">
                    @if($currentStep === 1)
                        Bayar cicilan pertama
                    @elseif($currentStep === 2)
                        Serahkan berkas ke sekolah
                    @else
                        Selesai
                    @endif
                </span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden" role="progressbar" aria-valuenow="{{ $currentStep }}" aria-valuemin="1" aria-valuemax="3">
                <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ round(($currentStep / 3) * 100) }}%"></div>
            </div>
        </div>

        {{-- BAR INFO WAJIB: Aturan Pelaksanaan Daftar Ulang & Jatuh Tempo --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-5 flex items-start gap-3.5 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                <i class="fas fa-info-circle text-base"></i>
            </div>
            <p class="text-slate-800 text-xs sm:text-sm leading-relaxed m-0">
                Daftar ulang dianggap selesai setelah cicilan pertama dibayar dan berkas diterima sekolah. Sisa uang pembangunan dilunasi paling lambat {{ $jatuhTempoTampil }}.
            </p>
        </div>

        {{-- =========================================================
             KONDISI 5: LUNAS DAN SELESAI
             ========================================================= --}}
        @if($kondisi === 5)
            <div class="du-card overflow-hidden">
                <div class="bg-emerald-600 px-6 py-6 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0 text-2xl" aria-hidden="true">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider">Status: Selesai</span>
                            <h2 class="text-lg sm:text-xl font-bold mt-1">Daftar Ulang Selesai &amp; Lunas Penuh</h2>
                        </div>
                    </div>
                    @if(Route::has('calon_siswa.daftar_ulang.print'))
                        <a href="{{ route('calon_siswa.daftar_ulang.print') }}" target="_blank"
                           class="du-touch-target px-5 py-2.5 bg-white hover:bg-emerald-50 text-emerald-800 font-bold rounded-xl text-sm gap-2 shadow-sm shrink-0">
                            <i class="fas fa-print" aria-hidden="true"></i>
                            <span>Cetak Bukti Resmi</span>
                        </a>
                    @endif
                </div>

                <div class="p-6 sm:p-7 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <p class="text-xs text-slate-500 mb-1">Nama Calon Siswa</p>
                            <p class="font-bold text-slate-900 text-base">{{ $casis->nama_calon_siswa }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <p class="text-xs text-slate-500 mb-1">Total Biaya Daftar Ulang</p>
                            <p class="font-bold text-slate-900 text-base">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                        </div>
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                            <p class="text-xs text-emerald-800 mb-1 font-semibold">Total Terbayar Lunas</p>
                            <p class="font-extrabold text-emerald-700 text-base">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Riwayat Pembayaran --}}
                    <div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-base">
                            <i class="fas fa-history text-blue-600" aria-hidden="true"></i>
                            <span>Riwayat Pembayaran</span>
                        </h3>
                        @if(count($riwayatPembayaran) > 0)
                            <div class="overflow-x-auto rounded-xl border border-slate-200">
                                <table class="w-full text-center text-sm">
                                    <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 text-xs font-bold uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-center">Tanggal</th>
                                            <th class="px-4 py-3 text-center">Metode</th>
                                            <th class="px-4 py-3 text-center">Jumlah</th>
                                            <th class="px-4 py-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($riwayatPembayaran as $pay)
                                            @php
                                                $isPendingRow = in_array($pay->status_du, ['Pending', 'Menunggu']);
                                                $isLunasRow = in_array($pay->status_du, ['Lunas', 'Diterima']);
                                            @endphp
                                            <tr class="{{ $isPendingRow ? 'bg-amber-50/40' : '' }}">
                                                <td class="px-4 py-3 text-center text-slate-700">
                                                    @if($isPendingRow)
                                                        <span class="font-medium text-amber-900">Dibuat {{ format_school_time($pay->tgl_bayar_du, 'd M Y, H:i') }}</span>
                                                    @else
                                                        <span>{{ format_school_time($pay->tgl_bayar_du, 'd M Y, H:i') }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center text-slate-700">{{ $pay->metode_pembayaran ?: 'Midtrans' }}</td>
                                                <td class="px-4 py-3 text-center font-bold text-slate-900 tabular-nums">Rp {{ number_format($pay->jml_bayar_du, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($isLunasRow)
                                                        <span class="font-bold text-emerald-700">Lunas</span>
                                                    @elseif($isPendingRow)
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                            <i class="fas fa-clock text-[11px]" aria-hidden="true"></i>
                                                            <span>Menunggu Pembayaran</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                            <span>{{ $pay->status_du }}</span>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-sm text-slate-500 italic p-4 bg-slate-50 rounded-xl border border-slate-100">Belum ada pembayaran.</p>
                        @endif
                    </div>
                </div>
            </div>

        {{-- =========================================================
             KONDISI 2, 3, 4: LAYOUT DUA KOLOM (>= 900px, 1 Kolom < 900px)
             ========================================================= --}}
        @else
            <div class="du-layout-grid">

                {{-- =========================================================
                     KOLOM KIRI: RINCIAN TAGIHAN, PELUNASAN SISA, RIWAYAT
                     ========================================================= --}}
                <div class="space-y-6">

                    {{-- 1. RINCIAN TAGIHAN PER KOMPONEN --}}
                    <div class="du-card p-5 sm:p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fas fa-file-invoice text-blue-600" aria-hidden="true"></i>
                                <span>Rincian Tagihan</span>
                            </h2>
                            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full border border-slate-200">
                                TA {{ $tahunAjar?->tahun_ajaran ?? '2026/2027' }}
                            </span>
                        </div>

                        {{-- Tabel / Grid Komponen --}}
                        <div class="overflow-x-auto rounded-xl border border-slate-200 mb-4">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold uppercase text-[11px]">
                                    <tr>
                                        <th class="px-3.5 py-2.5">Komponen Biaya</th>
                                        <th class="px-3.5 py-2.5 text-right">Tagihan</th>
                                        <th class="px-3.5 py-2.5 text-right">Terbayar</th>
                                        <th class="px-3.5 py-2.5 text-right">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @php
                                        // Alokasi pembayaran per komponen secara informatif
                                        $sisaDanaBayar = $totalSudahDibayar;
                                    @endphp
                                    @foreach($biayaComponents as $idx => $biaya)
                                        @php
                                            $nom = (float) data_get($biaya, 'nominal', 0);
                                            $alokasiBayar = min($nom, max(0, $sisaDanaBayar));
                                            $sisaKomponen = max(0, $nom - $alokasiBayar);
                                            $sisaDanaBayar -= $alokasiBayar;

                                            // Penyesuaian nama baris seragam berdasarkan data siswa
                                            $namaKomponenTampil = (string) data_get($biaya, 'nama_biaya', 'Komponen Biaya');
                                            if (stripos($namaKomponenTampil, 'seragam') !== false && !empty($genderSeragam)) {
                                                $namaKomponenTampil .= $genderSeragam;
                                            }
                                            $ketKomponen = data_get($biaya, 'keterangan');
                                        @endphp
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-3.5 py-3">
                                                <p class="font-semibold text-slate-800">{{ $namaKomponenTampil }}</p>
                                                @if(!empty($ketKomponen))
                                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $ketKomponen }}</p>
                                                @endif
                                            </td>
                                            <td class="px-3.5 py-3 text-right text-slate-800 font-medium tabular-nums">
                                                Rp {{ number_format($nom, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3.5 py-3 text-right text-emerald-700 font-semibold tabular-nums">
                                                Rp {{ number_format($alokasiBayar, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3.5 py-3 text-right font-bold text-slate-900 tabular-nums">
                                                Rp {{ number_format($sisaKomponen, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-slate-50 font-bold border-t border-slate-200 text-xs sm:text-sm">
                                    <tr>
                                        <td class="px-3.5 py-3 text-slate-900">Total</td>
                                        <td class="px-3.5 py-3 text-right text-slate-900 tabular-nums">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                                        <td class="px-3.5 py-3 text-right text-emerald-700 tabular-nums">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</td>
                                        <td class="px-3.5 py-3 text-right text-slate-900 tabular-nums">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Progres Daftar Ulang jika status Selesai tapi cicilan belum lunas (Kondisi 4) --}}
                        @if($kondisi === 4)
                            <div class="mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-emerald-900 uppercase tracking-wide">
                                        <i class="fas fa-check-circle text-emerald-600 mr-1" aria-hidden="true"></i>
                                        Status Daftar Ulang: Selesai
                                    </span>
                                    <span class="text-xs font-extrabold text-emerald-800">
                                        {{ round(($totalSudahDibayar / ($totalBiaya ?: 1)) * 100) }}% Terbayar
                                    </span>
                                </div>
                                <div class="w-full bg-emerald-200 rounded-full h-2 overflow-hidden" role="progressbar" aria-valuenow="{{ $totalSudahDibayar }}" aria-valuemin="0" aria-valuemax="{{ $totalBiaya }}">
                                    <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min(100, round(($totalSudahDibayar / ($totalBiaya ?: 1)) * 100)) }}%"></div>
                                </div>
                                <p class="text-xs text-emerald-800 mt-2 leading-relaxed">
                                    Berkas fisik telah diverifikasi oleh sekolah. Sisa uang pembangunan dilunasi dalam satu pembayaran sesuai jadwal jatuh tempo di bawah.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- 2. KONDISI 3: INSTRUKSI SERAHKAN BERKAS KE SEKOLAH (Langkah 2 Aktif) --}}
                    @if($kondisi === 3)
                        <div class="du-card p-5 sm:p-6 border-l-4 border-l-blue-600">
                            <div class="flex items-start gap-3.5 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0" aria-hidden="true">
                                    <i class="fas fa-folder-open text-lg"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Langkah 2 Aktif</span>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Serahkan Berkas ke Sekolah</h3>
                                    <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                                        Cicilan pertama telah kami terima. Harap serahkan dokumen fisik berikut langsung ke panitia PPDB di sekolah:
                                    </p>
                                </div>
                            </div>

                            <ul class="space-y-2.5 text-xs sm:text-sm text-slate-700 pl-1 mb-4" aria-label="Daftar berkas wajib">
                                <li class="flex items-center gap-2.5">
                                    <i class="fas fa-check text-emerald-600 text-xs shrink-0" aria-hidden="true"></i>
                                    <span>Akte Kelahiran calon siswa (asli &amp; fotokopi)</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i class="fas fa-check text-emerald-600 text-xs shrink-0" aria-hidden="true"></i>
                                    <span>Kartu Keluarga (KK) orang tua/wali (asli &amp; fotokopi)</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i class="fas fa-check text-emerald-600 text-xs shrink-0" aria-hidden="true"></i>
                                    <span>Pas foto calon siswa ukuran 3x4 (2 lembar)</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <i class="fas fa-check text-emerald-600 text-xs shrink-0" aria-hidden="true"></i>
                                    <span>Bukti pembayaran cicilan pertama (dapat ditunjukkan dari gawai atau cetak)</span>
                                </li>
                            </ul>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>Batas waktu penyerahan:</span>
                                {{-- TODO: Ambil batas waktu penyerahan berkas secara dinamis jika kolom sudah ditambahkan pada tb_tahun_ajaran --}}
                                <span class="font-bold text-slate-700">{{ $batasWaktuBerkas ?? 'Belum ditentukan' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- 3. PELUNASAN SISA UANG PEMBANGUNAN (Muncul setelah cicilan pertama terpenuhi) --}}
                    @if($isCicilanPertamaLunas && $sisaTagihan > 0)
                        <div class="du-card p-5 sm:p-6">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-calendar-alt text-blue-600" aria-hidden="true"></i>
                                        <span>Pelunasan Sisa Uang Pembangunan</span>
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Sisa uang pembangunan dilunasi dalam satu pembayaran paling lambat tanggal jatuh tempo</p>
                                </div>
                            </div>

                            {{-- Kartu Ringkasan Sisa & Jatuh Tempo --}}
                            <div class="p-4 rounded-xl border {{ $statusTahap2 === 'Terlambat' ? 'bg-rose-50/70 border-rose-200' : ($statusTahap2 === 'Jatuh Tempo' ? 'bg-amber-50/70 border-amber-200' : 'bg-slate-50 border-slate-200') }}">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-500 block">Sisa Pembayaran Tahap 2</span>
                                        <span class="text-xl sm:text-2xl font-extrabold text-slate-900 tabular-nums">
                                            Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="sm:text-right">
                                        <span class="text-xs text-slate-500 block mb-1">Status Pelunasan</span>
                                        @if($statusTahap2 === 'Lunas')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-check-circle text-[11px]" aria-hidden="true"></i>
                                                <span>Lunas</span>
                                            </span>
                                        @elseif($statusTahap2 === 'Jatuh Tempo')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                <i class="fas fa-clock text-[11px]" aria-hidden="true"></i>
                                                <span>Jatuh Tempo</span>
                                            </span>
                                        @elseif($statusTahap2 === 'Terlambat')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                                <i class="fas fa-exclamation-circle text-[11px]" aria-hidden="true"></i>
                                                <span>Terlambat</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                                <i class="fas fa-calendar text-[11px]" aria-hidden="true"></i>
                                                <span>Belum Jatuh Tempo</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 text-slate-700">
                                        <i class="far fa-calendar-check text-slate-500" aria-hidden="true"></i>
                                        <span>Jatuh Tempo: <strong>{{ $jatuhTempoTampil }}</strong></span>
                                        {{-- TODO: Hubungkan tanggal jatuh tempo dinamis dari modul backend tb_tagihan_tahap2 jika sudah aktif --}}
                                    </div>
                                    @if($sisaHariTeks)
                                        <span class="font-bold {{ $statusTahap2 === 'Terlambat' ? 'text-rose-700' : ($statusTahap2 === 'Jatuh Tempo' ? 'text-amber-700' : 'text-slate-600') }}">
                                            {{ $sisaHariTeks }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- KOTAK INFORMASI LAPOR KE ADMIN --}}
                            {{-- Tampil otomatis bila Jatuh Tempo atau Terlambat, dan bisa dibuka via tombol ringkas saat belum jatuh tempo --}}
                            @php
                                $autoShowLapor = in_array($statusTahap2, ['Jatuh Tempo', 'Terlambat']);
                            @endphp
                            <div x-data="{ openLapor: {{ $autoShowLapor ? 'true' : 'false' }} }" class="mt-4">
                                @if(!$autoShowLapor)
                                    <div class="text-right">
                                        <button type="button" @click="openLapor = !openLapor"
                                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 underline inline-flex items-center gap-1 focus:outline-none">
                                            <i class="fas fa-question-circle text-[11px]" aria-hidden="true"></i>
                                            <span>Belum mampu membayar sisa?</span>
                                        </button>
                                    </div>
                                @endif

                                <div x-show="openLapor" class="mt-2 bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-5 flex items-start gap-3 shadow-sm">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                        <i class="fas fa-headset text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <p class="text-[15px] leading-relaxed text-slate-800 m-0">
                                            @if($statusTahap2 === 'Terlambat')
                                                <strong>Pembayaran sisa telah melewati jatuh tempo.</strong>
                                            @elseif($statusTahap2 === 'Jatuh Tempo')
                                                <strong>Hari ini adalah batas waktu jatuh tempo pembayaran sisa.</strong>
                                            @endif
                                            Jika Anda belum mampu melunasi sisa pembayaran, silakan melapor ke admin sekolah agar dapat dibantu.
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 pt-1">
                                            {{-- Tautan WhatsApp resmi sekolah yang sudah ada --}}
                                            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin PPDB SD Kristen Rantai Damai, saya orang tua calon siswa ' . ($casis->nama_calon_siswa ?? '') . ' ingin melapor dan berkonsultasi perihal pelunasan sisa uang pembangunan.') }}"
                                               target="_blank" rel="noopener noreferrer"
                                               class="du-touch-target px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm">
                                                <i class="fab fa-whatsapp text-base" aria-hidden="true"></i>
                                                <span>Hubungi Admin via WhatsApp</span>
                                            </a>
                                            <span class="text-xs text-slate-500">atau datang langsung ke sekolah</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 4. RIWAYAT PEMBAYARAN --}}
                    <div class="du-card p-5 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-receipt text-blue-600" aria-hidden="true"></i>
                            <span>Riwayat Pembayaran</span>
                        </h3>

                        @if(count($riwayatPembayaran) > 0)
                            <div class="overflow-x-auto rounded-xl border border-slate-200">
                                <table class="w-full text-center text-xs sm:text-sm">
                                    <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold uppercase text-[11px]">
                                        <tr>
                                            <th class="px-3.5 py-2.5 text-center">Tanggal</th>
                                            <th class="px-3.5 py-2.5 text-center">Metode</th>
                                            <th class="px-3.5 py-2.5 text-center">Jumlah</th>
                                            <th class="px-3.5 py-2.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($riwayatPembayaran as $pay)
                                            @php
                                                $isPendingRow = in_array($pay->status_du, ['Pending', 'Menunggu']);
                                                $isLunasRow = in_array($pay->status_du, ['Lunas', 'Diterima']);
                                            @endphp
                                            <tr class="{{ $isPendingRow ? 'bg-amber-50/40' : 'hover:bg-slate-50/50' }}">
                                                <td class="px-3.5 py-3 text-center text-slate-700">
                                                    @if($isPendingRow)
                                                        <span class="font-medium text-amber-900">Dibuat {{ format_school_time($pay->tgl_bayar_du, 'd M Y, H:i') }}</span>
                                                    @else
                                                        <span>{{ format_school_time($pay->tgl_bayar_du, 'd M Y, H:i') }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-3.5 py-3 text-center text-slate-700">{{ $pay->metode_pembayaran ?: 'Midtrans (Gateway)' }}</td>
                                                <td class="px-3.5 py-3 text-center font-bold text-slate-900 tabular-nums">Rp {{ number_format($pay->jml_bayar_du, 0, ',', '.') }}</td>
                                                <td class="px-3.5 py-3 text-center">
                                                    @if($isLunasRow)
                                                        <span class="font-bold text-emerald-700">Lunas</span>
                                                    @elseif($isPendingRow)
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                                            <i class="fas fa-clock text-[10px]" aria-hidden="true"></i>
                                                            <span>Menunggu Pembayaran</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                                            <span>{{ $pay->status_du }}</span>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-xs sm:text-sm text-slate-500 italic p-4 bg-slate-50 rounded-xl border border-slate-100 m-0">
                                Belum ada pembayaran.
                            </p>
                        @endif
                    </div>

                    {{-- 5. KARTU SYARAT DAFTAR ULANG (Dipindahkan ke kolom kiri untuk keseimbangan layout) --}}
                    <div class="du-card p-5 sm:p-6">
                        <div class="border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fas fa-clipboard-check text-blue-600" aria-hidden="true"></i>
                                <span>Syarat Daftar Ulang</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pantau kelengkapan syarat kelulusan dan berkas</p>
                        </div>

                        {{-- Daftar Centang Dinamis dari Status Server --}}
                        <div class="space-y-3 mb-4" aria-label="Status Syarat Daftar Ulang">
                            {{-- Syarat 1: Hasil seleksi Lulus --}}
                            <div class="flex items-start gap-3 p-2.5 rounded-lg bg-emerald-50/70 border border-emerald-100">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                    <i class="fas fa-check text-[11px]"></i>
                                </div>
                                <div class="text-xs sm:text-sm">
                                    <p class="font-bold text-emerald-950">Hasil seleksi Lulus</p>
                                    <p class="text-emerald-800 text-[11px] mt-0.5">Calon siswa dinyatakan lulus seleksi penerimaan.</p>
                                </div>
                            </div>

                            {{-- Syarat 2: Cicilan pertama dibayar --}}
                            <div class="flex items-start gap-3 p-2.5 rounded-lg {{ $isCicilanPertamaLunas ? 'bg-emerald-50/70 border border-emerald-100' : 'bg-slate-50 border border-slate-200' }}">
                                <div class="w-6 h-6 rounded-full {{ $isCicilanPertamaLunas ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                    <i class="fas {{ $isCicilanPertamaLunas ? 'fa-check' : 'fa-hourglass-half' }} text-[11px]"></i>
                                </div>
                                <div class="text-xs sm:text-sm">
                                    <p class="font-bold {{ $isCicilanPertamaLunas ? 'text-emerald-950' : 'text-slate-800' }}">Cicilan pertama dibayar</p>
                                    <p class="{{ $isCicilanPertamaLunas ? 'text-emerald-800' : 'text-slate-500' }} text-[11px] mt-0.5">
                                        {{ $isCicilanPertamaLunas ? 'Pembayaran cicilan pertama telah diterima.' : 'Wajib membayar cicilan pertama (buku, seragam, dan uang muka pembangunan).' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Syarat 3: Berkas diserahkan ke sekolah --}}
                            <div class="flex items-start gap-3 p-2.5 rounded-lg {{ $isSelesai ? 'bg-emerald-50/70 border border-emerald-100' : 'bg-slate-50 border border-slate-200' }}">
                                <div class="w-6 h-6 rounded-full {{ $isSelesai ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600' }} flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                    <i class="fas {{ $isSelesai ? 'fa-check' : 'fa-clock' }} text-[11px]"></i>
                                </div>
                                <div class="text-xs sm:text-sm">
                                    <p class="font-bold {{ $isSelesai ? 'text-emerald-950' : 'text-slate-800' }}">Berkas diserahkan ke sekolah</p>
                                    <p class="{{ $isSelesai ? 'text-emerald-800' : 'text-slate-500' }} text-[11px] mt-0.5">
                                        {{ $isSelesai ? 'Berkas fisik asli & fotokopi telah diverifikasi.' : 'Serahkan dokumen ke sekolah setelah cicilan 1.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Batas Waktu Daftar Ulang --}}
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                            <span>Batas waktu daftar ulang:</span>
                            {{-- TODO: Ambil batas waktu dari tabel tb_tahun_ajaran jika kolom tersedia --}}
                            <span class="font-bold text-slate-800">{{ $batasWaktuDaftarUlang ?? 'Belum ditentukan' }}</span>
                        </div>
                    </div>

                    {{-- 6. KARTU BANTUAN PEMBAYARAN DI SEKOLAH --}}
                    <div class="du-card p-5 sm:p-6">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                                <i class="fas fa-school text-base"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-slate-900 mb-1">Pembayaran Langsung di Sekolah</h4>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-3 m-0">
                                    Bayar langsung di sekolah juga tersedia. Hubungi admin jika memerlukan bantuan khusus.
                                </p>
                                <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin PPDB SD Kristen Rantai Damai, saya ingin menanyakan perihal pembayaran daftar ulang langsung di sekolah untuk calon siswa: ' . ($casis->nama_calon_siswa ?? '')) }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="du-touch-target px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm inline-flex items-center gap-2 shadow-sm transition-all">
                                    <i class="fab fa-whatsapp text-base" aria-hidden="true"></i>
                                    <span>Hubungi Admin via WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- =========================================================
                     KOLOM KANAN: FORM PEMBAYARAN SAJA (ALIRAN NORMAL)
                     ========================================================= --}}
                <div class="du-col-right space-y-6">

                    {{-- KARTU FORMULIR PEMBAYARAN ONLINE (Tanpa sticky agar tidak menimpa elemen lain) --}}
                    <div class="du-card p-5 sm:p-6">
                        <div class="border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fas fa-credit-card text-blue-600" aria-hidden="true"></i>
                                <span>Pembayaran</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pilih nominal pembayaran resmi yang terverifikasi otomatis</p>
                        </div>

                        {{-- Ringkasan Biaya: Total, Terbayar, Sisa --}}
                        <div class="space-y-2 mb-5 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs sm:text-sm">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Total Tagihan:</span>
                                <span class="font-bold text-slate-800 tabular-nums">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Total Terbayar:</span>
                                <span class="font-bold text-emerald-700 tabular-nums">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                                <span class="font-bold text-slate-900">Sisa Tagihan:</span>
                                <span class="text-base sm:text-lg font-extrabold text-blue-700 tabular-nums">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- NOTIFIKASI SETORAN PENDING --}}
                        @if($pembayaranPending)
                            <div class="mb-5 p-4 bg-amber-50 border-2 border-amber-300 rounded-xl space-y-2.5">
                                <div class="flex items-start gap-2.5">
                                    <i class="fas fa-clock text-amber-600 mt-0.5 text-base shrink-0" aria-hidden="true"></i>
                                    <div class="flex-1">
                                        <p class="text-xs sm:text-sm font-bold text-amber-950 m-0">
                                            Ada pembayaran Rp {{ number_format($pembayaranPending->jml_bayar_du, 0, ',', '.') }} yang menunggu penyelesaian
                                        </p>
                                        @if(!empty($pembayaranPending->waktu_kadaluarsa))
                                            <p class="text-xs text-amber-800 mt-1 m-0">
                                                <i class="fas fa-stopwatch mr-1" aria-hidden="true"></i>
                                                Selesaikan sebelum: <strong>{{ format_school_time($pembayaranPending->waktu_kadaluarsa, 'd M Y, H:i') }}</strong>
                                            </p>
                                        @else
                                            {{-- TODO: Batas waktu kedaluwarsa belum ditentukan oleh service untuk transaksi pending ini --}}
                                        @endif
                                        <p class="text-[11px] text-amber-800 mt-1.5 leading-relaxed m-0">
                                            Transaksi sebelumnya sedang menunggu pembayaran. Selesaikan pembayaran tersebut atau tunggu hingga batas waktu berakhir sebelum membuat transaksi baru.
                                        </p>
                                    </div>
                                </div>

                                {{-- Tombol Lanjutkan Pembayaran (HANYA jika Snap Token tersedia) --}}
                                @if(!empty($pembayaranPending->snap_token))
                                    <button type="button" onclick="resumePendingPayment('{{ $pembayaranPending->snap_token }}')"
                                            class="du-touch-target w-full mt-2 py-3 px-4 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold rounded-xl shadow transition-all flex items-center justify-center gap-2 text-xs sm:text-sm">
                                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                                        <span>Lanjutkan Pembayaran via Midtrans</span>
                                    </button>
                                @else
                                    {{-- TODO: Token Snap atau Route lanjutan untuk setoran pending belum tersedia dari service --}}
                                @endif
                            </div>
                        @endif

                        {{-- Formulir Pembayaran --}}
                        <form id="payment-form" action="{{ route('calon_siswa.daftar_ulang.store') }}" method="POST">
                            @csrf

                            {{-- Sinkronisasi field backend: metode_pembayaran & snap_token --}}
                            <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="Lunas">
                            <input type="hidden" name="snap_token" id="snap_token">

                            {{-- Field Nominal Custom (dipakai backend / JS untuk nominal terkunci) --}}
                            <input type="hidden" id="nominal_custom" name="nominal_custom" value="{{ !$isCicilanPertamaLunas ? $totalBiaya : $sisaTagihan }}">

                            {{-- =========================================================
                                 SKENARIO A: SEBELUM CICILAN PERTAMA
                                 (Pilihan: Bayar Lunas vs Cicil Pertama)
                                 ========================================================= --}}
                            @if(!$isCicilanPertamaLunas)
                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                        Pilih Skema Pembayaran
                                    </label>
                                    <div class="space-y-2.5">
                                        {{-- Opsi 1: Bayar Lunas --}}
                                        <label for="opsi_lunas" class="du-touch-target w-full flex items-start gap-3 p-3.5 border-2 border-slate-200 rounded-xl {{ $pembayaranPending ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'cursor-pointer hover:border-blue-400 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50' }}">
                                            <input type="radio" id="opsi_lunas" name="jenis_bayar_du" value="Lunas"
                                                   class="mt-1 text-blue-600 focus:ring-blue-500" {{ !$pembayaranPending ? 'checked' : 'disabled' }}
                                                   {{ $pembayaranPending ? 'disabled' : '' }}
                                                   onchange="handlePaymentOptionChange('lunas', {{ $totalBiaya }})">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-slate-900 text-sm">Bayar Lunas</span>
                                                    <span class="font-extrabold text-slate-900 text-sm tabular-nums">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
                                                </div>
                                                <p class="text-xs text-slate-500 mt-0.5">Lunasi seluruh biaya masuk &amp; daftar ulang sekaligus</p>
                                            </div>
                                        </label>

                                        {{-- Opsi 2: Cicil Pertama (Nominal Terkunci) --}}
                                        <label for="opsi_cicil_pertama" class="du-touch-target w-full flex items-start gap-3 p-3.5 border-2 border-slate-200 rounded-xl {{ $pembayaranPending ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'cursor-pointer hover:border-blue-400 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50' }}">
                                            <input type="radio" id="opsi_cicil_pertama" name="jenis_bayar_du" value="Cicil"
                                                   class="mt-1 text-blue-600 focus:ring-blue-500"
                                                   {{ $pembayaranPending ? 'disabled' : '' }}
                                                   onchange="handlePaymentOptionChange('cicil_pertama', {{ $minDp }})">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-slate-900 text-sm">Cicil Pertama</span>
                                                    <span class="font-extrabold text-slate-900 text-sm tabular-nums">Rp {{ number_format($minDp, 0, ',', '.') }}</span>
                                                </div>
                                                <p class="text-xs text-slate-600 mt-1 leading-snug">
                                                    Rp {{ number_format($minDp, 0, ',', '.') }} (buku, seragam, dan uang muka pembangunan)
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                            {{-- =========================================================
                                 SKENARIO B: SETELAH CICILAN PERTAMA
                                 (Satu aksi: Lunasi Sisa Rp {sisa} sesuai Tahap 2)
                                 ========================================================= --}}
                            @else
                                <div class="mb-4">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                        Pelunasan Tahap 2
                                    </label>
                                    <div class="p-3.5 border-2 border-blue-600 bg-blue-50/50 rounded-xl {{ $pembayaranPending ? 'opacity-60' : '' }}">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-bold text-slate-900 text-sm">Lunasi Sisa Uang Pembangunan</span>
                                            <span class="font-extrabold text-blue-800 text-base tabular-nums">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 leading-snug m-0">
                                            Sesuai ketentuan tahap 2, sisa uang pembangunan dilunasi dalam satu pembayaran paling lambat tanggal jatuh tempo.
                                        </p>
                                        <input type="hidden" name="jenis_bayar_du" value="Lunas">
                                    </div>
                                </div>
                            @endif

                            {{-- Kotak Tampilan Nominal Terpilih (Teks Tetap, bukan kolom bebas) --}}
                            <div class="mb-4 p-3 bg-blue-50/80 border border-blue-200 rounded-xl flex items-center justify-between">
                                <span class="text-xs font-semibold text-blue-900">Nominal yang akan dibayar:</span>
                                <span id="nominal-display" class="text-base font-extrabold text-blue-800 tabular-nums">
                                    Rp {{ number_format(!$isCicilanPertamaLunas ? $totalBiaya : $sisaTagihan, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Pesan Validasi Inline (aria-live) --}}
                            <div id="validation-message" aria-live="polite" class="text-xs text-rose-600 font-semibold mb-3 hidden"></div>

                            {{-- Tombol Bayar via Midtrans (Desktop) --}}
                            <button type="button" id="pay-button"
                                    {{ $pembayaranPending ? 'disabled' : '' }}
                                    class="du-touch-target w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                <span>Bayar via Midtrans</span>
                            </button>
                            @if($pembayaranPending)
                                <p class="text-xs text-slate-500 text-center mt-2.5 m-0">
                                    Pilihan skema dan tombol bayar baru dinonaktifkan karena terdapat pembayaran yang sedang menunggu penyelesaian.
                                </p>
                            @endif

                            {{-- Keterangan Biaya & Kebijakan Privasi --}}
                            <div class="mt-3.5 space-y-1.5 text-center">
                                <p class="text-xs font-bold text-emerald-700 flex items-center justify-center gap-1.5 m-0">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    <span>Tidak ada biaya tambahan untuk pembayaran online.</span>
                                </p>
                                <p class="text-[11px] text-slate-500 leading-relaxed m-0">
                                    Pembayaran diproses oleh Midtrans. Data kartu atau rekening Anda tidak disimpan oleh sekolah.
                                </p>
                            </div>
                        </form>
                    </div>

                </div>

            </div>

            {{-- =========================================================
                 TOMBOL BAYAR STICKY DI MOBILE (< 600px)
                 Menempel di bawah layar dengan safe-area padding
                 ========================================================= --}}
            <div class="du-mobile-pay-bar" aria-label="Panel Pembayaran Mobile">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        @if($pembayaranPending)
                            <span class="text-[10px] uppercase font-bold text-amber-700 block leading-tight">Menunggu Bayar</span>
                            <span id="mobile-nominal-display" class="text-base font-extrabold text-amber-800 tabular-nums block leading-tight">
                                Rp {{ number_format($pembayaranPending->jml_bayar_du, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-slate-500 block leading-tight">Total Bayar</span>
                            <span id="mobile-nominal-display" class="text-base font-extrabold text-blue-700 tabular-nums block leading-tight">
                                Rp {{ number_format(!$isCicilanPertamaLunas ? $totalBiaya : $sisaTagihan, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    @if($pembayaranPending)
                        @if(!empty($pembayaranPending->snap_token))
                            <button type="button" onclick="resumePendingPayment('{{ $pembayaranPending->snap_token }}')"
                                    class="du-touch-target px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs sm:text-sm shrink-0 flex items-center gap-1.5 shadow-md">
                                <i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i>
                                <span>Lanjutkan Bayar</span>
                            </button>
                        @else
                            <button type="button" disabled
                                    class="du-touch-target px-4 py-2.5 bg-slate-300 text-slate-500 font-bold rounded-xl text-xs sm:text-sm shrink-0 flex items-center gap-1.5 cursor-not-allowed">
                                <i class="fas fa-clock text-xs" aria-hidden="true"></i>
                                <span>Menunggu</span>
                            </button>
                        @endif
                    @else
                        <button type="button" id="mobile-pay-button"
                                class="du-touch-target px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl text-xs sm:text-sm shrink-0 flex items-center gap-2 shadow-md">
                            <i class="fas fa-lock text-xs" aria-hidden="true"></i>
                            <span>Bayar via Midtrans</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif

    @endif

</div>

{{-- SCRIPT INTERAKSI & INTEGRASI MIDTRANS --}}
<script>
    // State Nominal Terpilih
    let currentNominal = {{ !$isCicilanPertamaLunas ? $totalBiaya : $sisaTagihan }};
    let currentMetode = 'Lunas';
    let currentJenis = 'Lunas';

    function formatRupiah(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    }

    function syncNominalToViews(num, metode, jenis) {
        currentNominal = num;
        currentMetode = metode;
        currentJenis = jenis;

        // Update hidden inputs form
        const nominalInput = document.getElementById('nominal_custom');
        if (nominalInput) {
            nominalInput.value = num;
        }

        const metodeInput = document.getElementById('metode_pembayaran');
        if (metodeInput) {
            metodeInput.value = metode;
        }

        // Update teks tetap di UI desktop & mobile
        const displayDesktop = document.getElementById('nominal-display');
        if (displayDesktop) {
            displayDesktop.textContent = formatRupiah(num);
        }

        const displayMobile = document.getElementById('mobile-nominal-display');
        if (displayMobile) {
            displayMobile.textContent = formatRupiah(num);
        }

        // Validasi tombol bayar
        const payBtn = document.getElementById('pay-button');
        const mobilePayBtn = document.getElementById('mobile-pay-button');
        const hasPending = {{ $pembayaranPending ? 'true' : 'false' }};
        const valid = (num > 0 && num <= {{ $sisaTagihan ?: 1 }}) && !hasPending;

        if (payBtn) payBtn.disabled = !valid;
        if (mobilePayBtn && !hasPending) mobilePayBtn.disabled = !valid;
    }

    function handlePaymentOptionChange(tipe, nominal) {
        if (tipe === 'lunas') {
            syncNominalToViews(nominal, 'Lunas', 'Lunas');
        } else if (tipe === 'cicil_pertama') {
            syncNominalToViews(nominal, 'DP 50%', 'Cicil');
        }
    }

    // Inisialisasi awal saat load
    document.addEventListener('DOMContentLoaded', function () {
        @if(!$isCicilanPertamaLunas)
            syncNominalToViews({{ $totalBiaya }}, 'Lunas', 'Lunas');
        @else
            syncNominalToViews({{ $sisaTagihan }}, 'Lunas', 'Lunas');
        @endif
    });

    // Lanjutkan pembayaran pending yang sudah memiliki Snap Token
    function resumePendingPayment(snapToken) {
        if (!snapToken) return;
        if (typeof window.snap !== 'undefined') {
            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    window.location.reload();
                },
                onPending: function(result) {
                    window.location.reload();
                },
                onError: function(result) {
                    alert('Pembayaran gagal: ' + (result.status_message || 'Terjadi kesalahan'));
                },
                onClose: function() {
                    // Tetap di halaman
                }
            });
        } else {
            alert('Sistem pembayaran Midtrans sedang memuat. Silakan coba sesaat lagi.');
        }
    }

    // Handler Eksekusi Pembayaran Midtrans (Desktop & Mobile)
    function executePayment() {
        const payButton = document.getElementById('pay-button');
        const mobilePayButton = document.getElementById('mobile-pay-button');
        const validationMsg = document.getElementById('validation-message');

        if (validationMsg) {
            validationMsg.classList.add('hidden');
            validationMsg.textContent = '';
        }

        if (currentNominal <= 0) {
            if (validationMsg) {
                validationMsg.textContent = 'Nominal pembayaran tidak valid.';
                validationMsg.classList.remove('hidden');
            }
            return;
        }

        if (payButton) {
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i><span>Menyiapkan Pembayaran...</span>';
        }
        if (mobilePayButton) {
            mobilePayButton.disabled = true;
            mobilePayButton.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i><span>Memproses...</span>';
        }

        // Request Snap Token ke backend controller
        fetch("{{ route('daftar_ulang.create_snap_token') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                metode_pembayaran: currentMetode,
                jenis_bayar_du: currentJenis,
                nominal_custom: currentNominal
            })
        })
        .then(response => response.json())
        .then(data => {
            if ((data.success || data.status === 'success') && data.snap_token) {
                // Trigger popup Snap Midtrans
                if (typeof window.snap !== 'undefined') {
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            window.location.reload();
                        },
                        onPending: function(result) {
                            window.location.reload();
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal: ' + (result.status_message || 'Terjadi kesalahan'));
                            resetPayButtons();
                        },
                        onClose: function() {
                            resetPayButtons();
                        }
                    });
                } else {
                    alert('Sistem pembayaran Midtrans sedang memuat. Silakan coba sesaat lagi.');
                    resetPayButtons();
                }
            } else {
                const msg = data.message || 'Gagal menyiapkan transaksi Midtrans.';
                if (validationMsg) {
                    validationMsg.textContent = msg;
                    validationMsg.classList.remove('hidden');
                } else {
                    alert(msg);
                }
                resetPayButtons();
            }
        })
        .catch(error => {
            console.error('Error snap transaction:', error);
            if (validationMsg) {
                validationMsg.textContent = 'Terjadi kesalahan koneksi saat memproses pembayaran.';
                validationMsg.classList.remove('hidden');
            } else {
                alert('Terjadi kesalahan koneksi saat memproses pembayaran.');
            }
            resetPayButtons();
        });
    }

    function resetPayButtons() {
        const payButton = document.getElementById('pay-button');
        const mobilePayButton = document.getElementById('mobile-pay-button');
        if (payButton) {
            payButton.disabled = false;
            payButton.innerHTML = '<i class="fas fa-lock" aria-hidden="true"></i><span>Bayar via Midtrans</span>';
        }
        if (mobilePayButton) {
            mobilePayButton.disabled = false;
            mobilePayButton.innerHTML = '<i class="fas fa-lock text-xs" aria-hidden="true"></i><span>Bayar via Midtrans</span>';
        }
    }

    const payBtn = document.getElementById('pay-button');
    if (payBtn) {
        payBtn.addEventListener('click', executePayment);
    }
    const mobilePayBtn = document.getElementById('mobile-pay-button');
    if (mobilePayBtn) {
        mobilePayBtn.addEventListener('click', executePayment);
    }
</script>
@endsection
