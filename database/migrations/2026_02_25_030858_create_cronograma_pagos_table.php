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
        Schema::dropIfExists('cronograma_pagos');
        Schema::create('cronograma_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credito_id');
            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('nrocuota');
            $table->date('fecha_prog');
            $table->string('nombredia', 10);
            $table->decimal('capital_programado', 9, 2);
            $table->decimal('interes_programado', 9, 2);
            $table->decimal('mora_programada', 9, 2);
            $table->decimal('capital_pagado', 9, 2);
            $table->decimal('interes_pagado', 9, 2);
            $table->decimal('mora_pagada', 9, 2);
            $table->string('estado', 40);
            $table->unique(['credito_id', 'nrocuota']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cronograma_pagos');
    }
};
