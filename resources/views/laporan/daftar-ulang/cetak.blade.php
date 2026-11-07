<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Daftar Ulang Calon Siswa</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
.period-text{
    font-size: 12px;
    color: #666;
}
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15mm;
            background-color: white;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a5f7a;
            margin: 0;
        }

        .school-address {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #1a5f7a;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #1a5f7a;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 12px;
        }

        .signature-line {
            width: 200px;
            border-bottom: 1px solid #333;
            margin-left: auto;
            margin-top: 50px;
        }

        .status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .text-nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo Sekolah" class="logo">

        <h1 class="school-name">SD KRISTEN DIAKUI RANTAI DAMAI</h1>
        <p class="school-address">Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kab. Luwu, Sulawesi Selatan</p>
    </div>

    <h2 class="document-title">Laporan Daftar Ulang Calon Siswa</h2>

    @php
        $filters = [];
        if (!empty($filterTahunAjaran)) {
            $filters[] = "Tahun Ajaran: <strong>" . e($filterTahunAjaran->tahun_ajaran) . "</strong>";
        } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
            $filters[] = "Tahun Ajaran ID: <strong>" . e($activeFilter['id_tahun_ajaran']) . "</strong>";
        }

        if (!empty($activeFilter['status'])) {
            $filters[] = "Status: <strong>" . strtoupper(e($activeFilter['status'])) . "</strong>";
        }

        if (!empty($activeFilter['status_berkas'])) {
            $filters[] = "Status Berkas: <strong>" . strtoupper(e($activeFilter['status_berkas'])) . "</strong>";
        }

        if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
            $tglMulai = !empty($activeFilter['start_date']) ? date('d/m/Y', strtotime($activeFilter['start_date'])) : 'Awal';
            $tglSelesai = !empty($activeFilter['end_date']) ? date('d/m/Y', strtotime($activeFilter['end_date'])) : 'Akhir';
            $filters[] = "Periode: <strong>{$tglMulai} s/d {$tglSelesai}</strong>";
        } elseif (!empty($periodText) && $periodText !== 'Periode: Seluruh Data') {
            $filters[] = e($periodText);
        }

        if (!empty($activeFilter['search'])) {
            $filters[] = "Kata Kunci: <strong>\"" . e($activeFilter['search']) . "\"</strong>";
        }

        $filters[] = "Jumlah Data: <strong>" . $daftarUlang->count() . " Siswa</strong>";
    @endphp

    <div style="margin-bottom: 15px; font-size: 11px; color: #333333; text-align: center; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 10px;">
        {!! implode(' &nbsp;|&nbsp; ', $filters) !!}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th style="width: 110px; text-align: center;">No. Registrasi</th>
                <th>Nama Lengkap Siswa</th>
                <th style="width: 90px; text-align: center;">Tgl Daftar Ulang</th>
                <th style="width: 100px;">Metode Pembayaran</th>
                <th style="width: 100px; text-align: right;">Jumlah Bayar</th>
                <th style="width: 90px; text-align: center;">Status Berkas</th>
                <th style="width: 90px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarUlang as $index => $item)
            @php
                $casis = $item->calonSiswa ?? ($item->pendaftaran?->casis ?? ($item->seleksi?->pembayaran?->calonSiswa ?? null));
                $nama = $casis?->nama_calon_siswa ?? ($casis?->nama ?? '-');
                $noReg = $casis?->no_registrasi ?? '-';
                $tglDu = $item->tgl_daftar_ulang ? date('d/m/Y', strtotime($item->tgl_daftar_ulang)) : '-';
                $metode = $item->metode_pembayaran ?? 'Gateway / Tunai';
                $nominal = (float) ($item->jumlah_bayar ?? 0);
                $status = $item->status_bayar ?? ($item->status_du ?? 'Berhasil');
                $statusBerkas = $item->status_berkas ?? 'Menunggu';
                $isBerkasDiterima = in_array($statusBerkas, ['Diterima', 'Sudah Diterima', 'Lengkap']);
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $noReg }}</td>
                <td style="font-weight: bold;">{{ $nama }}</td>
                <td style="text-align: center;">{{ $tglDu }}</td>
                <td>{{ $metode }}</td>
                <td class="text-nowrap" style="text-align: right; font-weight: bold;">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
                <td style="text-align: center;">
                    <span class="status {{ $isBerkasDiterima ? 'status-success' : 'status-pending' }}">
                        {{ $isBerkasDiterima ? 'Diterima' : 'Menunggu' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span class="status {{ in_array($status, ['Berhasil', 'Lunas', 'Selesai']) ? 'status-success' : (in_array($status, ['Menunggu Konfirmasi', 'Menunggu', 'Pending']) ? 'status-pending' : 'status-failed') }}">
                        {{ $status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 15px; color: #666;">Tidak ada data daftar ulang yang sesuai kriteria filter</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>Petugas: {{ Auth::user()?->name ?? 'Administrator' }}</p>
        <div class="signature-line"></div>
        <p>Tanda Tangan Petugas</p>
    </div>
</body>

</html>
