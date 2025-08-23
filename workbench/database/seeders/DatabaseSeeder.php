<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 04, 10, 2, 38, 15));

        $dates = [
            // Last year data
            Carbon::now()->subYear()->subMonth(2),
            Carbon::now()->subYear()->subMonth(1),
            Carbon::now()->subYear(),

            // This year data (different months)
            Carbon::now()->subMonths(3),
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonths(1),

            // This month data (different weeks)
            Carbon::now()->subWeeks(3),
            Carbon::now()->subWeeks(2),
            Carbon::now()->subWeeks(1),

            // This week data (different days)
            Carbon::now()->subDays(4),
            Carbon::now()->subDays(3),
            Carbon::now()->subDays(2),
            Carbon::now()->subDays(1),

            // This day data (different hours)
            Carbon::now()->subHours(4),
        ];

        // Generate 10,000 rows in the test_data table
        foreach ($dates as $date) {
            for ($i = 0; $i < 10000; $i++) {
                DB::table('test_data')->insert([
                    'name' => 'Test Data '.$i,
                    'value' => rand(1, 100000),
                    'category' => 'Category '.random_int(1, 1000),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        // Duplicate the data to reach 1,000,000 rows
        for ($i = 0; $i < 100; $i++) {
            DB::statement('INSERT INTO test_data (name, value, category, created_at, updated_at) SELECT name, value, category, created_at, updated_at FROM test_data');
            DB::statement('UPDATE test_data SET name = CONCAT(\'Test Data \', id)');
        }
    }
}
