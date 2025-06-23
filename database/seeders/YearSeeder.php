<?php

namespace Database\Seeders;

use App\Models\Year;
use Illuminate\Database\Seeder;

class YearSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = date('Y');
        $startYear = $currentYear - 5;
        $endYear = $currentYear + 2;

        for ($year = $startYear; $year <= $endYear; $year++) {
            Year::create([
                'year' => $year,
                'status' => true,
                'created_by' => 1,
            ]);
        }
    }
}
