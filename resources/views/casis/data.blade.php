@extends('layout.tampilancasis')

@section('page-title', 'Pendaftaran')
@section('title', 'Data Pendaftaran')

@section('content')
@php
    $isArchived = !empty($isArchived);
    $casis = $casis ?? $user->calonSiswa ?? $user->casis ?? $archivedCasis;
    $tahunAjaran = $casis?->tahunAjaran ?? $aktivTahunAjaran;

    // Normalisasi Status Pendaftaran (Hanya di Tampilan)
    $rawStatus = $casis?->status_pendaftaran ?? 'Menunggu';
    $statusDisplayMap = [
        'Diterima' => [
            'label' => 'Data Terverifikasi',
            'badgeClass' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'icon' => 'fa-check-circle text-emerald-700',
            'desc' => 'Langkah berikutnya: seleksi observasi. Hasil diumumkan di menu Pengumuman Seleksi.',
            'locked' => true,
        ],
        'Ditolak' => [
            'label' => 'Perlu Perbaikan',
            'badgeClass' => 'bg-rose-50 text-rose-800 border-rose-200',
            'icon' => 'fa-exclamation-circle text-rose-700',
            'desc' => 'Terdapat data atau berkas yang belum sesuai. Silakan hubungi panitia PPDB untuk perbaikan.',
            'locked' => false,
        ],
        'Menunggu' => [
            'label' => 'Menunggu Verifikasi',
            'badgeClass' => 'bg-amber-50 text-amber-800 border-amber-200',
            'icon' => 'fa-clock text-amber-700',
            'desc' => 'Data pendaftaran Anda sedang dalam antrean verifikasi oleh panitia PPDB.',
            'locked' => false,
        ],
    ];

    if ($isArchived) {
        $statusInfo = [
            'label' => 'Data Diarsipkan',
            'badgeClass' => 'bg-amber-50 text-amber-800 border-amber-300',
            'icon' => 'fa-archive text-amber-700',
            'desc' => 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.',
            'locked' => true,
        ];
        $isEditLocked = true;
    } else {
        $statusInfo = $statusDisplayMap[$rawStatus] ?? $statusDisplayMap['Menunggu'];

        // Cek status pembayaran formulir
        $isLunasBayar = ($casis?->pembayaran?->status_pembayaran === 'Lunas');

        // Penguncian edit (tampilan): terkunci jika data terverifikasi (Diterima) ATAU pembayaran sudah lunas
        $isEditLocked = $statusInfo['locked'] || $isLunasBayar;
    }

    // Format Tanggal Pendaftaran
    $tglDaftarFormat = $casis?->tgl_pendaftaran
        ? \Carbon\Carbon::parse($casis->tgl_pendaftaran)->locale('id')->translatedFormat('d M Y')
        : '-';

    // Format Tanggal Seleksi Observasi jika tersedia di view
    $tglSeleksiFormat = $tahunAjaran?->tgl_seleksi
        ? \Carbon\Carbon::parse($tahunAjaran->tgl_seleksi)->locale('id')->translatedFormat('l, d F Y')
        : null;

    $jamSeleksi = '08:00 – 10:00 ' . (function_exists('school_timezone_label') ? school_timezone_label() : 'WITA');
    $waPanitiaUrl = 'https://wa.me/6281234567890?text=' . urlencode('Halo Panitia PPDB SD Kristen Rantai Damai, saya ingin menanyakan perihal data pendaftaran calon siswa: ' . ($casis?->nama_calon_siswa ?? ''));

    // Inisial avatar nama calon siswa (bukan nama akun)
    $namaSiswa = $casis?->nama_calon_siswa ?? $user->nama ?? $user->name;
    $initialSiswa = strtoupper(substr(trim($namaSiswa), 0, 1) ?: 'C');
@endphp

{{-- Scoped CSS untuk Data Pendaftaran: Inter typography, baris data padat 48-52px, kartu statis tanpa animasi hover --}}
<style>
    .datapendaftaran-page,
    .datapendaftaran-page h1, .datapendaftaran-page h2, .datapendaftaran-page h3,
    .datapendaftaran-page h4, .datapendaftaran-page p, .datapendaftaran-page span,
    .datapendaftaran-page dt, .datapendaftaran-page dd, .datapendaftaran-page a,
    .datapendaftaran-page button {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }

    .datapendaftaran-page {
        --text-page-title: 1.875rem; /* 30px (28-32px) */
        --text-card-title: 1.125rem; /* 18px */
        --text-body: 0.9375rem;     /* 15px */
        --text-value: 1rem;         /* 16px */
        --text-label: 0.875rem;     /* 14px */
        --text-meta: 0.8125rem;     /* 13px */
    }

    .data-card-static {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
        transform: none !important;
        transition: none !important;
        animation: none !important;
    }
    .data-card-static:hover {
        transform: none !important;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05) !important;
    }

    /* Grid Label - Value yang rapi dan padat */
    .data-row-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        padding-top: 0.625rem;
        padding-bottom: 0.625rem;
        min-height: 48px;
        border-bottom: 1px solid #f1f5f9;
        box-sizing: border-box;
    }
    .data-row-item:last-child {
        border-bottom: none;
    }
    @media (min-width: 600px) {
        .data-row-item {
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 0;
        }
        .data-row-label {
            width: 175px !important;
            min-width: 175px !important;
            max-width: 175px !important;
            flex-shrink: 0;
        }
        .data-row-val {
            flex: 1 1 0%;
            min-width: 0;
        }
    }

    .data-row-label {
        font-size: var(--text-label); /* 14px */
        font-weight: 500;
        color: #475569; /* slate-600, contrast > 4.5:1 */
    }
    .data-row-val {
        font-size: var(--text-value); /* 16px */
        font-weight: 600;
        color: #0f172a; /* slate-900 */
        line-height: 1.4;
        word-break: break-word;
    }

    /* Bar Informasi Terkunci */
    .info-locked-bar {
        padding: 16px !important;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 18px;
        background-color: #f0f7ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        box-sizing: border-box;
    }
    .info-locked-bar .icon-lock-wrap {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        border-radius: 8px;
        background-color: #dbeafe;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    /* Ratio container thumbnail */
    .thumb-pasfoto {
        aspect-ratio: 3 / 4;
        width: 100%;
        max-width: 160px;
        margin-left: auto;
        margin-right: auto;
    }
    .thumb-doc {
        aspect-ratio: 4 / 3;
        width: 100%;
    }

    /* Wadah ikon seragam */
    .icon-box-fixed {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>

<div class="datapendaftaran-page space-y-6 pb-20">

    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="text-2xl sm:text-3xl text-slate-900 font-bold tracking-tight">Data Pendaftaran</h1>
        <p class="text-slate-600 text-sm sm:text-[15px] mt-1">Lengkapi profil calon siswa dan pantau verifikasi dokumen persyaratan.</p>
    </div>

    @if(!empty($isArchived))
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-6 py-5 rounded-2xl flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-700">
                <i class="fas fa-archive text-lg" aria-hidden="true"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-base mb-1 text-amber-950">Data Pendaftaran Sedang Diarsipkan</h4>
                <p class="text-sm leading-relaxed text-amber-900">
                    Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.
                </p>
                <div class="mt-3">
                    <a href="{{ $waPanitiaUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <span>Hubungi Panitia PPDB</span>
                    </a>
                </div>
            </div>
        </div>
    @elseif(!$kuotaTersedia)
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-6 py-5 rounded-2xl flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-700">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-base mb-1 text-amber-950">Pendaftaran Tidak Tersedia!</h4>
                <p class="text-sm opacity-90 leading-relaxed">{{ $pesanKuota }}</p>
                <hr class="my-3 border-amber-200">
                <p class="text-xs text-amber-700 font-medium">Silakan hubungi panitia PPDB untuk informasi lebih lanjut.</p>
            </div>
        </div>
    @endif

    {{-- 1. Kartu Profil Calon Siswa (Ringkas & Terpadu) --}}
    <div class="order-mobile-profile data-card-static p-5 sm:p-6 space-y-4">
        {{-- Baris Utama: Avatar Inisial, Nama Siswa + Info Pendaftaran, dan Badge Status di Kanan --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5 sm:gap-4 min-w-0 flex-1">
                {{-- Avatar inisial (lingkaran 48-52px, latar hijau muda, huruf pertama nama siswa hijau tua bold 18-20px) --}}
                <div class="w-12 h-12 sm:w-[52px] sm:h-[52px] rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center justify-center text-lg sm:text-xl font-bold shrink-0 select-none shadow-xs mt-0.5 sm:mt-0" aria-hidden="true">
                    {{ $initialSiswa }}
                </div>

                {{-- Blok Teks: Nama Siswa di atas, Baris Info Dua Item Berlabel di bawah --}}
                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl sm:text-[26px] font-bold text-slate-900 leading-tight break-words">
                        {{ Str::title($namaSiswa) }}
                    </h2>

                    {{-- Baris Info Dua Item Berlabel --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6 mt-3">
                        {{-- Item 1: Nomor Registrasi --}}
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs sm:text-[13px] font-medium text-slate-500">Nomor Registrasi</span>
                            <div class="flex items-center gap-2.5">
                                <span class="text-base font-semibold text-slate-900 tracking-tight" id="text-no-reg">{{ $casis?->no_registrasi ?? '-' }}</span>
                                @if(!empty($casis?->no_registrasi))
                                    <button type="button"
                                            id="btn-copy-reg"
                                            onclick="copyNoReg()"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-300 rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-emerald-600 min-h-[32px] sm:min-h-[34px]"
                                            title="Salin Nomor Registrasi"
                                            aria-label="Salin nomor registrasi">
                                        <i class="far fa-copy text-xs" id="icon-copy-reg" aria-hidden="true"></i>
                                        <span id="text-copy-btn">Salin</span>
                                    </button>
                                    <span id="copy-feedback-reg" class="sr-only" aria-live="polite"></span>
                                @endif
                            </div>
                        </div>

                        {{-- Garis Pemisah Vertikal (Desktop) --}}
                        <div class="hidden sm:block w-px self-stretch bg-slate-200" aria-hidden="true"></div>

                        {{-- Item 2: Tanggal Daftar --}}
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs sm:text-[13px] font-medium text-slate-500">Tanggal Daftar</span>
                            <span class="text-base font-semibold text-slate-900">{{ $tglDaftarFormat }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Badge Status di Sisi Kanan (Di < 600px turun ke bawah nama, rata kiri) --}}
            <div class="shrink-0 self-start sm:self-center">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-semibold border {{ $statusInfo['badgeClass'] }}">
                    <i class="fas {{ $statusInfo['icon'] }} text-[14px]"></i>
                    <span>{{ $statusInfo['label'] }}</span>
                </span>
            </div>
        </div>

        {{-- Bar Informasi Ringkas untuk Status Terkunci / Petunjuk Tahap --}}
        @if(!empty($isArchived))
            <div class="info-locked-bar bg-amber-50/80 border-amber-300 text-amber-950">
                <div class="icon-lock-wrap bg-amber-100 text-amber-700" aria-hidden="true">
                    <i class="fas fa-archive"></i>
                </div>
                <p class="text-sm sm:text-[15px] leading-relaxed m-0 text-amber-950">
                    Data pendaftaran Anda sedang diarsipkan. Silakan <a href="{{ $waPanitiaUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 hover:text-emerald-900 underline focus-visible:outline-2 focus-visible:outline-emerald-600">hubungi panitia</a>.
                </p>
            </div>
        @elseif(!empty($isEditLocked))
            <div class="info-locked-bar">
                <div class="icon-lock-wrap" aria-hidden="true">
                    <i class="fas fa-lock"></i>
                </div>
                <p class="text-sm sm:text-[15px] text-blue-900 leading-relaxed m-0">
                    Data terkunci setelah verifikasi. Untuk perubahan, <a href="{{ $waPanitiaUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-700 hover:text-blue-900 underline focus-visible:outline-2 focus-visible:outline-blue-600">hubungi panitia</a>.
                </p>
            </div>
        @else
            <div class="info-locked-bar {{ $rawStatus === 'Ditolak' ? 'bg-rose-50/80 border-rose-200 text-rose-900' : 'bg-amber-50/80 border-amber-200 text-amber-900' }}">
                <div class="icon-lock-wrap {{ $rawStatus === 'Ditolak' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}" aria-hidden="true">
                    <i class="fas {{ $statusInfo['icon'] }}"></i>
                </div>
                <p class="text-sm sm:text-[15px] leading-relaxed m-0">
                    {{ $statusInfo['desc'] }}
                    @if($rawStatus === 'Ditolak' && !empty($casis?->catatan_pendaftaran))
                        <br><span class="font-semibold text-rose-800">Catatan Perbaikan: {{ $casis->catatan_pendaftaran }}</span>
                    @endif
                    @if($rawStatus === 'Ditolak')
                        Untuk informasi lebih lanjut, <a href="{{ $waPanitiaUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">hubungi panitia</a>.
                    @endif
                </p>
            </div>
        @endif
    </div>

    @if(!empty($casis?->no_registrasi))
        <script>
            function copyNoReg() {
                const noReg = '{{ $casis->no_registrasi }}';
                if (!noReg) return;
                navigator.clipboard.writeText(noReg).then(() => {
                    const feedback = document.getElementById('copy-feedback-reg');
                    if (feedback) feedback.textContent = 'Nomor registrasi berhasil disalin';
                    const textBtn = document.getElementById('text-copy-btn');
                    const icon = document.getElementById('icon-copy-reg');
                    if (textBtn) textBtn.textContent = 'Tersalin';
                    if (icon) icon.className = 'fas fa-check text-emerald-600 text-xs';
                    setTimeout(() => {
                        if (textBtn) textBtn.textContent = 'Salin';
                        if (icon) icon.className = 'far fa-copy text-xs';
                    }, 1500);
                }).catch(() => {
                    alert('Nomor registrasi: ' + noReg);
                });
            }
        </script>
    @endif

    @if($kuotaTersedia)
        {{-- 2 & 3. Grid Data Calon Siswa & Data Orang Tua (Seimbang, Stretch) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            {{-- Biodata Calon Siswa --}}
            <div class="flex flex-col">
                @include('components.casis.data-casis', ['isEditLocked' => $isEditLocked, 'waPanitiaUrl' => $waPanitiaUrl])
            </div>

            {{-- Data Orang Tua / Wali --}}
            <div class="flex flex-col">
                @include('components.casis.data-ortu', ['isEditLocked' => $isEditLocked, 'waPanitiaUrl' => $waPanitiaUrl])
            </div>
        </div>

        {{-- 4. Berkas Persyaratan (4 Dokumen Termasuk KPS Opsional) --}}
        <div>
            @include('components.casis.berkas', ['isEditLocked' => $isEditLocked, 'waPanitiaUrl' => $waPanitiaUrl])
        </div>

        {{-- 5. Kartu Langkah Berikutnya --}}
        <div class="data-card-static p-6 border-blue-200 bg-linear-to-r from-blue-50/60 to-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="icon-box-fixed bg-blue-100 text-blue-700" aria-hidden="true">
                        <i class="fas fa-calendar-check text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-700 block mb-0.5">Langkah Berikutnya</span>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Seleksi Observasi Kesiapan Belajar</h3>
                        
                        @if($tglSeleksiFormat)
                            <div class="flex items-center gap-3 mt-1.5 flex-wrap text-xs sm:text-sm text-slate-600">
                                <span class="font-semibold text-slate-800"><i class="far fa-calendar-alt text-blue-600 mr-1.5"></i>{{ $tglSeleksiFormat }}</span>
                                <span>&bull;</span>
                                <span><i class="far fa-clock text-blue-600 mr-1.5"></i>{{ $jamSeleksi }}</span>
                                <span>&bull;</span>
                                <span><i class="fas fa-map-marker-alt text-rose-500 mr-1.5"></i>Gedung SD Kristen Rantai Damai</span>
                            </div>
                        @else
                            {{-- TODO: Tampilkan jadwal observasi individual jika kolom tabel jadwal sudah dibuat --}}
                            <p class="text-xs text-slate-500 mt-1">Jadwal pelaksanaan observasi dapat dilihat melalui menu Pengumuman Seleksi.</p>
                        @endif
                    </div>
                </div>

                <div class="shrink-0 flex items-center gap-3">
                    {{-- TODO: Tambahkan tombol Cetak Kartu Peserta jika fitur cetak PDF kartu peserta telah diimplementasikan di controller --}}
                    <a href="{{ route('pengumuman') }}"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors min-h-[44px]">
                        <i class="fas fa-bullhorn text-xs"></i>
                        <span>Lihat Pengumuman Seleksi</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
