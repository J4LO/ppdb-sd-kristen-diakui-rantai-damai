@extends('layout.tampilan')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <h1><b>DATA ORANG TUA CALON SISWA</b></h1>
                        </div>
                        <div class="col-sm-6 mb-2 mb-sm-0 text-right">
                            <form action="{{ route('dataortu') }}" method="get" class="form-inline d-flex justify-content-end">
                                <div class="input-group input-group-sm w-100">
                                    <input type="text" name="search" class="form-control" placeholder="Cari Orang Tua Calon Siswa" value="{{ $search ?? '' }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Lengkap</th>
                                    <th scope="col">Pendidikan</th>
                                    <th scope="col">Pekerjaan</th>
                                    <th scope="col">Gaji Orang Tua</th>
                                    <th scope="col">Alamat</th>
                                    <th scope="col">No Telepon</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $no => $value)
                                <tr>
                                    <td>{{ $no+1 }}</td>
                                    <td>{{ $value->nama_ortu }}</td>
                                    <td>{{ $value->pendidikan_ortu }}</td>
                                    <td>{{ $value->pekerjaan_ortu ?? 'belum diinput' }}</td>
                                    <td>{{ $value->gaji_ortu ?? 'belum diinput' }}</td>
                                    <td>{{ $value->alamat }}</td>
                                    <td>{{ $value->no_hp }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection