<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('ahorro_movimientos')]
class AhorroMovimiento extends Model
{
    // Tipos de movimiento
    public const TIPO_DEPOSITO  = 'DEPOSITO';
    public const TIPO_RETIRO    = 'RETIRO';
    public const TIPO_INTERES   = 'INTERES';
    public const TIPO_AJUSTE    = 'AJUSTE';

    protected $fillable = [
        'ahorro_id',
        'caja_id',
        'user_id',
        'fecha',
        'hora',
        'tipo',
        'monto',
        'saldo_anterior',
        'saldo_posterior',
        'metodo_pago',
        'referencia',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha'           => 'date',
            'monto'           => 'decimal:2',
            'saldo_anterior'  => 'decimal:2',
            'saldo_posterior' => 'decimal:2',
        ];
    }

    public function ahorro(): BelongsTo
    {
        return $this->belongsTo(Ahorro::class);
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
