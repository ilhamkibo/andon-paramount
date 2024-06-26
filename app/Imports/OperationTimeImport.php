<?php

namespace App\Imports;

use App\Models\OperationName;
use App\Models\OperationTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

        for ($i = 1; $i <= 1003; $i++) {
            $mapping["id{$i}"] = "A" . ($i + 3);
            $mapping["name_day_operation{$i}"] = "B" . ($i + 3);
            $mapping["start{$i}"] = "C" . ($i + 3);
            $mapping["finish{$i}"] = "D" . ($i + 3);
            $mapping["status{$i}"] = "E" . ($i + 3);
        }
        return $mapping;
    }

    public function model(array $row)
    {
        $name_id = 0;
        DB::table('operation_times')->update([
            'start' => null,
            'finish' => null,
            'status' => null
        ]);
        for ($i = 1; $i <= 1003; $i++) {
            $options = isset($row["name_day_operation{$i}"]) ? $row["name_day_operation{$i}"] : null;
            if ($options !== null) {
                $name_id = $name_id + 1;
                $nama_waktu = $options;
                OperationName::updateOrInsert(
                    [
                        'id' => $name_id
                    ],
                    [
                        'name' => $nama_waktu,
                    ]
                );
            }


            if (!isset($row["id{$i}"])) {
                $row["name_day_operation{$i}"] = null;
            } else {
                $row["name_day_operation{$i}"] = $name_id ?? null;
            }

            $row["status{$i}"] = isset($row["status{$i}"]) ? (strtolower($row["status{$i}"]) == "work" ? 1 : 2) : null;

            // Periksa dan konversi waktu jika diperlukan
            $row["start{$i}"] = isset($row["start{$i}"]) ? $this->excelTimeToString($row["start{$i}"]) : null;
            $row["finish{$i}"] = isset($row["finish{$i}"]) ? $this->excelTimeToString($row["finish{$i}"]) : null;
            $id =  $row["id{$i}"];
            $option =  $row["name_day_operation{$i}"];
            $finish =  $row["finish{$i}"];
            $start =  $row["start{$i}"];
            $status =  $row["status{$i}"];

            if ($id && $option && $finish && $start && $status) {
                OperationTime::updateOrInsert(
                    [
                        'id' => $id
                    ],
                    [
                        'name_id' => $option,
                        'start' => $start,
                        'finish' => $finish,
                        'status' => $status
                    ]
                );
            }
        }

        DB::table('operation_times')->whereNull('start')->whereNull('finish')->delete();

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
