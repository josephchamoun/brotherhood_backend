<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        Section::insert([
            ['name' => 'Chabiba', 'description' => 'شبيبة'],
            ['name' => 'Talaee', 'description' => 'طلائع'],
            ['name' => 'Forsan', 'description' => 'فرسان'],
        ]);
    }
}
