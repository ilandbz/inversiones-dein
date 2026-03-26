<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('creditos')]
class Credito extends Model
{
    // ───── Estados del crédito ─────
    public const ESTADO_PENDIENTE    = 'PENDIENTE';
    public const ESTADO_APROBADO     = 'APROBADO';
    public const ESTADO_DESEMBOLSADO = 'DESEMBOLSADO';
    public const ESTADO_FINALIZADO   = 'FINALIZADO';
    public const ESTADO_RECHAZADO    = 'RECHAZADO';
    public const ESTADO_EN_MORA      = 'EN_MORA';
    public const ESTADO_OBSERVADO    = 'OBSERVADO';
    public const ESTADO_PAGAR_RCS    = 'PAGAR POR RCS';

    // ───── Frecuencias ─────
    public const FRECUENCIA_DIARIA    = 'DIARIA';
    public const FRECUENCIA_SEMANAL   = 'SEMANAL';
    public const FRECUENCIA_QUINCENAL = 'QUINCENAL';
    public const FRECUENCIA_MENSUAL   = 'MENSUAL';

    protected $fillable = [
        'cliente_id',
        'asesor_id',
        'agencia_id',
        'aval_id',
        'fecha_reg',
        'fecha_inicio',
        'fecha_venc',
        'tipo',
        'monto',
        'origen_financiamiento_id',
        'frecuencia',
        'plazo',
        'tasainteres',
        'interes',
        'total',
        'saldo_capital',
        'saldo_interes',
        'saldo_total',
        'costomora',
        'estado',
        'mencion',
    ];

    protected function casts(): array
    {
        return [
            'monto'         => 'decimal:2',
            'interes'       => 'decimal:2',
            'total'         => 'decimal:2',
            'saldo_capital' => 'decimal:2',
            'saldo_interes' => 'decimal:2',
            'saldo_total'   => 'decimal:2',
            'costomora'     => 'decimal:2',
            'tasainteres'   => 'decimal:2',
            'fecha_reg'     => 'date',
            'fecha_inicio'  => 'date',
            'fecha_venc'    => 'date',
        ];
    }

    // ───── Relaciones ─────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'asesor_id');
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }

    public function aval(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'aval_id');
    }

    public function origenFinanciamiento(): BelongsTo
    {
        return $this->belongsTo(OrigenFinanciamiento::class, 'origen_financiamiento_id');
    }

    public function desembolso(): HasOne
    {
        return $this->hasOne(Desembolso::class, 'credito_id');
    }

    public function cronogramas(): HasMany
    {
        return $this->hasMany(CronogramaPago::class, 'credito_id');
    }

    public function kardex(): HasMany
    {
        return $this->hasMany(KardexCredito::class, 'credito_id');
    }

    public function pagoMoras(): HasMany
    {
        return $this->hasMany(PagoMora::class, 'credito_id');
    }

    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class, 'credito_id');
    }

    // ───── Scopes ─────

    public function scopeDesembolsados($query)
    {
        return $query->where('estado', self::ESTADO_DESEMBOLSADO);
    }

    public function scopePorEstado($query, string|array $estado)
    {
        return is_array($estado)
            ? $query->whereIn('estado', $estado)
            : $query->where('estado', $estado);
    }

    // ───── Helpers ─────

    public function estaDesembolsado(): bool
    {
        return $this->estado === self::ESTADO_DESEMBOLSADO;
    }

    public function estaFinalizado(): bool
    {
        return $this->estado === self::ESTADO_FINALIZADO;
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }
}
