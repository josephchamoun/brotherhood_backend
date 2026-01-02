<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                Event::insert([
            ['title' => 'Christmas', 'description' => 'Christmas ','event_date' => '2024-12-12', 'type' => 'meeting', 'total_spent' => 1500,'total_revenue' => 2000,'created_by' => 1],
            ['title' => 'Halloween', 'description' => 'Halloween','event_date' => '2024-12-13', 'type' => 'meeting','total_spent' => 200,'total_revenue' => 100,'created_by' => 1],
            ['title' => 'Festival', 'description' => 'Festival','event_date' => '2024-12-14', 'type' => 'meeting','total_spent' => 55,'total_revenue' => 100,'created_by' => 1],
        ]);
    }
}
