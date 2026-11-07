<!DOCTYPE html>
<html>

<head>
    <title>Laporan Data Pembayaran Pendaftaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px 8px;
            text-align: center;
            white-space: nowrap;
        }

        th {
            background-color: #059669;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }

        .filter-info-box {
            margin-bottom: 15px;
            font-size: 11px;
            color: #333333;
            text-align: center;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .jalan {
            font-size: 11px;
            margin-bottom: 5px;
            color: #444;
        }

        .line {
            border: none;
            border-top: 2px solid black;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <div class="center title">SD KRISTEN DIAKUI RANTAI DAMAI</div>
    <div class="center jalan">Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kab. Luwu, Sulawesi Selatan</div>
    <div class="center line"></div>

    <h3 style="text-align: center; margin-bottom: 6px; font-size: 15px; text-transform: uppercase;">
        Laporan Rekapitulasi Pembayaran Formulir Pendaftaran
    </h3>

    @php
        $filters = [];
        if (!empty($filterTahunAjaran)) {
            $filters[] = "Tahun Ajaran: <strong>" . e($filterTahunAjaran->tahun_ajaran) . "</strong>";
        } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
            $filters[] = "Tahun Ajaran ID: <strong>" . e($activeFilter['id_tahun_ajaran']) . "</strong>";
        }

        if (!empty($activeFilter['filter_program'])) {
            $filters[] = "Status Pembayaran: <strong>" . strtoupper(e($activeFilter['filter_program'])) . "</strong>";
        }

        if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
            $tglMulai = !empty($activeFilter['start_date']) ? \Carbon\Carbon::parse($activeFilter['start_date'])->format('d/m/Y') : 'Awal';
            $tglSelesai = !empty($activeFilter['end_date']) ? \Carbon\Carbon::parse($activeFilter['end_date'])->format('d/m/Y') : 'Akhir';
            $filters[] = "Periode Bayar: <strong>{$tglMulai} s/d {$tglSelesai}</strong>";
        }

        if (!empty($activeFilter['search'])) {
            $filters[] = "Kata Kunci: <strong>\"" . e($activeFilter['search']) . "\"</strong>";
        }

        $totalNominal = $data->whereIn('status_pembayaran', ['Lunas', 'Diterima'])->sum('jml_pembayaran');
        if ($totalNominal == 0) {
            $totalNominal = $data->where('status_bayar', 'Lunas')->sum('jml_pembayaran');
        }

        $filters[] = "Jumlah Transaksi: <strong>" . $data->count() . " Transaksi</strong>";
        $filters[] = "Total Dana Masuk: <strong>Rp " . number_format($totalNominal, 0, ',', '.') . "</strong>";
    @endphp

    <div class="filter-info-box">
        {!! implode(' &nbsp;|&nbsp; ', $filters) !!}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 110px; text-align: center;">No. Pendaftaran</th>
                <th style="width: 190px; text-align: center;">Order ID</th>
                <th style="text-align: left;">Nama Calon Siswa</th>
                <th style="width: 85px; text-align: center;">Tgl Bayar</th>
                <th style="width: 95px; text-align: right;">Nominal</th>
                <th style="width: 120px; text-align: center;">Metode</th>
                <th style="width: 75px; text-align: center;">Status</th>
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
                $metode = $value->metode_pembayaran ?? ($value->channel ?? 'Midtrans Gateway');
                $status = $value->status_pembayaran ?? ($value->status_bayar ?? '-');
                $isLunas = in_array(strtoupper($status), ['LUNAS', 'DITERIMA', 'SETTLEMENT', 'SUCCESS']);
            @endphp
            <tr>
                <td style="text-align: center;">{{ $no + 1 }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $noReg }}</td>
                <td style="text-align: center; font-family: monospace;">{{ $value->order_id ?? '-' }}</td>
                <td style="text-align: left; font-weight: bold;">{{ $nama }}</td>
                <td style="text-align: center;">{{ $tglBayar }}</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $metode }}</td>
                <td style="color: {{ $isLunas ? '#047857' : '#b91c1c' }}; font-weight: bold; text-align: center;">{{ $status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding: 15px; color: #666; text-align: center;">Tidak ada data pembayaran yang sesuai kriteria filter</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <br><br>
    <table style="border: none; width: 100%;">
        <tr style="border: none;">
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                <div>Rantai Damai, {{ date('d F Y') }}</div>
                <div style="font-weight: bold;">Bendahara / Panitia PPDB</div>
                <br><br><br><br>
                <div style="text-decoration: underline; font-weight: bold;">( Panitia PPDB )</div>
            </td>
        </tr>
    </table>
</body>

</html>
