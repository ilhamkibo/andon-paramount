<?php

namespace App\Imports;

use App\Models\BedModels;
use App\Models\Plan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class PlanImport implements WithMappedCells, ToModel
{
    /**
     * @return array
     */
    public function mapping(): array
    {
        $mapping = [
            'line_id' => 'E2',
        ];

        for ($i = 1; $i <= 6; $i++) {
            $mapping["id{$i}"] = "A" . ($i + 1);
            $mapping["code{$i}"] = "B" . ($i + 1);
            $mapping["quantity{$i}"] = "C" . ($i + 1);
            $mapping["date{$i}"] = "D" . ($i + 1);
        }

        return $mapping;
    }

    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row): void
    {
        $lineId = $row['line_id'];

        for ($i = 1; $i <= 6; $i++) {
            $id = $row["id{$i}"];
            $code = $row["code{$i}"];
            $quantity = $row["quantity{$i}"];
            $date = $row["date{$i}"];

            if ($id && $code && $quantity && $date && $lineId) {
                $dateR = Carbon::parse(gmdate('Y-m-d', ((int)$date - 25569) * 86400));
                $bedModel = BedModels::where('name', $code)->first();

                if ($bedModel) {
                    Plan::updateOrInsert(
                        [
                            'date' => $dateR,
                            'queue' => $i,
                        ],
                        [
                            'queue' => $i,
                            'bed_models_id' => $bedModel->id,
                            'target_quantity' => $quantity,
                            'line_id' => $lineId,
                        ]
                    );
                }
            }
        }
    }
}
