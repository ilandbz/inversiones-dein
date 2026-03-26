<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')
                ->constrained('cajas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->comment('Usuario que generó el movimiento')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->time('hora');
            // Tipo: INGRESO | EGRESO
            $table->string('tipo', 15);
            // Concepto: DESEMBOLSO | PAGO_CREDITO | DEPOSITO_AHORRO | RETIRO_AHORRO | GASTO | INGRESO_MANUAL | PAGO_MORA | COMISION
            $table->string('concepto', 60);
            $table->decimal('monto', 12, 2);
            // referencia polimórfica: credito_id, ahorro_movimiento_id, etc.
            $table->string('entidad_tipo', 60)->nullable()->comment('Modelo relacionado');
            $table->unsignedBigInteger('entidad_id')->nullable()->comment('ID del registro relacionado');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['caja_id', 'tipo']);
            $table->index(['entidad_tipo', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
