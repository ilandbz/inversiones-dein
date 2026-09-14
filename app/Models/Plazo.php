<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plazo extends Model
{
    protected $table = 'plazos';

    protected $fillable = [
        'origen_financiamiento_id',
        'frecuencia',
        'plazo',
        'tasainteres',
        'costomora',
        'gastos_administrativos',
    ];

    public function origenFinanciamiento()
    {
        return $this->belongsTo(OrigenFinanciamiento::class);
    }
}
