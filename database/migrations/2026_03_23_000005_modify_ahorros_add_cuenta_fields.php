<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reestructura la tabla ahorros para que sea la CUENTA de ahorro
 * (cabecera). Los movimientos van en ahorro_movimientos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ahorros', function (Blueprint $table) {
            // Agregar campos de cuenta si no existen
            if (!Schema::hasColumn('ahorros', 'numero_cuenta')) {
                $table->string('numero_cuenta', 30)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('ahorros', 'asesor_id')) {
                $table->foreignId('asesor_id')
                    ->nullable()
                    ->after('cliente_id')
                    ->constrained('asesors')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('ahorros', 'agencia_id')) {
                $table->foreignId('agencia_id')
                    ->nullable()
                    ->after('asesor_id')
                    ->constrained('agencias')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('ahorros', 'saldo')) {
                $table->decimal('saldo', 12, 2)->default(0.00)->after('monto');
            }
            if (!Schema::hasColumn('ahorros', 'tasa_interes')) {
                $table->decimal('tasa_interes', 5, 4)->default(0.0000)
                    ->comment('Tasa de interés anual en decimal, ej: 0.0300 = 3%')
                    ->after('saldo');
            }
            if (!Schema::hasColumn('ahorros', 'fecha_apertura')) {
                $table->date('fecha_apertura')->nullable()->after('tasa_interes');
            }
            if (!Schema::hasColumn('ahorros', 'fecha_cierre')) {
                $table->date('fecha_cierre')->nullable()->after('fecha_apertura');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ahorros', function (Blueprint $table) {
            $table->dropColumn([
                'numero_cuenta', 'saldo', 'tasa_interes',
                'fecha_apertura', 'fecha_cierre',
            ]);
            $table->dropForeign(['asesor_id']);
            $table->dropColumn('asesor_id');
            $table->dropForeign(['agencia_id']);
            $table->dropColumn('agencia_id');
        });
    }
};
