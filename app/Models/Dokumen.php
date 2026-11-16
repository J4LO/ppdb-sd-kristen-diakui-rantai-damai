<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'tb_dokumen';
    protected $primaryKey = 'id_dokumen';
    public $timestamps = false;

    protected $fillable = [
        'id_dokumen',
        'id_calon_siswa',
        'jenis_dokumen',
        'file_path',
        'tgl_upload',
        'status_verifikasi',
        'catatan',
    ];

    protected $casts = [
        'tgl_upload' => 'datetime',
    ];

    public function calonSiswa()
    {
        return $this->belongsTo(CalonSiswa::class, 'id_calon_siswa', 'id_calon_siswa')->withTrashed();
    }

    public function casis()
    {
        return $this->calonSiswa();
    }
}

