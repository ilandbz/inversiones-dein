<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionPrestamo extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_prestamos';

    protected $fillable = [
        'credito_id',
        'user_id',
        'cargo',
        'estado',
        'observacion',
    ];

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
