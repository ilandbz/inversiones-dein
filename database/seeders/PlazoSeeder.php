<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlazoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plazos = [

            // ===================== DIARIA =====================
            ['frecuencia' => 'DIARIA', 'plazo' => 7,  'tasainteres' => 0, 'costomora' => 0],
            ['frecuencia' => 'DIARIA', 'plazo' => 15, 'tasainteres' => 5, 'costomora' => 0],
            ['frecuencia' => 'DIARIA', 'plazo' => 30, 'tasainteres' => 10, 'costomora' => 0],
            ['frecuencia' => 'DIARIA', 'plazo' => 45, 'tasainteres' => 15, 'costomora' => 0],
            ['frecuencia' => 'DIARIA', 'plazo' => 60, 'tasainteres' => 20, 'costomora' => 0],

            // ===================== SEMANAL =====================
            ['frecuencia' => 'SEMANAL', 'plazo' => 4,  'tasainteres' => 0, 'costomora' => 0],
            ['frecuencia' => 'SEMANAL', 'plazo' => 8,  'tasainteres' => 5, 'costomora' => 0],
            ['frecuencia' => 'SEMANAL', 'plazo' => 12, 'tasainteres' => 10, 'costomora' => 0],
            ['frecuencia' => 'SEMANAL', 'plazo' => 16, 'tasainteres' => 15, 'costomora' => 0],
            ['frecuencia' => 'SEMANAL', 'plazo' => 20, 'tasainteres' => 20, 'costomora' => 0],

            // ===================== QUINCENAL =====================
            ['frecuencia' => 'QUINCENAL', 'plazo' => 2,  'tasainteres' => 0, 'costomora' => 0],
            ['frecuencia' => 'QUINCENAL', 'plazo' => 4,  'tasainteres' => 5, 'costomora' => 0],
            ['frecuencia' => 'QUINCENAL', 'plazo' => 6,  'tasainteres' => 10, 'costomora' => 0],
            ['frecuencia' => 'QUINCENAL', 'plazo' => 8,  'tasainteres' => 15, 'costomora' => 0],
            ['frecuencia' => 'QUINCENAL', 'plazo' => 10, 'tasainteres' => 20, 'costomora' => 0],
            ['frecuencia' => 'QUINCENAL', 'plazo' => 12, 'tasainteres' => 25, 'costomora' => 0],

            // ===================== MENSUAL =====================
            ['frecuencia' => 'MENSUAL', 'plazo' => 3,  'tasainteres' => 0, 'costomora' => 0],
            ['frecuencia' => 'MENSUAL', 'plazo' => 6,  'tasainteres' => 5, 'costomora' => 0],
            ['frecuencia' => 'MENSUAL', 'plazo' => 9,  'tasainteres' => 10, 'costomora' => 0],
            ['frecuencia' => 'MENSUAL', 'plazo' => 12, 'tasainteres' => 15, 'costomora' => 0],
            ['frecuencia' => 'MENSUAL', 'plazo' => 18, 'tasainteres' => 20, 'costomora' => 0],
            ['frecuencia' => 'MENSUAL', 'plazo' => 24, 'tasainteres' => 25, 'costomora' => 0],
        ];

        DB::table('plazos')->insert($plazos);
    }
}
