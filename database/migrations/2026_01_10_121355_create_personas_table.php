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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->char('dni', 8)->nullable()->unique();
            $table->string('ape_pat', 60);
            $table->string('ape_mat', 60);
            $table->string('primernombre', 70);
            $table->string('otrosnombres', 70)->nullable();
            $table->date('fecha_nac')->default('2000-01-01');
            $table->char('ubigeo_nac', 6)->nullable()->default('090101');
            $table->char('ubigeo_dom', 6)->nullable()->default('090101');
            $table->char('genero', 1)->default('M');
            $table->char('celular', 11)->nullable();
            $table->char('celular2', 11)->nullable();
            $table->string('email', 70)->nullable();
            $table->char('ruc', 11)->nullable();
            $table->string('estado_civil', 35)->default('SOLTERO');
            $table->string('profesion', 90)->nullable()->default('NINGUNO');
            $table->string('grado_instr', 40)->nullable();
            $table->string('origen_labor', 35)->nullable()->default('INDEPENDIENTE');
            $table->string('ocupacion', 90)->nullable()->default('NINGUNO');
            $table->string('institucion_lab', 90)->nullable()->default('NINGUNO');
            $table->foreignId('conyugue')->nullable()->constrained('personas')->onUpdate('restrict')->onDelete('restrict');
            $table->text('direccion')->nullable();
            $table->text('latitud_longitud')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
