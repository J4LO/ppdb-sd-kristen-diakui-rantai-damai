@extends('layout.tampilan')

@section('page-title', 'Pengumuman Seleksi')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pantauan Pengumuman Seleksi Calon Siswa</h1>
            <p class="text-slate-500 text-sm mt-1">
                Pantau status penilaian dan hasil evaluasi seleksi calon siswa TA {{ $aktivTahunAjaran->tahun_ajaran ?? ($aktivTahunAjaran->tahun_ajar ?? '-') }}.
                Proses penilaian dan publikasi dikelola oleh Panitia PPDB.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xs">
            <i class="fas fa-check-circle"></i>
            <p class="font-medium text-sm">{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xs">
            <i class="fas fa-exclamation-circle"></i>
            <p class="font-medium text-sm">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-center w-12">No</th>
                        <th class="px-6 py-4 font-semibold">Nama Lengkap</th>
                        <th class="px-4 py-4 font-semibold text-center">Baca</th>
                        <th class="px-4 py-4 font-semibold text-center">Tulis</th>
                        <th class="px-4 py-4 font-semibold text-center">Hitung</th>
                        <th class="px-4 py-4 font-semibold text-center">Total Nilai</th>
                        <th class="px-6 py-4 font-semibold text-center">Hasil Otomatis</th>
                        <th class="px-6 py-4 font-semibold text-center">Status Publikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $no => $value)
                    @php
                        $seleksi = $value->pembayaran?->seleksi;
                        $isLulus = $seleksi && $seleksi->hasil_seleksi === 'Lulus';
                        $statusDiumumkan = $seleksi ? $seleksi->isDiumumkan() : false;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors {{ $isLulus ? 'bg-green-50/20' : '' }}">
                        <td class="px-6 py-4 text-center text-slate-500">{{ $no + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800">
                            <div>{{ $value->nama_calon_siswa ?? $value->nama }}</div>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600">{{ $seleksi?->nilai_baca ?? '-' }}</td>
                        <td class="px-4 py-4 text-center text-slate-600">{{ $seleksi?->nilai_tulis ?? '-' }}</td>
                        <td class="px-4 py-4 text-center text-slate-600">{{ $seleksi?->nilai_hitung ?? '-' }}</td>
                        <td class="px-4 py-4 text-center font-bold text-blue-600">{{ $seleksi?->total_nilai ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($seleksi && $seleksi->total_nilai !== null)
                                @if($isLulus)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        <i class="fas fa-check-circle text-[10px]"></i> LULUS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                        <i class="fas fa-times-circle text-[10px]"></i> TIDAK LULUS
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                                    Belum Dinilai
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($statusDiumumkan)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-lock text-[10px]"></i> Terpublikasi
                                </span>
                            @elseif($seleksi)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fas fa-hourglass-half text-[10px]"></i> Menunggu Publikasi
                                </span>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum Dinilai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            Tidak ada data calon siswa untuk seleksi ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
