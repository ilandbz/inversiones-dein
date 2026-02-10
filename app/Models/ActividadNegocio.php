<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadNegocio extends Model
{
    protected $table = 'actividad_negocios';
    protected $fillable = [
        'nombre',
    ];

    public function detalleActividades()
    {
        return $this->hasMany(DetalleActividadNegocio::class);
    }
}
