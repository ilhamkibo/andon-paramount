<?php

namespace App\Imports;

use App\Models\BedModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class BedModelsImport implements WithMappedCells, ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function mapping(): array
    {


        for ($i = 1; $i <= 2000; $i++) {
            $mapping["id{$i}"] = "A" . ($i + 1);
            $mapping["code{$i}"] = "B" . ($i + 1);
            $mapping["RT{$i}"] = "C" . ($i + 1);
            $mapping["ST{$i}"] = "D" . ($i + 1);
        }
        return $mapping;
    }

    public function model(array $row)
    {
        DB::table('bed_models')->update([
            'name' => null,
            'tact_time' => null,
            'setting_time' => null
        ]);

        for ($i = 1; $i <= 2000; $i++) {
            $id = $row["id{$i}"];
            $tact_time = $row["RT{$i}"];
            $setting_time = $row["ST{$i}"];
            if (is_numeric($id) && is_numeric($tact_time)) {
                BedModels::updateOrInsert(
                    [
                        'id' => $id,
                    ],
                    [
                        'name' => $row["code{$i}"],
                        'tact_time' => $tact_time,
                        'setting_time' => $setting_time
                    ]
                );
            }
        }

        DB::table('bed_models')->whereNull('name')->whereNull('tact_time')->delete();
    }
}
