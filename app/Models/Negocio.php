<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negocio extends Model
{
    protected $fillable = [
        'cliente_id',
        'razonsocial',
        'ruc',
        'celular',
        'detalle_actividad_id',
        'inicioactividad',
        'direccion',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

}
