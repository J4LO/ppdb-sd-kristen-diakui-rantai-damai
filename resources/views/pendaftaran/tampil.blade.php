@extends('layout.tampilan')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <div class="row justify-content-between align-items-center">
                        <!-- Kolom Tambah Data -->
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <h3><b>PENDAFTARAN CALON SISWA</b></h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p style="color: red;">Silakan mengisi form buat akun terlebih dahulu sebelum menginput pendaftaran:</p>
                    <form method="post" action="/admin/pendaftaran/proses-data/{{ $data->id_calon_siswa ?? $data->id_casis }}" onsubmit="return validateForm()">
                        @csrf
                        <!-- Form Pendaftaran Akun -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Username</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Form Pendaftaran Calon Siswa -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nik">NIK</label>
                                    <input type="text" class="form-control" name="nik" id="nik" required maxlength="10" oninput="validateNIK(this)">
                                    <small id="nikError" class="text-danger" style="display: none;">NIK harus terdiri dari 10 digit angka.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" name="jenis_kelamin" required>
                                        <option>Jenis Kelamin</option>
                                        <option value="Laki-Laki">Laki-Laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" name="tempat_lahir">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" name="tanggal_lahir">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_ortu" class="form-label">Nama Orang Tua</label>
                                    <input type="text" class="form-control" name="nama_ortu" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pendidikan_ortu" class="form-label">Pendidikan Orang Tua</label>
                                    <select class="form-select" name="pendidikan_ortu" required>
                                        <option>Pendidikan Orang Tua</option>
                                        <option value="Tidak Bersekolah">Tidak Bersekolah</option>
                                        <option value="SD">SD</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA">SMA</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <input type="text" class="form-control" name="alamat" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">No Telepon</label>
                                    <input type="tel" pattern="[0-9]+" class="form-control" name="no_hp" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info">SIMPAN</button>
                        <a href="/admin/pendaftaran" class="btn btn-warning">BATAL</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function validateNIK(input) {
        // Hapus karakter non-digit
        input.value = input.value.replace(/\D/g, '');

        // Periksa panjang NIK
        if (input.value.length !== 10) {
            document.getElementById('nikError').style.display = 'block';
        } else {
            document.getElementById('nikError').style.display = 'none';
        }
    }
</script>

<script>
    function validateForm() {
        var nik = document.getElementById('nik');
        if (nik.value.length !== 10) {
            alert('NIK harus terdiri dari 10 digit angka.');
            return false;
        }
        return true;
    }
</script>

<style>
    /* CSS untuk Bahan Baku dan Supplier */
    .form-label {
        font-weight: bold;
    }

    .form-select,
    .form-control {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        box-sizing: border-box;
    }

    .btn-primary {
        background-color: #007bff;
        color: #fff;
        padding: 10px 10px;
        border: none;
        cursor: pointer;
    }

    /* Gaya tambahan sesuai kebutuhan Anda */
    .content-wrapper {
        margin: 10px;
    }

    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .card-body {
        padding: 30px;
    }
</style>

@endsection