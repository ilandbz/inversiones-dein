<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('pago_moras')]
class PagoMora extends Model
{
    protected $fillable = [
        'credito_id',
        'kardex_credito_id',
        'caja_id',
        'user_id',
        'fecha',
        'hora',
        'diasmora',
        'costomora',
        'montomora',
        'metodo_pago',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha'     => 'date',
            'diasmora'  => 'integer',
            'costomora' => 'decimal:2',
            'montomora' => 'decimal:2',
        ];
    }

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class);
    }

    public function kardex(): BelongsTo
    {
        return $this->belongsTo(KardexCredito::class, 'kardex_credito_id');
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
