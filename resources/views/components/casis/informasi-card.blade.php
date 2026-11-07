@php
    $tahapAktif = $tahapAktif ?? 2;
    $nominalBiaya = $casis?->pembayaran?->jml_pembayaran ?? $tahunajar?->biaya_pendaftaran ?? 150000;
    
    // Cek jenis kelamin calon siswa untuk sorotan total biaya
    $jk = strtolower($casis?->jenis_kelamin ?? '');
    $isPutra = in_array($jk, ['laki-laki', 'pria', 'l']);
    $isPutri = in_array($jk, ['perempuan', 'wanita', 'p']);
@endphp

<div class="info-card info-card--static" id="card-informasi">

    {{-- Header Ringkas & Netral dengan Aksen Oranye Tipis di Atas --}}
    <div class="info-card-header">
        <div class="flex items-center gap-3">
            <div class="info-header-icon-wrap" aria-hidden="true">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <h3 class="info-header-title">Informasi Pembayaran &amp; Daftar Ulang</h3>
                <p class="info-header-subtitle">Ringkasan pembayaran online dan ketentuan daftar ulang.</p>
            </div>
        </div>
    </div>

    {{-- Body Kartu --}}
    <div class="p-5 sm:p-6"
         x-data="{
             isMobile: window.innerWidth < 700,
             openBayar: window.innerWidth >= 700 || {{ $tahapAktif < 4 ? 'true' : 'false' }},
             openDaftarUlang: window.innerWidth >= 700 || {{ $tahapAktif >= 4 ? 'true' : 'false' }}
         }"
         @resize.window="isMobile = window.innerWidth < 700; if(!isMobile) { openBayar = true; openDaftarUlang = true; }">

        {{-- Dua Kolom Berdampingan --}}
        <div class="info-grid">

            {{-- =========================================================
                 KOLOM KIRI: PEMBAYARAN PENDAFTARAN (3 Poin Ringkas)
                 ========================================================= --}}
            <div class="info-col {{ $tahapAktif < 4 ? 'info-order-first' : 'info-order-second' }}">
                
                {{-- Header Seksi / Tombol Accordion (< 700px) --}}
                <div class="info-section-header-wrap">
                    <button type="button"
                            @click="if(isMobile) openBayar = !openBayar"
                            :aria-expanded="openBayar"
                            class="info-section-header-btn">
                        <span class="flex items-center gap-2.5">
                            <span class="info-sec-icon info-sec-emerald" aria-hidden="true">
                                <i class="fas fa-credit-card"></i>
                            </span>
                            <span class="info-sec-title">Pembayaran Pendaftaran</span>
                        </span>
                        <i class="fas fa-chevron-down info-chevron-mobile"
                           :class="{ 'rotate-180': openBayar }"
                           aria-hidden="true"></i>
                    </button>
                </div>

                {{-- Konten Pembayaran: 3 Poin Ringkas --}}
                <div x-show="openBayar" class="pt-3">
                    
                    <ul class="space-y-4" aria-label="Ketentuan pembayaran pendaftaran">
                        <li class="flex items-start gap-3">
                            <span class="info-point-wrap" aria-hidden="true">
                                <i class="fas fa-qrcode"></i>
                            </span>
                            <span class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                <strong>Bayar online</strong> lewat QRIS, Virtual Account, atau gerai ritel.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="info-point-wrap" aria-hidden="true">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <span class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                Status <strong>lunas otomatis</strong>, tanpa unggah bukti transfer.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="info-point-wrap" aria-hidden="true">
                                <i class="fas fa-history"></i>
                            </span>
                            <span class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                Tagihan kedaluwarsa? Buat tagihan baru di menu <strong>Pembayaran</strong>.
                            </span>
                        </li>
                    </ul>

                </div>
            </div>

            {{-- =========================================================
                 KOLOM KANAN: INFORMASI DAFTAR ULANG (4 Poin Ringkas)
                 ========================================================= --}}
            <div class="info-col {{ $tahapAktif >= 4 ? 'info-order-first' : 'info-order-second' }}">
                
                {{-- Header Seksi / Tombol Accordion (< 700px) --}}
                <div class="info-section-header-wrap">
                    <button type="button"
                            @click="if(isMobile) openDaftarUlang = !openDaftarUlang"
                            :aria-expanded="openDaftarUlang"
                            class="info-section-header-btn">
                        <span class="flex items-center gap-2.5">
                            <span class="info-sec-icon info-sec-blue" aria-hidden="true">
                                <i class="fas fa-user-check"></i>
                            </span>
                            <span class="info-sec-title">Informasi Daftar Ulang</span>
                        </span>
                        <i class="fas fa-chevron-down info-chevron-mobile"
                           :class="{ 'rotate-180': openDaftarUlang }"
                           aria-hidden="true"></i>
                    </button>
                </div>

                {{-- Konten Daftar Ulang: 3 Poin Sesuai Ketentuan --}}
                <div x-show="openDaftarUlang" class="pt-3">
                    
                    <ul class="space-y-4" aria-label="Ketentuan daftar ulang">
                        {{-- Poin 1: Bayar cicilan pertama dulu --}}
                        <li class="flex items-start gap-3">
                            <span class="info-point-wrap" aria-hidden="true">
                                <i class="fas fa-credit-card"></i>
                            </span>
                            <span class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                @if(!empty($minDp))
                                    Bayar cicilan pertama dulu (minimal <strong>Rp {{ number_format($minDp, 0, ',', '.') }}</strong>).
                                @else
                                    {{-- TODO: Operkan variabel $minDp dari service daftar ulang ke BerandaController agar nominal minimum tampil otomatis --}}
                                    Bayar cicilan pertama dulu.
                                @endif
                            </span>
                        </li>

                        {{-- Poin 2: Serahkan berkas asli/fotokopi ke sekolah --}}
                        <li class="flex items-start gap-3">
                            <span class="info-point-wrap" aria-hidden="true">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            <span class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                Serahkan <strong>berkas asli/fotokopi</strong> ke sekolah.
                            </span>
                        </li>

                        {{-- Poin 3: Sisa uang pembangunan dilunasi paling lambat jatuh tempo; lapor admin jika belum mampu --}}
                        <li class="flex items-start gap-3">
                            <span class="info-point-wrap" aria-hidden="true">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <span class="text-sm sm:text-[15px] text-slate-700 leading-relaxed pt-1">
                                @if(!empty($tglJatuhTempo))
                                    Sisa uang pembangunan dilunasi paling lambat <strong>{{ $tglJatuhTempo }}</strong>; jika belum mampu, lapor ke admin.
                                @else
                                    {{-- TODO: Operkan variabel $tglJatuhTempo dari service daftar ulang ke BerandaController agar tanggal jatuh tempo tampil otomatis --}}
                                    Sisa uang pembangunan dilunasi paling lambat pada tanggal jatuh tempo; jika belum mampu, lapor ke admin.
                                @endif
                            </span>
                        </li>
                    </ul>

                </div>
            </div>

        </div>

        {{-- =========================================================
             BARIS DUA KOTAK INFO (Grid 2 Kolom di Desktop, 1 Kolom di Mobile)
             ========================================================= --}}
        <div class="info-notes-grid">
            
            {{-- Kotak Kiri: Terkait Pembayaran --}}
            <div class="info-note-box">
                <span class="info-note-icon-wrap" aria-hidden="true">
                    <i class="fas fa-question-circle"></i>
                </span>
                <div class="info-note-content">
                    <h6 class="info-note-title">Sudah bayar tapi status belum berubah?</h6>
                    <p class="info-note-text">
                        Tunggu 5–10 menit lalu muat ulang halaman. Jika masih sama, <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Panitia PPDB, saya butuh informasi / bantuan seputar status pembayaran calon siswa: ' . ($casis->nama_calon_siswa ?? '')) }}" target="_blank" rel="noopener noreferrer">hubungi panitia</a>.
                    </p>
                </div>
            </div>

            {{-- Kotak Kanan: Terkait Daftar Ulang --}}
            <div class="info-note-box">
                <span class="info-note-icon-wrap" aria-hidden="true">
                    <i class="fas fa-info-circle"></i>
                </span>
                <div class="info-note-content">
                    <h6 class="info-note-title">Kapan dan di mana daftar ulang?</h6>
                    <p class="info-note-text">
                        Waktu dan tempat pelaksanaan tertera pada pengumuman resmi kelulusan calon siswa.
                    </p>
                </div>
            </div>

        </div>

        {{-- =========================================================
             BAGIAN SELEBAR PENUH: RINCIAN BIAYA DAFTAR ULANG
             ========================================================= --}}
        {{-- Rincian komponen biaya daftar ulang diambil dinamis dari pengaturan tahun ajaran di database --}}
        @php
            $biayaPembangunan = (float)($tahunajar?->biaya_pembangunan ?? 0);
            $biayaBuku = (float)($tahunajar?->biaya_buku ?? 0);
            $biayaSeragam = (float)($tahunajar?->biaya_seragam ?? 0);
            $biayaTotal = (float)($tahunajar?->biaya_daftar_ulang ?? 0);
            if ($biayaPembangunan <= 0 && $biayaBuku <= 0 && $biayaSeragam <= 0 && $biayaTotal > 0) {
                $biayaPembangunan = round($biayaTotal * 0.6);
                $biayaBuku = round($biayaTotal * 0.2);
                $biayaSeragam = $biayaTotal - $biayaPembangunan - $biayaBuku;
            }
        @endphp
        <div class="info-full-biaya-wrap">
            
            {{-- Header Rincian Biaya: Ikon Wadah 36px Selaras dengan Judul Seksi Lain --}}
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="info-sec-icon info-sec-indigo" aria-hidden="true">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </span>
                    <h4 class="info-sec-title">
                        Rincian Biaya Daftar Ulang
                    </h4>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full border border-slate-200">
                    TA {{ $tahunajar->tahun_ajar ?? '2026/2027' }}
                </span>
            </div>

            {{-- Tabel Komponen Biaya --}}
            <div class="info-biaya-table">
                <div class="info-biaya-row">
                    <span class="text-slate-600 text-sm sm:text-[15px]">Uang Pembangunan & Sarana</span>
                    <span class="font-bold text-slate-800 text-sm sm:text-[15px] info-tabular">Rp {{ number_format($biayaPembangunan, 0, ',', '.') }}</span>
                </div>
                <div class="info-biaya-row">
                    <span class="text-slate-600 text-sm sm:text-[15px]">Buku Paket Pelajaran & Modul</span>
                    <span class="font-bold text-slate-800 text-sm sm:text-[15px] info-tabular">Rp {{ number_format($biayaBuku, 0, ',', '.') }}</span>
                </div>
                <div class="info-biaya-row">
                    <span class="text-slate-600 text-sm sm:text-[15px]">Seragam Sekolah & Atribut</span>
                    <span class="font-bold text-slate-800 text-sm sm:text-[15px] info-tabular">Rp {{ number_format($biayaSeragam, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Kartu Total Biaya Daftar Ulang --}}
            <div class="mt-3.5">
                <div class="info-total-card info-total-active">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-blue-800">
                            Total Biaya Masuk / Daftar Ulang
                        </span>
                        <span class="info-active-badge">
                            <i class="fas fa-check-circle text-[10px]"></i> Resmi TA {{ $tahunajar->tahun_ajar ?? '2026/2027' }}
                        </span>
                    </div>
                    <p class="text-xl sm:text-2xl font-extrabold text-blue-700 info-tabular leading-tight">
                        Rp {{ number_format($biayaTotal, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                * Total biaya di atas sudah mencakup biaya pembangunan & sarana, buku paket pelajaran & modul, serta seragam sekolah & atribut lengkap.
            </p>

        </div>

        {{-- =========================================================
             BAR BANTUAN SELEBAR PENUH (Kontak Panitia Resmi)
             ========================================================= --}}
        <div class="info-help-bar-wrap">
            <div class="info-help-bar">
                <div class="info-help-bar-content">
                    <div class="info-help-bar-icon" aria-hidden="true">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h5 class="info-help-bar-title">Butuh bantuan?</h5>
                        <p class="info-help-bar-desc">
                            Panitia PPDB siap membantu seputar pendaftaran, seleksi, dan daftar ulang.
                        </p>
                    </div>
                </div>
                <div class="info-help-bar-action">
                    {{-- TODO: Ganti nomor WhatsApp panitia dengan nomor resmi jika sudah tersedia di pengaturan sekolah --}}
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Panitia PPDB, saya butuh informasi / bantuan seputar PPDB calon siswa: ' . ($casis->nama_calon_siswa ?? '')) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="info-wa-btn">
                        <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i>
                        <span>Hubungi WhatsApp Panitia</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
