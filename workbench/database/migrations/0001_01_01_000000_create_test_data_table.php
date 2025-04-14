<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_data');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 04, 10, 2, 38, 15));

        Schema::create('test_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('value');
            $table->string('category');
            $table->timestamps();
        });

        /** @var Carbon $dates */
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
            Carbon::now()->subHours(3),
            Carbon::now()->subHours(2),
            Carbon::now()->subHours(1),

            // This hour data (different minutes)
            Carbon::now()->subMinutes(4),
            Carbon::now()->subMinutes(3),
            Carbon::now()->subMinutes(2),
            Carbon::now()->subMinutes(1),

            // This minute data (different seconds)
            Carbon::now()->subSeconds(4),
            Carbon::now()->subSeconds(3),
            Carbon::now()->subSeconds(2),
            Carbon::now()->subSeconds(1),

            Carbon::now(),
        ];

        // Insert records with different categories
        $categories = ['category1', 'category2', 'category3'];

        /** @var Carbon $date */
        foreach ($dates as $index => $date) {
            $category = $categories[$index % count($categories)];
            $value = ($index + 1) * 10;

            DB::table('test_data')->insert([
                'name' => "Item $index",
                'value' => $value,
                'category' => $category,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
};
