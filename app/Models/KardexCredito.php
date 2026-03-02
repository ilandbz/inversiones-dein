<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KardexCredito extends Model
{
    protected $table = 'kardex_creditos';

    protected $fillable = [
        'credito_id',
        'nro',
        'fecha',
        'hora',
        'montopagado',
        'user_id',
        'mediopago',
    ];

    // public $timestamps = false; // Removed as user added timestamps in migration

    // protected $primaryKey = ['credito_id', 'nro']; // Removed as user added id() in migration

    // public $incrementing = false; // Removed as user added id() in migration

    public function detalles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetalleKardexCredito::class, 'kardex_credito_id');
    }

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Desembolso::class, 'credito_id', 'credito_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
