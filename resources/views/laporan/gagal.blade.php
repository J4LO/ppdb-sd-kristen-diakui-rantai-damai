@extends('layout.tampilanpanitia')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <h5><b>LAPORAN DATA CALON SISWA TIDAK LULUS</b></h5>
                        </div>
                    </div>
                </div>

                <div class="card-header">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <a href="/unduh/siswa tidak lulus" class="btn btn-warning">
                                <i class="fas fa-print"></i> Cetak Laporan
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <form action="{{ route('gagalcasis') }}" method="get" class="form-inline d-flex justify-content-end">
                                <div class="input-group input-group-sm mr-2">
                                    <input type="date" name="start_date" id="start_date" class="form-control" placeholder="Tanggal Mulai" value="{{ request('start_date') }}">
                                </div>
                                <div class="input-group input-group-sm mr-2">
                                    <input type="date" name="end_date" id="end_date" class="form-control" placeholder="Tanggal Selesai" value="{{ request('end_date') }}">
                                </div>
                                <button type="submit" class="btn btn-info">
                                    <i class="fa fa-search"></i> Cari
                                </button>
                                <a href="/laporan/siswa/tidak lulus" class="btn btn-secondary ml-2">
                                    <i class="fa fa-reply"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" style="text-align: center;">
                            <thead>
                                <tr>
                                    <th class="bg-dark text-white">No</th>
                                    <th class="bg-dark text-white">NIK</th>
                                    <th class="bg-dark text-white">Nama Calon Siswa</th>
                                    <th class="bg-dark text-white">Tanggal Seleksi</th>
                                    <th class="bg-dark text-white">Baca</th>
                                    <th class="bg-dark text-white">Tulis</th>
                                    <th class="bg-dark text-white">Hitung</th>
                                    <th class="bg-dark text-white">Total Nilai</th>
                                    <th class="bg-dark text-white">Rata-rata</th>
                                    <th class="bg-dark text-white">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $no => $value)
                                <tr>
                                    <td>{{ $no+1 }}</td>
                                    <td>{{ $value->casis->nik ?? '-' }}</td>
                                    <td class="text-left">{{ $value->casis->nama ?? '-' }}</td>
                                    <td>{{ $value->tgl_seleksi ? \Carbon\Carbon::parse($value->tgl_seleksi)->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $value->nilai_baca }}</td>
                                    <td>{{ $value->nilai_tulis }}</td>
                                    <td>{{ $value->nilai_hitung }}</td>
                                    <td><strong>{{ $value->total_nilai }}</strong></td>
                                    <td>{{ number_format($value->nilai_akhir ?? 0, 2) }}</td>
                                    <td><span class="badge badge-danger">TIDAK LULUS</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data calon siswa tidak lulus</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
