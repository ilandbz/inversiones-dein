<?php

namespace Database\Seeders;

use App\Models\Propiedad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropiedadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propiedades = [
            'EQUIPO DE SONIDO',
            'MOTO LINEAL',
            'MOTO TAXI (BAJAT)',
            'AUTO',
            'MOTO FURGON',
            'CAMIONETA',
            'AUTO',
            'VOLVO',
            'COMPUTADORA',
            'REFRIGERADORA',
            'TELEVISOR',
            'HORNO',
            'COCINA',
            'CONGELADORA',
        ];

        foreach ($propiedades as $propiedad) {
            Propiedad::create([
                'nombre' => $propiedad,
            ]);
        }
    }
}
