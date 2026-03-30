<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener ID del grupo Administración
        $grupoId = DB::table('grupo_menus')->where('titulo', 'Administración')->value('id');
        // Obtener ID del menú principal Configuración
        $configuracionId = DB::table('menus')->where('nombre', 'Configuración')->value('id');

        if ($grupoId && $configuracionId) {
            $menuId = DB::table('menus')->insertGetId([
                'nombre' => 'Tasas y Plazos',
                'slug' => 'tasas-plazos',
                'grupo_menu_id' => $grupoId,
                'padre_menu_id' => $configuracionId,
                'icono' => 'fa-percentage',
                'url' => '/configuracion/plazos',
                'orden' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Asociar a roles SUPER USUARIO (1) y ADMINISTRADOR (2)
            DB::table('menu_role')->insert([
                ['menu_id' => $menuId, 'role_id' => 1],
                ['menu_id' => $menuId, 'role_id' => 2],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuId = DB::table('menus')->where('slug', 'tasas-plazos')->value('id');
        if ($menuId) {
            DB::table('menu_role')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }
    }
};
