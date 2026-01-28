<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superusuario = User::firstOrCreate([
                'name' => 'admin',
                'dni' => '45532962',
            ],[
            'password' => Hash::make('admin'),
            'role_id' => Role::where('nombre', 'SUPER USUARIO')->value('id'),
            ]);
        $roleId = Role::where('nombre', 'SUPER USUARIO')->value('id');
        $superusuario->roles()->sync([$roleId]);

        $superusuario = User::firstOrCreate([
                'name' => 'javier',
                'dni' => '46925538',
            ],[
            'password' => Hash::make('46925538'),
            'role_id' => Role::where('nombre', 'SUPER USUARIO')->value('id'),
            ]);
        $roleId = Role::where('nombre', 'SUPER USUARIO')->value('id');
        $superusuario->roles()->sync([$roleId]);



    }
}
