<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\panitia;
use App\Models\CalonSiswa;
use App\Models\casis;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tb_users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'username',
        'email',
        'no_hp',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Accessor & Mutator for compatibility with code using $user->name
    public function getNameAttribute()
    {
        return $this->attributes['nama'] ?? null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['nama'] = $value;
    }

    public function panitia()
    {
        return $this->hasOne(panitia::class, 'id_user', 'id_user');
    }

    public function calonSiswa()
    {
        return $this->hasOne(CalonSiswa::class, 'id_user', 'id_user');
    }

    // Alias for existing codebase using $user->casis
    public function casis()
    {
        return $this->calonSiswa();
    }
}
