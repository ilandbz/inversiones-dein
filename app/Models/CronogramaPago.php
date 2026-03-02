<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronogramaPago extends Model
{
    protected $fillable = [
        'credito_id',
        'nrocuota',
        'fecha_prog',
        'nombredia',
        'cuota',
        'saldo',
    ];

    public $timestamps = false;

    // protected $primaryKey = ['credito_id', 'nrocuota']; // Removed as user added id() in migration
    // public $incrementing = false; // Removed as user added id() in migration

    public function credito()
    {
        return $this->belongsTo(Desembolso::class, 'credito_id', 'credito_id');
    }
}
