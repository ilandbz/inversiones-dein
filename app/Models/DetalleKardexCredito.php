<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('detalle_kardex_creditos')]
class DetalleKardexCredito extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'kardex_credito_id',
        'cronograma_id',
        'capital_pagado',
        'interes_pagado',
        'mora_pagada',
    ];

    protected function casts(): array
    {
        return [
            'capital_pagado' => 'decimal:2',
            'interes_pagado' => 'decimal:2',
            'mora_pagada'    => 'decimal:2',
        ];
    }

    public function kardex(): BelongsTo
    {
        return $this->belongsTo(KardexCredito::class, 'kardex_credito_id');
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(CronogramaPago::class, 'cronograma_id');
    }
}
