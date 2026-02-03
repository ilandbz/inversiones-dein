<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceMueble extends Model
{

    protected $fillable = [
        'balance_id',
        'descripcion',
        'valor',
    ];

    public function balance(): BelongsTo
    {
        return $this->belongsTo(Balance::class, 'balance_id');
    }
}
