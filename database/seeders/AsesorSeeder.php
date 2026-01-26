<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Asesor;
use App\Models\User;

class AsesorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superusuario = User::where('role_id', '1')->first();

        Asesor::create([
            'user_id' => $superusuario->id,
            'es_activo' => '1',
        ]);
    }
}
