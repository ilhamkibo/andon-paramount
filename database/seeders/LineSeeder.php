<?php

namespace Database\Seeders;

use App\Models\Line;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Line::create([
            'name' => 'Line 1',
            'created_at' => Carbon::now()
        ]);

        Line::create([
            'name' => 'Line 2',
            'created_at' => Carbon::now()
        ]);
    }
}
