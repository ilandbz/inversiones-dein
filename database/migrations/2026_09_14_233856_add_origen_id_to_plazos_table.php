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
        Schema::table('plazos', function (Blueprint $table) {
            $table->unsignedBigInteger('origen_financiamiento_id')->nullable()->after('id');
            $table->foreign('origen_financiamiento_id')->references('id')->on('origen_financiamientos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plazos', function (Blueprint $table) {
            $table->dropForeign(['origen_financiamiento_id']);
            $table->dropColumn('origen_financiamiento_id');
        });
    }
};
