<?php

namespace Database\Seeders; // ✅ THIS LINE WAS MISSING

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Section;
use App\Models\Role;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'chamounjoseph25@gmail.com',
            'password' => bcrypt('admin123$'),
            'created_by' => null,
            'is_global_admin' => true,
        ]);


        $chabiba = Section::where('name', 'Chabiba')->first();
        $talaee = Section::where('name', 'Talaee')->first();
        $highAdminRole = Role::where('name', 'High Admin')->first();
        $normalUserRole = Role::where('name', 'Normal User')->first();

        // Attach Super Admin to multiple sections
        $superAdmin->sections()->attach([
            $chabiba->id => ['role_id' => $highAdminRole->id],
            $talaee->id => ['role_id' => $highAdminRole->id],
        ]);

        // Create a normal user
        $user = User::create([
            'name' => 'Joseph Chamoun',
            'email' => 'chamounjoseph@gmail.com',
            'password' => bcrypt('password'),
            'created_by' => $superAdmin->id,
        ]);

        $user->sections()->attach([
            $chabiba->id => ['role_id' => $normalUserRole->id],
        ]);

        $globalAdmin = User::create([
    'name' => 'Global Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('admin123$'),
    'is_global_admin' => true,
]);





    }
}
