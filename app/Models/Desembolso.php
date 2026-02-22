<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
