<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Credito extends Model
{
    protected $table = 'creditos';
    protected $fillable = [
        'cliente_id',
        'asesor_id',
        'aval_id',
        'fecha_reg',
        'fecha_venc',
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
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'asesor_id');
    }
    public function aval(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'aval_id');
    }
    public function desembolsos(): HasMany
    {
        return $this->hasMany(Desembolso::class, 'credito_id');
    }
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCredito::class, 'credito_id');
    }
}
