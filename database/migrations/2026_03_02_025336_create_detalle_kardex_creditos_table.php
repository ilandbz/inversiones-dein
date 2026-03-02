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
        Schema::create('detalle_kardex_creditos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kardex_credito_id')
                ->constrained('kardex_creditos')
                ->cascadeOnDelete();

            $table->foreignId('cronograma_id')
                ->constrained('cronograma_pagos')
                ->restrictOnDelete();

            $table->decimal('capital_pagado', 12, 2)->default(0);
            $table->decimal('interes_pagado', 12, 2)->default(0);
            $table->decimal('mora_pagada', 12, 2)->default(0);

            $table->text('observacion')->nullable();

            $table->timestamps();
            $table->index(['cronograma_id']);
            $table->index(['kardex_credito_id']);
            $table->index(['kardex_credito_id', 'cronograma_id']);
            $table->unique(['kardex_credito_id', 'cronograma_id'], 'uk_kardex_cuota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_kardex_creditos');
    }
};
