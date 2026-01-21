<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'usuario_id',
        'persona_id',
        'aval_id',
        'estado',
        'fecha_reg',
        'hora_reg',
    ];
}
