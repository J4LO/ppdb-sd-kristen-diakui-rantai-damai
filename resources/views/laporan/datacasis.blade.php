<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Calon Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px 0;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 650px;
            width: 100%;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 20px;
        }
        .kop-surat h1 {
            color: #1e3a8a;
            font-size: 20px;
            margin: 5px 0;
            font-weight: bold;
        }
        .kop-surat .jalan {
            color: #555;
            font-size: 12px;
            margin: 3px 0;
        }
        .kop-surat .line {
            border-top: 2px solid #333;
            margin: 15px 0;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 18px;
            text-transform: uppercase;
        }
        .data-item {
            margin: 12px 0;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .data-label {
            font-weight: bold;
            color: #475569;
            min-width: 180px;
            position: relative;
            padding-right: 10px;
        }
        .data-label::after {
            content: ":";
            position: absolute;
            right: 0;
        }
        .data-value {
            flex: 1;
            padding-left: 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 12px;
            color: #1e293b;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="kop-surat">
            <h1>SD KRISTEN DIAKUI RANTAI DAMAI</h1>
            <p class="jalan">Jl. Andi Pangeran, Desa Rantai Damai, Kec. Walenrang Timur, Kab. Luwu, Sulawesi Selatan</p>
            <div class="line"></div>
        </div>
        <h2>Data Formulir Calon Siswa</h2>

        <div class="data-item">
            <span class="data-label">NIK Calon Siswa</span>
            <span class="data-value">{{ $casis?->nik_calon_siswa ?? $casis?->nik ?? '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Nama Lengkap</span>
            <span class="data-value"><strong>{{ $casis?->nama_calon_siswa ?? $casis?->nama ?? '-' }}</strong></span>
        </div>
        <div class="data-item">
            <span class="data-label">Tempat, Tanggal Lahir</span>
            <span class="data-value">{{ $casis?->tmpt_lahir ?? '-' }}, {{ $casis?->tgl_lahir ? \Carbon\Carbon::parse($casis->tgl_lahir)->format('d F Y') : '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Jenis Kelamin</span>
            <span class="data-value">{{ $casis?->jenis_kelamin ?? '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Agama</span>
            <span class="data-value">{{ $casis?->agama ?? '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Jumlah Saudara</span>
            <span class="data-value">{{ $casis?->jmlh_saudara ?? 0 }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Nama Ayah / Ibu</span>
            <span class="data-value">{{ $casis?->nama_ayah ?: '-' }} / {{ $casis?->nama_ibu ?: '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Pendidikan Orang Tua</span>
            <span class="data-value">{{ $casis?->pendidikan_ayah ?: '-' }} / {{ $casis?->pendidikan_ibu ?: '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Pekerjaan Orang Tua</span>
            <span class="data-value">{{ $casis?->pekerjaan_ayah ?: '-' }} / {{ $casis?->pekerjaan_ibu ?: '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Penghasilan Orang Tua</span>
            <span class="data-value">Rp {{ number_format((float)($casis?->penghasilan_ayah ?? 0), 0, ',', '.') }} / Rp {{ number_format((float)($casis?->penghasilan_ibu ?? 0), 0, ',', '.') }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Alamat Lengkap</span>
            <span class="data-value">{{ $casis?->alamat_calon_siswa ?? '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">No. Telepon / WhatsApp</span>
            <span class="data-value">{{ $casis?->no_hp_calon_siswa ?? $casis?->no_hp_orang_tua ?? '-' }}</span>
        </div>
        <div class="data-item">
            <span class="data-label">Status Pendaftaran</span>
            <span class="data-value font-bold">{{ $casis?->status_pendaftaran ?? 'Menunggu' }}</span>
        </div>
    </div>
</body>
</html>
