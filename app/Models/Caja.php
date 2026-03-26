<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('cajas')]
class Caja extends Model
{
    // Estados
    public const ESTADO_ABIERTA = 'ABIERTA';
    public const ESTADO_CERRADA = 'CERRADA';

    protected $fillable = [
        'agencia_id',
        'user_id',
        'fecha',
        'hora_apertura',
        'hora_cierre',
        'saldo_inicial',
        'saldo_final',
        'efectivo_declarado',
        'diferencia',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha'              => 'date',
            'saldo_inicial'      => 'decimal:2',
            'saldo_final'        => 'decimal:2',
            'efectivo_declarado' => 'decimal:2',
            'diferencia'         => 'decimal:2',
        ];
    }

    // ───── Relaciones ─────

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    // ───── Scopes ─────

    public function scopeAbierta($query)
    {
        return $query->where('estado', self::ESTADO_ABIERTA);
    }

    public function scopeDelDia($query, string $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    // ───── Helpers ─────

    public function estaAbierta(): bool
    {
        return $this->estado === self::ESTADO_ABIERTA;
    }

    /**
     * Saldo calculado = saldo_inicial + ingresos - egresos
     */
    public function saldoCalculado(): float
    {
        $ingresos = $this->movimientos()->where('tipo', 'INGRESO')->sum('monto');
        $egresos  = $this->movimientos()->where('tipo', 'EGRESO')->sum('monto');
        return round((float)$this->saldo_inicial + $ingresos - $egresos, 2);
    }
}
