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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->nullable();
            $table->foreignId('grupo_menu_id')->constrained('grupo_menus')->onDelete('cascade');
            $table->foreignId('padre_menu_id')
                ->nullable()
                ->constrained('menus')
                ->nullOnDelete();
            $table->string('icono')->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
