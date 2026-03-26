<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('kardex_creditos')]
class KardexCredito extends Model
{
    // Medios de pago
    public const MEDIO_EFECTIVO      = 'EFECTIVO';
    public const MEDIO_TRANSFERENCIA = 'TRANSFERENCIA';
    public const MEDIO_YAPE          = 'YAPE';
    public const MEDIO_PLIN          = 'PLIN';
    public const MEDIO_DEPOSITO      = 'DEPOSITO';

    protected $fillable = [
        'credito_id',
        'nro',
        'fecha',
        'hora',
        'montopagado',
        'user_id',
        'mediopago',
    ];

    protected function casts(): array
    {
        return [
            'montopagado' => 'decimal:2',
            'fecha'       => 'date',
            'nro'         => 'integer',
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleKardexCredito::class, 'kardex_credito_id');
    }

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagoMora(): HasMany
    {
        return $this->hasMany(PagoMora::class, 'kardex_credito_id');
    }
}
