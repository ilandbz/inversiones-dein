<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ahorro_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahorro_id')
                ->constrained('ahorros')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('caja_id')
                ->nullable()
                ->constrained('cajas')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora');
            // Tipo: DEPOSITO | RETIRO | INTERES | AJUSTE
            $table->string('tipo', 20);
            $table->decimal('monto', 12, 2);
            $table->decimal('saldo_anterior', 12, 2);
            $table->decimal('saldo_posterior', 12, 2);
            // Método: EFECTIVO | TRANSFERENCIA | DESCUENTO_CREDITO
            $table->string('metodo_pago', 50)->default('EFECTIVO');
            $table->string('referencia', 100)->nullable()->comment('Nro. operación o voucher');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['ahorro_id', 'fecha', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ahorro_movimientos');
    }
};
