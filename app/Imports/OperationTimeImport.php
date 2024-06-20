<?php

namespace App\Imports;

use App\Models\OperationTime;
use Carbon\Carbon;
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
        $mapping = [];

        for ($i = 1; $i <= 50; $i++) {
            $mapping["id{$i}"] = "A" . ($i + 1);
            $mapping["option{$i}"] = "B" . ($i + 1);
            $mapping["start{$i}"] = "C" . ($i + 1);
            $mapping["finish{$i}"] = "D" . ($i + 1);
            $mapping["status{$i}"] = "E" . ($i + 1);
        }

        return $mapping;
    }

    public function model(array $row)
    {
        for ($i = 1; $i <= 50; $i++) {
            $options = isset($row["option{$i}"]) ? strtolower($row["option{$i}"]) : null;
            if ($options !== null) {
                $ani = $options === "normal day" ? 1 : ($options === "friday" ? 2 : 3);
            }

            if (!isset($row["id{$i}"])) {
                $row["option{$i}"] = null;
            } else {
                $row["option{$i}"] = $ani ?? null;
            }

            $row["status{$i}"] = isset($row["status{$i}"]) ? (strtolower($row["status{$i}"]) == "work" ? 1 : 2) : null;

            // Periksa dan konversi waktu jika diperlukan
            $row["start{$i}"] = isset($row["start{$i}"]) ? $this->excelTimeToString($row["start{$i}"]) : null;
            $row["finish{$i}"] = isset($row["finish{$i}"]) ? $this->excelTimeToString($row["finish{$i}"]) : null;
            $id =  $row["id{$i}"];
            $option =  $row["option{$i}"];
            $finish =  $row["finish{$i}"];
            $start =  $row["start{$i}"];
            $status =  $row["status{$i}"];

            if ($id && $option && $finish && $start && $status) {
                OperationTime::updateOrInsert(
                    [
                        'id' => $id
                    ],
                    [
                        'option' => $option,
                        'start' => $start,
                        'finish' => $finish,
                        'status' => $status
                    ]
                );
            }
        }
        // dd($row);
    }

    private function excelTimeToString($value)
    {
        if (is_numeric($value)) {
            $hours = floor($value * 24);
            $minutes = round(($value * 1440) % 60);
            return sprintf('%02d:%02d', $hours, $minutes);
        }
        return $value; // Jika bukan desimal, kembalikan nilai asli
    }
}
