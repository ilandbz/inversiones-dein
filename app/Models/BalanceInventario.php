<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceInventario extends Model
{
    protected $fillable = [
        'balance_id',
        'inv_materiales',
        'inv_prodproc',
        'inv_prodtermi',
    ];

    public function balance(): BelongsTo
    {
        return $this->belongsTo(Balance::class, 'balance_id');
    }
}
