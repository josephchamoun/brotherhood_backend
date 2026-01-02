<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $highAdminRole = Role::where('name', 'High Admin')->first();

        User::create([
            'name' => 'Super Admin',
            'email' => 'chamounjoseph25@gmail.com',
            'password' => Hash::make('admin123$'),
            'role_id' => $highAdminRole->id,
            'section_id' => null,
            'created_by' => null,
        ]);

        User::create([
            'name' => 'Joseph Chamoun',
            'email' => 'chamounjoseph@gmail.com',
            'password' => Hash::make('joseph123$'),
            'role_id' => 10,
            'section_id' => 1,
            'created_by' => 1,
        ]);

        User::create([
            'name' => 'Carla Issa',
            'email' => 'carlaissa@gmail.com',
            'password' => Hash::make('carla123$'),
            'role_id' => 9,
            'section_id' => 1,
            'created_by' => 1,
        ]);

        User::create([
            'name' => 'Josephine Abdallah',
            'email' => 'jadchamoun@gmail.com',
            'password' => Hash::make('jad123$'),
            'role_id' => 3,
            'section_id' =>3 ,
            'created_by' => 1,
        ]);


    }
}

