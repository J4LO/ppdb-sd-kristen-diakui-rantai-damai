@php
    $today = \Carbon\Carbon::today();
    $mulaiDaftar = ($aktivTahunAjaran && $aktivTahunAjaran->mulai_pendaftaran) 
        ? \Carbon\Carbon::parse($aktivTahunAjaran->mulai_pendaftaran)->startOfDay() 
        : null;
    $batasDaftar = ($aktivTahunAjaran && $aktivTahunAjaran->batas_pendaftaran) 
        ? \Carbon\Carbon::parse($aktivTahunAjaran->batas_pendaftaran)->endOfDay() 
        : null;
    $tglSeleksi = ($aktivTahunAjaran && $aktivTahunAjaran->tgl_seleksi) 
        ? \Carbon\Carbon::parse($aktivTahunAjaran->tgl_seleksi)->startOfDay() 
        : null;

    // Status Pendaftaran
    if ($mulaiDaftar && $batasDaftar) {
        if ($today->lt($mulaiDaftar)) {
            $statusDaftar = 'upcoming';
            $labelDaftar = 'Akan Datang';
        } elseif ($today->gt($batasDaftar)) {
            $statusDaftar = 'ended';
            $labelDaftar = 'Selesai';
        } else {
            $statusDaftar = 'ongoing';
            $labelDaftar = 'Sedang Berlangsung';
        }
    } else {
        $statusDaftar = null;
    }

    // Status Seleksi Observasi
    if ($tglSeleksi) {
        if ($today->lt($tglSeleksi)) {
            $statusSeleksi = 'upcoming';
            $labelSeleksi = 'Akan Datang';
        } elseif ($today->gt($tglSeleksi->copy()->endOfDay())) {
            $statusSeleksi = 'ended';
            $labelSeleksi = 'Selesai';
        } else {
            $statusSeleksi = 'ongoing';
            $labelSeleksi = 'Sedang Berlangsung';
        }
    } else {
        $statusSeleksi = null;
    }

    // Status Pengumuman (Tersedia setelah hari pelaksanaan observasi)
    if ($tglSeleksi) {
        if ($today->lte($tglSeleksi)) {
            $statusPengumuman = 'upcoming';
            $labelPengumuman = 'Akan Datang';
        } else {
            $statusPengumuman = 'ongoing';
            $labelPengumuman = 'Sedang Berlangsung';
        }
    } else {
        $statusPengumuman = null;
    }
@endphp

<section id="pendaftaran" class="py-20 bg-slate-50 border-y border-slate-100 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-green-600 font-bold tracking-wide uppercase text-sm mb-2">Informasi PPDB {{ $aktivTahunAjaran->tahun_ajar ?? ($aktivTahunAjaran->tahun_ajaran ?? date('Y').'/'.(date('Y')+1)) }}</h2>
            <h3 class="font-heading text-3xl md:text-4xl font-bold text-slate-800 mb-4">Syarat, Biaya &amp; Alur Pendaftaran</h3>
            <p class="text-slate-600 text-base md:text-lg">Pendaftaran peserta didik baru dilakukan secara online. Simak rincian biaya pendidikan, dokumen persyaratan, dan alur pendaftarannya di bawah ini.</p>
        </div>

        {{-- LAPISAN 1: KARTU BIAYA --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-stretch">
            {{-- Card 1: Pendaftaran Online --}}
            <div class="reveal bg-white rounded-2xl border border-slate-200/80 p-6 lg:p-7 flex flex-col justify-between shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 ease-out h-full" data-delay="0">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 text-green-700 rounded-2xl flex items-center justify-center text-xl shadow-xs">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200/80">
                            Tahap 1 · Pendaftaran
                        </span>
                    </div>

                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Biaya Formulir</span>
                    <h4 class="font-heading text-xl font-bold text-slate-900 mb-2">Pendaftaran Online</h4>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Biaya formulir dibayarkan saat pengisian formulir online dan verifikasi berkas administrasi calon siswa.
                    </p>

                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 space-y-2.5 mb-4">
                        <div class="flex items-center gap-2.5 text-sm text-slate-700">
                            <i class="fas fa-check text-green-600 text-xs shrink-0"></i>
                            <span>Formulir pendaftaran online</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-slate-700">
                            <i class="fas fa-check text-green-600 text-xs shrink-0"></i>
                            <span>Verifikasi kelengkapan berkas</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-slate-700">
                            <i class="fas fa-check text-green-600 text-xs shrink-0"></i>
                            <span>Undangan seleksi observasi</span>
                        </div>
                        <div class="pt-3 border-t border-slate-200/80 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-700">Total Biaya Pendaftaran</span>
                            <span class="font-heading text-xl font-extrabold text-green-700">
                                Rp {{ number_format($aktivTahunAjaran->biaya_pendaftaran ?? 50000, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500 min-h-[44px]">
                    <i class="fas fa-check-circle text-green-600 shrink-0"></i>
                    <span>Dibayar 1x secara online (transfer bank, QRIS, atau gerai ritel). Tanpa unggah bukti pembayaran.</span>
                </div>
            </div>

            {{-- Card 2: Biaya Daftar Ulang --}}
            <div class="reveal bg-white rounded-2xl border border-emerald-200/80 p-6 lg:p-7 flex flex-col justify-between shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 ease-out h-full" data-delay="120">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center text-xl shadow-xs">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200/80">
                            Tahap 2 · Setelah Lulus Seleksi
                        </span>
                    </div>

                    <div class="flex items-baseline justify-between gap-2 mb-1 flex-wrap">
                        <div>
                            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider block mb-1">Daftar Ulang Siswa Baru</span>
                            <h4 class="font-heading text-xl font-bold text-slate-900">Biaya Daftar Ulang</h4>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                            Dapat Dicicil
                        </span>
                    </div>

                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Hanya dibayarkan apabila calon siswa telah dinyatakan <strong>Lulus Seleksi Observasi</strong>.
                    </p>

                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3.5 space-y-2 mb-4">
                        <div class="flex justify-between items-center text-xs text-slate-600">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-building text-slate-400"></i> Biaya Pembangunan
                            </span>
                            <span class="font-semibold text-slate-800">
                                Rp {{ number_format($aktivTahunAjaran->biaya_pembangunan ?? 600000, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-slate-600">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-book text-slate-400"></i> Buku &amp; Modul Belajar
                            </span>
                            <span class="font-semibold text-slate-800">
                                Rp {{ number_format($aktivTahunAjaran->biaya_buku ?? 200000, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-slate-600">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-tshirt text-slate-400"></i> Seragam Sekolah Lengkap
                            </span>
                            <span class="font-semibold text-slate-800">
                                Rp {{ number_format($aktivTahunAjaran->biaya_seragam ?? 200000, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="pt-2 border-t border-slate-200/80 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-700">Total Biaya Daftar Ulang</span>
                            <span class="font-heading text-xl font-extrabold text-emerald-800">
                                Rp {{ number_format($aktivTahunAjaran->biaya_daftar_ulang ?? 1000000, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500 min-h-[44px]">
                    <i class="fas fa-info-circle text-emerald-600 shrink-0"></i>
                    <span>Cicilan pertama Rp 500.000 (buku, seragam, uang muka pembangunan) saat daftar ulang. Pelunasan sisa bulan berikutnya.</span>
                </div>
            </div>
        </div>

        {{-- LAPISAN 2: JADWAL PPDB (Dipindah dari kolom kiri ke baris mandiri) --}}
        <div class="mt-12">
            <div class="mb-4">
                <h4 class="text-xl font-bold text-slate-900 font-heading">
                    Jadwal PPDB {{ $aktivTahunAjaran->tahun_ajar ?? ($aktivTahunAjaran->tahun_ajaran ?? date('Y').'/'.(date('Y')+1)) }}
                </h4>
            </div>

            <div class="jadwal-cards grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                {{-- Card 1: Pendaftaran --}}
                <div class="reveal jadwal-card-item bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-150 ease-out flex flex-col justify-between" data-delay="0">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 bg-green-100 text-green-700 rounded-lg flex items-center justify-center text-base shrink-0">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            @if($statusDaftar === 'ongoing')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200" aria-label="Status: Sedang Berlangsung">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Sedang Berlangsung
                                </span>
                            @elseif($statusDaftar === 'upcoming')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200" aria-label="Status: Akan Datang">
                                    Akan Datang
                                </span>
                            @elseif($statusDaftar === 'ended')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 line-through" aria-label="Status: Selesai">
                                    Selesai
                                </span>
                            @endif
                        </div>
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 block mb-1">Pendaftaran</span>
                        <p class="text-base font-semibold text-slate-900 mb-1">
                            @if($mulaiDaftar && $batasDaftar)
                                {{ $mulaiDaftar->translatedFormat('d M') }} – {{ $batasDaftar->translatedFormat('d M Y') }}
                            @else
                                Belum Ditentukan
                            @endif
                        </p>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Daftar akun dan lengkapi berkas</p>
                </div>

                {{-- Card 2: Seleksi Observasi --}}
                <div class="reveal jadwal-card-item bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-150 ease-out flex flex-col justify-between" data-delay="100">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 bg-green-100 text-green-700 rounded-lg flex items-center justify-center text-base shrink-0">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            @if($statusSeleksi === 'ongoing')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200" aria-label="Status: Sedang Berlangsung">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Sedang Berlangsung
                                </span>
                            @elseif($statusSeleksi === 'upcoming')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200" aria-label="Status: Akan Datang">
                                    Akan Datang
                                </span>
                            @elseif($statusSeleksi === 'ended')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 line-through" aria-label="Status: Selesai">
                                    Selesai
                                </span>
                            @endif
                        </div>
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 block mb-1">Seleksi Observasi</span>
                        <p class="text-base font-semibold text-slate-900 mb-0.5">
                            @if($tglSeleksi)
                                {{ $tglSeleksi->translatedFormat('d M Y') }}
                            @else
                                Belum Ditentukan
                            @endif
                        </p>
                        <p class="text-sm text-slate-500">08.00–10.00 {{ school_timezone_label() }}</p>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Tes kemampuan dasar di sekolah</p>
                </div>

                {{-- Card 3: Pengumuman --}}
                <div class="reveal jadwal-card-item bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm hover:-translate-y-0.5 transition-all duration-150 ease-out flex flex-col justify-between" data-delay="200">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 bg-green-100 text-green-700 rounded-lg flex items-center justify-center text-base shrink-0">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            @if($statusPengumuman === 'ongoing')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200" aria-label="Status: Sedang Berlangsung">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Sedang Berlangsung
                                </span>
                            @elseif($statusPengumuman === 'upcoming')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200" aria-label="Status: Akan Datang">
                                    Akan Datang
                                </span>
                            @elseif($statusPengumuman === 'ended')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-400 border border-slate-200 line-through" aria-label="Status: Selesai">
                                    Selesai
                                </span>
                            @endif
                        </div>
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 block mb-1">Pengumuman</span>
                        <p class="text-base font-semibold text-slate-900 mb-0.5">
                            Melalui Website Portal
                        </p>
                        <p class="text-sm text-slate-500">Diumumkan setelah seleksi selesai</p>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Cek hasil di portal setelah seleksi selesai</p>
                </div>
            </div>
        </div>

        {{-- LAPISAN 3: DOKUMEN PERSYARATAN & ALUR PENDAFTARAN --}}
        <div class="mt-12 space-y-8">
            {{-- Bagian 1: Syarat Dokumen Pendaftaran (Card Horizontal Proporsional) --}}
            <div class="reveal bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div>
                        <h4 class="text-lg font-bold text-slate-900 font-heading flex items-center gap-2.5">
                            <i class="fas fa-clipboard-check text-green-600"></i> Syarat Dokumen Pendaftaran
                        </h4>
                        <p class="text-sm text-slate-500 mt-0.5">Siapkan berkas berikut dalam format digital (foto/scan) sebelum mengisi formulir</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 w-fit self-start sm:self-auto shrink-0">
                        <i class="fas fa-cloud-arrow-up text-xs"></i> Unggah Digital di Portal
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Dokumen 1 --}}
                    <div class="reveal p-4 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3.5 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all duration-200" data-delay="0">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center shrink-0 text-base">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-0.5">Dokumen 1</span>
                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Akta Kelahiran</h5>
                            <p class="text-xs text-slate-500 mt-1">Fotokopi / scan akta asli</p>
                        </div>
                    </div>

                    {{-- Dokumen 2 --}}
                    <div class="reveal p-4 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3.5 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all duration-200" data-delay="60">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center shrink-0 text-base">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-0.5">Dokumen 2</span>
                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Kartu Keluarga (KK)</h5>
                            <p class="text-xs text-slate-500 mt-1">Fotokopi / scan KK terbaru</p>
                        </div>
                    </div>

                    {{-- Dokumen 3 --}}
                    <div class="reveal p-4 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3.5 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all duration-200" data-delay="120">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center shrink-0 text-base">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-0.5">Dokumen 3</span>
                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Pas Foto Calon Siswa</h5>
                            <p class="text-xs text-slate-500 mt-1">Pas foto berwarna terbaru</p>
                        </div>
                    </div>

                    {{-- Dokumen 4 --}}
                    <div class="reveal p-4 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3.5 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all duration-200" data-delay="180">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 text-base">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Dokumen 4</span>
                                <span class="px-1.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Opsional</span>
                            </div>
                            <h5 class="text-sm font-bold text-slate-900 leading-snug">KPS / KIP</h5>
                            <p class="text-xs text-slate-500 mt-1">Bagi pemegang kartu bantuan</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center gap-2.5 text-sm text-slate-600">
                    <i class="fas fa-info-circle text-amber-500 text-sm shrink-0"></i>
                    <span>Semua dokumen diunggah melalui formulir digital di portal setelah akun dibuat. Tidak perlu membawa berkas fisik saat mendaftar online.</span>
                </div>
            </div>

            {{-- Bagian 2: Alur Pendaftaran Online (Flowchart 2 Baris Melingkar) --}}
            <div id="flowchart-alur" class="w-full bg-[#f0fdf4]/50 border border-green-200/60 rounded-[20px] p-6 sm:p-8 lg:p-10 shadow-xs">
                <div class="text-center max-w-xl mx-auto mb-10">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200 mb-3">
                        6 Langkah Mudah
                    </span>
                    <h4 class="text-2xl sm:text-3xl font-bold text-slate-900 font-heading">
                        Alur Pendaftaran Online
                    </h4>
                    <p class="text-[15px] text-slate-600 mt-2">
                        6 langkah proses pendaftaran calon siswa dari awal hingga daftar ulang.
                    </p>
                </div>

                {{-- TATA LETAK DESKTOP (>= 768px): 2 Baris (Baris 1: 1 -> 2 -> 3, Baris 2: 6 <- 5 <- 4) --}}
                <div class="hidden md:block">
                    {{-- Baris 1: Langkah 1 -> 2 -> 3 --}}
                    <div class="grid grid-cols-3 gap-6 lg:gap-8 items-start relative">
                        {{-- Langkah 1 --}}
                        <div class="flow-step flex flex-col items-center text-center relative" data-delay="0">
                            <div class="flow-icon w-[100px] h-[100px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                                <svg class="w-11 h-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                            </div>
                            <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-4">Langkah 1</span>
                            <h5 class="text-base font-bold text-slate-900 mt-1 max-w-[160px] text-center leading-snug">Registrasi Akun</h5>
                            <p class="text-[13px] text-slate-600 mt-1.5 max-w-[160px] text-center leading-relaxed">
                                Buat akun dengan email aktif, lalu masuk ke portal.
                            </p>

                            {{-- Panah 1 -> 2 --}}
                            <div class="flow-arrow hidden md:flex absolute top-[50px] left-[calc(50%+54px)] right-[calc(-50%+54px)] -translate-y-1/2 items-center pointer-events-none z-0" data-delay="80">
                                <div class="flex-1 h-[2.5px] bg-[#86efac]"></div>
                                <svg class="w-3.5 h-3.5 -ml-1 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 2 L9 7 L4 12" />
                                </svg>
                            </div>
                        </div>

                        {{-- Langkah 2 --}}
                        <div class="flow-step flex flex-col items-center text-center relative" data-delay="120">
                            <div class="flow-icon w-[100px] h-[100px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                                <svg class="w-11 h-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    <path d="M9 14l2 2 4-4"></path>
                                </svg>
                            </div>
                            <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-4">Langkah 2</span>
                            <h5 class="text-base font-bold text-slate-900 mt-1 max-w-[160px] text-center leading-snug">Isi Formulir &amp; Upload Dokumen</h5>
                            <p class="text-[13px] text-slate-600 mt-1.5 max-w-[160px] text-center leading-relaxed">
                                Lengkapi data calon siswa, orang tua, dan unggah dokumen persyaratan.
                            </p>

                            {{-- Panah 2 -> 3 --}}
                            <div class="flow-arrow hidden md:flex absolute top-[50px] left-[calc(50%+54px)] right-[calc(-50%+54px)] -translate-y-1/2 items-center pointer-events-none z-0" data-delay="200">
                                <div class="flex-1 h-[2.5px] bg-[#86efac]"></div>
                                <svg class="w-3.5 h-3.5 -ml-1 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 2 L9 7 L4 12" />
                                </svg>
                            </div>
                        </div>

                        {{-- Langkah 3 --}}
                        <div class="flow-step flex flex-col items-center text-center relative" data-delay="240">
                            <div class="flow-icon w-[100px] h-[100px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                                <svg class="w-11 h-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                    <line x1="6" y1="15" x2="10" y2="15"></line>
                                </svg>
                            </div>
                            <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-4">Langkah 3</span>
                            <h5 class="text-base font-bold text-slate-900 mt-1 max-w-[160px] text-center leading-snug">Biaya Pendaftaran</h5>
                            <p class="text-[13px] text-slate-600 mt-1.5 max-w-[160px] text-center leading-relaxed">
                                Bayar online lewat Midtrans. Tanpa unggah bukti pembayaran.
                            </p>
                        </div>
                    </div>

                    {{-- Tikungan: Panah Vertikal dari Langkah 3 ke Langkah 4 --}}
                    <div class="grid grid-cols-3 gap-6 lg:gap-8 my-2">
                        <div></div>
                        <div></div>
                        <div class="flex justify-center items-center py-1">
                            <div class="flow-arrow flex flex-col items-center justify-center pointer-events-none" data-delay="320">
                                <div class="w-[2.5px] h-12 bg-[#86efac]"></div>
                                <svg class="w-4 h-4 -mt-1 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 4 L7 9 L12 4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 2: Langkah 6 <- 5 <- 4 (Kanan ke Kiri) --}}
                    <div class="grid grid-cols-3 gap-6 lg:gap-8 items-start relative">
                        {{-- Langkah 6: Daftar Ulang (di bawah Langkah 1) --}}
                        <div class="flow-step flex flex-col items-center text-center relative" data-delay="600">
                            <div class="flow-icon w-[100px] h-[100px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                                <svg class="w-11 h-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                </svg>
                            </div>
                            <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-4">Langkah 6</span>
                            <h5 class="text-base font-bold text-slate-900 mt-1 max-w-[160px] text-center leading-snug">Daftar Ulang</h5>
                            <p class="text-[13px] text-slate-600 mt-1.5 max-w-[160px] text-center leading-relaxed">
                                Bayar cicilan pertama biaya pendidikan dan serahkan berkas fisik ke sekolah.
                            </p>
                        </div>

                        {{-- Langkah 5: Pengumuman Hasil (di bawah Langkah 2) --}}
                        <div class="flow-step flex flex-col items-center text-center relative" data-delay="480">
                            <div class="flow-icon w-[100px] h-[100px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                                <svg class="w-11 h-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m3 11 18-5v12L3 14v-3z"></path>
                                    <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                                </svg>
                            </div>
                            <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-4">Langkah 5</span>
                            <h5 class="text-base font-bold text-slate-900 mt-1 max-w-[160px] text-center leading-snug">Pengumuman Hasil</h5>
                            <p class="text-[13px] text-slate-600 mt-1.5 max-w-[160px] text-center leading-relaxed">
                                Cek hasil seleksi di portal untuk mengetahui status kelulusan calon siswa.
                            </p>

                            {{-- Panah 5 -> 6 (Mengarah ke Kiri) --}}
                            <div class="flow-arrow hidden md:flex absolute top-[50px] right-[calc(50%+54px)] left-[calc(-50%+54px)] -translate-y-1/2 items-center pointer-events-none z-0" data-delay="560">
                                <svg class="w-3.5 h-3.5 -mr-1 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 2 L5 7 L10 12" />
                                </svg>
                                <div class="flex-1 h-[2.5px] bg-[#86efac]"></div>
                            </div>
                        </div>

                        {{-- Langkah 4: Seleksi Observasi (di bawah Langkah 3) --}}
                        <div class="flow-step flex flex-col items-center text-center relative" data-delay="360">
                            <div class="flow-icon w-[100px] h-[100px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                                <svg class="w-11 h-11 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                </svg>
                            </div>
                            <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-4">Langkah 4</span>
                            <h5 class="text-base font-bold text-slate-900 mt-1 max-w-[160px] text-center leading-snug">Seleksi Observasi</h5>
                            <p class="text-[13px] text-slate-600 mt-1.5 max-w-[160px] text-center leading-relaxed">
                                Tes kemampuan membaca, menulis, dan berhitung di sekolah.
                            </p>

                            {{-- Panah 4 -> 5 (Mengarah ke Kiri) --}}
                            <div class="flow-arrow hidden md:flex absolute top-[50px] right-[calc(50%+54px)] left-[calc(-50%+54px)] -translate-y-1/2 items-center pointer-events-none z-0" data-delay="440">
                                <svg class="w-3.5 h-3.5 -mr-1 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 2 L5 7 L10 12" />
                                </svg>
                                <div class="flex-1 h-[2.5px] bg-[#86efac]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TATA LETAK MOBILE (< 768px): 1 Kolom Vertikal dengan Panah Bawah --}}
                <div class="block md:hidden space-y-2">
                    {{-- Langkah 1 --}}
                    <div class="flow-step flex flex-col items-center text-center" data-delay="0">
                        <div class="flow-icon w-[80px] h-[80px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </div>
                        <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-3">Langkah 1</span>
                        <h5 class="text-sm font-bold text-slate-900 mt-1 max-w-[180px] text-center leading-snug">Registrasi Akun</h5>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px] text-center leading-relaxed">
                            Buat akun dengan email aktif, lalu masuk ke portal.
                        </p>
                    </div>

                    {{-- Panah Vertikal 1 -> 2 --}}
                    <div class="flow-arrow flex flex-col items-center justify-center py-1" data-delay="80">
                        <div class="w-[2px] h-6 bg-[#86efac]"></div>
                        <svg class="w-3 h-3 -mt-0.5 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 4 L7 9 L12 4" />
                        </svg>
                    </div>

                    {{-- Langkah 2 --}}
                    <div class="flow-step flex flex-col items-center text-center" data-delay="120">
                        <div class="flow-icon w-[80px] h-[80px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                <path d="M9 14l2 2 4-4"></path>
                            </svg>
                        </div>
                        <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-3">Langkah 2</span>
                        <h5 class="text-sm font-bold text-slate-900 mt-1 max-w-[180px] text-center leading-snug">Isi Formulir &amp; Upload Dokumen</h5>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px] text-center leading-relaxed">
                            Lengkapi data calon siswa, orang tua, dan unggah dokumen persyaratan.
                        </p>
                    </div>

                    {{-- Panah Vertikal 2 -> 3 --}}
                    <div class="flow-arrow flex flex-col items-center justify-center py-1" data-delay="200">
                        <div class="w-[2px] h-6 bg-[#86efac]"></div>
                        <svg class="w-3 h-3 -mt-0.5 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 4 L7 9 L12 4" />
                        </svg>
                    </div>

                    {{-- Langkah 3 --}}
                    <div class="flow-step flex flex-col items-center text-center" data-delay="240">
                        <div class="flow-icon w-[80px] h-[80px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                                <line x1="6" y1="15" x2="10" y2="15"></line>
                            </svg>
                        </div>
                        <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-3">Langkah 3</span>
                        <h5 class="text-sm font-bold text-slate-900 mt-1 max-w-[180px] text-center leading-snug">Biaya Pendaftaran</h5>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px] text-center leading-relaxed">
                            Bayar online lewat Midtrans. Tanpa unggah bukti pembayaran.
                        </p>
                    </div>

                    {{-- Panah Vertikal 3 -> 4 --}}
                    <div class="flow-arrow flex flex-col items-center justify-center py-1" data-delay="320">
                        <div class="w-[2px] h-6 bg-[#86efac]"></div>
                        <svg class="w-3 h-3 -mt-0.5 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 4 L7 9 L12 4" />
                        </svg>
                    </div>

                    {{-- Langkah 4 --}}
                    <div class="flow-step flex flex-col items-center text-center" data-delay="360">
                        <div class="flow-icon w-[80px] h-[80px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        </div>
                        <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-3">Langkah 4</span>
                        <h5 class="text-sm font-bold text-slate-900 mt-1 max-w-[180px] text-center leading-snug">Seleksi Observasi</h5>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px] text-center leading-relaxed">
                            Tes kemampuan membaca, menulis, dan berhitung di sekolah.
                        </p>
                    </div>

                    {{-- Panah Vertikal 4 -> 5 --}}
                    <div class="flow-arrow flex flex-col items-center justify-center py-1" data-delay="440">
                        <div class="w-[2px] h-6 bg-[#86efac]"></div>
                        <svg class="w-3 h-3 -mt-0.5 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 4 L7 9 L12 4" />
                        </svg>
                    </div>

                    {{-- Langkah 5 --}}
                    <div class="flow-step flex flex-col items-center text-center" data-delay="480">
                        <div class="flow-icon w-[80px] h-[80px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 11 18-5v12L3 14v-3z"></path>
                                <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                            </svg>
                        </div>
                        <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-3">Langkah 5</span>
                        <h5 class="text-sm font-bold text-slate-900 mt-1 max-w-[180px] text-center leading-snug">Pengumuman Hasil</h5>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px] text-center leading-relaxed">
                            Cek hasil seleksi di portal untuk mengetahui status kelulusan calon siswa.
                        </p>
                    </div>

                    {{-- Panah Vertikal 5 -> 6 --}}
                    <div class="flow-arrow flex flex-col items-center justify-center py-1" data-delay="560">
                        <div class="w-[2px] h-6 bg-[#86efac]"></div>
                        <svg class="w-3 h-3 -mt-0.5 text-[#86efac] shrink-0" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 4 L7 9 L12 4" />
                        </svg>
                    </div>

                    {{-- Langkah 6 --}}
                    <div class="flow-step flex flex-col items-center text-center" data-delay="600">
                        <div class="flow-icon w-[80px] h-[80px] rounded-full bg-gradient-to-br from-[#15803d] to-[#16a34a] flex items-center justify-center shrink-0 cursor-default" style="box-shadow: 0 8px 24px rgba(21,128,61,0.35);">
                            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                            </svg>
                        </div>
                        <span class="text-xs uppercase font-bold tracking-wider text-green-700 mt-3">Langkah 6</span>
                        <h5 class="text-sm font-bold text-slate-900 mt-1 max-w-[180px] text-center leading-snug">Daftar Ulang</h5>
                        <p class="text-xs text-slate-600 mt-1 max-w-[200px] text-center leading-relaxed">
                            Bayar cicilan pertama biaya pendidikan dan serahkan berkas fisik ke sekolah.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- LAPISAN 4: TOMBOL CTA (Mandiri di luar semua kartu) --}}
        <div class="mt-10 flex flex-col items-center justify-center text-center">
            <a href="#registrasi" @click.prevent="registerModalOpen = true" 
               class="btn-cta-pulse inline-flex items-center justify-center gap-2.5 h-14 min-h-[56px] max-w-[420px] w-full px-8 text-lg font-semibold text-white bg-green-700 hover:bg-green-800 active:bg-green-900 rounded-[14px] shadow-md hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-green-500/20 max-sm:w-full">
                <span>Mulai Mendaftar Sekarang</span>
                <i class="fas fa-arrow-right text-sm"></i>
            </a>
            <p class="mt-3 text-sm text-slate-500">
                Pendaftaran gratis. Biaya formulir Rp {{ number_format($aktivTahunAjaran->biaya_pendaftaran ?? 50000, 0, ',', '.') }} dibayar setelah akun dibuat dan data dilengkapi.
            </p>
        </div>
    </div>
</section>
