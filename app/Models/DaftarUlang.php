<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarUlang extends Model
{
    use HasFactory;

    protected $table = 'tb_daftar_ulang';
    protected $primaryKey = 'id_daftar_ulang';
    public $timestamps = false;

    protected $fillable = [
        'id_daftar_ulang',
        'id_seleksi',
        'tgl_daftar_ulang',
        'status_daftar_ulang',
        'status_berkas',
        'jenis_berkas',
        'tgl_berkas_diterima',
        'penerima_berkas',
        'catatan_berkas',
        'tgl_jatuh_tempo_du',
    ];

    protected $casts = [
        'tgl_daftar_ulang' => 'date',
        'tgl_berkas_diterima' => 'datetime',
        'tgl_jatuh_tempo_du' => 'datetime',
    ];

    public function seleksi()
    {
        return $this->belongsTo(Seleksi::class, 'id_seleksi', 'id_seleksi');
    }

    public function pembayaranDaftarUlang()
    {
        return $this->hasMany(PembayaranDaftarUlang::class, 'id_daftar_ulang', 'id_daftar_ulang');
    }

    public function pembayaranTerakhir()
    {
        return $this->hasOne(PembayaranDaftarUlang::class, 'id_daftar_ulang', 'id_daftar_ulang')
            ->latestOfMany('id_bayar_daftar_ulang');
    }

    // Helper untuk mengambil data calon siswa melalui seleksi
    public function getCalonSiswaAttribute()
    {
        return $this->seleksi ? $this->seleksi->calonSiswa : null;
    }

    public function getCasisAttribute()
    {
        return $this->calonSiswa;
    }

    // Kompatibilitas untuk view lama yang memanggil $daftarUlang->pendaftaran
    public function getPendaftaranAttribute()
    {
        return $this->calonSiswa;
    }

    // Accessor kompatibilitas nilai pembayaran
    public function getJumlahBayarAttribute()
    {
        return $this->pembayaranTerakhir ? $this->pembayaranTerakhir->jml_bayar_du : 0;
    }

    public function getStatusBayarAttribute()
    {
        if ($this->status_daftar_ulang === 'Selesai') {
            return 'Berhasil';
        }
        $last = $this->pembayaranTerakhir;
        if (!$last) {
            return 'Menunggu Konfirmasi';
        }
        if (in_array($last->status_du, ['Lunas', 'Diterima'])) {
            return 'Berhasil';
        }
        if (in_array($last->status_du, ['Ditolak', 'Gagal'])) {
            return 'Gagal';
        }
        return 'Menunggu Konfirmasi';
    }

    public function getBuktiPembayaranAttribute()
    {
        return $this->pembayaranTerakhir ? $this->pembayaranTerakhir->bukti_bayar_du : null;
    }

    public function getMetodePembayaranAttribute()
    {
        $last = $this->pembayaranTerakhir;
        if (!$last) {
            return 'Transfer Bank';
        }
        return $last->jenis_bayar_du === 'Cicil' ? 'Cicilan (DP)' : 'Lunas';
    }

    public function getTotalBiayaAttribute()
    {
        return $this->calonSiswa?->tahunAjaran?->biaya_daftar_ulang ?? 5000000;
    }
}
