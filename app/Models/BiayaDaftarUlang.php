<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaDaftarUlang extends Model
{
    use HasFactory;

    protected $table = 'biaya_daftar_ulang';

    protected $fillable = [
        'nama_biaya',
        'nominal',
        'tahun_ajaran',
        'jenis_kelamin',
        'is_active'
    ];

    // Tambahkan relasi ke TahunAjar jika diperlukan
    public function tahunAjar()
    {
        return $this->belongsTo(TahunAjar::class, 'tahun_ajaran', 'tahun_ajar');
    }
}
