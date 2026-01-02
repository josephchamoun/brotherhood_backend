<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['name' => 'High Admin'],
            ['name' => 'Chabiba President'],
            ['name' => 'Tala2e3 President'],
            ['name' => 'Forsan President'],
            ['name' => 'Wakil Risele'],
            ['name' => 'Wakil E3lem'],
            ['name' => 'Amin Ser'],
            ['name' => 'Amin sandou2'],
            ['name' => 'Ne2b al Ra2is'],
            ['name' => 'Normal User'],
        ]);
    }
}
