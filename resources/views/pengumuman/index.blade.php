@extends('layout.tampilancasis')

@section('page-title', 'Pengumuman Seleksi')

@section('content')
<div class="space-y-6">

    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold font-heading">Pengumuman Hasil Seleksi</h1>
        <p class="text-slate-500 text-sm mt-1">Cek hasil tes observasi seleksi penerimaan peserta didik baru Anda.</p>
    </div>

    @if(!empty($isArchived))
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-6 py-5 rounded-2xl flex items-start gap-4 shadow-xs mb-6">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-700">
                <i class="fas fa-archive text-lg" aria-hidden="true"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-base mb-1 text-amber-950">Data Pendaftaran Sedang Diarsipkan</h4>
                <p class="text-sm leading-relaxed text-amber-900">
                    Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.
                </p>
                <div class="mt-3">
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Panitia PPDB SD Kristen Rantai Damai, saya ingin menanyakan perihal status seleksi dan data pendaftaran calon siswa: ' . ($archivedCasis?->nama_calon_siswa ?? '')) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <span>Hubungi Panitia PPDB</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if (!isset($data) || count($data) === 0)
        @php
            $tglSeleksiRaw = $tahunajar?->tgl_seleksi;
            $tglSeleksiFormat = $tglSeleksiRaw
                ? \Carbon\Carbon::parse($tglSeleksiRaw)->locale('id')->translatedFormat('l, d F Y')
                : 'Sesuai Jadwal Resmi';
            $jamSeleksi = '08:00 – 10:00 ' . (function_exists('school_timezone_label') ? school_timezone_label() : 'WITA');
        @endphp

        <div class="space-y-6">
            {{-- Kartu Rincian Jadwal Observasi Seleksi (Di Bagian Atas) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-check text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Jadwal Seleksi Observasi Kesiapan Belajar</h3>
                            <p class="text-xs text-slate-500">Tahun Ajaran {{ $tahunajar?->tahun_ajaran ?? '2026/2027' }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-7">
                    <p class="text-sm text-slate-600 leading-relaxed mb-6">
                        Calon siswa diharapkan hadir bersama orang tua/wali ke sekolah untuk mengikuti kegiatan observasi dengan rincian sebagai berikut:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        {{-- Tanggal --}}
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 text-base">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Tanggal Pelaksanaan</span>
                                <span class="font-bold text-slate-900 text-base mt-0.5 block">{{ $tglSeleksiFormat }}</span>
                            </div>
                        </div>

                        {{-- Waktu / Jam --}}
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-base">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Waktu Sesi</span>
                                <span class="font-bold text-slate-900 text-base mt-0.5 block">{{ $jamSeleksi }}</span>
                            </div>
                        </div>

                        {{-- Lokasi --}}
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 text-base">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Tempat / Lokasi</span>
                                <span class="font-bold text-slate-900 text-base mt-0.5 block">Gedung SD Kristen Rantai Damai</span>
                            </div>
                        </div>
                    </div>

                    {{-- Panduan Singkat Kegiatan Observasi --}}
                    <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 sm:p-5">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-purple-600"></i>
                            Hal yang Perlu Diperhatikan:
                        </h4>
                        <ul class="text-xs sm:text-sm text-slate-600 space-y-1.5 list-disc list-inside leading-relaxed">
                            <li>Calon siswa hadir 15 menit sebelum kegiatan observasi dimulai.</li>
                            <li>Mengenakan pakaian seragam TK / pakaian bebas rapi dan bersepatu.</li>
                            <li>Observasi meliputi pemetaan kesiapan belajar siswa (baca, tulis, dan hitung sederhana secara menyenangkan).</li>
                            <li>Hasil pengumuman kelulusan akan diterbitkan di halaman ini setelah proses penilaian seluruh peserta selesai.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Alert Informasi Status Pengumuman (Di Bagian Bawah) --}}
            <div class="bg-blue-50 border border-blue-200 text-blue-900 px-6 py-5 rounded-2xl flex items-start gap-4 shadow-sm">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-info-circle text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-lg mb-1 text-blue-950">Pengumuman Belum Tersedia</h4>
                    <p class="text-sm opacity-90 leading-relaxed text-blue-900">
                        Hasil penilaian seleksi belum dipublikasikan oleh pihak sekolah. Silakan pastikan Anda telah menyelesaikan pembayaran pendaftaran dan hadir mengikuti tes observasi offline di sekolah sesuai jadwal di atas.
                    </p>
                    <hr class="my-3 border-blue-200/80">
                    <p class="text-xs text-blue-700 font-medium">
                        Jika Anda memiliki kendala atau pertanyaan seputar jadwal observasi, silakan hubungi kontak panitia PPDB.
                    </p>
                </div>
            </div>
        </div>
    @else
        @foreach($data as $value)
            @php
                $siswa = $casis ?? $value->calonSiswa ?? $value->casis;
                $isLulus = in_array($value->hasil_seleksi, ['Lulus', 'Lolos']);
                $rataRata = $value->total_nilai !== null ? number_format($value->total_nilai / 3, 1) : '0';
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Detail Hasil Seleksi</h2>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 pb-8 border-b border-slate-100">
                        <div class="space-y-4">
                            <h4 class="font-bold text-slate-700 border-b border-slate-100 pb-2">Data Calon Siswa</h4>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">No. Pendaftaran</p>
                                <p class="font-semibold text-slate-800">{{ $siswa->no_registrasi ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Nama Lengkap</p>
                                <p class="font-semibold text-slate-800">{{ $siswa->nama_calon_siswa ?? ($siswa->nama ?? '-') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Jenis Kelamin</p>
                                <p class="font-semibold text-slate-800">{{ $siswa->jenis_kelamin ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Tempat, Tanggal Lahir</p>
                                <p class="font-semibold text-slate-800">
                                    {{ $siswa->tmpt_lahir ?? '-' }}, 
                                    {{ $siswa->tgl_lahir ? \Carbon\Carbon::parse($siswa->tgl_lahir)->format('d F Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Tanggal Seleksi</p>
                                <p class="font-semibold text-slate-800">
                                    {{ $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->format('d F Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="font-bold text-slate-700 border-b border-slate-100 pb-2">Rincian Nilai Observasi</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-slate-50 p-3 rounded-xl text-center border border-slate-100">
                                    <p class="text-xs text-slate-500 font-semibold mb-1">Membaca</p>
                                    <p class="font-bold text-slate-800 text-lg">{{ $value->nilai_baca ?? '0' }}</p>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl text-center border border-slate-100">
                                    <p class="text-xs text-slate-500 font-semibold mb-1">Menulis</p>
                                    <p class="font-bold text-slate-800 text-lg">{{ $value->nilai_tulis ?? '0' }}</p>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl text-center border border-slate-100">
                                    <p class="text-xs text-slate-500 font-semibold mb-1">Berhitung</p>
                                    <p class="font-bold text-slate-800 text-lg">{{ $value->nilai_hitung ?? '0' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                                <div class="text-center">
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Total Nilai</p>
                                    <p class="font-black text-blue-600 text-2xl">{{ $value->total_nilai ?? '0' }}</p>
                                </div>
                                <div class="text-center border-l border-blue-100">
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Nilai Rata-rata</p>
                                    <p class="font-black text-amber-500 text-2xl">{{ $rataRata }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isLulus)
                        <div class="bg-green-100 border border-green-200 text-green-800 px-6 py-8 rounded-2xl text-center shadow-sm relative overflow-hidden mb-8">
                            <div class="absolute -right-6 -top-6 text-green-500/20 text-9xl">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-lg shadow-green-500/30">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h3 class="text-2xl font-bold mb-2">Selamat, Anda Dinyatakan LULUS Seleksi!</h3>
                                <p class="text-green-700 font-medium">Silakan segera melakukan proses daftar ulang melalui tombol di bawah ini.</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-6 rounded-2xl text-center shadow-sm relative overflow-hidden mb-8">
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-red-500 text-white rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-lg shadow-red-500/30">
                                    <i class="fas fa-times"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Mohon Maaf, Anda Dinyatakan Tidak Lulus</h3>
                                <p class="text-red-600">Terima kasih atas partisipasi Anda dalam seleksi PPDB SD Kristen Diakui Rantai Damai.</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @if($siswa)
                            <a href="{{ route('unduh', $siswa->id_calon_siswa) }}" class="w-full sm:w-auto px-6 py-3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-download"></i> Unduh Surat Keterangan (PDF)
                            </a>
                        @endif

                        @if($isLulus)
                            <a href="{{ route('calon_siswa.daftar_ulang.show') }}" class="w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30 hover:-translate-y-0.5">
                                <i class="fas fa-arrow-right"></i> Lanjutkan Daftar Ulang
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
