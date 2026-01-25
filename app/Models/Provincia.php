<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
