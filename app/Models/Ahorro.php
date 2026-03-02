<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ahorro extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'tipo_ahorro',
        'monto',
        'fecha_movimiento',
        'metodo_pago',
        'estado',
        'notas',
    ];

    /**
     * Obtener el cliente dueño del ahorro.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
