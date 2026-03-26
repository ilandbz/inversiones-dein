<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_moras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')
                ->constrained('creditos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('kardex_credito_id')
                ->nullable()
                ->comment('Pago asociado al kardex')
                ->constrained('kardex_creditos')
                ->onUpdate('cascade')
                ->onDelete('set null');
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
            $table->integer('diasmora')->comment('Días de mora pagados en esta transacción');
            $table->decimal('costomora', 5, 2)->comment('Costo por día al momento del pago');
            $table->decimal('montomora', 9, 2)->comment('diasmora * costomora');
            $table->string('metodo_pago', 50)->default('EFECTIVO');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['credito_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_moras');
    }
};
