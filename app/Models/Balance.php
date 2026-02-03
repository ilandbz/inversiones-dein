<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Balance extends Model
{
    protected $fillable = [
        'credito_id',
        'fecha',
        'activocaja',
        'activobancos',
        'activoctascobrar',
        'activoinventarios',
        'totalacorriente',
        'activomueble',
        'activootrosact',
        'activodepre',
        'totalancorriente',
        'total_activo',
        'pasivodeudaprove',
        'pasivodeudaent',
        'totalpcorriente',
        'pasivolargop',
        'otrascuentaspagar',
        'totalpncorriente',
        'total_pasivo',
        'patrimonio',
        'paspatrimonio',
        'captrabajo',
        'estado_crud',
    ];

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function inventario(): HasOne
    {
        return $this->hasOne(BalanceInventario::class, 'balance_id');
    }

    public function muebles(): HasMany
    {
        return $this->hasMany(BalanceMueble::class, 'balance_id');
    }

    public function deudas(): HasMany
    {
        return $this->hasMany(BalanceDeuda::class, 'balance_id');
    }
}
