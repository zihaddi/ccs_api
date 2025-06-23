<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['title' => 'Academic', 'parent_id' => null],
            ['title' => 'Cultural', 'parent_id' => null],
            ['title' => 'Sports', 'parent_id' => null],
            ['title' => 'Annual Function', 'parent_id' => null],
            ['title' => 'Seminar', 'parent_id' => 1],
            ['title' => 'Workshop', 'parent_id' => 1],
            ['title' => 'Conference', 'parent_id' => 1],
            ['title' => 'Music', 'parent_id' => 2],
            ['title' => 'Dance', 'parent_id' => 2],
            ['title' => 'Drama', 'parent_id' => 2],
            ['title' => 'Indoor', 'parent_id' => 3],
            ['title' => 'Outdoor', 'parent_id' => 3],
            ['title' => 'Tournament', 'parent_id' => 3],
        ];

        foreach ($categories as $category) {
            EventCategory::create([
                'title' => $category['title'],
                'slug' => Str::slug($category['title']),
                'parent_id' => $category['parent_id'],
                'status' => true,
                'created_by' => 1,
            ]);
        }
    }
}
