<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laporan PPDB')</title>
    <style>
        @page {
            size: @yield('page-size', 'A4 landscape');
            margin: 15mm 15mm 15mm 15mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Semua teks menggunakan font seragam keluarga Arial / Helvetica sans-serif */
        html, body, table, th, td, p, div, span, h1, h2, h3, a, strong {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body {
            font-size: 11px;
            color: #111827;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }

        .laporan-wrapper {
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .header-wrapper {
            text-align: center;
            margin-bottom: 0;
        }

        .logo-sekolah {
            width: 80px;
            height: 80px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .nama-sekolah {
            font-size: 16px;
            font-weight: 700;
            color: #000000;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .alamat-sekolah {
            font-size: 11px;
            color: #374151;
            margin-top: 4px;
            margin-bottom: 0;
            line-height: 1.3;
        }

        /* Garis Pemisah Ganda Kop Surat */
        .garis-pemisah-ganda {
            border-top: 3px solid #000000;
            border-bottom: 1px solid #000000;
            padding-top: 3px;
            margin-top: 10px;
            margin-bottom: 14px;
            width: 100%;
        }

        /* Judul dan Meta Laporan */
        .judul-laporan {
            font-size: 13px;
            font-weight: 700;
            color: #000000;
            text-transform: uppercase;
            text-align: center;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .baris-meta {
            font-size: 11px;
            color: #4b5563;
            text-align: center;
            margin: 0 0 2px 0;
        }

        .baris-jumlah-data {
            font-size: 11px;
            color: #4b5563;
            text-align: center;
            margin: 0 0 14px 0;
        }

        /* Tabel Data Seragam dengan Layout Proporsional */
        table.laporan-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        table.laporan-table thead {
            display: table-header-group;
        }

        table.laporan-table thead tr th {
            background-color: #15803d !important;
            color: #ffffff !important;
            font-weight: 600;
            text-align: center;
            padding: 7px 6px;
            border: 1px solid #15803d;
            font-size: 11px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.laporan-table tbody tr {
            page-break-inside: avoid;
        }

        table.laporan-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        table.laporan-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        table.laporan-table tbody td {
            padding: 6px 6px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
            color: #111827;
            font-size: 11px;
            word-wrap: break-word;
        }

        /* Kolom alignment helpers */
        .col-no {
            text-align: center;
        }

        .col-center {
            text-align: center;
        }

        .col-left {
            text-align: left;
        }

        .col-right {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .font-bold {
            font-weight: 700;
        }

        /* Footer Tanda Tangan */
        .footer-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .footer-kiri {
            width: 55%;
            font-size: 10px;
            color: #6b7280;
            vertical-align: bottom !important;
        }

        .footer-kanan {
            width: 45%;
            text-align: center;
            font-size: 11px;
            color: #111827;
        }

        .garis-tanda-tangan {
            border-bottom: 1px solid #000000;
            width: 180px;
            margin: 40px auto 4px auto;
        }

        /* Tanda Tangan Dua Kolom */
        .ttd-table {
            width: 100%;
            margin-top: 32px;
            border-collapse: collapse;
        }

        .ttd-table td {
            vertical-align: bottom;
            padding: 0;
        }

        .ttd-col {
            width: 38%;
            text-align: center;
            font-size: 11px;
            color: #111827;
        }

        .ttd-tengah {
            width: 24%;
        }

        .ttd-col-kanan {
            text-align: center;
        }

        .ttd-label {
            margin: 0 0 2px 0;
            font-size: 11px;
        }

        .ttd-jabatan {
            margin: 0;
            font-size: 11px;
        }

        .ttd-garis {
            border-bottom: 1px solid #111827;
            width: 180px;
            margin: 40px auto 4px auto;
        }

        .ttd-nama {
            margin: 0;
            font-size: 11px;
            font-weight: 600;
        }

        /* Tombol print & elemen non-cetak */
        .no-print-bar {
            background-color: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 24px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .btn-print {
            background-color: #15803d;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: #166534;
        }

        .btn-back {
            background-color: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            .no-print, .no-print-bar, nav, aside, .btn {
                display: none !important;
            }

            body {
                padding: 0 !important;
                margin: 0 !important;
                background-color: #ffffff !important;
            }

            .laporan-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>

    @if(!isset($isDomPdf) || !$isDomPdf)
    <style>
        @media screen {
            body {
                background-color: #f1f5f9 !important;
                padding: 20px 0 40px 0 !important;
            }

            .laporan-wrapper {
                max-width: 1060px !important;
                margin: 0 auto !important;
                padding: 24px 32px !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.06) !important;
                border-radius: 6px !important;
            }
        }
    </style>
    @endif

    @yield('extra-styles')
</head>
<body @if(isset($autoPrint) && $autoPrint) onload="window.print()" @endif>
    {{-- Bar aksi cetak untuk preview browser --}}
    @if(!isset($isDomPdf) || !$isDomPdf)
    <div class="no-print-bar no-print">
        <a href="javascript:history.back()" class="btn-back">&larr; Kembali</a>
        <button type="button" onclick="window.print()" class="btn-print">
            <span>Cetak Dokumen</span>
        </button>
    </div>
    @endif

    <div class="laporan-wrapper">
        {{-- A. Header Laporan (Identik untuk Semua) --}}
        <div class="header-wrapper">
            @php
                $logoJpg = public_path('images/logo-sd-kristen-diakui-rantai-damai.jpg');
                $logoPng = public_path('images/logo-sd-kristen-diakui-rantai-damai.png');

                // Format lokal JPG digunakan untuk DomPDF karena CPDF memproses JPG tanpa ekstensi GD PHP
                if (isset($isDomPdf) && $isDomPdf && file_exists($logoJpg)) {
                    $logoSrc = $logoJpg;
                } elseif (file_exists($logoPng)) {
                    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPng));
                } elseif (file_exists($logoJpg)) {
                    $logoSrc = $logoJpg;
                } else {
                    $logoSrc = null;
                }
            @endphp

            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo SD Kristen Diakui Rantai Damai" class="logo-sekolah">
            @else
                {{-- TODO: Logo sekolah tidak ditemukan di public/images/logo-sd-kristen-diakui-rantai-damai.png --}}
            @endif

            <h2 class="nama-sekolah">SD KRISTEN DIAKUI RANTAI DAMAI</h2>
            <p class="alamat-sekolah">Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kab. Luwu, Sulawesi Selatan</p>
            <div class="garis-pemisah-ganda"></div>
        </div>

        {{-- B. Judul dan Meta Laporan --}}
        <h1 class="judul-laporan">@yield('judul-laporan')</h1>
        <div class="baris-meta">@yield('baris-meta')</div>
        <div class="baris-jumlah-data">@yield('jumlah-data')</div>

        {{-- C. Konten Tabel Laporan --}}
        @yield('konten-tabel')

        {{-- D. Footer Laporan (Tanda Tangan Dua Kolom: Kepala Sekolah & Petugas) --}}
        @php
            $bulanIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $now = \Carbon\Carbon::now();
            $tanggalCetak = $tanggalCetak ?? ($now->day . ' ' . $bulanIndo[$now->month] . ' ' . $now->year);
            $kotaSekolah = $kotaSekolah ?? config('sekolah.kota', 'Rantai Damai');

            // 1. Nama Kepala Sekolah: Prioritas dari controller -> Tahun Ajaran aktif -> config
            if (empty($namaKepalaSekolah)) {
                $namaKepalaSekolah = \App\Models\TahunAjaran::where('status', 'Berlangsung')->value('nama_kepala_sekolah')
                    ?? config('sekolah.kepala_sekolah', 'Serni, S.Pd.K');
            }

            // 2. Jabatan & Nama Petugas: Prioritas dari controller -> Auth user -> default
            $authRole = Auth::user()?->role ?? null;
            if (empty($jabatanPetugas)) {
                $jabatanPetugas = $rolePetugas ?? ($authRole === 'Panitia' ? 'Panitia PPDB' : 'Administrator');
            }
            if (empty($namaPetugas)) {
                $namaPetugas = Auth::user()?->nama ?? (Auth::user()?->name ?? ($authRole === 'Panitia' ? 'Panitia PPDB' : 'Administrator'));
            }
        @endphp

        <table class="ttd-table">
            <tr>
                <!-- Kolom kiri: Kepala Sekolah -->
                <td class="ttd-col">
                    <p class="ttd-label">Mengetahui,</p>
                    <p class="ttd-jabatan">Kepala Sekolah</p>
                    <div class="ttd-garis"></div>
                    <p class="ttd-nama">{{ $namaKepalaSekolah ?? 'Kepala Sekolah' }}</p>
                </td>

                <!-- Sel tengah: ruang kosong -->
                <td class="ttd-tengah"></td>

                <!-- Kolom kanan: Petugas pencetak -->
                <td class="ttd-col ttd-col-kanan">
                    <p class="ttd-label">{{ $kotaSekolah ?? 'Rantai Damai' }}, {{ $tanggalCetak }}</p>
                    <p class="ttd-jabatan">Petugas: {{ $jabatanPetugas ?? 'Administrator' }}</p>
                    <div class="ttd-garis"></div>
                    <p class="ttd-nama">{{ $namaPetugas ?? (Auth::user()?->nama ?? (Auth::user()?->name ?? 'Administrator')) }}</p>
                </td>
            </tr>
        </table>
    </div>

    @yield('extra-scripts')
</body>
</html>
