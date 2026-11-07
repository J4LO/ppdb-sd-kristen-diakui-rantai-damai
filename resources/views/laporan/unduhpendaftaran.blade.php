<!DOCTYPE html>
<html>

<head>
    <title>Laporan Data Pendaftaran Calon Siswa</title>
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
        Laporan Rekapitulasi Data Pendaftaran Calon Siswa
    </h3>

    @php
        $filters = [];
        if (!empty($filterTahunAjaran)) {
            $filters[] = "Tahun Ajaran: <strong>" . e($filterTahunAjaran->tahun_ajaran) . "</strong>";
        } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
            $filters[] = "Tahun Ajaran ID: <strong>" . e($activeFilter['id_tahun_ajaran']) . "</strong>";
        }

        if (!empty($activeFilter['filter_program'])) {
            $filters[] = "Status Pendaftaran: <strong>" . strtoupper(e($activeFilter['filter_program'])) . "</strong>";
        }

        if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
            $tglMulai = !empty($activeFilter['start_date']) ? \Carbon\Carbon::parse($activeFilter['start_date'])->format('d/m/Y') : 'Awal';
            $tglSelesai = !empty($activeFilter['end_date']) ? \Carbon\Carbon::parse($activeFilter['end_date'])->format('d/m/Y') : 'Akhir';
            $filters[] = "Periode Pendaftaran: <strong>{$tglMulai} s/d {$tglSelesai}</strong>";
        }

        if (!empty($activeFilter['search'])) {
            $filters[] = "Kata Kunci: <strong>\"" . e($activeFilter['search']) . "\"</strong>";
        }

        $filters[] = "Jumlah Data: <strong>" . $data->count() . " Pendaftar</strong>";
    @endphp

    <div class="filter-info-box">
        {!! implode(' &nbsp;|&nbsp; ', $filters) !!}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 100px; text-align: center;">No. Pendaftaran</th>
                <th style="width: 105px; text-align: center;">NIK</th>
                <th style="text-align: left;">Nama Lengkap Calon Siswa</th>
                <th style="width: 125px; text-align: center;">Tempat, Tanggal Lahir</th>
                <th style="width: 70px; text-align: center;">Jenis Kelamin</th>
                <th style="text-align: left;">Alamat</th>
                <th style="width: 90px; text-align: center;">No. Telepon / WA</th>
                <th style="width: 75px; text-align: center;">Tanggal Daftar</th>
                <th style="width: 65px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $no => $value)
            @php
                $casis = $value->casis ?? $value;
                $noReg = $value->no_registrasi ?? ($casis->no_registrasi ?? '-');
                $nik = $value->nik_calon_siswa ?? ($value->nik ?? ($casis->nik_calon_siswa ?? ($casis->nik ?? '-')));
                $nama = $value->nama_calon_siswa ?? ($value->nama ?? ($casis->nama ?? '-'));
                $tmpt = $value->tmpt_lahir ?? ($value->tempat_lahir ?? ($casis->tempat_lahir ?? '-'));
                $tgl = $value->tgl_lahir ?? ($value->tanggal_lahir ?? ($casis->tanggal_lahir ?? null));
                $jk = $value->jenis_kelamin ?? ($casis->jenis_kelamin ?? '-');
                $alamat = $value->alamat_calon_siswa ?? ($value->alamat ?? ($casis->alamat ?? '-'));
                $noHp = $value->no_hp_orang_tua ?? ($value->no_hp ?? ($casis->no_hp ?? '-'));
                $tglDaftar = $value->tgl_pendaftaran ?? ($casis->tgl_pendaftaran ?? null);
                $status = $value->status_pendaftaran ?? ($casis->status_pendaftaran ?? 'Menunggu');
            @endphp
            <tr>
                <td style="text-align: center;">{{ $no + 1 }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $noReg }}</td>
                <td style="text-align: center;">{{ $nik }}</td>
                <td style="text-align: left; font-weight: bold;">{{ $nama }}</td>
                <td style="text-align: center;">{{ $tmpt }}, {{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d-m-Y') : '-' }}</td>
                <td style="text-align: center;">{{ $jk }}</td>
                <td style="text-align: left;">{{ $alamat }}</td>
                <td style="text-align: center;">{{ $noHp }}</td>
                <td style="text-align: center;">{{ $tglDaftar ? \Carbon\Carbon::parse($tglDaftar)->format('d-m-Y') : '-' }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="padding: 15px; color: #666; text-align: center;">Tidak ada data pendaftaran yang sesuai kriteria filter</td>
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
                <div style="font-weight: bold;">Panitia PPDB</div>
                <br><br><br><br>
                <div style="text-decoration: underline; font-weight: bold;">( Panitia PPDB )</div>
            </td>
        </tr>
    </table>
</body>

</html>
