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
        Schema::create('ubicacions', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 30)->nullable()->default('NDF'); // NDF = No Definido
            $table->char('ubigeo', 6);
            $table->string('tipovia', 35)->nullable()->default('S/N'); // Jr, Av, etc.
            $table->string('nombrevia', 90)->nullable();
            $table->string('nro', 25)->nullable()->default('S/N');
            $table->char('interior', 25)->nullable()->default('S/N');
            $table->char('mz', 25)->nullable()->default('S/N');
            $table->char('lote', 25)->nullable()->default('S/N');
            $table->string('tipozona', 35)->nullable(); // Urbanización, asentamiento, etc.
            $table->string('nombrezona', 50)->nullable();
            $table->string('referencia', 90)->nullable();
            $table->string('latitud', 60)->nullable();
            $table->string('longitud', 60)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubicacions');
    }
};
