<?php

namespace Database\Seeders;

use App\Models\ActividadNegocio;
use App\Models\DetalleActividadNegocio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActividadNegocioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registros = [
            'COMERCIO',
            'PRODUCCION',
            'SERVICIO',
        ];

        foreach ($registros as $nombre) {
            ActividadNegocio::firstOrCreate(
                ['nombre' => $nombre]
            );
        }

        


        $detalles = [
            'COMERCIO' => [
                'Bodega / abarrotes',
                'Venta de ropa',
                'Ferretería',
                'Farmacia / botica',
                'Restaurante / comida rápida',
            ],
            'PRODUCCION' => [
                'Panadería artesanal',
                'Confección de prendas',
                'Carpintería',
                'Elaboración de lácteos',
                'Producción agrícola',
            ],
            'SERVICIO' => [
                'Taxi / transporte',
                'Peluquería / barbería',
                'Taller mecánico',
                'Servicio técnico de celulares',
                'Consultoría / asesoría',
            ],
        ];

        foreach ($detalles as $padreNombre => $hijos) {
            $padre = ActividadNegocio::where('nombre', $padreNombre)->first();

            if (!$padre) continue; // por si acaso

            foreach ($hijos as $hijo) {
                DetalleActividadNegocio::firstOrCreate([
                    'actividad_negocio_id' => $padre->id,
                    'nombre' => $hijo,
                ]);
            }
        }


    }
}
