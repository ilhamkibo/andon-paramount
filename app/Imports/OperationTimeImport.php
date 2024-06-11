<?php

namespace App\Imports;

use App\Models\OperationTime;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class OperationTimeImport implements WithMappedCells, ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
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

    public function model(array $row)
    {
        return new OperationTime([
            //
        ]);
    }
}
