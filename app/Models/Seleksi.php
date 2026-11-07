<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seleksi extends Model
{
    use HasFactory;

    protected $table = 'tb_seleksi';
    protected $primaryKey = 'id_seleksi';
    public $timestamps = false;

    protected $fillable = [
        'id_seleksi',
        'id_panitia',
        'id_pembayaran',
        'tgl_seleksi',
        'nilai_baca',
        'nilai_tulis',
        'nilai_hitung',
        'total_nilai',
        'hasil_seleksi',
        'diumumkan_pada',
        'diumumkan_oleh',
    ];

    protected $casts = [
        'tgl_seleksi' => 'date',
        'nilai_baca' => 'integer',
        'nilai_tulis' => 'integer',
        'nilai_hitung' => 'integer',
        'total_nilai' => 'integer',
        'diumumkan_pada' => 'datetime',
        'diumumkan_oleh' => 'integer',
    ];

    public function panitia()
    {
        return $this->belongsTo(Panitia::class, 'id_panitia', 'id_panitia');
    }

    public function panitiaPengumum()
    {
        return $this->belongsTo(Panitia::class, 'diumumkan_oleh', 'id_panitia');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    public function daftarUlang()
    {
        return $this->hasOne(DaftarUlang::class, 'id_seleksi', 'id_seleksi');
    }

    /**
     * Cek apakah seleksi sudah dipublikasikan kepada calon siswa
     */
    public function isDiumumkan(): bool
    {
        return !is_null($this->diumumkan_pada);
    }

    /**
     * Cek apakah nilai seleksi terkunci (tidak bisa diedit atau dihapus)
     */
    public function isTerkunci(): bool
    {
        return $this->isDiumumkan();
    }

    /**
     * Hitung hasil seleksi dari total nilai berdasarkan konfigurasi ambang batas
     */
    public static function hitungHasilSeleksi(int $totalNilai): string
    {
        $ambang = (int) config('ppdb.seleksi.nilai_minimal_lulus', 150);
        return ($totalNilai >= $ambang) ? 'Lulus' : 'Tidak Lulus';
    }

    /**
     * Hitung akumulasi total nilai dari ketiga mata uji
     */
    public static function hitungTotalNilai(int $baca, int $tulis, int $hitung): int
    {
        return $baca + $tulis + $hitung;
    }

    // Helper untuk mengambil data calon siswa melalui relasi pembayaran
    public function getCalonSiswaAttribute()
    {
        return $this->pembayaran ? $this->pembayaran->calonSiswa : null;
    }

    public function getCasisAttribute()
    {
        return $this->calonSiswa;
    }

    public function getNilaiAkhirAttribute()
    {
        if (!is_null($this->total_nilai) && $this->total_nilai > 0) {
            return round($this->total_nilai / 3, 2);
        }
        return 0;
    }

    // Accessor kompatibilitas untuk template lama
    public function getNilaiNgajiAttribute()
    {
        return '-';
    }

    public function getNilaiWawancaraAttribute()
    {
        return '-';
    }
}

