<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleActividadNegocio extends Model
{
    protected $fillable = [
        'tipo_actividad_id',
        'nombre',
    ];

    public function tipo_actividad()
    {
        return $this->belongsTo(TipoActividad::class);
    }
}
