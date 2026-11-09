@extends('layout.cetak-laporan')

@section('title', 'Laporan Pembayaran Pendaftaran')
@section('page-size', 'A4 landscape')

@php
    $isDomPdf = true;

    // Menentukan teks tahun ajaran
    $taText = 'Semua';
    if (!empty($filterTahunAjaran)) {
        $taText = $filterTahunAjaran->tahun_ajaran;
    } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
        $taText = $activeFilter['id_tahun_ajaran'];
    } elseif ($data->isNotEmpty() && $data->first()?->calonSiswa?->tahunAjaran) {
        $taText = $data->first()->calonSiswa->tahunAjaran->tahun_ajaran;
    }

    // Menentukan teks periode
    if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
        $tglMulai = !empty($activeFilter['start_date']) ? \Carbon\Carbon::parse($activeFilter['start_date'])->format('d/m/Y') : 'Awal';
        $tglSelesai = !empty($activeFilter['end_date']) ? \Carbon\Carbon::parse($activeFilter['end_date'])->format('d/m/Y') : 'Akhir';
        $periodeText = "{$tglMulai} s/d {$tglSelesai}";
    } else {
        $periodeText = "Seluruh Data";
    }

    $totalNominal = $data->whereIn('status_pembayaran', ['Lunas', 'Diterima', 'Settlement', 'success'])->sum('jml_pembayaran');
    if ($totalNominal == 0) {
        $totalNominal = $data->where('status_bayar', 'Lunas')->sum('jml_pembayaran');
    }
@endphp

@section('judul-laporan', 'LAPORAN PEMBAYARAN FORMULIR PENDAFTARAN')

@section('baris-meta')
    Tahun Ajaran: {{ $taText }} &middot; Periode: {{ $periodeText }}
    @if(!empty($activeFilter['filter_program']))
        &middot; Status: {{ ucfirst(strtolower($activeFilter['filter_program'])) }}
    @endif
    @if(!empty($activeFilter['search']))
        &middot; Kata Kunci: "{{ $activeFilter['search'] }}"
    @endif
@endsection

@section('jumlah-data')
    Jumlah Data: {{ $data->count() }} Transaksi &middot; Total Dana Masuk: Rp {{ number_format($totalNominal, 0, ',', '.') }}
@endsection

@section('konten-tabel')
<table class="laporan-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 15%;">No. Pendaftaran</th>
            <th style="width: 19%;">Order ID</th>
            <th style="width: 22%; text-align: left;">Nama Calon Siswa</th>
            <th style="width: 11%;">Tgl Bayar</th>
            <th style="width: 12%; text-align: right;">Nominal</th>
            <th style="width: 9%;">Metode</th>
            <th style="width: 8%;">Status Pembayaran</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $no => $value)
        @php
            $casis = $value->calonSiswa ?? $value->casis;
            $noReg = $casis?->no_registrasi ?? '-';
            $nama = $casis?->nama_calon_siswa ?? ($casis?->nama ?? '-');
            $tglBayar = $value->tgl_pembayaran ? \Carbon\Carbon::parse($value->tgl_pembayaran)->format('d-m-Y') : '-';
            $nominal = (float) ($value->jml_pembayaran ?? ($value->jumlah_pembayaran ?? 0));
            $metode = $value->metode_pembayaran ?? ($value->channel ?? 'Midtrans');
            $rawStatus = $value->status_pembayaran ?? ($value->status_bayar ?? '-');

            // Normalisasi status pembayaran ke istilah standar tanpa chip berwarna
            $statusText = match (strtoupper((string)$rawStatus)) {
                'LUNAS', 'DITERIMA', 'SETTLEMENT', 'SUCCESS', 'BERHASIL' => 'Lunas',
                'MENUNGGU', 'PENDING', 'BELUM BAYAR' => 'Menunggu Pembayaran',
                'GAGAL', 'DITOLAK' => 'Gagal',
                'EXPIRE', 'KEDALUWARSA', 'EXPIRED' => 'Kedaluwarsa',
                'CANCEL', 'BATAL' => 'Dibatalkan',
                default => ucfirst(strtolower((string)$rawStatus)),
            };
        @endphp
        <tr>
            <td class="col-no">{{ $no + 1 }}</td>
            <td class="col-center font-bold">{{ $noReg }}</td>
            <td class="col-center">{{ $value->order_id ?? '-' }}</td>
            <td class="col-left font-bold">{{ $nama }}</td>
            <td class="col-center">{{ $tglBayar }}</td>
            <td class="col-right font-bold">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
            <td class="col-center">{{ $metode }}</td>
            <td class="col-center">{{ $statusText }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="col-center" style="padding: 16px; color: #6b7280;">Tidak ada data pembayaran yang sesuai kriteria filter</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
