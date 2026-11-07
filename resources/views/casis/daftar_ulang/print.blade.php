<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran dan Daftar Ulang</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            background-color: white;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a5f7a;
            margin: 0;
        }

        .school-address {
            font-size: 11px;
            color: #555;
            margin: 5px 0;
        }

        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #1a5f7a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            margin-bottom: 30px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .info-table th,
        .info-table td {
            padding: 9px 12px;
            border: 1px solid #ddd;
        }

        .info-table th {
            width: 32%;
            text-align: left;
            background-color: #f8f8f8;
            font-weight: bold;
            color: #1a5f7a;
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
            background-color: #e7f3fe;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 20px 0;
            font-size: 13px;
            color: #0c5460;
            line-height: 1.5;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 13px;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            width: 220px;
        }

        .signature-line {
            margin-top: 70px;
            border-bottom: 1px solid #333;
        }

        .notes {
            margin-top: 30px;
            font-size: 11px;
            color: #555;
            background: #fafafa;
            padding: 12px;
            border-left: 3px solid #2563eb;
        }

        .notes h4 {
            color: #1a5f7a;
            margin: 0 0 6px 0;
            font-size: 12px;
        }

        .notes ol {
            padding-left: 20px;
            margin: 0;
            list-style-type: decimal;
        }

        .notes ol li {
            margin-bottom: 4px;
            line-height: 1.5;
        }

        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
                padding: 15mm;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h1 class="school-name">SD KRISTEN DIAKUI RANTAI DAMAI</h1>
            <p class="school-address">Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kabupaten Luwu.</p>
        </div>

        <h2 class="document-title">Tanda Bukti Pembayaran dan Daftar Ulang</h2>

        @php
            $calonSiswa = $daftarUlang->calonSiswa ?? ($casis ?? $daftarUlang->pendaftaran?->casis);
            $namaSiswa = $calonSiswa?->nama_calon_siswa ?? ($calonSiswa?->nama ?? '-');
            $noPendaftaran = $calonSiswa?->no_registrasi ?? ($calonSiswa?->no_pendaftaran ?? '-');
        @endphp

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
                    <td>{{ $daftarUlang->tgl_daftar_ulang ? \Carbon\Carbon::parse($daftarUlang->tgl_daftar_ulang)->format('d F Y') : date('d F Y') }}</td>
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

        <div class="footer">
            <div class="signature-box">
                <p>Rantai Damai, {{ date('d F Y') }}<br>Panitia Penerimaan Siswa Baru,</p>
                <div class="signature-line"></div>
                <p><strong>Panitia PPDB</strong></p>
            </div>
        </div>
    </div>
</body>

</html>
