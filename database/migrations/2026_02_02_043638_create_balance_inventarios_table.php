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
        Schema::create('balance_inventarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('balance_id')
                ->constrained('balances')
                ->cascadeOnDelete();

            $table->decimal('inv_materiales', 14, 2)->default(0);
            $table->decimal('inv_prodproc', 14, 2)->default(0);
            $table->decimal('inv_prodtermi', 14, 2)->default(0);

            $table->timestamps();


            $table->unique('balance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_inventarios');
    }
};
