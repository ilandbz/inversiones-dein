<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')
                ->constrained('asesors')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('caja_id')
                ->nullable()
                ->constrained('cajas')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('user_id')
                ->comment('Quien autorizó el pago')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora');
            $table->decimal('monto_total', 12, 2);
            $table->string('metodo_pago', 50)->default('EFECTIVO');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['asesor_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_comisiones');
    }
};
