@extends('layout.tampilan')

@section('page-title', 'Dashboard')

@section('content')
@php
    $hasAntreanVar = isset($dokumenMenungguVerifikasi) || isset($pembayaranPerluPerhatian) || isset($daftarUlangBelumBayar);
    $totalAntrean = ($dokumenMenungguVerifikasi ?? 0) + ($pembayaranPerluPerhatian ?? 0) + ($daftarUlangBelumBayar ?? 0);
@endphp

<div class="space-y-8">
    {{-- 1. Welcome Banner --}}
    <div class="relative overflow-hidden bg-linear-to-br from-green-700 via-green-600 to-teal-700 rounded-2xl p-6 sm:p-8 shadow-xs">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2 font-heading tracking-tight">
                    Halo, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="text-green-50 text-sm sm:text-base max-w-2xl leading-relaxed">
                    Selamat datang di Panel Admin PPDB. Kelola pendaftaran, verifikasi berkas, pembayaran, dan seleksi calon siswa dengan mudah dan cepat.
                </p>
                @if($hasAntreanVar)
                    <p class="text-xs font-semibold text-green-100 mt-3 inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full border border-white/20">
                        @if($totalAntrean > 0)
                            <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                            <span>Ada {{ $totalAntrean }} tugas yang menunggu tindakan Anda.</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-green-300"></span>
                            <span>Semua tugas sudah tertangani.</span>
                        @endif
                    </p>
                @endif
            </div>
            
            {{-- Kartu Tahun Ajaran Aktif Terintegrasi --}}
            <div class="shrink-0 bg-green-900/40 backdrop-blur-md border border-white/30 rounded-xl px-6 py-4 text-center shadow-xs">
                <p class="text-green-100 text-xs font-semibold uppercase tracking-wider mb-1">Tahun Ajaran Aktif</p>
                <p class="text-2xl sm:text-3xl font-bold text-white font-heading">{{ $tahunAjaranString }}</p>
            </div>
        </div>
        
        {{-- Decorative Glow --}}
        <div class="absolute right-0 top-0 w-72 h-72 bg-white/5 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/3"></div>
    </div>

    {{-- 2. Bagian Perlu Tindakan Sekarang (Hanya tampil bila ada antrean > 0 dari controller) --}}
    @if($hasAntreanVar && $totalAntrean > 0)
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-amber-900 font-heading flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Perlu Tindakan Sekarang</span>
                </h2>
                <span class="text-xs text-amber-800 font-medium bg-amber-100/70 border border-amber-200 px-2.5 py-0.5 rounded-full">
                    {{ $totalAntrean }} antrean tertunda
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- A. Dokumen Menunggu Verifikasi --}}
                @if(isset($dokumenMenungguVerifikasi) && $dokumenMenungguVerifikasi > 0)
                    <div class="bg-white border border-amber-200/90 rounded-2xl p-5 shadow-xs flex flex-col justify-between hover:border-amber-300 transition-colors">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-base shrink-0">
                                    <i class="fas fa-file-circle-exclamation"></i>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>
                            </div>
                            <span class="text-3xl font-bold text-slate-900 block font-heading" aria-label="{{ $dokumenMenungguVerifikasi }} berkas menunggu verifikasi">
                                {{ $dokumenMenungguVerifikasi }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 mt-1">Dokumen Menunggu Verifikasi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Berkas pendaftaran calon siswa baru</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="/verifikasi-dokumen" class="w-full text-green-700 hover:text-green-800 text-xs font-bold inline-flex items-center justify-between transition-colors focus-visible:outline-none focus-visible:underline">
                                <span>Periksa berkas</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- B. Pembayaran Perlu Dicek --}}
                @if(isset($pembayaranPerluPerhatian) && $pembayaranPerluPerhatian > 0)
                    <div class="bg-white border border-amber-200/90 rounded-2xl p-5 shadow-xs flex flex-col justify-between hover:border-amber-300 transition-colors">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-base shrink-0">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Perhatian</span>
                            </div>
                            <span class="text-3xl font-bold text-slate-900 block font-heading" aria-label="{{ $pembayaranPerluPerhatian }} pembayaran perlu perhatian">
                                {{ $pembayaranPerluPerhatian }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 mt-1">Pembayaran Perlu Dicek</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Transaksi tertunda &gt; 24 jam / kendala</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="/pembayaran" class="w-full text-green-700 hover:text-green-800 text-xs font-bold inline-flex items-center justify-between transition-colors focus-visible:outline-none focus-visible:underline">
                                <span>Pantau transaksi</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- C. Daftar Ulang Belum Bayar --}}
                @if(isset($daftarUlangBelumBayar) && $daftarUlangBelumBayar > 0)
                    <div class="bg-white border border-amber-200/90 rounded-2xl p-5 shadow-xs flex flex-col justify-between hover:border-amber-300 transition-colors">
                        <div>
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-base shrink-0">
                                    <i class="fas fa-user-clock"></i>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Belum Bayar</span>
                            </div>
                            <span class="text-3xl font-bold text-slate-900 block font-heading" aria-label="{{ $daftarUlangBelumBayar }} siswa lulus belum bayar daftar ulang">
                                {{ $daftarUlangBelumBayar }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 mt-1">Daftar Ulang Belum Bayar</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Siswa dinyatakan lulus seleksi</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="/admin/daftar-ulang" class="w-full text-green-700 hover:text-green-800 text-xs font-bold inline-flex items-center justify-between transition-colors focus-visible:outline-none focus-visible:underline">
                                <span>Lihat siswa</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @else
        {{-- TODO: Variabel antrean tindakan berikut belum dikirim oleh BerandaController:
             - $dokumenMenungguVerifikasi: Dokumen pendaftar berstatus 'Menunggu' / belum diverifikasi panitia
             - $pembayaranPerluPerhatian: Transaksi formulir/daftar ulang berstatus pending > 24 jam
             - $daftarUlangBelumBayar: Siswa lulus yang belum membayar cicilan pertama daftar ulang
             Elemen 'Perlu Tindakan Sekarang' disembunyikan agar tidak menampilkan angka 0 palsu. --}}
    @endif

    {{-- 3. Bagian Ringkasan Tahun Ajaran (Grid 5 Kartu Setinggi Sama Mengikuti Alur PPDB) --}}
    <section class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 font-heading">
                    Ringkasan Tahun Ajaran {{ $tahunAjaranString }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Progres tahapan PPDB dari kuota, pendaftaran, seleksi, hingga daftar ulang
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200 w-fit">
                <span class="w-2 h-2 rounded-full bg-green-600"></span>
                <span>TA Aktif</span>
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-5 items-stretch">
            
            {{-- Kartu 1: Kuota Siswa (Klik langsung menuju master data tahun ajaran & kuota) --}}
            <a href="/admin/data/tahun-ajaran" 
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Kuota Siswa</span>
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Keterisian kuota: {{ $kuotaTerisi }} dari {{ $kuotaTotal }} siswa">
                        <span class="text-3xl font-bold text-slate-900 font-heading group-hover:text-green-800 transition-colors">{{ $kuotaTerisi }}</span>
                        <span class="text-sm font-medium text-slate-400">/ {{ $kuotaTotal }}</span>
                    </div>
                    
                    {{-- Progress Bar Kuota --}}
                    <div class="space-y-1.5 mt-3">
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden" 
                             role="progressbar" 
                             aria-valuenow="{{ $kuotaTerisi }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $kuotaTotal }}"
                             aria-label="Persentase keterisian kuota siswa">
                            <div class="bg-green-700 h-2 rounded-full transition-all duration-300" style="width: {{ $kuotaTotal > 0 ? min(100, ($kuotaTerisi / $kuotaTotal) * 100) : 0 }}%"></div>
                        </div>
                        <div class="flex justify-between items-center text-xs text-slate-500 pt-0.5">
                            <span>Tersisa <strong class="font-semibold text-slate-700">{{ $kuotaTersisa }}</strong></span>
                            <span class="font-semibold {{ $kuotaTerisi >= $kuotaTotal ? 'text-amber-600' : 'text-green-700' }}">
                                {{ $kuotaTotal > 0 ? round(($kuotaTerisi / $kuotaTotal) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-green-700 group-hover:text-green-800">
                    <span>Kelola kuota &amp; TA</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            {{-- Kartu 2: Total Pendaftar (Klik langsung menuju data casis) --}}
            <a href="/admin/data/casis" 
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Total Pendaftar</span>
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Total pendaftar: {{ $totalPendaftar }} calon siswa">
                        <span class="text-3xl font-bold text-slate-900 font-heading group-hover:text-green-800 transition-colors">{{ $totalPendaftar }}</span>
                        <span class="text-sm font-medium text-slate-500">Casis</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Akun calon siswa terdaftar pada tahun ajaran aktif
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-green-700 group-hover:text-green-800">
                    <span>Lihat semua pendaftar</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            {{-- Kartu 3: Berkas Valid (Klik langsung menuju verifikasi dokumen) --}}
            <a href="/admin/pendaftaran" 
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Berkas Valid</span>
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Dokumen terverifikasi: {{ $dokumenTerverifikasi ?? 'Belum tersedia' }}">
                        @if(isset($dokumenTerverifikasi))
                            <span class="text-3xl font-bold text-slate-900 font-heading group-hover:text-green-800 transition-colors">{{ $dokumenTerverifikasi }}</span>
                            <span class="text-sm font-medium text-slate-500">Berkas</span>
                        @else
                            {{-- TODO: Variabel $dokumenTerverifikasi belum dikirim oleh BerandaController --}}
                            <span class="text-3xl font-bold text-slate-300 font-heading">—</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Dokumen persyaratan casis dinyatakan valid
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-green-700 group-hover:text-green-800">
                    <span>Periksa berkas dokumen</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            {{-- Kartu 4: Lulus Seleksi (Klik langsung menuju data pengumuman seleksi) --}}
            <a href="/admin/data/pengumuman-seleksi" 
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Lulus Seleksi</span>
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Siswa lulus seleksi: {{ $totalDiterima }} calon siswa">
                        <span class="text-3xl font-bold text-slate-900 font-heading group-hover:text-green-800 transition-colors">{{ $totalDiterima }}</span>
                        <span class="text-sm font-medium text-slate-500">Casis</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Hasil observasi resmi diumumkan lulus
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-green-700 group-hover:text-green-800">
                    <span>Pengumuman kelulusan</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            {{-- Kartu 5: Daftar Ulang Selesai (Klik langsung menuju verifikasi daftar ulang) --}}
            <a href="{{ route('admin.daftar_ulang.index') }}" 
               class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between h-full hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600 block cursor-pointer">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Daftar Ulang Selesai</span>
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm shrink-0 group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 my-2" aria-label="Daftar ulang selesai: {{ $daftarUlangSelesai ?? 'Belum tersedia' }}">
                        @if(isset($daftarUlangSelesai))
                            <span class="text-3xl font-bold text-slate-900 font-heading group-hover:text-green-800 transition-colors">{{ $daftarUlangSelesai }}</span>
                            <span class="text-sm font-medium text-slate-500">Siswa</span>
                        @else
                            {{-- TODO: Variabel $daftarUlangSelesai belum dikirim oleh BerandaController --}}
                            <span class="text-3xl font-bold text-slate-300 font-heading">—</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Cicilan pertama lunas &amp; berkas fisik diterima
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-green-700 group-hover:text-green-800">
                    <span>Verifikasi daftar ulang</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

        </div>
    </section>

    {{-- 4. Bagian Aktivitas Terbaru (Hanya bila log aktivitas dikirim oleh controller) --}}
    @if(isset($aktivitasTerbaru) && count($aktivitasTerbaru) > 0)
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 font-heading">Aktivitas Terbaru</h2>
                    <p class="text-xs text-slate-500">10 entri aktivitas terbaru dari semua modul PPDB</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="divide-y divide-slate-100">
                    @foreach($aktivitasTerbaru as $log)
                        <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-green-50 text-green-700 flex items-center justify-center text-sm shrink-0">
                                    <i class="{{ $log->icon ?? 'fas fa-history' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $log->nama_pengguna ?? 'Pengguna' }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $log->deskripsi ?? 'Melakukan aktivitas' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-xs text-slate-400">{{ $log->waktu ?? '' }}</span>
                                @if(!empty($log->url_detail))
                                    <a href="{{ $log->url_detail }}" class="text-green-700 hover:text-green-800 text-xs font-semibold">Detail</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        {{-- TODO: Variabel $aktivitasTerbaru belum dikirim oleh BerandaController.
             Belum ada tabel audit log terpadu lintas-modul pada database.
             Bagian 'Aktivitas Terbaru' disembunyikan agar tidak menampilkan daftar kosong yang janggal. --}}
    @endif
</div>
@endsection
