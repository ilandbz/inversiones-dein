<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desembolso extends Model
{
    protected $fillable = [
        'credito_id',
        'fecha',
        'hora',
        'user_id',
        'descontado',
        'totalentregado',
    ];




    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cronograma(): HasMany
    {
        return $this->hasMany(CronogramaPago::class, 'credito_id', 'credito_id');
    }
}
