<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleKardexCredito extends Model
{
    protected $fillable = [
        'kardex_credito_id',
        'cronograma_id',
        'capital_pagado',
        'interes_pagado',
        'mora_pagada',
        'observacion',
    ];

    public function kardex(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KardexCredito::class, 'kardex_credito_id');
    }

    public function cuota(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CronogramaPago::class, 'cronograma_id');
    }
}
