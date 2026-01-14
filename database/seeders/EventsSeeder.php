<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure you have at least 3 sections in your database
        $sections = DB::table('sections')->pluck('id')->toArray();

        if (count($sections) < 3) {
            $this->command->info("You need at least 3 sections in the database.");
            return;
        }

        // Users for created_by (change IDs according to your users table)
        $adminId = 1;
        $userId = 1;

        // Create test events
        $events = [
            [
                'title' => 'Chabiba Fundraiser',
                'description' => 'A fundraising event for Chabiba.',
                'event_date' => Carbon::now()->addDays(5),
                'type' => 'Fundraiser',
                'total_spent' => 100,
                'total_revenue' => 500,
                'created_by' => $adminId,
                'notes' => 'Bring flyers',
                'drive_link' => 'https://drive.google.com/example1',
            ],
            [
                'title' => 'Tala2e3 Meeting',
                'description' => 'Monthly Tala2e3 president meeting.',
                'event_date' => Carbon::now()->addDays(10),
                'type' => 'Meeting',
                'total_spent' => 50,
                'total_revenue' => 0,
                'created_by' => $userId,
                'notes' => null,
                'drive_link' => null,
            ],
            [
                'title' => 'Shared Festival',
                'description' => 'Event shared across all sections.',
                'event_date' => Carbon::now()->addDays(15),
                'type' => 'Festival',
                'total_spent' => 300,
                'total_revenue' => 1000,
                'created_by' => $adminId,
                'notes' => 'All sections invited',
                'drive_link' => 'https://drive.google.com/example2',
            ],
        ];

        foreach ($events as $event) {
            $eventId = DB::table('events')->insertGetId([
                'title' => $event['title'],
                'description' => $event['description'],
                'event_date' => $event['event_date'],
                'type' => $event['type'],
                'total_spent' => $event['total_spent'],
                'total_revenue' => $event['total_revenue'],
                'created_by' => $event['created_by'],
                'notes' => $event['notes'],
                'drive_link' => $event['drive_link'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Attach sections
            if ($event['title'] === 'Shared Festival') {
                // Attach all sections for shared event
                foreach ($sections as $sid) {
                    DB::table('event_section')->insert([
                        'event_id' => $eventId,
                        'section_id' => $sid,
                    ]);
                }
            } else {
                // Attach only the first section
                DB::table('event_section')->insert([
                    'event_id' => $eventId,
                    'section_id' => $sections[0],
                ]);
            }
        }

        $this->command->info('Test events seeded successfully!');
    }
}
