<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'tb_pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = false;

    protected $fillable = [
        'id_calon_siswa',
        'order_id',
        'transaction_id',
        'tgl_pembayaran',
        'waktu_dibuat',
        'jml_pembayaran',
        'metode_pembayaran',
        'channel',
        'va_number',
        'snap_token',
        'payment_url',
        'bukti_pembayaran',
        'status_pembayaran',
        'waktu_kadaluarsa',
        'waktu_lunas',
    ];

    protected $casts = [
        'tgl_pembayaran' => 'datetime',
        'waktu_dibuat' => 'datetime',
        'waktu_kadaluarsa' => 'datetime',
        'waktu_lunas' => 'datetime',
        'jml_pembayaran' => 'decimal:2',
    ];

    public function calonSiswa()
    {
        return $this->belongsTo(CalonSiswa::class, 'id_calon_siswa', 'id_calon_siswa')->withTrashed();
    }

    public function casis()
    {
        return $this->calonSiswa();
    }

    public function seleksi()
    {
        return $this->hasOne(Seleksi::class, 'id_pembayaran', 'id_pembayaran');
    }

    // Kompatibilitas atribut lama dan alias field baru
    public function getCasisIdAttribute()
    {
        return $this->attributes['id_calon_siswa'] ?? null;
    }

    public function setCasisIdAttribute($value)
    {
        $this->attributes['id_calon_siswa'] = $value;
    }

    public function getJumlahPembayaranAttribute()
    {
        return $this->attributes['jml_pembayaran'] ?? 0;
    }

    public function setJumlahPembayaranAttribute($value)
    {
        $this->attributes['jml_pembayaran'] = $value;
    }

    // Bidirectional accessors for tgl_bayar / tgl_pembayaran
    public function getTglBayarAttribute()
    {
        return $this->attributes['tgl_pembayaran'] ?? null;
    }

    public function setTglBayarAttribute($value)
    {
        $this->attributes['tgl_pembayaran'] = $value;
    }

    // Bidirectional accessors for jml_bayar / jml_pembayaran
    public function getJmlBayarAttribute()
    {
        return $this->attributes['jml_pembayaran'] ?? 0;
    }

    public function setJmlBayarAttribute($value)
    {
        $this->attributes['jml_pembayaran'] = $value;
    }

    // Bidirectional accessors for status_bayar / status_pembayaran
    public function getStatusBayarAttribute()
    {
        return $this->attributes['status_pembayaran'] ?? null;
    }

    public function setStatusBayarAttribute($value)
    {
        $this->attributes['status_pembayaran'] = $value;
    }

    // Bidirectional accessors for bukti_bayar / bukti_pembayaran
    public function getBuktiBayarAttribute()
    {
        return $this->attributes['bukti_pembayaran'] ?? null;
    }

    public function setBuktiBayarAttribute($value)
    {
        $this->attributes['bukti_pembayaran'] = $value;
    }
}

// Alias class untuk backward compatibility
if (!class_exists('App\Models\pembayaran', false)) {
    class_alias(Pembayaran::class, 'App\Models\pembayaran');
}
