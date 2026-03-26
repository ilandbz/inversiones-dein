<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones_asesor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')
                ->constrained('asesors')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('credito_id')
                ->nullable()
                ->constrained('creditos')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->date('fecha');
            // Tipo: DESEMBOLSO | RECUPERACION | BONO | DESCUENTO
            $table->string('tipo', 40);
            $table->decimal('monto_base', 12, 2)->comment('Monto sobre el cual se calcula la comisión');
            $table->decimal('porcentaje', 5, 4)->comment('Porcentaje aplicado, ej: 0.0300 = 3%');
            $table->decimal('monto_comision', 12, 2);
            // Estados: PENDIENTE | PAGADO | ANULADO
            $table->string('estado', 20)->default('PENDIENTE');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['asesor_id', 'estado', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones_asesor');
    }
};
