<!DOCTYPE html>
<html>

<head>
    <title>Laporan Hasil Seleksi Calon Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
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
        Laporan Rekapitulasi Hasil Seleksi Calon Siswa
    </h3>

    @php
        $filters = [];
        if (!empty($filterTahunAjaran)) {
            $filters[] = "Tahun Ajaran: <strong>" . e($filterTahunAjaran->tahun_ajaran) . "</strong>";
        } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
            $filters[] = "Tahun Ajaran ID: <strong>" . e($activeFilter['id_tahun_ajaran']) . "</strong>";
        }

        if (!empty($activeFilter['status'])) {
            $filters[] = "Status Hasil: <strong>" . strtoupper(e($activeFilter['status'])) . "</strong>";
        }

        if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
            $tglMulai = !empty($activeFilter['start_date']) ? \Carbon\Carbon::parse($activeFilter['start_date'])->format('d/m/Y') : 'Awal';
            $tglSelesai = !empty($activeFilter['end_date']) ? \Carbon\Carbon::parse($activeFilter['end_date'])->format('d/m/Y') : 'Akhir';
            $filters[] = "Periode Observasi: <strong>{$tglMulai} s/d {$tglSelesai}</strong>";
        }

        if (!empty($activeFilter['search'])) {
            $filters[] = "Kata Kunci: <strong>\"" . e($activeFilter['search']) . "\"</strong>";
        }

        $filters[] = "Jumlah Data: <strong>" . $data->count() . " Peserta</strong>";
    @endphp

    <div class="filter-info-box">
        {!! implode(' &nbsp;|&nbsp; ', $filters) !!}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 95px;">No. Registrasi</th>
                <th style="width: 105px;">NIK</th>
                <th>Nama Lengkap</th>
                <th style="width: 80px;">Tgl Observasi</th>
                <th style="width: 45px;">Baca</th>
                <th style="width: 45px;">Tulis</th>
                <th style="width: 45px;">Hitung</th>
                <th style="width: 55px;">Total</th>
                <th style="width: 55px;">Rata-rata</th>
                <th style="width: 90px;">Hasil Seleksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $no => $value)
            @php
                $casis = $value->casis;
                $isLulus = in_array($value->hasil_seleksi, ['Lolos', 'Lulus']);
            @endphp
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ $casis->no_registrasi ?? '-' }}</td>
                <td>{{ $casis->nik ?? '-' }}</td>
                <td style="text-align: left;">{{ $casis->nama ?? '-' }}</td>
                <td>{{ $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->format('d-m-Y') : '-' }}</td>
                <td>{{ $value->nilai_baca ?? 0 }}</td>
                <td>{{ $value->nilai_tulis ?? 0 }}</td>
                <td>{{ $value->nilai_hitung ?? 0 }}</td>
                <td><strong>{{ $value->total_nilai ?? 0 }}</strong></td>
                <td>{{ number_format($value->nilai_akhir ?? 0, 2) }}</td>
                <td style="color: {{ $isLulus ? '#047857' : '#b91c1c' }}; font-weight: bold;">
                    {{ $isLulus ? 'LULUS' : 'TIDAK LULUS' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="padding: 15px; color: #666;">Tidak ada data hasil seleksi yang sesuai kriteria filter</td>
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
                <div style="font-weight: bold;">Panitia Seleksi PPDB</div>
                <br><br><br><br>
                <div style="text-decoration: underline; font-weight: bold;">( Panitia PPDB )</div>
            </td>
        </tr>
    </table>
</body>

</html>
