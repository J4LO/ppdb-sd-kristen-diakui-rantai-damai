<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Hasil Seleksi</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 20mm 12mm 20mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #111827;
            font-size: 10pt;
            line-height: 1.4;
        }

        .kop-surat {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo-sekolah {
            width: 70px;
            height: 70px;
            margin-bottom: 6px;
            display: inline-block;
        }

        .kop-surat h1 {
            font-size: 15pt;
            margin: 0;
            font-weight: bold;
            color: #111827;
            letter-spacing: 0.5px;
        }

        .kop-surat p {
            font-size: 9pt;
            margin: 3px 0 0 0;
            color: #4b5563;
        }

        .kop-surat .line {
            border-top: 3px solid #111827;
            border-bottom: 1px solid #111827;
            height: 2px;
            margin: 8px 0 12px 0;
        }

        .judul-surat {
            text-align: center;
            margin-bottom: 12px;
        }

        .judul-surat h3 {
            font-size: 13pt;
            margin: 0;
            text-decoration: underline;
            font-weight: bold;
            letter-spacing: 1px;
            color: #111827;
        }

        .content {
            margin: 0;
        }

        .p-intro {
            margin: 0 0 8px 0;
            text-align: justify;
        }

        /* Tabel Identitas Siswa */
        .table-identitas {
            width: 100%;
            margin: 6px 0 12px 0;
            border-collapse: collapse;
        }

        .table-identitas td {
            padding: 3px 4px;
            font-size: 10pt;
            vertical-align: top;
        }

        .table-identitas .col-label {
            width: 180px;
            color: #374151;
        }

        .table-identitas .col-separator {
            width: 15px;
            text-align: center;
            color: #374151;
        }

        .table-identitas .col-value {
            color: #111827;
        }

        /* Tabel Nilai Observasi (Fixed Width 100%, 20% each column) */
        .subjudul-nilai {
            margin: 10px 0 6px 0;
            font-weight: bold;
            font-size: 10pt;
            color: #1f2937;
        }

        .table-nilai {
            width: 100%;
            margin: 0 0 12px 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-nilai th,
        .table-nilai td {
            border: 1px solid #4b5563;
            padding: 6px 4px;
            text-align: center;
            font-size: 9.5pt;
            width: 20%;
        }

        .table-nilai th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #111827;
        }

        .table-nilai td {
            color: #1f2937;
        }

        /* Status Hasil Seleksi Banner */
        .announcement {
            padding: 9px 14px;
            margin: 12px 0;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            letter-spacing: 0.5px;
        }

        .announcement.success {
            background-color: #dcfce7;
            color: #166534;
            border: 1.5px solid #86efac;
        }

        .announcement.danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #fca5a5;
        }

        .p-closing {
            margin: 8px 0 12px 0;
            text-align: justify;
        }

        /* Tanda Tangan Dua Kolom: Kepala Sekolah di Kiri, Panitia di Kanan */
        .ttd-table {
            width: 100%;
            margin-top: 14px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .ttd-table td {
            vertical-align: bottom;
            padding: 0;
        }

        .ttd-col {
            width: 42%;
            text-align: center;
            font-size: 10pt;
            color: #111827;
        }

        .ttd-tengah {
            width: 16%;
        }

        .ttd-col-kanan {
            text-align: center;
        }

        .ttd-label {
            margin: 0 0 2px 0;
            font-size: 10pt;
        }

        .ttd-jabatan {
            margin: 0;
            font-size: 10pt;
        }

        .ttd-garis {
            border-bottom: 1px solid #111827;
            width: 180px;
            margin: 38px auto 4px auto;
        }

        .ttd-nama {
            margin: 0;
            font-size: 10pt;
            font-weight: bold;
        }

        /* Catatan Daftar Ulang */
        .note-box {
            margin-top: 8px;
            font-size: 9pt;
            color: #374151;
            background-color: #f9fafb;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            line-height: 1.4;
        }

        .note-title {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
            font-size: 9.5pt;
        }

        .note-list {
            margin: 0;
            padding-left: 18px;
        }

        .note-list li {
            margin-bottom: 2px;
        }

        .note-list li:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

@php
    $logoJpg = public_path('images/logo-sd-kristen-diakui-rantai-damai.jpg');
    $logoPng = public_path('images/logo-sd-kristen-diakui-rantai-damai.png');

    if (file_exists($logoJpg)) {
        $logoSrc = $logoJpg;
    } elseif (file_exists($logoPng)) {
        $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPng));
    } else {
        $logoSrc = null;
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
        $namaKepalaSekolah = $aktivTahunAjaran?->nama_kepala_sekolah 
            ?? (\App\Models\TahunAjaran::where('status', 'Berlangsung')->value('nama_kepala_sekolah') ?? config('sekolah.kepala_sekolah', 'Serni, S.Pd.K'));
    }

    if (empty($namaPanitia)) {
        $panitia = \App\Models\User::where('role', 'Panitia')->first();
        $namaPanitia = $panitia?->nama ?? 'Panitia PPDB';
    }
@endphp

<body>
    <div class="kop-surat">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo SD Kristen Diakui Rantai Damai" class="logo-sekolah">
        @endif
        <h1>SD KRISTEN DIAKUI RANTAI DAMAI</h1>
        <p>Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kabupaten Luwu, Sulawesi Selatan</p>
        <div class="line"></div>
    </div>

    <div class="judul-surat">
        <h3>SURAT KETERANGAN HASIL SELEKSI</h3>
    </div>

    <div class="content">
        <p class="p-intro">
            Berdasarkan kegiatan observasi dan evaluasi seleksi calon peserta didik baru Tahun Ajaran <strong>{{ $aktivTahunAjaran->tahun_ajaran ?? ($aktivTahunAjaran->tahun_ajar ?? date('Y')) }}</strong>, panitia penerimaan siswa baru menerangkan bahwa calon siswa:
        </p>

        <table class="table-identitas">
            <tr>
                <td class="col-label">No. Pendaftaran</td>
                <td class="col-separator">:</td>
                <td class="col-value"><strong>{{ $casis->no_registrasi ?? ($data->calonSiswa->no_registrasi ?? '-') }}</strong></td>
            </tr>
            <tr>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-separator">:</td>
                <td class="col-value"><strong>{{ $casis->nama_calon_siswa ?? ($data->calonSiswa->nama_calon_siswa ?? '-') }}</strong></td>
            </tr>
            <tr>
                <td class="col-label">Tempat, Tanggal Lahir</td>
                <td class="col-separator">:</td>
                <td class="col-value">{{ $casis->tmpt_lahir ?? '-' }}, {{ $casis->tgl_lahir ? \Carbon\Carbon::parse($casis->tgl_lahir)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">Jenis Kelamin</td>
                <td class="col-separator">:</td>
                <td class="col-value">{{ $casis->jenis_kelamin ?? '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">Nama Orang Tua (Ayah)</td>
                <td class="col-separator">:</td>
                <td class="col-value">{{ $casis->nama_ayah ?? ($data->calonSiswa->nama_ayah ?? '-') }}</td>
            </tr>
        </table>

        <div class="subjudul-nilai">Rincian Nilai Observasi:</div>
        <table class="table-nilai">
            <thead>
                <tr>
                    <th>Membaca</th>
                    <th>Menulis</th>
                    <th>Berhitung</th>
                    <th>Total Nilai</th>
                    <th>Nilai Rata-Rata</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $data->nilai_baca ?? 0 }}</td>
                    <td>{{ $data->nilai_tulis ?? 0 }}</td>
                    <td>{{ $data->nilai_hitung ?? 0 }}</td>
                    <td><strong>{{ $data->total_nilai ?? 0 }}</strong></td>
                    <td><strong>{{ $data->total_nilai !== null ? number_format($data->total_nilai / 3, 1) : '-' }}</strong></td>
                </tr>
            </tbody>
        </table>

        @php
            $isLulus = in_array($data->hasil_seleksi, ['Lulus', 'Lolos']);
        @endphp
        <div class="announcement {{ $isLulus ? 'success' : 'danger' }}">
            @if($isLulus)
                DINYATAKAN LULUS SELEKSI
            @else
                BELUM DINYATAKAN LULUS SELEKSI
            @endif
        </div>

        <p class="p-closing">
            Demikian surat keterangan hasil seleksi ini diterbitkan untuk dipergunakan sebagaimana mestinya.
        </p>

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

                <!-- Kolom Kanan: Panitia Seleksi PPDB -->
                <td class="ttd-col ttd-col-kanan">
                    <p class="ttd-label">{{ $kotaSekolah }}, {{ $tanggalCetak }}</p>
                    <p class="ttd-jabatan">Panitia Seleksi PPDB</p>
                    <div class="ttd-garis"></div>
                    <p class="ttd-nama">{{ $namaPanitia }}</p>
                </td>
            </tr>
        </table>

        @if($isLulus)
            <div class="note-box">
                <div class="note-title">Catatan Daftar Ulang:</div>
                <ol class="note-list">
                    <li>Calon siswa yang dinyatakan <strong>LULUS</strong> wajib melakukan proses daftar ulang sesuai jadwal yang ditentukan sekolah.</li>
                    <li>Daftar ulang dapat dilakukan secara daring melalui menu <strong>Daftar Ulang</strong> pada portal PPDB atau langsung di sekretariat sekolah.</li>
                    <li>Apabila sampai dengan batas waktu yang ditentukan tidak melakukan daftar ulang, maka calon siswa dianggap <strong>mengundurkan diri</strong>.</li>
                </ol>
            </div>
        @endif
    </div>
</body>

</html>
