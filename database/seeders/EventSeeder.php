<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventDetail;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Annual Sports Meet',
                'category_id' => 3, // Sports
                'description' => 'Annual inter-department sports competition featuring various indoor and outdoor games.',
                'years' => [2023, 2024],
                'venues' => ['Main Stadium', 'Sports Complex']
            ],
            [
                'title' => 'Cultural Festival',
                'category_id' => 2, // Cultural
                'description' => 'A grand celebration of art, music, and cultural diversity.',
                'years' => [2023, 2024],
                'venues' => ['Auditorium', 'Open Air Theater']
            ],
            [
                'title' => 'Technology Conference',
                'category_id' => 7, // Conference under Academic
                'description' => 'Annual technology conference featuring industry experts and latest innovations.',
                'years' => [2023, 2024],
                'venues' => ['Conference Hall', 'Virtual Platform']
            ],
        ];

        foreach ($events as $eventData) {
            $event = Event::create([
                'title' => $eventData['title'],
                'slug' => Str::slug($eventData['title']),
                'category_id' => $eventData['category_id'],
                'description' => $eventData['description'],
                'status' => true,
                'created_by' => 1,
            ]);

            foreach ($eventData['years'] as $index => $yearValue) {
                $year = Year::where('year', $yearValue)->first();
                if ($year) {
                    EventDetail::create([
                        'event_id' => $event->id,
                        'year_id' => $year->id,
                        'venue' => $eventData['venues'][$index % count($eventData['venues'])],
                        'start_date' => "$yearValue-01-01",
                        'end_date' => "$yearValue-01-03",
                        'start_time' => '09:00:00',
                        'end_time' => '18:00:00',
                        'status' => true,
                        'created_by' => 1,
                    ]);
                }
            }
        }
    }
}
