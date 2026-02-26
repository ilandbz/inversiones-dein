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

    protected $primaryKey = ['credito_id', 'nrocuota'];

    public $incrementing = false;

    public function credito()
    {
        return $this->belongsTo(Desembolso::class, 'credito_id', 'credito_id');
    }
}
