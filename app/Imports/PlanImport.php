<?php

namespace App\Imports;

use App\Models\BedModels;
use App\Models\Plan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

use function PHPSTORM_META\type;

class PlanImport implements WithMappedCells, ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function mapping(): array
    {
        $mapping["line_id"] = "E2";

        for ($i = 1; $i <= 6; $i++) {
            $mapping["id{$i}"] = "A" . ($i + 1);
            $mapping["code{$i}"] = "B" . ($i + 1);
            $mapping["quantity{$i}"] = "C" . ($i + 1);
            $mapping["date{$i}"] = "D" . ($i + 1);
        }
        return $mapping;
    }

    public function model(array $row)
    {
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($row["id{$i}"]) && !empty($row["code{$i}"]) && !empty($row["quantity{$i}"]) && !empty($row["date{$i}"]) && !empty($row["line_id"])) {
                // Ambil nilai tanggal mentah dari kolom
                $rawDate = $row["date{$i}"];
                $dateR = Carbon::parse(gmdate('Y-m-d', ((int)$rawDate - 25569) * 86400));
                $bedModel = BedModels::where('name', $row["code{$i}"])->first();

                if ($bedModel && $dateR) {
                    Plan::updateOrInsert(
                        [
                            'date' => $dateR, 'queue' => $i
                        ],
                        [
                            'queue' => $i,
                            'bed_models_id' => $bedModel->id,
                            'target_quantity' => $row["quantity{$i}"],
                            'line_id' => $row["line_id"]
                        ]
                    );
                }
            }
        }
    }
}
