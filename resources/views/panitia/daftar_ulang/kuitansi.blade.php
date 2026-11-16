<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - {{ $casis->nama_calon_siswa }} - {{ $pembayaran->no_kuitansi ?: $pembayaran->order_id_du }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
        }
        body {
            margin: 0;
            padding: 24px;
            background: #f8fafc;
        }
        .receipt-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 36px 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f4c2a;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .brand-logo {
            width: 56px;
            height: 56px;
            background: #0f4c2a;
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
        .school-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f4c2a;
            margin: 0;
            line-height: 1.2;
        }
        .school-sub {
            font-size: 13px;
            color: #475569;
            margin-top: 3px;
        }
        .receipt-title-box {
            text-align: right;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
        }
        .receipt-no {
            font-family: monospace;
            font-size: 13px;
            color: #059669;
            font-weight: 700;
            margin-top: 4px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-size: 13.5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        .info-label {
            color: #64748b;
        }
        .info-val {
            font-weight: 600;
            color: #0f172a;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13.5px;
        }
        .detail-table th {
            background: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 11.5px;
            letter-spacing: 0.05em;
            padding: 10px 14px;
            text-align: left;
            border: 1px solid #cbd5e1;
        }
        .detail-table td {
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
        }
        .total-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .total-label {
            font-size: 14px;
            font-weight: 700;
            color: #065f46;
        }
        .total-amount {
            font-size: 22px;
            font-weight: 800;
            color: #047857;
            font-family: monospace;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 40px;
            padding-top: 10px;
        }
        .sig-box {
            text-align: center;
            font-size: 13px;
        }
        .sig-line {
            width: 180px;
            border-bottom: 1px solid #334155;
            margin: 65px auto 6px;
        }
        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-print {
            background: #0f4c2a;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover {
            background: #146c3c;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none;
            }
            .receipt-card {
                box-shadow: none;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="javascript:window.close()" style="text-decoration: none; color: #475569; font-size: 14px; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Tutup Halaman
        </a>
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Cetak Dokumen Kuitansi
        </button>
    </div>

    <div class="receipt-card">
        {{-- Header Kuitansi --}}
        <div class="header">
            <div class="brand">
                <div class="brand-logo">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h1 class="school-name">SD KRISTEN DIAKUI RANTAI DAMAI</h1>
                    <div class="school-sub">Penerimaan Peserta Didik Baru &bull; Tahun Ajaran {{ $casis->tahunAjaran?->tahun_ajaran }}</div>
                </div>
            </div>
            <div class="receipt-title-box">
                <div class="receipt-title">Tanda Terima Pembayaran</div>
                <div class="receipt-no">No: {{ $pembayaran->no_kuitansi ?: $pembayaran->order_id_du }}</div>
            </div>
        </div>

        {{-- Info Calon Siswa & Waktu --}}
        <div class="info-grid">
            <div>
                <div class="info-row">
                    <span class="info-label">Nama Siswa:</span>
                    <span class="info-val">{{ $casis->nama_calon_siswa }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. Registrasi:</span>
                    <span class="info-val font-mono">{{ $casis->no_registrasi ?: '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">NIK:</span>
                    <span class="info-val font-mono">{{ $casis->nik_calon_siswa ?: '-' }}</span>
                </div>
            </div>
            <div>
                <div class="info-row">
                    <span class="info-label">Waktu Bayar:</span>
                    <span class="info-val">{{ format_school_time($pembayaran->waktu_lunas ?? $pembayaran->tgl_bayar_du, 'j F Y, H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Setoran:</span>
                    <span class="info-val">{{ $pembayaran->metode_pembayaran ?: 'Gateway Midtrans' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Penerima/Petugas:</span>
                    <span class="info-val">{{ $pembayaran->dicatat_oleh ?: 'Sistem Midtrans' }}</span>
                </div>
            </div>
        </div>

        {{-- Detail Transaksi --}}
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 55%;">Uraian Pembayaran</th>
                    <th style="width: 20%; text-align: center;">Kategori</th>
                    <th style="width: 25%; text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">Setoran Biaya Masuk &amp; Daftar Ulang Siswa Baru</div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                            Alokasi bertahap: Buku Pelajaran &amp; Modul, Seragam &amp; Atribut, dan Sarana Pembangunan.
                            @if($pembayaran->keterangan)
                                <br><span style="color: #0369a1;">Catatan: {{ $pembayaran->keterangan }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #334155;">
                        {{ $pembayaran->jenis_bayar_du === 'Lunas' ? 'Pelunasan Penuh' : 'Cicilan Awal / Bertahap' }}
                    </td>
                    <td style="text-align: right; font-weight: 800; font-family: monospace; font-size: 14px;">
                        Rp {{ number_format($pembayaran->jml_bayar_du, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Total Setoran --}}
        <div class="total-box">
            <div>
                <div class="total-label">TOTAL JUMLAH YANG DITERIMA:</div>
                <div style="font-size: 12px; color: #065f46; margin-top: 2px;">Status: Sah &amp; Terverifikasi ({{ $pembayaran->status_du }})</div>
            </div>
            <div class="total-amount">
                Rp {{ number_format($pembayaran->jml_bayar_du, 0, ',', '.') }}
            </div>
        </div>

        {{-- Akumulasi Keuangan Calon Siswa --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 18px; margin-bottom: 24px; font-size: 12.5px; display: flex; justify-content: space-between;">
            <div>Total Kewajiban Uang Pangkal: <strong>Rp {{ number_format($item->totalBiaya, 0, ',', '.') }}</strong></div>
            <div>Total Akumulasi Terbayar: <strong style="color: #059669;">Rp {{ number_format($item->totalTerbayar, 0, ',', '.') }}</strong></div>
            <div>Sisa Tagihan: <strong style="color: {{ $item->sisaBiaya > 0 ? '#d97706' : '#059669' }};">Rp {{ number_format($item->sisaBiaya, 0, ',', '.') }}</strong></div>
        </div>

        {{-- Tanda Tangan --}}
        <div class="sig-grid">
            <div class="sig-box">
                <div>Orang Tua / Wali Siswa,</div>
                <div class="sig-line"></div>
                <div>( .................................................... )</div>
            </div>
            <div class="sig-box">
                <div>Dicatat oleh Panitia PPDB,</div>
                <div class="sig-line"></div>
                <div style="font-weight: 700;">{{ $pembayaran->dicatat_oleh ?: 'Tata Usaha / Kasir PPDB' }}</div>
            </div>
        </div>

        <div style="margin-top: 32px; padding-top: 12px; border-top: 1px dashed #cbd5e1; text-align: center; font-size: 11px; color: #94a3b8;">
            Dokumen kuitansi ini diterbitkan secara resmi oleh Sistem PPDB Online SD Kristen Diakui Rantai Damai pada {{ format_school_time(now(), 'j F Y, H:i') }}.
        </div>
    </div>

</body>
</html>

