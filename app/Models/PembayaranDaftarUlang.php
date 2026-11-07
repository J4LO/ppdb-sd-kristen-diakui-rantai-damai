<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranDaftarUlang extends Model
{
    use HasFactory;

    protected $table = 'tb_pembayaran_daftar_ulang';
    protected $primaryKey = 'id_bayar_daftar_ulang';
    public $timestamps = false;

    protected $fillable = [
        'id_bayar_daftar_ulang',
        'id_daftar_ulang',
        'order_id_du',
        'transaction_id_du',
        'tgl_bayar_du',
        'jml_bayar_du',
        'jenis_bayar_du',
        'metode_pembayaran',
        'va_number',
        'snap_token',
        'payment_url',
        'bukti_bayar_du',
        'status_du',
        'waktu_kadaluarsa',
        'waktu_lunas',
        'no_kuitansi',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tgl_bayar_du' => 'datetime',
        'waktu_kadaluarsa' => 'datetime',
        'waktu_lunas' => 'datetime',
        'jml_bayar_du' => 'decimal:2',
    ];

    public function daftarUlang()
    {
        return $this->belongsTo(DaftarUlang::class, 'id_daftar_ulang', 'id_daftar_ulang');
    }
}

