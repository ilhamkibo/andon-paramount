<?php

namespace App\Imports;

use App\Models\BedModels;
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
        }
        return $mapping;
    }

    public function model(array $row)
    {
        for ($i = 1; $i <= 2000; $i++) {
            $id = $row["id{$i}"];
            $tact_time = $row["RT{$i}"];
            if (is_numeric($id) && is_numeric($tact_time)) {
                BedModels::updateOrInsert(
                    [
                        'id' => $id,
                    ],
                    [
                        'name' => $row["code{$i}"],
                        'tact_time' => $tact_time
                    ]
                );
            }
        }
    }
}
