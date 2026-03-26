<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // Módulo: CREDITO | AHORRO | CAJA | CLIENTE | ASESOR | DESEMBOLSO | PAGO_MORA | COMISION
            $table->string('modulo', 60);
            // Acción: CREAR | ACTUALIZAR | ELIMINAR | DESEMBOLSAR | PAGAR | ABRIR_CAJA | CERRAR_CAJA
            $table->string('accion', 60);
            $table->string('entidad_tipo', 80)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['modulo', 'accion', 'created_at']);
            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_logs');
    }
};
