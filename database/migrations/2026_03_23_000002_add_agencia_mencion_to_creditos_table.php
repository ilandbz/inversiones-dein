<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            if (!Schema::hasColumn('creditos', 'agencia_id')) {
                $table->foreignId('agencia_id')
                    ->nullable()
                    ->after('asesor_id')
                    ->constrained('agencias')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('creditos', 'mencion')) {
                $table->string('mencion', 100)->nullable()->after('fecha_venc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropForeign(['agencia_id']);
            $table->dropColumn(['agencia_id', 'mencion']);
        });
    }
};
