<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;


class MeetingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        // Assuming you already have sections with these IDs
        $sections = [
            ['id' => 1, 'title' => 'Chabiba Meetings', 'drive_link' => 'https://drive.google.com/chabiba'],
            ['id' => 2, 'title' => 'Tala2e3 Meetings', 'drive_link' => 'https://drive.google.com/tala2e3'],
            ['id' => 3, 'title' => 'Forsan Meetings', 'drive_link' => 'https://drive.google.com/forsan'],
        ];

        foreach ($sections as $section) {
            DB::table('meetings')->insert([
                'section_id' => $section['id'],
                'title' => $section['title'],
                'drive_link' => $section['drive_link'],
                'created_by' => 1, // admin user ID
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
