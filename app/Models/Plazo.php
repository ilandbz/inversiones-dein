<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plazo extends Model
{
    protected $table = 'plazos';

    protected $fillable = [
        'frecuencia',
        'plazo',
        'tasainteres',
        'costomora',
    ];
}
