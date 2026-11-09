@extends('layout.cetak-laporan')

@section('title', 'Laporan Rekapitulasi Hasil Seleksi Calon Siswa')
@section('page-size', 'A4 landscape')

@php
    $isDomPdf = true;

    // Menentukan teks tahun ajaran
    $taText = 'Semua';
    if (!empty($filterTahunAjaran)) {
        $taText = $filterTahunAjaran->tahun_ajaran;
    } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
        $taText = $activeFilter['id_tahun_ajaran'];
    } elseif ($data->isNotEmpty() && $data->first()?->pembayaran?->calonSiswa?->tahunAjaran) {
        $taText = $data->first()->pembayaran->calonSiswa->tahunAjaran->tahun_ajaran;
    }

    // Menentukan teks periode
    if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
        $tglMulai = !empty($activeFilter['start_date']) ? \Carbon\Carbon::parse($activeFilter['start_date'])->format('d/m/Y') : 'Awal';
        $tglSelesai = !empty($activeFilter['end_date']) ? \Carbon\Carbon::parse($activeFilter['end_date'])->format('d/m/Y') : 'Akhir';
        $periodeText = "{$tglMulai} s/d {$tglSelesai}";
    } else {
        $periodeText = "Seluruh Data";
    }

    // Helper penyamaran NIK (4 digit awal, ****, 4 digit akhir)
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

@section('judul-laporan', 'LAPORAN REKAPITULASI HASIL SELEKSI CALON SISWA')

@section('baris-meta')
    Tahun Ajaran: {{ $taText }} &middot; Periode Observasi: {{ $periodeText }}
    @if(!empty($activeFilter['status']))
        &middot; Status Hasil: {{ ucfirst(strtolower($activeFilter['status'])) }}
    @endif
    @if(!empty($activeFilter['search']))
        &middot; Kata Kunci: "{{ $activeFilter['search'] }}"
    @endif
@endsection

@section('jumlah-data')
    Jumlah Data: {{ $data->count() }} Peserta
@endsection

@section('konten-tabel')
<table class="laporan-table">
    <thead>
        <tr>
            <th style="width: 3.5%;">No</th>
            <th style="width: 13%;">No. Registrasi</th>
            <th style="width: 12.5%;">NIK</th>
            <th style="width: 19%; text-align: left;">Nama Lengkap</th>
            <th style="width: 11%;">Tgl Observasi</th>
            <th style="width: 6%;">Baca</th>
            <th style="width: 6%;">Tulis</th>
            <th style="width: 6%;">Hitung</th>
            <th style="width: 7%;">Total</th>
            <th style="width: 8%;">Rata-rata</th>
            <th style="width: 8%;">Hasil Seleksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $no => $value)
        @php
            $casis = $value->casis ?? ($value->pembayaran?->calonSiswa ?? null);
            $noReg = $casis?->no_registrasi ?? '-';
            $rawNik = $casis?->nik_calon_siswa ?? ($casis?->nik ?? '-');
            $maskedNik = $maskNik($rawNik);
            $nama = $casis?->nama_calon_siswa ?? ($casis?->nama ?? '-');
            $tglSeleksi = $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->format('d-m-Y') : '-';
            $isLulus = in_array($value->hasil_seleksi, ['Lolos', 'Lulus']);
            $hasilText = $isLulus ? 'LULUS' : 'TIDAK LULUS';
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
            <td class="col-center font-bold">{{ $hasilText }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="col-center" style="padding: 16px; color: #6b7280;">Tidak ada data hasil seleksi yang sesuai kriteria filter</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
