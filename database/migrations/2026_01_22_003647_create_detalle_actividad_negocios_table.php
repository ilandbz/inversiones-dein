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
        Schema::create('detalle_actividad_negocios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_negocio_id')->nullable()->constrained('actividad_negocios','id')->onDelete('restrict')->onUpdate('cascade');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_actividad_negocios');
    }
};
