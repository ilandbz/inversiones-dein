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
            $table->unsignedBigInteger('credito_id');
            $table->foreign('credito_id')->references('credito_id')->on('desembolsos')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('nrocuota');
            $table->date('fecha_prog');
            $table->string('nombredia', 10);
            $table->decimal('cuota', 9, 2);
            $table->decimal('saldo', 9, 2);
            $table->primary(['credito_id', 'nrocuota']);
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
