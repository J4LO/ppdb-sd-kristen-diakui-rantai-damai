<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panitia extends Model
{
    use HasFactory;

    protected $table = 'tb_panitia';
    protected $primaryKey = 'id_panitia';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama_panitia',
        'no_hp',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function seleksi()
    {
        return $this->hasMany(Seleksi::class, 'id_panitia', 'id_panitia');
    }

    // Kompatibilitas atribut lama
    public function getUserIdAttribute()
    {
        return $this->attributes['id_user'] ?? null;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['id_user'] = $value;
    }

    public function getNamaAttribute()
    {
        return $this->attributes['nama_panitia'] ?? null;
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama_panitia'] = $value;
    }
}

// Alias class untuk backward compatibility
if (!class_exists('App\Models\panitia', false)) {
    class_alias(Panitia::class, 'App\Models\panitia');
}
