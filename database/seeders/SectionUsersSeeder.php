<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Section;
use App\Models\Role;

class SectionUsersSeeder extends Seeder
{
    public function run(): void
    {
        $sections = Section::all();
        $roles = Role::all();

        foreach ($sections as $section) {
            for ($i = 1; $i <= 3; $i++) {
                $user = User::create([
                    'name' => $section->name . ' User ' . $i,
                    'email' => strtolower($section->name) . $i . '@example.com',
                    'password' => bcrypt('password123'),
                    'is_global_admin' => false,
                    'created_by' => null,
                ]);

                // Attach user to this section with a random role
                $randomRole = $roles->random();
                $user->sections()->attach([
                    $section->id => ['role_id' => $randomRole->id]
                ]);
            }
        }
    }
}
