<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class foto extends Model
{
    use HasFactory;

    protected $table = 'foto';
    protected $primaryKey = 'id_foto';
    public $timestamps = true;

    protected $fillable = [
        'id_foto',
        'foto',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id_foto)) {
                $maxId = static::max('id_foto') ?? 0;
                $model->id_foto = $maxId + 1;
            }
        });
    }
}
