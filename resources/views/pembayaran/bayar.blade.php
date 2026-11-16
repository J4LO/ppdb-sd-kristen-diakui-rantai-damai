@extends('layout.tampilancasis')

@section('page-title', 'Pembayaran')
@section('title', 'Pembayaran Formulir Pendaftaran')

@section('content')
{{-- Load Midtrans Snap JS --}}
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>

{{-- Scoped Page Styling: Inter Single Font Family, Typography Scale, Zero Card Movement, Responsive Grid --}}
<style>
    /* 1. Paksa satu font family (Inter) ke seluruh elemen di halaman pembayaran */
    .pay-page,
    .pay-page h1, .pay-page h2, .pay-page h3, .pay-page h4, .pay-page h5, .pay-page h6,
    .pay-page p, .pay-page span, .pay-page dt, .pay-page dd, .pay-page label,
    .pay-page button, .pay-page a, .pay-page ol, .pay-page li {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    /* Monospace khusus nomor registrasi dan order ID (ukuran tetap 14-15px seimbang teks sekitar) */
    .pay-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        font-size: 0.9375rem !important; /* 15px */
    }

    /* 2. Kartu Statis (Bebas dari efek gerak/hover/animasi) */
    .pay-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
        transform: none !important;
        transition: none !important;
        animation: none !important;
    }
    .pay-card:hover,
    .pay-card:focus {
        transform: none !important;
        transition: none !important;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05) !important;
    }

    /* 3. Kotak Field Seragam untuk Rincian Transaksi */
    .pay-field-box {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.625rem !important; /* 10px */
        padding: 0.5rem 0.875rem !important; /* 8px 14px */
        height: 52px !important;
        min-height: 52px !important;
        max-height: 52px !important;
        box-sizing: border-box !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.75rem !important;
    }

    /* Grid Data 2 Kolom dengan Celah Seimbang */
    .pay-data-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        row-gap: 20px !important;
        column-gap: 24px !important;
    }
    @media (min-width: 640px) {
        .pay-data-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
        .pay-col-full {
            grid-column: span 2 / span 2 !important;
        }
    }

    .info-tabular {
        font-variant-numeric: tabular-nums;
    }

    /* 4. Sistem Layout Grid Desktop & Reflow Mobile */
    .pay-layout-grid,
    .pay-layout-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    /* Di layar mobile (<900px), gunakan contents agar urutan kartu dapat ditata ulang sesuai permintaan */
    @media (max-width: 899.98px) {
        .pay-layout-grid,
        .pay-layout-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .pay-main-col {
            display: contents;
        }
        .pay-sidebar-col {
            display: contents;
        }
        /* Urutan visual di mobile */
        .order-mobile-lunas-amount { order: 1; }
        .order-mobile-next-step { order: 2; }
        .order-mobile-data-cards { order: 3; }
        .order-mobile-help { order: 4; }
    }

    /* Tablet dan Desktop (900px - 1099px) */
    @media (min-width: 900px) and (max-width: 1099.98px) {
        .pay-layout-container {
            grid-template-columns: 1fr 330px;
        }
        .pay-sidebar-sticky {
            position: sticky;
            top: 1.5rem;
        }
    }

    /* Desktop Lebar (>= 1100px) */
    @media (min-width: 1100px) {
        .pay-layout-container {
            grid-template-columns: 1fr 360px;
        }
        .pay-sidebar-sticky {
            position: sticky;
            top: 1.5rem;
        }
    }

    /* Utility Fallbacks untuk Palet Hijau / Emerald & Slate */
    .w-11 { width: 2.75rem !important; }
    .h-11 { height: 2.75rem !important; }
    .bg-emerald-50 { background-color: #ecfdf5 !important; }
    .bg-emerald-100 { background-color: #d1fae5 !important; }
    .bg-emerald-500 { background-color: #10b981 !important; }
    .bg-emerald-600 { background-color: #059669 !important; }
    .bg-emerald-700 { background-color: #047857 !important; }
    .bg-emerald-800 { background-color: #065f46 !important; }
    .text-emerald-700 { color: #047857 !important; }
    .text-emerald-800 { color: #065f46 !important; }
    .text-emerald-950 { color: #022c22 !important; }
    .border-emerald-200 { border-color: #a7f3d0 !important; }
    .border-emerald-300 { border-color: #6ee7b7 !important; }
    .border-slate-200 { border-color: #e2e8f0 !important; }
    .border-slate-300 { border-color: #cbd5e1 !important; }

    /* Wadah Ikon Berukuran Tetap & Baris Fleksibel (Anti-Overlap) */
    .pay-icon-box-36 {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .pay-icon-box-30 {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        min-height: 30px !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .pay-icon-box-40 {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
        border-radius: 12px !important;
        background-color: #dbeafe !important;
        color: #1d4ed8 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .pay-icon-box-42 {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        min-height: 42px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .pay-banner-row {
        display: flex !important;
        align-items: flex-start !important;
        gap: 16px !important;
    }
    .pay-header-row {
        display: flex !important;
        align-items: flex-start !important;
        gap: 14px !important;
    }
    .pay-header-row-center {
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
    }
    .pay-schedule-row {
        display: flex !important;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    .pay-btn-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }

    /* Aksesibilitas prefers-reduced-motion */
    @media (prefers-reduced-motion: reduce) {
        *, ::before, ::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>

@php
    // Mapping Status Konsisten & Aksesibel
    $rawStatus = strtolower($status_pembayaran ?? 'belum bayar');
    if (in_array($rawStatus, ['lunas', 'diterima', 'settlement', 'success', 'capture'])) {
        $statusKey = 'lunas';
        $statusLabel = 'Lunas';
        $statusBadgeClass = 'bg-emerald-100 text-emerald-950 border-emerald-300';
        $statusBannerBg = 'bg-emerald-50 border-emerald-300 text-emerald-950';
        $statusIcon = 'fa-check-circle text-emerald-700';
    } elseif (in_array($rawStatus, ['menunggu', 'pending', 'menunggu pembayaran'])) {
        $statusKey = 'menunggu';
        $statusLabel = 'Menunggu Pembayaran';
        $statusBadgeClass = 'bg-amber-100 text-amber-950 border-amber-300';
        $statusBannerBg = 'bg-amber-50 border-amber-300 text-amber-950';
        $statusIcon = 'fa-hourglass-half text-amber-700';
        $statusDesc = 'Tagihan pembayaran sedang aktif. Silakan selesaikan transaksi melalui metode pembayaran yang telah dipilih.';
    } elseif (in_array($rawStatus, ['kadaluarsa', 'expired', 'expire'])) {
        $statusKey = 'kadaluarsa';
        $statusLabel = 'Kedaluwarsa';
        $statusBadgeClass = 'bg-orange-100 text-orange-950 border-orange-300';
        $statusBannerBg = 'bg-orange-50 border-orange-300 text-orange-950';
        $statusIcon = 'fa-exclamation-triangle text-orange-700';
        $statusDesc = 'Sesi tagihan sebelumnya telah kedaluwarsa. Silakan klik tombol di bawah untuk membuat transaksi baru.';
    } elseif (in_array($rawStatus, ['gagal', 'ditolak', 'deny', 'cancel'])) {
        $statusKey = 'gagal';
        $statusLabel = 'Gagal';
        $statusBadgeClass = 'bg-red-100 text-red-950 border-red-300';
        $statusBannerBg = 'bg-red-50 border-red-300 text-red-950';
        $statusIcon = 'fa-times-circle text-red-700';
        $statusDesc = 'Pembayaran sebelumnya tidak berhasil. Silakan buat transaksi pembayaran baru.';
    } else {
        $statusKey = 'belum_bayar';
        $statusLabel = 'Belum Dibayar';
        $statusBadgeClass = 'bg-slate-200 text-slate-900 border-slate-300';
        $statusBannerBg = 'bg-slate-100 border-slate-300 text-slate-900';
        $statusIcon = 'fa-clock text-slate-700';
        $statusDesc = 'Silakan selesaikan pembayaran biaya formulir pendaftaran untuk memproses berkas pendaftaran Anda.';
    }

    // Penyamaran NIK di sisi View (hanya tampilkan 4 digit awal & 4 digit akhir)
    $nikRaw = (string) ($casis->nik_calon_siswa ?? '');
    $nikMasked = strlen($nikRaw) > 8
        ? substr($nikRaw, 0, 4) . '••••••••' . substr($nikRaw, -4)
        : ($nikRaw ?: '-');

    // Data waktu kedaluwarsa & countdown jika ada
    $waktuKadaluarsa = !empty($pembayaran?->waktu_kadaluarsa)
        ? \Carbon\Carbon::parse($pembayaran->waktu_kadaluarsa)
        : null;

    // Tanggal seleksi observasi dari tahun ajaran aktif casis
    $tglSeleksiRaw = $casis?->tahunAjaran?->tgl_seleksi;
    $tglSeleksiFormat = $tglSeleksiRaw
        ? \Carbon\Carbon::parse($tglSeleksiRaw)->locale('id')->translatedFormat('d F Y')
        : 'Sesuai Jadwal Resmi';
@endphp

<div class="pay-page space-y-6 pb-28 lg:pb-12">

    {{-- Header Halaman --}}
    <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl text-slate-900 font-bold tracking-tight break-words">
            Pembayaran Formulir Pendaftaran
        </h1>
        <p class="text-slate-600 text-sm sm:text-[15px] mt-1.5 leading-relaxed">
            @if($statusKey === 'lunas')
                Pembayaran Anda sudah diterima.
            @else
                Pembayaran instan dan otomatis terverifikasi secara aman melalui Midtrans.
            @endif
        </p>
    </div>

    @if(!$casis)
        {{-- State: Calon Siswa Belum Mengisi Formulir --}}
        <div class="bg-amber-50 border border-amber-300 p-6 rounded-2xl flex flex-col sm:flex-row gap-4 items-start text-amber-950">
            <div class="rounded-xl bg-amber-200/80 text-amber-900 flex items-center justify-center shrink-0 text-xl" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-amber-950">Formulir Pendaftaran Belum Lengkap</h2>
                <p class="text-sm text-amber-900 mt-1 leading-relaxed">
                    Anda belum melengkapi biodata calon siswa. Silakan lengkapi formulir pendaftaran terlebih dahulu sebelum melakukan pembayaran.
                </p>
                <div class="mt-4">
                    <a href="{{ route('daftarcasis') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition-colors shadow-xs focus-visible:outline-2 focus-visible:outline-blue-600 min-h-[44px]">
                        <i class="fas fa-user-plus text-xs" aria-hidden="true"></i> Lengkapi Formulir Sekarang
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- Grid Kontainer Utama --}}
        <div class="pay-layout-grid pay-layout-container">

            {{-- ==================== KOLOM KIRI (UTAMA) ==================== --}}
            <div class="pay-main-col space-y-6">

                {{-- A. Kartu Lunas Tunggal (Menggabungkan Status Lunas & Jumlah Dibayar) --}}
                @if($statusKey === 'lunas')
                    <div class="order-mobile-lunas-amount pay-card overflow-hidden">
                        {{-- Baris Status Lunas --}}
                        <div class="p-4 sm:p-5 bg-emerald-50 border-b border-emerald-200 pay-banner-row">
                            <div class="pay-icon-box-36 bg-emerald-600 text-white shadow-xs" aria-hidden="true">
                                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-[17px] sm:text-[18px] font-bold text-emerald-950 leading-snug">
                                    Pembayaran lunas
                                </h2>
                                <p class="text-[15px] text-emerald-800 mt-1 leading-relaxed">
                                    Terverifikasi otomatis. Anda siap mengikuti tahap seleksi.
                                </p>
                            </div>
                        </div>

                        {{-- Baris Jumlah Dibayar & Link Kembali ke Beranda --}}
                        <div class="p-4 sm:p-6 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <span class="text-xs sm:text-[13.5px] font-semibold uppercase tracking-wider text-slate-500 block mb-1">
                                    Jumlah Dibayar
                                </span>
                                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 info-tabular">
                                    Rp {{ number_format($biayaPendaftaran, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('berandacasis') }}"
                                   class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-4 py-2.5 rounded-xl transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 min-h-[44px]">
                                    <i class="fas fa-arrow-left text-xs shrink-0" aria-hidden="true"></i>
                                    <span>Kembali ke Beranda</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Status Banner Non-Lunas --}}
                    <div role="status" aria-live="polite" class="border rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 {{ $statusBannerBg }}">
                        <div class="rounded-xl flex items-center justify-center shrink-0 text-lg bg-white/70 shadow-xs mt-0.5" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px;">
                            <i class="fas {{ $statusIcon }}" aria-hidden="true"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <h2 class="text-sm sm:text-base font-bold">
                                    Status: {{ $statusLabel }}
                                </h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusBadgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm mt-1 leading-relaxed opacity-95">
                                {{ $statusDesc }}
                            </p>

                            {{-- Info Tambahan VA Number & Countdown jika Menunggu Pembayaran --}}
                            @if($statusKey === 'menunggu')
                                <div class="mt-3 pt-3 border-t border-amber-200/80 space-y-2 text-xs">
                                    @if(!empty($pembayaran?->va_number))
                                        <div class="flex items-center justify-between gap-2 flex-wrap bg-white/80 p-2.5 rounded-lg border border-amber-200">
                                            <div>
                                                <span class="text-slate-500 font-semibold uppercase text-[10px] block">Nomor Virtual Account</span>
                                                <span class="pay-mono font-bold text-slate-900 break-all">{{ $pembayaran->va_number }}</span>
                                            </div>
                                            <button type="button" onclick="copyToClipboard('{{ $pembayaran->va_number }}', this)"
                                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-md transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 min-h-[36px] flex items-center gap-1.5"
                                                    aria-label="Salin Nomor Virtual Account">
                                                <i class="fas fa-copy text-[11px]" aria-hidden="true"></i>
                                                <span class="btn-text">Salin VA</span>
                                            </button>
                                        </div>
                                    @endif

                                    @if($waktuKadaluarsa)
                                        <div id="countdown-wrapper" data-expire="{{ $waktuKadaluarsa->toIso8601String() }}" class="flex items-center gap-1.5 text-amber-950 font-medium">
                                            <i class="fas fa-stopwatch text-amber-800" aria-hidden="true"></i>
                                            <span>Bayar sebelum <strong>{{ $waktuKadaluarsa->format('d M Y, H:i') }}</strong> WIB:</span>
                                            <span id="countdown-timer" class="pay-mono font-bold text-amber-900 bg-amber-200/80 px-2 py-0.5 rounded">Memuat...</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Kartu Tagihan Biaya Non-Lunas --}}
                    <div class="pay-card p-5 sm:p-6">
                        <span class="text-xs sm:text-[13.5px] font-semibold uppercase tracking-wider text-slate-500 block mb-1">
                            Total Biaya Formulir Pendaftaran
                        </span>
                        <div class="text-3xl sm:text-4xl font-extrabold text-slate-950 mb-5 info-tabular">
                            Rp {{ number_format($biayaPendaftaran, 0, ',', '.') }}
                        </div>

                        {{-- Tombol Utama Pembayaran --}}
                        <div class="space-y-2.5">
                            <button type="button" id="pay-button"
                                    class="w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus-visible:outline-2 focus-visible:outline-blue-600 text-white font-bold text-base rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 min-h-[48px]">
                                <i class="fas fa-lock text-sm" aria-hidden="true"></i>
                                <span>
                                    @if($statusKey === 'menunggu')
                                        Lanjutkan Pembayaran
                                    @elseif($statusKey === 'kadaluarsa' || $statusKey === 'gagal')
                                        Buat Transaksi Baru
                                    @else
                                        Bayar Sekarang
                                    @endif
                                </span>
                            </button>
                            <p class="text-[12px] text-slate-500 text-center flex items-center justify-center gap-1.5">
                                <i class="fas fa-shield-alt text-blue-600" aria-hidden="true"></i>
                                Transaksi aman dilindungi enkripsi standar PCI-DSS Midtrans.
                            </p>
                        </div>

                        {{-- Tautan Sekunder: Kembali ke Beranda --}}
                        <div class="mt-4 text-center">
                            <a href="{{ route('berandacasis') }}"
                               class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 rounded py-1 px-2.5">
                                <i class="fas fa-arrow-left text-[10px]" aria-hidden="true"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                @endif

                {{-- B. Blok Data Calon Siswa & Rincian Transaksi --}}
                <div class="order-mobile-data-cards pay-card p-4 sm:p-7 space-y-6 sm:space-y-7">

                    {{-- BLOK 1: Data Calon Siswa --}}
                    <div>
                        <div class="pay-header-row-center mb-5">
                            <div class="pay-icon-box-40" aria-hidden="true">
                                <i class="fas fa-user-graduate text-[18px]"></i>
                            </div>
                            <h3 class="text-base sm:text-[18px] font-bold text-slate-900">
                                Data Calon Siswa
                            </h3>
                        </div>

                        <dl class="pay-data-grid text-sm">
                            <div>
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold">Nama Calon Siswa</dt>
                                <dd class="text-base font-semibold text-slate-900 mt-1 leading-snug">{{ $casis->nama_calon_siswa }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold">NIK (Disamarkan)</dt>
                                <dd class="pay-mono font-semibold text-slate-900 mt-1 leading-snug">{{ $nikMasked }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold">Tempat, Tanggal Lahir</dt>
                                <dd class="text-base font-semibold text-slate-900 mt-1 leading-snug">
                                    {{ $casis->tmpt_lahir }}, {{ $casis->tgl_lahir ? \Carbon\Carbon::parse($casis->tgl_lahir)->locale('id')->translatedFormat('j F Y') : '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold">Nama Orang Tua / Wali</dt>
                                <dd class="text-base font-semibold text-slate-900 mt-1 leading-snug">
                                    {{ Str::title($casis->nama_ayah . ($casis->nama_ibu ? ' / ' . $casis->nama_ibu : '')) }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- BLOK 2: Rincian Transaksi --}}
                    <div class="border-t border-slate-200 pt-6">
                        <div class="pay-header-row-center mb-5">
                            <div class="pay-icon-box-40" aria-hidden="true">
                                <i class="fas fa-receipt text-[18px]"></i>
                            </div>
                            <h3 class="text-base sm:text-[18px] font-bold text-slate-900">
                                Rincian Transaksi
                            </h3>
                        </div>

                        <dl class="pay-data-grid text-sm">
                            {{-- Nomor Registrasi --}}
                            <div>
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold mb-1">Nomor Registrasi</dt>
                                <dd class="pay-field-box">
                                    <span class="pay-mono font-semibold text-slate-900 break-all" id="text-no-reg">
                                        {{ $casis->no_registrasi ?? 'Belum Digenerate' }}
                                    </span>
                                    <button type="button" onclick="copyToClipboard('{{ $casis->no_registrasi }}', this)"
                                            class="px-3 py-1.5 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 shrink-0 min-h-[36px] flex items-center gap-1.5"
                                            aria-label="Salin Nomor Registrasi">
                                        <i class="fas fa-copy text-[11px]" aria-hidden="true"></i>
                                        <span class="btn-text" aria-live="polite">Salin</span>
                                    </button>
                                </dd>
                            </div>

                            {{-- Waktu Pembayaran (Memakai Kotak Seragam Tanpa Tombol Salin) --}}
                            <div>
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold mb-1">
                                    {{ $statusKey === 'lunas' ? 'Waktu Pembayaran' : 'Waktu Transaksi Terakhir' }}
                                </dt>
                                <dd class="pay-field-box">
                                    <span class="text-sm sm:text-base font-semibold text-slate-900 leading-snug">
                                        {{ format_school_time($pembayaran?->tgl_pembayaran, 'd M Y, H:i') }}
                                    </span>
                                </dd>
                            </div>

                            @if(!empty($pembayaran?->metode_pembayaran))
                                <div class="sm:col-span-2">
                                    <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold mb-1">Metode Pembayaran</dt>
                                    <dd class="pay-field-box">
                                        <span class="text-sm sm:text-base font-semibold text-slate-900 uppercase">
                                            {{ strtoupper($pembayaran->metode_pembayaran) }}
                                        </span>
                                    </dd>
                                </div>
                            @endif

                            {{-- Order ID (Rentang Lebar Penuh) --}}
                            <div class="sm:col-span-2">
                                <dt class="text-xs sm:text-[13.5px] text-slate-600 font-semibold mb-1">Order ID</dt>
                                <dd class="pay-field-box">
                                    <span class="pay-mono font-semibold text-slate-900 break-all" id="text-order-id">
                                        {{ $pembayaran->order_id ?? 'Belum Digenerate' }}
                                    </span>
                                    @if(!empty($pembayaran?->order_id))
                                        <button type="button" onclick="copyToClipboard('{{ $pembayaran->order_id }}', this)"
                                                class="px-3 py-1.5 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 shrink-0 min-h-[36px] flex items-center gap-1.5"
                                                aria-label="Salin Order ID">
                                            <i class="fas fa-copy text-[11px]" aria-hidden="true"></i>
                                            <span class="btn-text" aria-live="polite">Salin</span>
                                        </button>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                </div>

            </div>

            {{-- ==================== KOLOM KANAN (SIDEBAR) ==================== --}}
            <div class="pay-sidebar-col pay-sidebar-sticky space-y-4">

                @if($statusKey === 'lunas')
                    {{-- 1. Kartu Langkah Berikutnya (Pengganti Accordion untuk Status Lunas) --}}
                    <div class="order-mobile-next-step pay-card p-5 sm:p-6 border-blue-200 bg-linear-to-b from-blue-50/40 to-white">
                        <div class="pay-header-row mb-4">
                            <div class="pay-icon-box-42 bg-blue-100 text-blue-700" aria-hidden="true">
                                <i class="fas fa-calendar-check text-[18px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[13px] font-bold uppercase tracking-wider text-blue-700 block">Langkah Berikutnya</span>
                                <h3 class="text-[18px] font-bold text-slate-900 leading-tight mt-1">Seleksi Observasi</h3>
                            </div>
                        </div>

                        <p class="text-xs sm:text-[14px] text-slate-600 leading-relaxed mb-4">
                            Calon siswa dijadwalkan mengikuti observasi kesiapan belajar di sekolah:
                        </p>

                        {{-- Rincian Tanggal & Waktu --}}
                        {{-- TODO: Jika di masa mendatang jadwal observasi dialokasikan per sesi per siswa secara individual, ambil dari tabel jadwal_observasi_siswa --}}
                        <div class="bg-white rounded-xl p-4 border border-slate-200 mb-5 space-y-3 text-[15px]">
                            <div class="pay-schedule-row text-slate-800">
                                <div class="pay-icon-box-30 bg-blue-50 text-blue-700" aria-hidden="true">
                                    <i class="fas fa-calendar text-[15px]"></i>
                                </div>
                                <span class="font-semibold pt-1 leading-snug">{{ $tglSeleksiFormat }}</span>
                            </div>
                            <div class="pay-schedule-row text-slate-700">
                                <div class="pay-icon-box-30 bg-blue-50 text-blue-700" aria-hidden="true">
                                    <i class="fas fa-clock text-[15px]"></i>
                                </div>
                                <span class="pt-1 leading-snug">08:00 – 10:00 WITA</span>
                            </div>
                            <div class="pay-schedule-row text-slate-600">
                                <div class="pay-icon-box-30 bg-blue-50 text-blue-700" aria-hidden="true">
                                    <i class="fas fa-map-marker-alt text-[15px]"></i>
                                </div>
                                <span class="pt-1 leading-snug">Gedung SD Kristen Rantai Damai</span>
                            </div>
                        </div>

                        {{-- Tombol Aksi Lihat Jadwal Seleksi --}}
                        <div>
                            <a href="{{ route('pengumuman') }}"
                               class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 focus-visible:outline-2 focus-visible:outline-emerald-600 text-white font-bold text-sm rounded-xl shadow-xs transition-colors pay-btn-icon min-h-[44px]">
                                <i class="fas fa-calendar-alt text-[18px] shrink-0" aria-hidden="true"></i>
                                <span>Lihat Jadwal & Hasil Seleksi</span>
                                <i class="fas fa-arrow-right text-[12px] shrink-0" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                @else
                    {{-- Accordion Panduan Cara Pembayaran (Hanya Ditampilkan Saat Belum Lunas) --}}
                    <details class="pay-card overflow-hidden group">
                        <summary class="flex items-center justify-between p-4 sm:p-5 cursor-pointer font-bold text-slate-800 hover:bg-slate-50 transition-colors focus-visible:outline-2 focus-visible:outline-blue-600 list-none min-h-[44px]">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm shrink-0" aria-hidden="true">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                <span class="text-sm sm:text-[15px] font-bold text-slate-800">Lihat cara pembayaran</span>
                            </div>
                            <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform duration-200 text-xs" aria-hidden="true"></i>
                        </summary>

                        <div class="p-4 sm:p-5 pt-0 border-t border-slate-100 text-slate-700 text-sm">
                            <ol class="list-decimal list-outside ml-4 space-y-2.5 pt-3 leading-relaxed text-xs sm:text-[13.5px]">
                                <li>
                                    <strong class="text-slate-900">Buka formulir bayar:</strong> Tekan tombol "Bayar Sekarang" pada halaman ini.
                                </li>
                                <li>
                                    <strong class="text-slate-900">Pilih kanal transaksi:</strong> Tentukan metode QRIS, Virtual Account bank, atau gerai ritel.
                                </li>
                                <li>
                                    <strong class="text-slate-900">Selesaikan pembayaran:</strong> Salin nomor VA atau scan kode QRIS sebelum batas waktu habis.
                                </li>
                                <li>
                                    <strong class="text-slate-900">Verifikasi otomatis:</strong> Sistem memverifikasi pembayaran secara real-time tanpa upload bukti.
                                </li>
                            </ol>
                        </div>
                    </details>
                @endif

                {{-- 2. Kontak Bantuan Panitia --}}
                <div class="order-mobile-help pay-card p-5">
                    <div class="pay-header-row">
                        <div class="pay-icon-box-36 bg-emerald-100 text-emerald-700" aria-hidden="true">
                            <i class="fab fa-whatsapp text-[18px]"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-[16px] text-slate-900 leading-snug">
                                Butuh Bantuan Pembayaran?
                            </h3>
                            <p class="text-[14px] text-slate-600 mt-1 leading-relaxed">
                                Jika mengalami kendala transaksi atau memiliki pertanyaan pendaftaran, panitia siap membantu melalui WhatsApp.
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Panitia PPDB, saya butuh bantuan pembayaran pendaftaran casis ' . ($casis->nama_calon_siswa ?? '')) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="w-full pay-btn-icon bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-sm rounded-xl py-2.5 px-4 min-h-[44px] transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600 shadow-xs">
                            <i class="fab fa-whatsapp text-[18px] shrink-0" aria-hidden="true"></i>
                            <span>Hubungi WhatsApp Panitia</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    @endif
</div>

{{-- Sticky Bottom Bar Seluler (Khusus Belum Lunas) --}}
@if($casis && $statusKey !== 'lunas')
    <aside aria-label="Bilah pembayaran seluler" class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-2.5 z-30 lg:hidden shadow-[0_-4px_12px_rgba(0,0,0,0.08)]"
           style="padding-bottom: max(0.625rem, env(safe-area-inset-bottom, 0.625rem));">
        <div class="max-w-md mx-auto flex items-center justify-between gap-3">
            <div>
                <span class="block text-[11px] uppercase tracking-wider text-slate-500 font-semibold">Total Biaya</span>
                <span class="text-base sm:text-lg font-extrabold text-blue-600 info-tabular">
                    Rp {{ number_format($biayaPendaftaran, 0, ',', '.') }}
                </span>
            </div>
            <button type="button" id="pay-button-mobile"
                    class="flex-1 max-w-[200px] py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus-visible:outline-2 focus-visible:outline-blue-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5 min-h-[44px]">
                <i class="fas fa-lock text-xs" aria-hidden="true"></i>
                <span>
                    @if($statusKey === 'menunggu')
                        Lanjutkan Bayar
                    @elseif($statusKey === 'kadaluarsa' || $statusKey === 'gagal')
                        Buat Transaksi
                    @else
                        Bayar Sekarang
                    @endif
                </span>
            </button>
        </div>
    </aside>
@endif

{{-- Midtrans Snap & Clipboard JavaScript --}}
<script>
    // Fungsi Salin Clipboard Murni (View Only)
    window.copyToClipboard = function (text, btnElement) {
        if (!text || text === 'Belum Digenerate') return;
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            const tempInput = document.createElement('textarea');
            tempInput.value = text;
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.focus();
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
        }

        const textSpan = btnElement.querySelector('.btn-text');
        const originalText = textSpan ? textSpan.textContent : '';
        if (textSpan) textSpan.textContent = 'Tersalin!';
        btnElement.classList.add('bg-emerald-100', 'text-emerald-800', 'border-emerald-300');

        setTimeout(function () {
            if (textSpan) textSpan.textContent = originalText;
            btnElement.classList.remove('bg-emerald-100', 'text-emerald-800', 'border-emerald-300');
        }, 1500);
    };

    // Hitung Mundur Kedaluwarsa Murni (Tampilan Saja)
    document.addEventListener('DOMContentLoaded', function () {
        const expireWrapper = document.getElementById('countdown-wrapper');
        const countdownDisplay = document.getElementById('countdown-timer');

        if (expireWrapper && countdownDisplay) {
            const expireIso = expireWrapper.getAttribute('data-expire');
            if (expireIso) {
                const expireTime = new Date(expireIso).getTime();

                function refreshTimer() {
                    const now = new Date().getTime();
                    const distance = expireTime - now;

                    if (distance <= 0) {
                        countdownDisplay.textContent = 'Kedaluwarsa';
                        countdownDisplay.classList.add('bg-red-200', 'text-red-900');
                        return;
                    }

                    const hours = Math.floor(distance / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    countdownDisplay.textContent = hours + 'j ' + minutes + 'm ' + seconds + 'd';
                }

                refreshTimer();
                setInterval(refreshTimer, 1000);
            }
        }

        // Integrasi Midtrans Snap JS (Mempertahankan ID pay-button dan alur fetch asli)
        const payButton = document.getElementById('pay-button');
        const payMobileButton = document.getElementById('pay-button-mobile');

        if (payMobileButton && payButton) {
            payMobileButton.addEventListener('click', function () {
                payButton.click();
            });
        }

        if (!payButton) return;

        payButton.addEventListener('click', function () {
            payButton.disabled = true;
            const originalHtml = payButton.innerHTML;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> <span>Menyiapkan Pembayaran...</span>';

            if (payMobileButton) {
                payMobileButton.disabled = true;
                payMobileButton.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i>';
            }

            fetch("{{ route('pembayaran.create_snap_token') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                payButton.disabled = false;
                payButton.innerHTML = originalHtml;
                if (payMobileButton) {
                    payMobileButton.disabled = false;
                    payMobileButton.innerHTML = '<i class="fas fa-lock text-xs"></i> <span>Bayar Sekarang</span>';
                }

                if (!data.success || !data.snap_token) {
                    alert('Gagal mendapatkan sesi pembayaran: ' + (data.message || 'Terjadi kesalahan'));
                    return;
                }

                // Munculkan Popup Snap Midtrans
                window.snap.pay(data.snap_token, {
                    onSuccess: function (result) {
                        alert('Pembayaran berhasil! Terima kasih.');
                        window.location.reload();
                    },
                    onPending: function (result) {
                        alert('Menunggu penyelesaian pembayaran.');
                        window.location.reload();
                    },
                    onError: function (result) {
                        alert('Pembayaran gagal diproses.');
                        window.location.reload();
                    },
                    onClose: function () {
                        console.log('Customer closed Midtrans popup without finishing the payment');
                    }
                });
            })
            .catch(error => {
                payButton.disabled = false;
                payButton.innerHTML = originalHtml;
                if (payMobileButton) {
                    payMobileButton.disabled = false;
                    payMobileButton.innerHTML = '<i class="fas fa-lock text-xs"></i> <span>Bayar Sekarang</span>';
                }
                console.error('Error Midtrans:', error);
                alert('Terjadi kesalahan koneksi saat menghubungi server pembayaran.');
            });
        });
    });
</script>
@endsection
