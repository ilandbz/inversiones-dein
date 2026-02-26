<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function desembolso(): HasOne
    {
        return $this->hasOne(Desembolso::class, 'credito_id');
    }
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCredito::class, 'credito_id');
    }
    public function balance()
    {
        return $this->hasOne(Balance::class, 'credito_id');
    }
    public function cronogramas(): HasMany
    {
        return $this->hasMany(CronogramaPago::class, 'credito_id');
    }
}
