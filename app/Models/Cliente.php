<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Cliente extends Model
{
    protected $fillable = [
        'usuario_id',
        'persona_id',
        'estado',
        'fecha_reg',
        'hora_reg',
        'referente_id',
        'referente_parentesco',
    ];
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }    
    public function negocio(): HasOne
    {
        return $this->hasOne(Negocio::class, 'cliente_id')->latest();
    }   
    public function negocios(): HasMany
    {
        return $this->HasMany(Negocio::class, 'cliente_id');
    }
    public function creditos(): HasMany
    {
        return $this->HasMany(Credito::class, 'cliente_id');
    }
    public function juntas(): HasMany
    {
        return $this->HasMany(Junta::class, 'cliente_id');
    }
    public function aval(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'aval_id')->latest();
    } 
    public function creditosVigentesConMora()
    {
        return Credito::join('desembolsos', 'creditos.id', '=', 'desembolsos.credito_id')
        ->leftJoin('view_cronograma_pagos as v', 'creditos.id', '=', 'v.credito_id')
        ->leftJoin(DB::raw("(SELECT credito_id, SUM(diasmora) as total_diasmorapagado 
                    FROM pago_moras 
                    GROUP BY credito_id) as pm"), 
                    'creditos.id', '=', 'pm.credito_id')
        ->select(
            'creditos.id',
            'creditos.total',
            'creditos.agencia_id',
            'creditos.monto',
            'creditos.tipo',
            'creditos.plazo',
            'creditos.frecuencia',
            'creditos.fecha_reg',
            'creditos.estado',
            'creditos.costomora',
            'creditos.mencion',
            'desembolsos.fecha',
            'desembolsos.hora',
            DB::raw('COALESCE(SUM(v.moradias), 0) AS total_moradias'),
            DB::raw('COALESCE(SUM(v.montopagado), 0) AS total_pagado'),
            DB::raw('creditos.total - COALESCE(SUM(v.montopagado), 0) AS Saldo'),
            DB::raw('COALESCE(pm.total_diasmorapagado, 0) AS total_diasmorapagado'),
            DB::raw('MAX(CASE WHEN v.resta <= 0 THEN v.fechapagado END) AS ultimafechapagadocompleto'),
            DB::raw('(COALESCE(SUM(v.moradias), 0) - COALESCE(pm.total_diasmorapagado, 0)) AS saldo_mora_dias'),
            DB::raw('
                (COALESCE(SUM(v.moradias), 0) - COALESCE(pm.total_diasmorapagado, 0))
                * COALESCE(creditos.costomora, 0) AS saldo_mora
            '),
            DB::raw("
                    calcular_mora(
                        MIN(
                            CASE
                                WHEN COALESCE(v.montopagado, 0) < v.cuota THEN 
                                    CASE 
                                        WHEN v.nrocuota = 1 OR creditos.frecuencia <> 'DIARIO' 
                                            THEN fecha_prog
                                        ELSE 
                                            GREATEST(
                                                (SELECT MAX(fecha) FROM pago_creditos WHERE credito_id = creditos.id),
                                                fecha_prog
                                            )
                                    END
                            END
                            ),
                        CURDATE(),
                        creditos.agencia_id,
                        creditos.frecuencia
                    ) AS retraso
            ")
        )
        ->where('creditos.cliente_id', $this->id)
        ->groupBy(
            'creditos.id',
            'creditos.total',
            'creditos.monto',
            'creditos.tipo',
            'creditos.plazo',
            'creditos.frecuencia',
            'creditos.fecha_reg',
            'creditos.estado',
            'creditos.costomora',
            'creditos.mencion',
            'desembolsos.fecha',
            'desembolsos.hora'
        )
        ->havingRaw('total_moradias > total_diasmorapagado OR creditos.estado IN ("DESEMBOLSADO", "PAGAR POR RCS")')
        ->get();
    }
    public function creditosVigentes(){
        return Desembolso::with('credito')
        ->whereHas('credito', function ($query) {
            $query->where('cliente_id', $this->id)
            ->where('estado', 'DESEMBOLSADO');
        })
        ->get();
    }
    public function creditosVigentesCliente(): HasMany
    {
        return $this->hasMany(Credito::class)->where('estado', 'DESEMBOLSADO');
        
    }
    public function juntasVigentes(){
        return Junta::where('cliente_id', $this->id)
        ->whereIn('estado', ['REGISTRADO', 'APROBADO'])
        ->get();
    }
    public function juntasVigentesCliente() : HasMany
    {
        return $this->hasMany(Junta::class)
            ->whereIn('estado', ['REGISTRADO', 'APROBADO']);
    }
}
