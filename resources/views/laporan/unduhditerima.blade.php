<!DOCTYPE html>
<html>

<head>
    <title>Laporan Calon Siswa Diterima</title>
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
            background-color: cornflowerblue;
            color: white;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .jalan {
            font-size: 12px;
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

    <h3 style="text-align: center; margin-bottom: 15px; color: #1e3a8a;">Laporan Calon Siswa Baru Diterima (Lulus Seleksi)</h3>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 100px; text-align: center;">No. Pendaftaran</th>
                <th style="width: 105px; text-align: center;">NIK</th>
                <th style="text-align: left;">Nama Lengkap</th>
                <th style="width: 85px; text-align: center;">Tanggal Seleksi</th>
                <th style="width: 50px; text-align: center;">Membaca</th>
                <th style="width: 50px; text-align: center;">Menulis</th>
                <th style="width: 50px; text-align: center;">Berhitung</th>
                <th style="width: 60px; text-align: center;">Total Nilai</th>
                <th style="width: 60px; text-align: center;">Rata-rata</th>
                <th style="width: 100px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $no => $value)
            <tr>
                <td style="text-align: center;">{{ $no + 1 }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $value->casis->no_registrasi ?? '-' }}</td>
                <td style="text-align: center;">{{ $value->casis->nik ?? '-' }}</td>
                <td style="text-align: left; font-weight: bold;">{{ $value->casis->nama ?? '-' }}</td>
                <td style="text-align: center;">{{ $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->format('d-m-Y') : '-' }}</td>
                <td style="text-align: center;">{{ $value->nilai_baca ?? 0 }}</td>
                <td style="text-align: center;">{{ $value->nilai_tulis ?? 0 }}</td>
                <td style="text-align: center;">{{ $value->nilai_hitung ?? 0 }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $value->total_nilai ?? 0 }}</td>
                <td style="text-align: center;">{{ number_format($value->nilai_akhir ?? 0, 2) }}</td>
                <td style="text-align: center; color: green; font-weight: bold;">DITERIMA (LULUS)</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 15px;">Tidak ada data siswa diterima</td>
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
