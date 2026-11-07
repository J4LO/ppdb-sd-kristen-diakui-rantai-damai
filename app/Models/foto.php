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
}
