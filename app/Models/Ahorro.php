<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('ahorros')]
class Ahorro extends Model
{
    use HasFactory;

    // Estados de la cuenta
    public const ESTADO_ACTIVO   = 'ACTIVO';
    public const ESTADO_CERRADO  = 'CERRADO';
    public const ESTADO_BLOQUEADO= 'BLOQUEADO';

    // Tipos de ahorro
    public const TIPO_LIBRE        = 'LIBRE';
    public const TIPO_PLAZO_FIJO   = 'PLAZO_FIJO';
    public const TIPO_PROGRAMADO   = 'PROGRAMADO';

    protected $fillable = [
        'cliente_id',
        'asesor_id',
        'agencia_id',
        'numero_cuenta',
        'tipo_ahorro',
        'monto',           // Monto inicial (depósito de apertura)
        'saldo',           // Saldo actual (actualizado en cada movimiento)
        'tasa_interes',
        'metodo_pago',
        'estado',
        'notas',
        'fecha_apertura',
        'fecha_cierre',
        'fecha_movimiento',
    ];

    protected function casts(): array
    {
        return [
            'monto'          => 'decimal:2',
            'saldo'          => 'decimal:2',
            'tasa_interes'   => 'decimal:4',
            'fecha_apertura' => 'date',
            'fecha_cierre'   => 'date',
            'fecha_movimiento'=> 'date',
        ];
    }

    // ───── Relaciones ─────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class);
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(AhorroMovimiento::class)->orderByDesc('fecha')->orderByDesc('hora');
    }

    // ───── Scopes ─────

    public function scopeActivos($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    // ───── Helpers ─────

    public function estaActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    /**
     * Calcula el interés generado por el saldo actual para N días.
     */
    public function calcularInteresDiario(int $dias = 1): float
    {
        // tasa_interes es anual: ej. 0.0300 = 3%
        $tasaDiaria = (float)$this->tasa_interes / 365;
        return round((float)$this->saldo * $tasaDiaria * $dias, 2);
    }
}
