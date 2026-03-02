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
        Schema::create('kardex_creditos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('credito_id');
            $table->foreign('credito_id')->references('credito_id')->on('desembolsos')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('nro');
            $table->date('fecha');
            $table->time('hora');

            $table->decimal('montopagado', 9, 2);

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');

            $table->string('mediopago', 35);

            $table->unique(['credito_id', 'nro'], 'uk_kardex_credito_nro');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex_creditos');
    }
};
