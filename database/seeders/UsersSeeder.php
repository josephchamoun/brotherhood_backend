<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Section;
use App\Models\Role;
use App\Models\SectionUserRole;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */
        $superAdmin = User::updateOrCreate(
            ['email' => 'chamounjoseph25@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('admin123$'),
                'created_by' => null,
                'is_global_admin' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Sections & Roles
        |--------------------------------------------------------------------------
        */
        $chabiba = Section::where('name', 'Chabiba')->firstOrFail();
        $talaee  = Section::where('name', 'Talaee')->firstOrFail();

        $highAdminRole  = Role::where('name', 'High Admin')->firstOrFail();
        $normalUserRole = Role::where('name', 'Normal User')->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Assign Super Admin to Sections
        |--------------------------------------------------------------------------
        */
        SectionUserRole::updateOrCreate(
            [
                'user_id'    => $superAdmin->id,
                'section_id' => $chabiba->id,
                'role_id'    => $highAdminRole->id,
                'start_date' => $today,
            ],
            [
                'end_date' => null,
            ]
        );

        SectionUserRole::updateOrCreate(
            [
                'user_id'    => $superAdmin->id,
                'section_id' => $talaee->id,
                'role_id'    => $highAdminRole->id,
                'start_date' => $today,
            ],
            [
                'end_date' => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Normal User
        |--------------------------------------------------------------------------
        */
        $user = User::updateOrCreate(
            ['email' => 'chamounjoseph@gmail.com'],
            [
                'name' => 'Joseph Chamoun',
                'password' => bcrypt('password'),
                'created_by' => $superAdmin->id,
                'is_global_admin' => false,
            ]
        );

        SectionUserRole::updateOrCreate(
            [
                'user_id'    => $user->id,
                'section_id' => $chabiba->id,
                'role_id'    => $normalUserRole->id,
                'start_date' => $today,
            ],
            [
                'end_date' => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Global Admin (no section assignment)
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Global Admin',
                'password' => bcrypt('admin123$'),
                'is_global_admin' => true,
            ]
        );
    }
}
