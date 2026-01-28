<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    protected $table = 'creditos';
    protected $fillable = [
        'cliente_id',
        'asesor_id',
        'aval_id',
        'tipo',
        'monto',
        'origen_financiamiento_id',
        'frecuencia',
        'plazo',
        'dondepagara',
        'tasainteres',
        'costomora',
        'total',
        'estado',
    ];
}
