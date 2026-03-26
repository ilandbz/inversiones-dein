<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('caja_movimientos')]
class CajaMovimiento extends Model
{
    // Tipos
    public const TIPO_INGRESO = 'INGRESO';
    public const TIPO_EGRESO  = 'EGRESO';

    // Conceptos
    public const CONCEPTO_DESEMBOLSO      = 'DESEMBOLSO';
    public const CONCEPTO_PAGO_CREDITO    = 'PAGO_CREDITO';
    public const CONCEPTO_DEPOSITO_AHORRO = 'DEPOSITO_AHORRO';
    public const CONCEPTO_RETIRO_AHORRO   = 'RETIRO_AHORRO';
    public const CONCEPTO_PAGO_MORA       = 'PAGO_MORA';
    public const CONCEPTO_GASTO           = 'GASTO';
    public const CONCEPTO_INGRESO_MANUAL  = 'INGRESO_MANUAL';
    public const CONCEPTO_COMISION        = 'COMISION';

    protected $fillable = [
        'caja_id',
        'user_id',
        'hora',
        'tipo',
        'concepto',
        'monto',
        'entidad_tipo',
        'entidad_id',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'monto'      => 'decimal:2',
            'entidad_id' => 'integer',
        ];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
