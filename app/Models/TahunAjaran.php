<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tb_tahun_ajaran';
    protected $primaryKey = 'id_tahun_ajaran';
    public $timestamps = false;

    protected $fillable = [
        'id_tahun_ajaran',
        'tahun_ajaran',
        'mulai_pendaftaran',
        'batas_pendaftaran',
        'kuota',
        'biaya_pendaftaran',
        'biaya_pembangunan',
        'biaya_buku',
        'biaya_seragam',
        'biaya_daftar_ulang',
        'status',
        'tgl_seleksi',
        'nama_kepala_sekolah',
    ];

    protected $casts = [
        'mulai_pendaftaran' => 'date',
        'batas_pendaftaran' => 'date',
        'tgl_seleksi' => 'date',
        'kuota' => 'integer',
        'biaya_pendaftaran' => 'decimal:2',
        'biaya_pembangunan' => 'decimal:2',
        'biaya_buku' => 'decimal:2',
        'biaya_seragam' => 'decimal:2',
        'biaya_daftar_ulang' => 'decimal:2',
    ];

    public function calonSiswa()
    {
        return $this->hasMany(CalonSiswa::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function casis()
    {
        return $this->calonSiswa();
    }

    /**
     * Mengambil 3 komponen rincian biaya masuk/daftar ulang secara terstruktur
     */
    public function getBiayaComponentsAttribute(): array
    {
        $total = (float) ($this->biaya_daftar_ulang ?? 0);
        $pembangunan = (float) ($this->biaya_pembangunan ?? 0);
        $buku = (float) ($this->biaya_buku ?? 0);
        $seragam = (float) ($this->biaya_seragam ?? 0);

        // Fallback jika belum terisi
        if ($pembangunan <= 0 && $buku <= 0 && $seragam <= 0 && $total > 0) {
            $pembangunan = round($total * 0.60, 2);
            $buku = round($total * 0.20, 2);
            $seragam = $total - $pembangunan - $buku;
        }

        return [
            (object) [
                'nama_biaya' => 'Uang Pembangunan & Sarana',
                'nominal' => $pembangunan,
                'keterangan' => 'Fasilitas & sarana pembelajaran sekolah',
            ],
            (object) [
                'nama_biaya' => 'Buku Paket Pelajaran & Modul',
                'nominal' => $buku,
                'keterangan' => 'Buku paket & modul belajar 1 tahun ajaran',
            ],
            (object) [
                'nama_biaya' => 'Seragam Sekolah & Atribut',
                'nominal' => $seragam,
                'keterangan' => 'Seragam lengkap beserta kelengkapan atribut',
            ],
        ];
    }

    // Accessors untuk kompatibilitas kode lama
    public function getTahunAjarAttribute()
    {
        return $this->tahun_ajaran;
    }

    public function getIdAjarAttribute()
    {
        return $this->id_tahun_ajaran;
    }
}

