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
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('razonsocial', 80)->nullable();
            $table->string('ruc', 11)->nullable();
            $table->char('celular')->nullable();
            $table->foreignId('detalle_actividad_id')->nullable()->constrained('detalle_actividad_negocios', 'id')->onDelete('set null')->onUpdate('set null');
            $table->date('inicioactividad')->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negocios');
    }
};
