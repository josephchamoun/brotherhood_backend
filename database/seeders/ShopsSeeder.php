<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop;

class ShopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                Shop::insert([
            ['name' => 'maktabit al zewye', 'description' => 'idk', 'phone_number' => '0700000000', 'place' => 'al zewye']

        ]);
    }
}
