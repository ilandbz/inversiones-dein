<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')
                ->constrained('agencias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->comment('Cajero responsable')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_apertura');
            $table->time('hora_cierre')->nullable();
            $table->decimal('saldo_inicial', 12, 2)->default(0.00);
            $table->decimal('saldo_final', 12, 2)->nullable();
            $table->decimal('efectivo_declarado', 12, 2)->nullable()->comment('Arqueo físico al cerrar');
            $table->decimal('diferencia', 12, 2)->nullable()->comment('efectivo_declarado - saldo_final');
            // Estados: ABIERTA | CERRADA
            $table->string('estado', 20)->default('ABIERTA');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['agencia_id', 'fecha', 'user_id'], 'uk_caja_agencia_fecha_user');
            $table->index(['fecha', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
