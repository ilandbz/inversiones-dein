<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('auditoria_logs')]
class AuditoriaLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'modulo',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'descripcion',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'json',
            'datos_nuevos'     => 'json',
            'created_at'       => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica manual para la entidad afectada.
     */
    public function entidad()
    {
        if (!$this->entidad_tipo || !$this->entidad_id) {
            return null;
        }
        return $this->entidad_tipo::find($this->entidad_id);
    }
}
