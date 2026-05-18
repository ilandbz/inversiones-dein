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
        // Corregir URL del menú Configuración (slug: configuracion)
        DB::table('menus')->where('slug', 'configuracion')->update(['url' => null]);
        
        // Obtener el ID del menú Configuración para asignarlo como padre
        $configMenu = DB::table('menus')->where('slug', 'configuracion')->first();
        
        if ($configMenu) {
            // Mover Tasas y Plazos (slug: tasas-plazos) debajo de Configuración
            DB::table('menus')->where('slug', 'tasas-plazos')->update([
                'padre_menu_id' => $configMenu->id,
                'grupo_menu_id' => $configMenu->grupo_menu_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir Configuración
        DB::table('menus')->where('slug', 'configuracion')->update(['url' => '/configuracion']);
        
        // Revertir Tasas y Plazos al menú original Gestión de Riesgos (slug: riesgos, grupo 4)
        $riesgosMenu = DB::table('menus')->where('slug', 'riesgos')->first();
        if ($riesgosMenu) {
            DB::table('menus')->where('slug', 'tasas-plazos')->update([
                'padre_menu_id' => $riesgosMenu->id,
                'grupo_menu_id' => $riesgosMenu->grupo_menu_id,
            ]);
        }
    }
};
