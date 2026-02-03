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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')
                ->constrained('creditos')
                ->cascadeOnDelete();
            $table->date('fecha')->nullable();
            // ACTIVO - corriente
            $table->decimal('activocaja', 14, 2)->default(0);
            $table->decimal('activobancos', 14, 2)->default(0);
            $table->decimal('activoctascobrar', 14, 2)->default(0);
            $table->decimal('activoinventarios', 14, 2)->default(0);
            $table->decimal('totalacorriente', 14, 2)->default(0);
            // ACTIVO - no corriente
            $table->decimal('activomueble', 14, 2)->default(0);
            $table->decimal('activootrosact', 14, 2)->default(0);
            $table->decimal('activodepre', 14, 2)->default(0);
            $table->decimal('totalancorriente', 14, 2)->default(0);
            // Totales activo
            $table->decimal('total_activo', 14, 2)->default(0);
            // PASIVO - corriente
            $table->decimal('pasivodeudaprove', 14, 2)->default(0);
            $table->decimal('pasivodeudaent', 14, 2)->default(0);
            $table->decimal('totalpcorriente', 14, 2)->default(0);
            // PASIVO - no corriente
            $table->decimal('pasivolargop', 14, 2)->default(0);
            $table->decimal('otrascuentaspagar', 14, 2)->default(0);
            $table->decimal('totalpncorriente', 14, 2)->default(0);
            // Totales pasivo
            $table->decimal('total_pasivo', 14, 2)->default(0);
            // PATRIMONIO / derivados
            $table->decimal('patrimonio', 14, 2)->default(0);
            $table->decimal('paspatrimonio', 14, 2)->default(0);
            $table->decimal('captrabajo', 14, 2)->default(0);
            $table->string('estado_crud', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
