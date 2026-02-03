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
        Schema::create('balance_deudas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('balance_id')
                ->constrained('balances')
                ->cascadeOnDelete();

            $table->string('entidad', 255)->nullable();
            $table->decimal('saldo', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_deudas');
    }
};
