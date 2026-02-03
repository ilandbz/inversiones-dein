<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceDeuda extends Model
{
    protected $fillable = [
        'balance_id',
        'entidad',
        'saldo',
    ];

    public function balance(): BelongsTo
    {
        return $this->belongsTo(Balance::class, 'balance_id');
    }
}
