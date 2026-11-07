<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogVerifikasiDokumen extends Model
{
    use HasFactory;

    protected $table = 'tb_log_verifikasi_dokumen';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_dokumen',
        'id_calon_siswa',
        'id_user_admin',
        'status_lama',
        'status_baru',
        'catatan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'id_dokumen', 'id_dokumen');
    }

    public function calonSiswa()
    {
        return $this->belongsTo(CalonSiswa::class, 'id_calon_siswa', 'id_calon_siswa')->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_user_admin', 'id_user');
    }
}

