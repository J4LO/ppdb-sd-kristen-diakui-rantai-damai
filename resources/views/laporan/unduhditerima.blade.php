@extends('layout.cetak-laporan')

@section('title', 'Laporan Calon Siswa Diterima')
@section('page-size', 'A4 landscape')

@php
    $isDomPdf = true;

    // Helper penyamaran NIK
    $maskNik = function($nik) {
        $clean = trim((string)$nik);
        if (empty($clean) || $clean === '-') {
            return '-';
        }
        if (strlen($clean) >= 8) {
            return substr($clean, 0, 4) . '****' . substr($clean, -4);
        }
        return $clean;
    };
@endphp

@section('judul-laporan', 'LAPORAN CALON SISWA DITERIMA (LULUS SELEKSI)')

@section('baris-meta')
    Status: Diterima (Lulus Seleksi) &middot; Tanggal Unduh: {{ $tanggalCetak ?? \Carbon\Carbon::now()->format('d/m/Y') }}
@endsection

@section('jumlah-data')
    Jumlah Data: {{ $data->count() }} Siswa Diterima
@endsection

@section('konten-tabel')
<table class="tabel-laporan">
    <thead>
        <tr>
            <th class="col-no" style="width: 3%;">No</th>
            <th class="col-center" style="width: 12%;">No. Registrasi</th>
            <th class="col-center" style="width: 12%;">NIK</th>
            <th class="col-left" style="width: 19%;">Nama Lengkap</th>
            <th class="col-center" style="width: 10%;">Tgl Observasi</th>
            <th class="col-center" style="width: 7%;">Baca</th>
            <th class="col-center" style="width: 7%;">Tulis</th>
            <th class="col-center" style="width: 7%;">Hitung</th>
            <th class="col-center" style="width: 7%;">Total</th>
            <th class="col-center" style="width: 7%;">Rata-rata</th>
            <th class="col-center" style="width: 9%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $no => $value)
        @php
            $casis = $value->pembayaran?->calonSiswa ?? $value->casis;
            $noReg = $casis?->no_registrasi ?? '-';
            $rawNik = $casis?->nik_calon_siswa ?? ($casis?->nik ?? '-');
            $maskedNik = $maskNik($rawNik);
            $nama = $casis?->nama_calon_siswa ?? ($casis?->nama ?? '-');
            $tglSeleksi = $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->format('d-m-Y') : '-';
        @endphp
        <tr>
            <td class="col-no">{{ $no + 1 }}</td>
            <td class="col-center font-bold">{{ $noReg }}</td>
            <td class="col-center">{{ $maskedNik }}</td>
            <td class="col-left font-bold">{{ $nama }}</td>
            <td class="col-center">{{ $tglSeleksi }}</td>
            <td class="col-center">{{ $value->nilai_baca ?? 0 }}</td>
            <td class="col-center">{{ $value->nilai_tulis ?? 0 }}</td>
            <td class="col-center">{{ $value->nilai_hitung ?? 0 }}</td>
            <td class="col-center font-bold">{{ $value->total_nilai ?? 0 }}</td>
            <td class="col-center">{{ number_format($value->nilai_akhir ?? 0, 2) }}</td>
            <td class="col-center font-bold">LULUS</td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="col-center" style="padding: 16px; color: #6b7280;">Tidak ada data calon siswa diterima</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
