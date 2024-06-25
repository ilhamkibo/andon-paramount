<?php

namespace Database\Seeders;

use App\Models\OperationTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OperationTime::create([
            'name_id' => 1,
            'start' => '08:10:00',
            'finish' => '10:00:00',
            'status' => 1
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '10:00:00',
            'finish' => '10:10:00',
            'status' => 2
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '10:10:00',
            'finish' => '12:15:00',
            'status' => 1
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '12:15:00',
            'finish' => '13:00:00',
            'status' => 2
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '13:00:00',
            'finish' => '15:30:00',
            'status' => 1
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '15:30:00',
            'finish' => '15:45:00',
            'status' => 2
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '15:45:00',
            'finish' => '17:55:00',
            'status' => 1
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '17:55:00',
            'finish' => '18:15:00',
            'status' => 2
        ]);

        OperationTime::create([
            'name_id' => 1,
            'start' => '18:15:00',
            'finish' => '19:00:00',
            'status' => 1
        ]);
    }
}
