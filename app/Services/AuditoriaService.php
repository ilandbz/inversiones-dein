<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditoriaLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    /**
     * Registrar una acción en el log de auditoría.
     */
    public function registrar(
        string $modulo,
        string $accion,
        ?string $entidadTipo = null,
        ?int $entidadId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $descripcion = null,
        ?int $userId = null
    ): AuditoriaLog {
        return AuditoriaLog::create([
            'user_id'          => $userId ?? Auth::id(),
            'modulo'           => strtoupper($modulo),
            'accion'           => strtoupper($accion),
            'entidad_tipo'     => $entidadTipo,
            'entidad_id'       => $entidadId,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos'     => $datosNuevos,
            'ip'               => Request::ip(),
            'descripcion'      => $descripcion,
            'created_at'       => now(),
        ]);
    }

    /**
     * Registro rápido de creación.
     */
    public function logCreacion(string $modulo, object $modelo, ?string $descripcion = null): void
    {
        $this->registrar(
            $modulo,
            'CREAR',
            get_class($modelo),
            (int) $modelo->id,
            null,
            $modelo->toArray(),
            $descripcion
        );
    }

    /**
     * Registro rápido de actualización.
     */
    public function logActualizacion(string $modulo, object $modelo, array $anteriores, ?string $descripcion = null): void
    {
        $this->registrar(
            $modulo,
            'ACTUALIZAR',
            get_class($modelo),
            (int) $modelo->id,
            $anteriores,
            $modelo->toArray(),
            $descripcion
        );
    }

    /**
     * Registro rápido de eliminación.
     */
    public function logEliminacion(string $modulo, object $modelo, ?string $descripcion = null): void
    {
        $this->registrar(
            $modulo,
            'ELIMINAR',
            get_class($modelo),
            (int) $modelo->id,
            $modelo->toArray(),
            null,
            $descripcion
        );
    }
}
