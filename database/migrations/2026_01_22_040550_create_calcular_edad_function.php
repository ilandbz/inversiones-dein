<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS calcularedad;
            CREATE FUNCTION calcularedad(fecha_nac DATE) RETURNS INT
            DETERMINISTIC
            RETURN TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE());
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS calcularedad");
    }
};
