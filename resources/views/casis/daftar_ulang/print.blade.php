<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran dan Daftar Ulang</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #111827;
        }

        .container {
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 20mm;
            margin: 0 auto;
            background-color: white;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .logo-sekolah {
            width: 75px;
            height: 75px;
            margin: 0 auto 8px auto;
            display: block;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .school-address {
            font-size: 11px;
            color: #4b5563;
            margin: 4px 0 0 0;
        }

        .garis-pemisah-ganda {
            border-top: 3px solid #111827;
            border-bottom: 1px solid #111827;
            height: 2px;
            margin: 10px 0 16px 0;
            width: 100%;
        }

        .document-title {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 16px 0;
            text-align: center;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .info-table th,
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
        }

        .info-table th {
            width: 32%;
            text-align: left;
            background-color: #f9fafb;
            font-weight: 600;
            color: #1f2937;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
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

        .announcement {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            padding: 10px 14px;
            margin: 14px 0;
            font-size: 12px;
            color: #166534;
            line-height: 1.5;
            border-radius: 4px;
        }

        .notes {
            margin-top: 14px;
            font-size: 11px;
            color: #4b5563;
            background: #f8fafc;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #2563eb;
            border-radius: 4px;
        }

        .notes h4 {
            color: #1e3a8a;
            margin: 0 0 6px 0;
            font-size: 12px;
            font-weight: 600;
        }

        .notes ol {
            padding-left: 18px;
            margin: 0;
            list-style-type: decimal;
        }

        .notes ol li {
            margin-bottom: 3px;
            line-height: 1.45;
        }

        .notes ol li:last-child {
            margin-bottom: 0;
        }

        /* Tanda Tangan Dua Kolom: Kepala Sekolah di Kiri, Panitia di Kanan */
        .ttd-table {
            width: 100%;
            margin-top: 28px;
            margin-bottom: 8px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .ttd-table td {
            vertical-align: bottom;
            padding: 0;
            border: none;
        }

        .ttd-col {
            width: 40%;
            text-align: center;
            font-size: 12px;
            color: #111827;
        }

        .ttd-tengah {
            width: 20%;
        }

        .ttd-col-kanan {
            text-align: center;
        }

        .ttd-label {
            margin: 0 0 3px 0;
            font-size: 12px;
        }

        .ttd-jabatan {
            margin: 0;
            font-size: 12px;
            font-weight: 500;
        }

        .ttd-garis {
            border-bottom: 1px solid #111827;
            width: 180px;
            margin: 50px auto 5px auto;
        }

        .ttd-nama {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
        }

        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
                padding: 12mm 15mm;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>

@php
    $logoPng = public_path('images/logo-sd-kristen-diakui-rantai-damai.png');
    $logoJpg = public_path('images/logo-sd-kristen-diakui-rantai-damai.jpg');

    if (file_exists($logoPng)) {
        $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPng));
    } elseif (file_exists($logoJpg)) {
        $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoJpg));
    } else {
        $logoSrc = asset('images/logo-sd-kristen-diakui-rantai-damai.png');
    }

    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $now = \Carbon\Carbon::now();
    $tanggalCetak = $tanggalCetak ?? ($now->day . ' ' . $bulanIndo[$now->month] . ' ' . $now->year);
    $kotaSekolah = $kotaSekolah ?? config('sekolah.kota', 'Rantai Damai');

    if (empty($namaKepalaSekolah)) {
        $namaKepalaSekolah = $tahunAjar?->nama_kepala_sekolah 
            ?? (\App\Models\TahunAjaran::where('status', 'Berlangsung')->value('nama_kepala_sekolah') ?? config('sekolah.kepala_sekolah', 'Serni, S.Pd.K'));
    }

    if (empty($namaPanitia)) {
        $panitia = \App\Models\User::where('role', 'Panitia')->first();
        $namaPanitia = $panitia?->nama ?? 'Panitia PPDB';
    }

    $calonSiswa = $daftarUlang->calonSiswa ?? ($casis ?? $daftarUlang->pendaftaran?->casis);
    $namaSiswa = $calonSiswa?->nama_calon_siswa ?? ($calonSiswa?->nama ?? '-');
    $noPendaftaran = $calonSiswa?->no_registrasi ?? ($calonSiswa?->no_pendaftaran ?? '-');
@endphp

<body onload="window.print()">
    <div class="container">
        <div class="header">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" alt="Logo SD Kristen Diakui Rantai Damai" class="logo-sekolah">
            @endif
            <h1 class="school-name">SD KRISTEN DIAKUI RANTAI DAMAI</h1>
            <p class="school-address">Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kabupaten Luwu, Sulawesi Selatan</p>
            <div class="garis-pemisah-ganda"></div>
        </div>

        <h2 class="document-title">Tanda Bukti Pembayaran dan Daftar Ulang</h2>

        <div class="content">
            <table class="info-table">
                <tr>
                    <th>No. Pendaftaran</th>
                    <td><strong>{{ $noPendaftaran }}</strong></td>
                </tr>
                <tr>
                    <th>Nama Lengkap Siswa</th>
                    <td><strong>{{ $namaSiswa }}</strong></td>
                </tr>
                <tr>
                    <th>Tahun Ajaran</th>
                    <td>{{ $tahunAjar->tahun_ajaran ?? ($tahunAjar->tahun_ajar ?? date('Y')) }}</td>
                </tr>
                <tr>
                    <th>Opsi Pembayaran</th>
                    <td>{{ $daftarUlang->metode_pembayaran }}</td>
                </tr>
                <tr>
                    <th>Nominal Terbayar</th>
                    <td><strong>Rp {{ number_format($daftarUlang->jumlah_bayar, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <th>Status Administrasi</th>
                    <td>
                        {{ $daftarUlang->status_daftar_ulang == 'Selesai' ? 'Selesai (Lunas Penuh)' : $daftarUlang->status_bayar }}
                    </td>
                </tr>
                <tr>
                    <th>Tanggal Konfirmasi</th>
                    <td>{{ $daftarUlang->tgl_daftar_ulang ? \Carbon\Carbon::parse($daftarUlang->tgl_daftar_ulang)->translatedFormat('d F Y') : date('d F Y') }}</td>
                </tr>
            </table>

            <div class="announcement">
                Tanda bukti ini menyatakan bahwa administrasi pendaftaran ulang calon siswa di atas telah tercatat secara resmi di sistem PPDB SD Kristen Diakui Rantai Damai.
            </div>

            <div class="notes">
                <h4>Informasi dan Ketentuan:</h4>
                <ol>
                    <li>Tanda bukti pendaftaran ulang ini adalah dokumen sah dan harap disimpan oleh orang tua/wali murid.</li>
                    <li>Informasi mengenai pembagian kelas dan jadwal tahun ajaran baru akan diberitahukan pada saat penyerahan berkas ke sekolah.</li>
                    <li>Bagi yang memilih metode pembayaran cicilan, batas pelunasan sisa biaya adalah maksimal 3 bulan sejak tahun ajaran dimulai.</li>
                </ol>
            </div>
        </div>

        <table class="ttd-table">
            <tr>
                <!-- Kolom Kiri: Kepala Sekolah -->
                <td class="ttd-col">
                    <p class="ttd-label">Mengetahui,</p>
                    <p class="ttd-jabatan">Kepala Sekolah</p>
                    <div class="ttd-garis"></div>
                    <p class="ttd-nama">{{ $namaKepalaSekolah }}</p>
                </td>

                <!-- Sel Tengah: Ruang Kosong -->
                <td class="ttd-tengah"></td>

                <!-- Kolom Kanan: Panitia Penerimaan Siswa Baru -->
                <td class="ttd-col ttd-col-kanan">
                    <p class="ttd-label">{{ $kotaSekolah }}, {{ $tanggalCetak }}</p>
                    <p class="ttd-jabatan">Panitia Penerimaan Siswa Baru</p>
                    <div class="ttd-garis"></div>
                    <p class="ttd-nama">{{ $namaPanitia }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
