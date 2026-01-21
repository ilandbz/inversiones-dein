<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('estado', 40);
            $table->date('fecha_reg');
            $table->string('tipo', 40);
            $table->decimal('monto', 9, 2);
            $table->foreignId('origen_financiamiento_id')->constrained('origen_financiamientos')->onUpdate('cascade')->onDelete('cascade');
            $table->string('frecuencia', 30);
            $table->integer('plazo')->length(15);
            $table->string('dondepagara', 40)->default('NINGUNO');
            $table->string('fuenterecursos', 50);
            $table->decimal('tasainteres', 5, 2)->default(0.00);
            $table->decimal('total', 9, 2)->default(0.00);
            $table->decimal('costomora', 5, 2)->default(0.00);
            $table->date('fecha_venc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
