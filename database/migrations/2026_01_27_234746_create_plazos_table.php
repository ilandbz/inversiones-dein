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
        Schema::create('plazos', function (Blueprint $table) {
            $table->id();
            $table->string('frecuencia', 50);
            $table->integer('plazo')->length(15);
            $table->decimal('tasainteres', 5, 2)->default(0.00);
            $table->decimal('costomora', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plazos');
    }
};
