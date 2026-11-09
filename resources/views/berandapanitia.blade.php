@extends('layout.tampilanpanitia')

@section('page-title', 'Dashboard Panitia')

@section('content')

@php
    // Prioritaskan variabel yang dikirim controller, fallback ke data yang sudah tersedia dari model seleksi
    $pesertaBelumDinilai = $belumDinilai ?? null;
    $statusPublikasiTersedia = isset($sudahDipublikasikan) || isset($statusPublikasi) || ($casisTertinggi->isNotEmpty() && $casisTertinggi->first()?->diumumkan_pada !== null);
    $isDipublikasikan = $sudahDipublikasikan ?? ($statusPublikasi ?? ($casisTertinggi->first()?->isDiumumkan() ?? false));
    $tglPublikasi = $tanggalPublikasi ?? $casisTertinggi->first()?->diumumkan_pada;
    $namaPetugasPublikasi = $namaPetugas ?? ($casisTertinggi->first()?->panitiaPengumum?->nama ?? $casisTertinggi->first()?->panitiaPengumum?->name ?? null);

    $pesertaLulus = $totalLulus ?? $totalDiterima ?? null;
    $pesertaTidakLulus = $totalTidakLulus ?? $totalDitolak ?? null;
    $pesertaTotal = $totalPeserta ?? $totalPendaftar ?? null;
@endphp

<div class="space-y-6">

    {{-- 1. Banner Sapaan Panitia --}}
    <section aria-labelledby="banner-panitia-heading" class="relative overflow-hidden bg-linear-to-br from-green-600 via-emerald-700 to-teal-800 rounded-2xl p-6 sm:p-8 shadow-sm text-white">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <h1 id="banner-panitia-heading" class="text-2xl sm:text-3xl font-bold font-heading mb-2 tracking-tight">
                    Halo, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="text-green-50 text-sm sm:text-base leading-relaxed">
                    Selamat datang di Panel Panitia PPDB. Kelola pendaftaran, seleksi observasi, dan publikasi kelulusan calon siswa secara terpadu.
                </p>

                {{-- Kalimat Dinamis Berdasarkan Status Seleksi --}}
                @if($isDipublikasikan)
                    <p class="text-emerald-100 text-xs sm:text-sm font-medium mt-3 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-emerald-300" aria-hidden="true"></i>
                        <span>Hasil seleksi sudah dipublikasikan.</span>
                    </p>
                @elseif($pesertaBelumDinilai !== null)
                    @if($pesertaBelumDinilai === 0)
                        <p class="text-emerald-100 text-xs sm:text-sm font-medium mt-3 flex items-center gap-1.5">
                            <i class="fas fa-check-circle text-emerald-300" aria-hidden="true"></i>
                            <span>Semua peserta sudah dinilai.</span>
                        </p>
                    @else
                        <p class="text-amber-200 text-xs sm:text-sm font-medium mt-3 flex items-center gap-1.5">
                            <i class="fas fa-exclamation-circle text-amber-300" aria-hidden="true"></i>
                            <span>Ada {{ $pesertaBelumDinilai }} peserta belum dinilai.</span>
                        </p>
                    @endif
                @else
                    {{-- TODO: Variabel $belumDinilai belum dikirim oleh BerandaController. Kalimat dinamis otomatis tampil saat variabel dikirim --}}
                @endif
            </div>

            {{-- Kartu Tahun Ajaran Aktif --}}
            <div class="shrink-0 bg-emerald-950/40 backdrop-blur-md border border-white/30 rounded-xl px-5 py-3 text-center shadow-xs">
                <p class="text-green-200 text-xs font-semibold uppercase tracking-wider mb-1">Tahun Ajaran Aktif</p>
                <p class="text-2xl sm:text-[26px] font-bold text-white tracking-tight font-heading">{{ $tahunAjaranString }}</p>
            </div>
        </div>

        {{-- Aksen Dekoratif Halus (Pre-rendered SVG / Radial Blur) --}}
        <div class="absolute right-0 top-0 w-72 h-72 bg-white/5 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/3" aria-hidden="true"></div>
    </section>

    {{-- 2. Bagian Ringkasan Penilaian (4 Kartu Tugas Panitia) --}}
    <section aria-labelledby="heading-ringkasan" class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 id="heading-ringkasan" class="text-lg font-bold text-slate-800 font-heading">
                    Ringkasan Penilaian
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pemantauan progres dan hasil seleksi observasi calon siswa
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>TA Aktif</span>
            </span>
        </div>

        {{-- Grid 4 Kartu: 4 kolom >= 1100px; 2x2 pada 600-1100px; 1 kolom < 600px. Semua setinggi sama via grid stretch --}}
        <div class="grid grid-cols-1 min-[600px]:grid-cols-2 min-[1100px]:grid-cols-4 items-stretch gap-4 sm:gap-5">

            {{-- Kartu 1: Total Peserta --}}
            <a href="/panitia/evaluasi-seleksi"
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-emerald-600 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">
                            Total Peserta
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-emerald-100 transition-colors">
                            <i class="fas fa-users" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Total peserta seleksi: {{ $pesertaTotal ?? 'Belum tersedia' }}">
                        @if($pesertaTotal !== null)
                            <span class="text-3xl font-bold text-slate-900 font-heading group-hover:text-emerald-800 transition-colors">{{ $pesertaTotal }}</span>
                            <span class="text-sm font-medium text-slate-500">Siswa</span>
                        @else
                            {{-- TODO: Variabel $totalPeserta belum dikirim oleh BerandaController --}}
                            <span class="text-3xl font-bold text-slate-300 font-heading">—</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Layak ikut seleksi
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                    <span>Lihat daftar peserta</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                </div>
            </a>

            {{-- Kartu 2: Belum Dinilai --}}
            <a href="/panitia/evaluasi-seleksi"
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-emerald-600 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">
                            Belum Dinilai
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-amber-100 transition-colors">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Peserta belum dinilai: {{ $pesertaBelumDinilai ?? 'Belum tersedia' }}">
                        @if($pesertaBelumDinilai !== null)
                            <span class="text-3xl font-bold font-heading transition-colors {{ $pesertaBelumDinilai > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                {{ $pesertaBelumDinilai }}
                            </span>
                            <span class="text-sm font-medium text-slate-500">Siswa</span>
                        @else
                            {{-- TODO: Variabel $belumDinilai belum dikirim oleh BerandaController --}}
                            <span class="text-3xl font-bold text-slate-300 font-heading">—</span>
                        @endif
                    </div>
                    <p class="text-xs mt-1 leading-relaxed font-medium {{ ($pesertaBelumDinilai !== null && $pesertaBelumDinilai > 0) ? 'text-amber-600' : 'text-slate-500' }}">
                        @if($pesertaBelumDinilai !== null)
                            {{ $pesertaBelumDinilai > 0 ? 'Perlu dinilai' : 'Semua sudah dinilai ✓' }}
                        @else
                            Perlu dinilai
                        @endif
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                    <span>Input nilai seleksi</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                </div>
            </a>

            {{-- Kartu 3: Lulus --}}
            <a href="/laporan/hasil casis"
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-emerald-600 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">
                            Lulus Seleksi
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-emerald-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Peserta lulus: {{ $pesertaLulus ?? 'Belum tersedia' }}">
                        @if($pesertaLulus !== null)
                            <span class="text-3xl font-bold font-heading transition-colors {{ $pesertaLulus > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $pesertaLulus }}
                            </span>
                            <span class="text-sm font-medium text-slate-500">Siswa</span>
                        @else
                            {{-- TODO: Variabel $totalLulus belum dikirim oleh BerandaController --}}
                            <span class="text-3xl font-bold text-slate-300 font-heading">—</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Total nilai ≥ 150
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                    <span>Lihat laporan kelulusan</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                </div>
            </a>

            {{-- Kartu 4: Tidak Lulus --}}
            <a href="/laporan/hasil casis"
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-emerald-600 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">
                            Tidak Lulus
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-rose-100 transition-colors">
                            <i class="fas fa-times-circle" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Peserta tidak lulus: {{ $pesertaTidakLulus ?? 'Belum tersedia' }}">
                        @if($pesertaTidakLulus !== null)
                            <span class="text-3xl font-bold font-heading transition-colors {{ $pesertaTidakLulus > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $pesertaTidakLulus }}
                            </span>
                            <span class="text-sm font-medium text-slate-500">Siswa</span>
                        @else
                            {{-- TODO: Variabel $totalTidakLulus belum dikirim oleh BerandaController --}}
                            <span class="text-3xl font-bold text-slate-300 font-heading">—</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Total nilai &lt; 150
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-700 group-hover:text-emerald-800">
                    <span>Lihat data seleksi</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                </div>
            </a>

        </div>

        {{-- Bilah Progres Penyelesaian (Hanya tampil bila variabel tersedia) --}}
        @if(isset($sudahDinilai) && isset($totalPeserta) && $totalPeserta > 0)
            @php
                $persenSelesai = min(100, max(0, round(($sudahDinilai / $totalPeserta) * 100)));
            @endphp
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs mt-3">
                <div class="flex items-center justify-between text-xs sm:text-sm font-semibold text-slate-700 mb-2">
                    <span>Penilaian selesai: <strong class="text-emerald-700">{{ $sudahDinilai }}</strong> dari <strong>{{ $totalPeserta }}</strong> peserta ({{ $persenSelesai }}%)</span>
                    <span class="text-slate-500">{{ $persenSelesai }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden" role="progressbar" aria-valuenow="{{ $persenSelesai }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progres penilaian peserta seleksi">
                    <div class="bg-emerald-600 h-2 rounded-full transition-all duration-500" style="width: {{ $persenSelesai }}%"></div>
                </div>
            </div>
        @else
            {{-- TODO: Variabel $sudahDinilai dan $totalPeserta belum dikirim oleh BerandaController untuk bilah progres --}}
        @endif
    </section>

    {{-- 3. Panel Status Publikasi --}}
    @if($statusPublikasiTersedia)
        <section aria-labelledby="heading-status-publikasi" class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            @if($isDipublikasikan)
                {{-- Kondisi 1: SUDAH DIPUBLIKASIKAN --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0 border border-emerald-100">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 id="heading-status-publikasi" class="text-base sm:text-lg font-bold text-slate-900 font-heading">
                                    Hasil Sudah Dipublikasikan
                                </h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    Resmi
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                                Seluruh hasil seleksi telah diumumkan secara serentak ke portal calon siswa dan data penilaian telah terkunci secara permanen.
                            </p>
                            @if($tglPublikasi)
                                <p class="text-xs text-slate-500 mt-2 flex items-center gap-1.5 font-medium">
                                    <i class="fas fa-calendar-check text-emerald-600" aria-hidden="true"></i>
                                    <span>Dipublikasikan pada {{ \Carbon\Carbon::parse($tglPublikasi)->isoFormat('D MMMM Y, HH.mm') }} WITA</span>
                                    @if($namaPetugasPublikasi)
                                        <span>oleh <strong class="text-slate-700 font-semibold">{{ $namaPetugasPublikasi }}</strong></span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 sm:self-center">
                        <a href="{{ route('panitia.pengumuman.seleksi') }}"
                           class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                           title="Buka Halaman Pengumuman Seleksi">
                            <span>Lihat Pengumuman</span>
                            <i class="fas fa-arrow-right ml-2 text-xs" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            @else
                {{-- Kondisi 2: BELUM DIPUBLIKASIKAN --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0 border border-amber-100">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 id="heading-status-publikasi" class="text-base sm:text-lg font-bold text-slate-900 font-heading">
                                    Hasil Belum Dipublikasikan
                                </h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Draft Internal
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-700 font-medium mt-1">
                                Lulus {{ $pesertaLulus ?? 0 }} · Tidak Lulus {{ $pesertaTidakLulus ?? 0 }} · Batas lulus: total ≥ 150
                            </p>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Setelah dipublikasikan, hasil terlihat oleh calon siswa dan nilai terkunci.
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 sm:self-center">
                        @if(!empty($pesertaBelumDinilai) && $pesertaBelumDinilai > 0)
                            <span class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-400 bg-slate-100 cursor-not-allowed"
                                  title="Masih ada {{ $pesertaBelumDinilai }} peserta belum dinilai"
                                  aria-disabled="true">
                                <span>Buka Halaman Publikasi</span>
                                <i class="fas fa-lock ml-2 text-xs" aria-hidden="true"></i>
                            </span>
                        @else
                            <a href="{{ route('panitia.pengumuman.seleksi') }}"
                               class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white bg-green-700 hover:bg-green-800 transition-colors shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600"
                               title="Buka Halaman Publikasi Pengumuman Seleksi">
                                <span>Buka Halaman Publikasi</span>
                                <i class="fas fa-arrow-right ml-2 text-xs" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    @else
        {{-- TODO: Variabel status publikasi belum dikirim oleh BerandaController --}}
    @endif

    {{-- 4. Tabel Hasil Penilaian (Menggantikan Top 5) --}}
    <section aria-labelledby="heading-hasil-penilaian" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h2 id="heading-hasil-penilaian" class="text-base sm:text-lg font-bold text-slate-900 font-heading">
                    Hasil Penilaian
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Diurutkan berdasarkan total nilai.
                </p>
            </div>
            <a href="/panitia/evaluasi-seleksi"
               class="text-xs sm:text-sm font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded-lg px-2 py-1"
               title="Lihat semua data penilaian">
                <span>Lihat semua</span>
                <i class="fas fa-arrow-right text-[11px]" aria-hidden="true"></i>
            </a>
        </div>

        {{-- Tampilan Desktop (Table View >= 768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100 whitespace-nowrap">
                    <tr>
                        <th scope="col" class="px-3.5 py-3 font-semibold w-10 text-center">No</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold">Nama Calon Siswa</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold text-center w-20">Membaca</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold text-center w-20">Menulis</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold text-center w-20">Berhitung</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold text-center w-20">Total</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold text-center w-28">Hasil</th>
                        <th scope="col" class="px-3.5 py-3 font-semibold text-center w-32">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($casisTertinggi as $index => $casis)
                        @php
                            $cs = $casis->calonSiswa ?? $casis->casis;
                            $namaCasis = $cs?->nama_calon_siswa ?? $cs?->nama ?? 'Nama tidak tersedia';
                            $noReg = $cs?->no_registrasi ?? $cs?->nomor_pendaftaran ?? '—';
                            $nilaiBaca = $casis->nilai_baca;
                            $nilaiTulis = $casis->nilai_tulis;
                            $nilaiHitung = $casis->nilai_hitung;
                            $totalNilai = $casis->total_nilai;
                            $isLulus = ($totalNilai !== null && $totalNilai >= 150);
                            $isDiumumkan = $casis->isDiumumkan();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- NO --}}
                            <td class="px-3.5 py-3.5 text-center text-xs font-semibold text-slate-500 tabular-nums">
                                {{ $index + 1 }}
                            </td>

                            {{-- NAMA CALON SISWA --}}
                            <td class="px-3.5 py-3.5">
                                <div class="font-semibold text-slate-800 text-[15px] leading-snug">
                                    {{ $namaCasis }}
                                </div>
                                <div class="font-mono text-[13px] text-slate-400 mt-0.5">
                                    {{ $noReg }}
                                </div>
                            </td>

                            {{-- MEMBACA --}}
                            <td class="px-3.5 py-3.5 text-center tabular-nums text-[15px] font-medium text-slate-700">
                                {{ $nilaiBaca !== null ? $nilaiBaca : '—' }}
                            </td>

                            {{-- MENULIS --}}
                            <td class="px-3.5 py-3.5 text-center tabular-nums text-[15px] font-medium text-slate-700">
                                {{ $nilaiTulis !== null ? $nilaiTulis : '—' }}
                            </td>

                            {{-- BERHITUNG --}}
                            <td class="px-3.5 py-3.5 text-center tabular-nums text-[15px] font-medium text-slate-700">
                                {{ $nilaiHitung !== null ? $nilaiHitung : '—' }}
                            </td>

                            {{-- TOTAL --}}
                            <td class="px-3.5 py-3.5 text-center tabular-nums text-base">
                                @if($totalNilai !== null && $totalNilai > 0)
                                    <span class="font-semibold {{ $isLulus ? 'text-emerald-700 font-bold' : 'text-rose-700 font-bold' }}">
                                        {{ $totalNilai }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- HASIL --}}
                            <td class="px-3.5 py-3.5 text-center whitespace-nowrap">
                                @if($totalNilai !== null && $totalNilai > 0)
                                    @if($isLulus)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <i class="fas fa-check-circle text-[11px]" aria-hidden="true"></i>
                                            <span>Lulus</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            <i class="fas fa-times-circle text-[11px]" aria-hidden="true"></i>
                                            <span>Tidak Lulus</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                        <i class="fas fa-minus-circle text-[11px]" aria-hidden="true"></i>
                                        <span>Belum Dinilai</span>
                                    </span>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="px-3.5 py-3.5 text-center whitespace-nowrap">
                                @if($isDiumumkan)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <i class="fas fa-check-circle text-emerald-600" aria-hidden="true"></i>
                                        <span>Dipublikasikan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                                        <i class="fas fa-clock text-amber-500" aria-hidden="true"></i>
                                        <span>Menunggu Publikasi</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-2 border border-slate-100">
                                        <i class="fas fa-clipboard-list text-xl" aria-hidden="true"></i>
                                    </div>
                                    <h4 class="text-slate-700 font-semibold text-sm">Belum ada data penilaian.</h4>
                                    <p class="text-slate-400 text-xs mt-1">Data hasil observasi akan tampil di sini setelah panitia melakukan penilaian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tampilan Mobile (Card View < 768px, mencegah overflow horizontal) --}}
        <div class="md:hidden divide-y divide-slate-100 p-4 space-y-3">
            @forelse ($casisTertinggi as $index => $casis)
                @php
                    $cs = $casis->calonSiswa ?? $casis->casis;
                    $namaCasis = $cs?->nama_calon_siswa ?? $cs?->nama ?? 'Nama tidak tersedia';
                    $noReg = $cs?->no_registrasi ?? $cs?->nomor_pendaftaran ?? '—';
                    $nilaiBaca = $casis->nilai_baca;
                    $nilaiTulis = $casis->nilai_tulis;
                    $nilaiHitung = $casis->nilai_hitung;
                    $totalNilai = $casis->total_nilai;
                    $isLulus = ($totalNilai !== null && $totalNilai >= 150);
                    $isDiumumkan = $casis->isDiumumkan();
                @endphp
                <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-200/70 space-y-3">
                    {{-- Header Kartu Mobile --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h3 class="font-semibold text-slate-800 text-[15px] leading-snug">
                                    {{ $namaCasis }}
                                </h3>
                                <p class="font-mono text-xs text-slate-400 mt-0.5">
                                    {{ $noReg }}
                                </p>
                            </div>
                        </div>

                        {{-- Status Publikasi Mobile --}}
                        @if($isDiumumkan)
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                <i class="fas fa-check-circle text-emerald-600 text-[10px]" aria-hidden="true"></i>
                                <span>Publik</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">
                                <i class="fas fa-clock text-amber-500 text-[10px]" aria-hidden="true"></i>
                                <span>Draft</span>
                            </span>
                        @endif
                    </div>

                    {{-- Nilai Tiga Komponen --}}
                    <div class="grid grid-cols-3 gap-2 bg-white rounded-lg p-2.5 border border-slate-200/60 text-center">
                        <div>
                            <span class="block text-[11px] text-slate-500 font-medium">Membaca</span>
                            <span class="font-semibold text-slate-800 text-sm tabular-nums">{{ $nilaiBaca !== null ? $nilaiBaca : '—' }}</span>
                        </div>
                        <div class="border-x border-slate-100">
                            <span class="block text-[11px] text-slate-500 font-medium">Menulis</span>
                            <span class="font-semibold text-slate-800 text-sm tabular-nums">{{ $nilaiTulis !== null ? $nilaiTulis : '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-slate-500 font-medium">Berhitung</span>
                            <span class="font-semibold text-slate-800 text-sm tabular-nums">{{ $nilaiHitung !== null ? $nilaiHitung : '—' }}</span>
                        </div>
                    </div>

                    {{-- Footer Kartu Mobile: Total & Hasil --}}
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xs text-slate-500 font-medium">Total:</span>
                            @if($totalNilai !== null && $totalNilai > 0)
                                <span class="font-bold text-base tabular-nums {{ $isLulus ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $totalNilai }}
                                </span>
                            @else
                                <span class="text-slate-400 font-semibold">—</span>
                            @endif
                        </div>

                        <div>
                            @if($totalNilai !== null && $totalNilai > 0)
                                @if($isLulus)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i>
                                        <span>Lulus</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <i class="fas fa-times-circle text-[10px]" aria-hidden="true"></i>
                                        <span>Tidak Lulus</span>
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                    <i class="fas fa-minus-circle text-[10px]" aria-hidden="true"></i>
                                    <span>Belum Dinilai</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center">
                    <p class="text-slate-600 font-medium text-sm">Belum ada data penilaian.</p>
                    <p class="text-slate-400 text-xs mt-1">Data hasil observasi akan tampil di sini.</p>
                </div>
            @endforelse
        </div>
    </section>

</div>

@endsection
