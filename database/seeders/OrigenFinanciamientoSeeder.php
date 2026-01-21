<?php

namespace Database\Seeders;

use App\Models\OrigenFinanciamiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrigenFinanciamientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registros = [
            'Prendario',
            'Transporte',
            'Garantia de Fondo',
        ];
        
        
        foreach ($registros as $role) {
            OrigenFinanciamiento::firstOrCreate(['nombre' => mb_strtoupper($role)]);
        }
    }
}
