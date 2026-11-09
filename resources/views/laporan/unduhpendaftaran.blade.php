@extends('layout.cetak-laporan')

@section('title', 'Laporan Pendaftaran Calon Siswa')
@section('page-size', 'A4 landscape')

@php
    $isDomPdf = true;

    // Menentukan teks tahun ajaran
    $taText = 'Semua';
    if (!empty($filterTahunAjaran)) {
        $taText = $filterTahunAjaran->tahun_ajaran;
    } elseif (!empty($activeFilter['id_tahun_ajaran'])) {
        $taText = $activeFilter['id_tahun_ajaran'];
    } elseif ($data->isNotEmpty() && $data->first()?->tahunAjaran) {
        $taText = $data->first()->tahunAjaran->tahun_ajaran;
    }

    // Menentukan teks periode
    if (!empty($activeFilter['start_date']) || !empty($activeFilter['end_date'])) {
        $tglMulai = !empty($activeFilter['start_date']) ? \Carbon\Carbon::parse($activeFilter['start_date'])->format('d/m/Y') : 'Awal';
        $tglSelesai = !empty($activeFilter['end_date']) ? \Carbon\Carbon::parse($activeFilter['end_date'])->format('d/m/Y') : 'Akhir';
        $periodeText = "{$tglMulai} s/d {$tglSelesai}";
    } else {
        $periodeText = "Seluruh Data";
    }

    // Helper penyamaran NIK (4 digit awal, ****, 4 digit akhir)
    $maskNik = function($nik) {
        $clean = trim((string)$nik);
        if (empty($clean) || $clean === '-') {
            return '-';
        }
        if (strlen($clean) >= 8) {
            return substr($clean, 0, 4) . '****' . substr($clean, -4);
        }
        return $clean;
    };
@endphp

@section('judul-laporan', 'LAPORAN PENDAFTARAN CALON SISWA')

@section('baris-meta')
    Tahun Ajaran: {{ $taText }} &middot; Periode: {{ $periodeText }}
    @if(!empty($activeFilter['filter_program']))
        &middot; Status: {{ ucfirst(strtolower($activeFilter['filter_program'])) }}
    @endif
    @if(!empty($activeFilter['search']))
        &middot; Kata Kunci: "{{ $activeFilter['search'] }}"
    @endif
@endsection

@section('jumlah-data')
    Jumlah Data: {{ $data->count() }} Pendaftar
@endsection

@section('konten-tabel')
<table class="laporan-table">
    <thead>
        <tr>
            <th style="width: 3.5%;">No</th>
            <th style="width: 11%;">No. Pendaftaran</th>
            <th style="width: 11%;">NIK</th>
            <th style="width: 16%; text-align: left;">Nama Lengkap</th>
            <th style="width: 14%;">Tempat, Tgl Lahir</th>
            <th style="width: 8%;">Jenis Kelamin</th>
            <th style="width: 13%; text-align: left;">Alamat</th>
            <th style="width: 9.5%;">No. Telepon/WA</th>
            <th style="width: 7%;">Tgl Daftar</th>
            <th style="width: 7%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $no => $value)
        @php
            $casis = $value->casis ?? $value;
            $noReg = $value->no_registrasi ?? ($casis->no_registrasi ?? '-');
            $rawNik = $value->nik_calon_siswa ?? ($value->nik ?? ($casis->nik_calon_siswa ?? ($casis->nik ?? '-')));
            $maskedNik = $maskNik($rawNik);
            $nama = $value->nama_calon_siswa ?? ($value->nama ?? ($casis->nama ?? '-'));
            $tmpt = $value->tmpt_lahir ?? ($value->tempat_lahir ?? ($casis->tempat_lahir ?? null));
            $tgl = $value->tgl_lahir ?? ($value->tanggal_lahir ?? ($casis->tanggal_lahir ?? null));
            
            // Format tempat, tanggal lahir
            if ($tmpt && $tgl) {
                $ttl = $tmpt . ', ' . \Carbon\Carbon::parse($tgl)->format('d-m-Y');
            } elseif ($tmpt) {
                $ttl = $tmpt;
            } elseif ($tgl) {
                $ttl = \Carbon\Carbon::parse($tgl)->format('d-m-Y');
            } else {
                $ttl = '-, -';
            }

            $jk = $value->jenis_kelamin ?? ($casis->jenis_kelamin ?? '-');
            $alamat = $value->alamat_calon_siswa ?? ($value->alamat ?? ($casis->alamat ?? '-'));
            $noHp = $value->no_hp_orang_tua ?? ($value->no_hp ?? ($casis->no_hp ?? '-'));
            $tglDaftar = $value->tgl_pendaftaran ?? ($casis->tgl_pendaftaran ?? null);
            $rawStatus = $value->status_pendaftaran ?? ($casis->status_pendaftaran ?? 'Menunggu');
            
            // Normalisasi status pendaftaran teks biasa
            $statusNormalized = match (strtoupper((string)$rawStatus)) {
                'BERHASIL', 'DITERIMA', 'LOLOS' => 'Diterima',
                'GAGAL', 'DITOLAK' => 'Ditolak',
                'PENDING', 'MENUNGGU' => 'Menunggu',
                default => ucfirst(strtolower((string)$rawStatus)),
            };
        @endphp
        <tr>
            <td class="col-no">{{ $no + 1 }}</td>
            <td class="col-center font-bold">{{ $noReg }}</td>
            <td class="col-center">{{ $maskedNik }}</td>
            <td class="col-left font-bold">{{ $nama }}</td>
            <td class="col-center">{{ $ttl }}</td>
            <td class="col-center">{{ $jk }}</td>
            <td class="col-left">{{ $alamat }}</td>
            <td class="col-center">{{ $noHp }}</td>
            <td class="col-center" style="white-space: nowrap;">{{ $tglDaftar ? \Carbon\Carbon::parse($tglDaftar)->format('d-m-Y') : '-' }}</td>
            <td class="col-center" style="white-space: nowrap;">{{ $statusNormalized }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="col-center" style="padding: 16px; color: #6b7280;">Tidak ada data pendaftaran yang sesuai kriteria filter</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
