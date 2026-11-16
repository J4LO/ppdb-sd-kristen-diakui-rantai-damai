@extends('layout.cetak-laporan')

@section('title', 'Laporan Daftar Ulang Calon Siswa')
@section('page-size', 'A4 landscape')

@php
    $autoPrint = true;

    // Menentukan teks tahun ajaran
    $taText = 'Semua';
    if (!empty($filterTahunAjaran)) {
        $taText = $filterTahunAjaran->tahun_ajaran;
    } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
        $taText = $activeFilter['id_tahun_ajaran'];
    } elseif ($daftarUlang->isNotEmpty()) {
        $firstCasis = $daftarUlang->first()->calonSiswa ?? ($daftarUlang->first()->pendaftaran?->casis ?? ($daftarUlang->first()->seleksi?->pembayaran?->calonSiswa ?? null));
        if ($firstCasis?->tahunAjaran) {
            $taText = $firstCasis->tahunAjaran->tahun_ajaran;
        }
    }

    // Menentukan teks periode
    if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
        $tglMulai = !empty($activeFilter['start_date']) ? date('d/m/Y', strtotime($activeFilter['start_date'])) : 'Awal';
        $tglSelesai = !empty($activeFilter['end_date']) ? date('d/m/Y', strtotime($activeFilter['end_date'])) : 'Akhir';
        $periodeText = "{$tglMulai} s/d {$tglSelesai}";
    } elseif (!empty($periodText) && $periodText !== 'Periode: Seluruh Data') {
        $periodeText = str_replace('Periode: ', '', $periodText);
    } else {
        $periodeText = "Seluruh Data";
    }

    $totalNominal = $daftarUlang->sum('jumlah_bayar');
@endphp

@section('judul-laporan', 'LAPORAN DAFTAR ULANG CALON SISWA')

@section('baris-meta')
    Tahun Ajaran: {{ $taText }} &middot; Periode: {{ $periodeText }}
    @if(!empty($activeFilter['status']))
        &middot; Status: {{ ucfirst(strtolower($activeFilter['status'])) }}
    @endif
    @if(!empty($activeFilter['status_berkas']))
        &middot; Status Berkas: {{ ucfirst(strtolower($activeFilter['status_berkas'])) }}
    @endif
    @if(!empty($activeFilter['search']))
        &middot; Kata Kunci: "{{ $activeFilter['search'] }}"
    @endif
@endsection

@section('jumlah-data')
    Jumlah Data: {{ $daftarUlang->count() }} Siswa &middot; Total Terbayar: Rp {{ number_format($totalNominal, 0, ',', '.') }}
@endsection

@section('konten-tabel')
<table class="laporan-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 15%;">No. Registrasi</th>
            <th style="width: 21%; text-align: left;">Nama Lengkap</th>
            <th style="width: 13%;">Tgl Daftar Ulang</th>
            <th style="width: 15%;">Metode Pembayaran</th>
            <th style="width: 13%; text-align: right;">Jumlah Terbayar</th>
            <th style="width: 10%;">Status Berkas</th>
            <th style="width: 9%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($daftarUlang as $index => $item)
        @php
            $casis = $item->calonSiswa ?? ($item->pendaftaran?->casis ?? ($item->seleksi?->pembayaran?->calonSiswa ?? null));
            $nama = $casis?->nama_calon_siswa ?? ($casis?->nama ?? '-');
            $noReg = $casis?->no_registrasi ?? '-';
            $tglDu = $item->tgl_daftar_ulang ? date('d-m-Y', strtotime($item->tgl_daftar_ulang)) : '-';
            
            // Penggantian istilah "Cicilan (DP)" menjadi "Cicilan Pertama" pada tampilan Blade
            $rawMetode = $item->metode_pembayaran ?? 'Gateway / Tunai';
            $metode = str_replace('Cicilan (DP)', 'Cicilan Pertama', $rawMetode);
            
            $nominal = (float) ($item->jumlah_bayar ?? 0);
            $rawStatus = $item->status_bayar ?? ($item->status_du ?? 'Berhasil');
            $rawStatusBerkas = $item->status_berkas ?? 'Menunggu';
            
            // Normalisasi status berkas tanpa chip berwarna
            $statusBerkasText = match (strtoupper((string)$rawStatusBerkas)) {
                'DITERIMA', 'SUDAH DITERIMA', 'LENGKAP' => 'Diterima',
                'DITOLAK' => 'Ditolak',
                default => 'Menunggu',
            };

            // Normalisasi status daftar ulang tanpa chip berwarna
            $statusText = match (strtoupper((string)$rawStatus)) {
                'BERHASIL', 'LUNAS', 'SELESAI' => 'Selesai',
                'MENUNGGU KONFIRMASI', 'MENUNGGU', 'PENDING' => 'Menunggu',
                'BATAL', 'GAGAL', 'DITOLAK' => 'Batal',
                default => ucfirst(strtolower((string)$rawStatus)),
            };
        @endphp
        <tr>
            <td class="col-no">{{ $index + 1 }}</td>
            <td class="col-center font-bold">{{ $noReg }}</td>
            <td class="col-left font-bold">{{ $nama }}</td>
            <td class="col-center">{{ $tglDu }}</td>
            <td class="col-center">{{ $metode }}</td>
            <td class="col-right font-bold">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
            <td class="col-center">{{ $statusBerkasText }}</td>
            <td class="col-center">{{ $statusText }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="col-center" style="padding: 16px; color: #6b7280;">Tidak ada data daftar ulang yang sesuai kriteria filter</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
