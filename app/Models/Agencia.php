<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('agencias')]
class Agencia extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'ciudad',
        'es_activa',
    ];

    protected function casts(): array
    {
        return [
            'es_activa' => 'boolean',
        ];
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'agencia_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'agencia_id');
    }

    public function ahorros(): HasMany
    {
        return $this->hasMany(Ahorro::class, 'agencia_id');
    }
}
