<?php

namespace Database\Seeders;

use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan3 = Carbon::now()->startOfDay();
        $plan2 = Carbon::now()->subday()->startOfDay();
        $plan1 = Carbon::now()->subDays(2)->startOfDay();
    }
}
