<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Persona extends Model
{
    protected $fillable = [
        'dni',
        'ape_pat',
        'ape_mat',
        'primernombre',
        'otrosnombres',
        'fecha_nac',
        'ubigeo_nac',
        'genero',
        'celular',
        'email',
        'ruc',
        'estado_civil',
        'profesion',
        'grado_instr',
        'origen_labor',
        'ocupacion',
        'institucion_lab',
        'conyugue',
        'direccion',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['apenom', 'edad'];


    public function apenom(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => "{$attributes['ape_pat']} {$attributes['ape_mat']} {$attributes['primernombre']} " . ($attributes['otrosnombres'] ?? ''),
        );
    }
    public function edad(): Attribute
    {
        return Attribute::make(
            get: function () {
                return DB::selectOne("SELECT calcularedad(?) AS edad", [$this->fecha_nac])->edad ?? 0;
            }
        );
    }
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class, 'persona_id', 'id');
    }
    public function personal(): HasOne
    {
        return $this->hasOne(Personal::class, 'dni', 'dni');
    }
    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'ubigeo_nac', 'ubigeo');
    }

    public function getEdadAttribute()
    {
        return Carbon::parse($this->fecha_nac)->age;
    }


    public function conyugePersona() : BelongsTo
    {
        return $this->belongsTo(Persona::class, 'conyugue', 'id');
    }
}
