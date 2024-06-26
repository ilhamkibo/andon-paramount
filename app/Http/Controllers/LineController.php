<?php

namespace App\Http\Controllers;

use App\Models\BedModels;
use App\Models\OperationName;
use App\Models\OperationTime;
use App\Models\Plan;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LineController extends Controller
{
    public $message = '';

    public function index()
    {
        $operationNames = OperationName::all();
        $today = Carbon::today();
        $dt = Carbon::now();
        $dataPlans = $this->getDataPlan($today);
        if (count($dataPlans) === 0) {
            if (request('date')) {
                $dt = Carbon::createFromFormat('Y-m-d', request('date'));
            }

            $cekTanggal  = $dt->toHijri()->isoFormat('MMMM');
            // Cek apakah bulan Hijriah adalah Ramadhan
            if ($cekTanggal === 'Ramadan') {
                $operationTimes = OperationTime::where('name_id', 3)->get();
            } else {
                $cekTanggal  = $dt->isoFormat('dddd');
                if ($cekTanggal === 'Friday') {
                    $operationTimes = OperationTime::where('name_id', 2)->get();
                } else {
                    $operationTimes = OperationTime::where('name_id', 1)->get();
                }
            }
        } else {
            $operationTimes = OperationTime::where('name_id', $dataPlans[0]->time_option)->whereNotNull('start')->get();
        }


        // dd($operationTimes);
        $newDataPlans = $this->newDataPlan($dataPlans, $operationTimes);
        // dd(json_encode($dataPlans));
        $chartTime = $this->timeChart($operationTimes);
        $timeBreakModal = $this->modalBreak($operationTimes);
        // $dataActual = $this->getDataActual($dataPlans);
        // dd($chartTime);
        //array breaktimes
        $breakTimes = $this->calculateBreakTimes($operationTimes);
        $breakTimes1 = $breakTimes[0];
        $breakTimes0 = $breakTimes[1];
        $dataChartObject = $this->createObjectChart($newDataPlans, $breakTimes1);
        // dd($dataChartObject);
        //array untuk memunculkan nilai tooltip pada chart
        $forTooltips = $this->calculateTooltip($operationTimes, $breakTimes);
        $forTooltip0 = $forTooltips[1];
        $forTooltip1 = $forTooltips[0];
        //array untuk breaktime dan menghitung value produksi setiap 5 menit
        // $breakValue = [];

        return view('line', compact('dataChartObject', 'operationNames', 'operationTimes', 'timeBreakModal', 'chartTime', 'newDataPlans', 'dataPlans', 'breakTimes0', 'breakTimes1', 'forTooltip0', 'forTooltip1'))->with('message', $this->message);
    }

    private function getDataPlan($today)
    {
        if (request('date') || request('line_id')) {
            $dataPlans = $this->handleDateLineRequest();
        } else {
            $dataPlans = Plan::whereDate('date', $today)
                ->with(['bed_models'])
                ->orderby('queue', 'asc')
                ->get();

            if ($dataPlans->count() === 0) {

                $this->message = 'The data on ' . $today->format('F, j Y') . ' could not be found.';
            }
        }

        return $dataPlans;
    }

    public function updateOperationTimePlanData(Request $request, $date)
    {
        $validated = $request->validate([
            'opTime' => 'required|integer', // Adjust validation rules as necessary
        ]);
        // dd($validated);
        // Retrieve the plans for the given date
        $plans = Plan::where('date', $date)->get();
        // dd($plans);
        // Check if any plans are found for the given date
        if ($plans->isEmpty()) {
            return redirect()->back()->with('gagal', 'Data plan on that date is not found!');
        }

        // Update the operation time for each plan
        foreach ($plans as $plan) {
            $plan->time_option = $validated['opTime'];
            $plan->save();
        }

        // Redirect back with success message
        return redirect()->back()->with('sukses', 'Plan operation time updated successfully!');
    }

    private function handleDateLineRequest()
    {
        $today = Carbon::parse(request('date'))->setHour(8);

        $dataPlans = Plan::where('line_id', request('line_id'))
            ->with(['bed_models'])
            ->whereDate('date', $today)
            ->orderby('queue', 'asc')
            ->get();

        if ($dataPlans->count() === 0) {

            $this->message = 'The data on ' . $today->format('F, j Y') . ' could not be found.';
        }

        return $dataPlans;
    }

    private function createObjectChart($resultSet, $breakTimes0)
    {

        // $resultSet = $this->newDataPlan($dataPlans, $operationTimes);
        // dd($resultSet);

        $newArrays = [];

        foreach ($resultSet as $item) {
            $startTime = strtotime($item->start_time);
            $endTime = strtotime($item->end_time);

            $tempArray = [];

            while ($startTime <= $endTime + 299) {
                $formattedTime = date('Y-m-d H:i:s', $startTime);
                $tempArray[] = (object)['x' => $formattedTime, 'y' => 0];
                $isStartTimeRounded = ($startTime % 300) === 0;
                if ($isStartTimeRounded) {
                    $startTime += 300; // 5 minutes in seconds
                } else {
                    $roundedStartTime = ceil($startTime / 300) * 300;
                    $minutesToNextInterval = ($roundedStartTime - $startTime) / 60;
                    $startTime = $startTime + $minutesToNextInterval * 60;
                }
                // Tambahkan 5 menit untuk indeks berikutnya
            }

            // Periksa apakah waktu akhir ada di kelipatan 5 menit terdekat
            if ($endTime % 300 !== 0) {
                // Hitung selisih menit untuk menyesuaikan indeks terakhir
                $minutesDifference = ($endTime % 300) / 60;

                // Ambil indeks terakhir dan tambahkan selisih menit
                $lastIndex = count($tempArray) - 1;
                $tempArray[$lastIndex]->x = date('Y-m-d H:i:s', $endTime);
            }

            $newArrays[] = $tempArray;
        }

        // $breakTimes0 = $this->calculateBreakTimes($operationTimes)[0];

        foreach ($newArrays as $timeSlots) {
            foreach ($timeSlots as $value) {
                $formattedTime = date("H:i:s", strtotime($value->x));
                if (in_array($formattedTime, $breakTimes0)) {
                    $value->y = null;
                }
            }
        }

        // dd($newArrays);
        $producedQuantities = array_fill(0, count($resultSet), 0);

        for ($i = 0; $i < count($resultSet); $i++) {
            foreach ($newArrays[$i] as $j => $item) {
                if ($item->y !== null && $j !== 0) {
                    if ($producedQuantities[$i] > $resultSet[$i]->target_quantity + 0.9) {
                        unset($newArrays[$i][$j]);
                        break;
                    }

                    if ($j + 1 < count($newArrays[$i])) {
                        // Lakukan operasi dengan $newArrays[$i][$j + 1]->y
                        if (!is_null($newArrays[$i][$j - 1]->y)) {
                            $producedQuantities[$i] += 5 / $resultSet[$i]->tact_time;
                        }
                        // Mengecek apakah nilai desimal sama dengan .99
                        $newArrays[$i][$j]->y = $j == count($newArrays[$i]) - 1 && !is_null($newArrays[$i][$j - 1]->y)
                            ? min(($producedQuantities[$i]), $resultSet[$i]->target_quantity)
                            : ($producedQuantities[$i]);
                    } else {
                        // Jika $j adalah indeks terakhir
                        $producedQuantities[$i] += 5 / $resultSet[$i]->tact_time;
                        $newArrays[$i][$j]->y = min(($producedQuantities[$i]), $resultSet[$i]->target_quantity);
                    }

                    // if ($i == 0) {
                    //     # code...
                    //     echo $j . ' ' . $producedQuantities[$i] . ' ' . $newArrays[$i][$j]->y . ' ' . $newArrays[$i][$j]->x . '</br>';
                    // }
                }
            }
        }
        // dd($newArrays);
        return $newArrays;
    }

    private function newDataPlan($dataPlans, $operationTimes)
    {
        $resultSet = [];
        $previousOperationEnd = '';

        foreach ($dataPlans as $key => $dataPlan) {
            $bedModelId = $dataPlan->bed_models_id;
            $targetQuantity = $dataPlan->target_quantity;
            $bedModel = BedModels::find($bedModelId);
            $estimatedSpendTime = number_format(($bedModel->tact_time * $targetQuantity) / 60, 2);
            $planDate = Carbon::parse($dataPlan->date);
            $startOperation = ($key == 0)
                ?  Carbon::parse($operationTimes[0]->start)->copy()->setDate(
                    Carbon::parse($dataPlan->date)->year,
                    Carbon::parse($dataPlan->date)->month,
                    Carbon::parse($dataPlan->date)->day
                )
                : Carbon::parse($previousOperationEnd);

            $estimatedEndTime = $startOperation->copy()->setDate($planDate->year, $planDate->month, $planDate->day)->addMinutes(ceil($estimatedSpendTime * 60));

            foreach ($operationTimes as $index => $operationTime) {
                if ($operationTime->status == 2) {
                    $breakStartTime = Carbon::parse($operationTime->start)->setDate($planDate->year, $planDate->month, $planDate->day);
                    $breakEndTime = Carbon::parse($operationTime->finis)->setDate($planDate->year, $planDate->month, $planDate->day);

                    if ($breakStartTime->gt($startOperation) && $estimatedEndTime->gt($breakStartTime)) {
                        $breakDuration = Carbon::parse($operationTime->finish)->diffInMinutes(Carbon::parse($operationTime->start));
                        $estimatedEndTime->addMinutes(ceil($breakDuration));
                    }

                    $previousOperationEnd = $estimatedEndTime->copy();
                }
                if ($index == (count($operationTimes) - 1)) {
                    $previousOperationEnd = $estimatedEndTime->copy()->addSeconds(7.5 * 60);
                }
            }

            foreach ($operationTimes as $value) {
                if ($value->status == 2) {
                    $breakStartTime1 = Carbon::parse($value->start)->setDate($planDate->year, $planDate->month, $planDate->day);
                    $breakEndTime1 = Carbon::parse($value->finish)->setDate($planDate->year, $planDate->month, $planDate->day);

                    // echo $previousOperationEnd . ' ' . $breakStartTime1 . ' ' . $breakEndTime1 . ' ' . $key . '</br>';
                    if ($previousOperationEnd->between($breakStartTime1, $breakEndTime1)) {
                        $breakDuration1 = Carbon::parse($value->finish)->diffInMinutes(Carbon::parse($value->start));
                        $previousOperationEnd->addMinutes(ceil($breakDuration1));
                        // $previousOperationEnd = $breakEndTime1;

                        // echo $previousOperationEnd . ' ' . $breakStartTime1 . ' ' . $breakEndTime1 . ' ' . $key . '</br>';
                    }
                }
            }

            $resultSet[] = (object)[
                'id' => $dataPlan->id,
                'line_id' => $dataPlan->line_id,
                'start_time' => $startOperation,
                'end_time' => $estimatedEndTime,
                'target_quantity' => $targetQuantity,
                'bed_models' => $dataPlan->bed_models->name,
                'bed_models_id' => $bedModelId,
                'tact_time' => floatval($dataPlan->bed_models->tact_time),
                // 'tact_time' => $dataPlan->bed_models->tact_time,
                'date' => $dataPlan->date
            ];
        }
        // dd($resultSet);
        return $resultSet;
    }

    private function calculateBreakTimes($operationTime)
    {
        $breakTimes1 = [];

        foreach ($operationTime as $row) {
            if ($row->status == 2) {
                $startTime = Carbon::parse($row->start);
                $finishTime = Carbon::parse($row->finish);

                // Hitung selisih waktu dalam menit
                $diffInMinutes = $finishTime->diffInMinutes($startTime);
                // $breakValue[] = ['mines' => $diffInMinutes - 5, 'times' => $finishTime->subHours(7)->format('H:i:s.v\Z'), 'minesInd' => $diffInMinutes / 5];
                // Tambahkan nilai antara start dan finish setiap 5 menit
                for ($i = 5; $i < $diffInMinutes; $i += 5) {
                    // $breakTime = $startTime->copy()->addMinutes($i)->format('H:i:s');
                    $breakTimes1[] = $startTime->copy()->addMinutes($i)->format('H:i:s');
                }
            }
        }

        //mengubah nilai breaktime menjadi UTC
        $breakTimes0 = array_map(function ($time) {
            return Carbon::createFromFormat('H:i:s', $time)->subHours(7)->format('H:i:s.v\Z');
        }, $breakTimes1);

        return [$breakTimes1, $breakTimes0];
    }

    private function calculateTooltip($operationTime)
    {
        $tooltip1 = [];
        $opLastIndex = $operationTime->last();
        $selisihMenit = Carbon::parse($opLastIndex->finish)->diffInMinutes(Carbon::parse($operationTime[0]->start));
        //insert nilai finish work dari last index untuk tooltip
        $tooltip1[] = Carbon::parse($opLastIndex->finish)->format("H:i:s");

        foreach ($operationTime as $key => $row) {
            $waktuStart = Carbon::parse($row->start)->format('H:i:s');
            $waktuFinish = Carbon::parse($row->finish)->format('H:i:s');

            for ($i = 0; $i <= $selisihMenit; $i += 60) {
                //membuat variabel untuk waktu tiap jam xx:00:00 untuk kemudian di cek apakah berada diantara jam break
                $hourTime = Carbon::parse($operationTime[0]->start)->setMinute(0)->copy()->addMinutes($i)->format('H:i:s');

                // Cek apakah jam ini berada di antara start break dan finish break
                if (Carbon::parse($hourTime)->greaterThanOrEqualTo($waktuStart) && Carbon::parse($hourTime)->lessThan($waktuFinish)) {
                    if ($row->status !== 2) {
                        //insert nilai tooltip tiap jam xx:00:00 jika nilai jam tersebut tidak berada di break time
                        $tooltip1[] = Carbon::parse($hourTime)->format("H:i:s");
                    }
                }
            }

            // Cek apakah nilai tooltip sudah ada sebelumnya di dalam array
            if (!in_array($row->start, $tooltip1)) {
                //insert nilai tiap waktu start work untuk tooltip
                $tooltip1[] = Carbon::parse($row->start)->format("H:i:s");
            }
        }


        //mengurutkan nilai tooltip
        sort($tooltip1);

        //mengubah nilai tooltip menjadi UTC
        $tooltip0 = array_map(function ($time) {
            return Carbon::createFromFormat('H:i:s', $time)->subHours(7)->format('H:i:s.v\Z');
        }, $tooltip1);

        return [$tooltip1, $tooltip0];
    }

    private function getDataActual($dataPlan)
    {
        if (!empty($dataPlan) && isset($dataPlan[0])) {
            $dataActual = Production::where('plan_id', $dataPlan[0]->id)->count();
        } else {
            $dataActual = Production::where('plan_id', 1)->count();
        }

        return $dataActual;
    }

    private function timeChart($operationTime)
    {
        $startTime = $operationTime[0]->start;
        $endTime = $operationTime->last();
        $endTime = $endTime->finish;
        $chartTime = [$startTime, $endTime];

        return $chartTime;
    }

    private function modalBreak($operationTime)
    {
        $timeBreakModal = [];
        foreach ($operationTime as $value) {
            if ($value->status == 2) {
                $timeBreakModal[] = ['start' => $value->start, 'finish' => $value->finish];
            }
        }

        return $timeBreakModal;
    }

    public function show($dateReq)
    {
        $plans = Plan::where('date', $dateReq)->get();
        if (count($plans) === 0) {
            $dt = Carbon::now();
            if (request('date')) {
                $dt = Carbon::createFromFormat('Y-m-d', request('date'));
            }

            $cekTanggal  = $dt->toHijri()->isoFormat('MMMM');
            // Cek apakah bulan Hijriah adalah Ramadhan
            if ($cekTanggal === 'Ramadan') {
                $operationTimes = OperationTime::where('name_id', 3)->get();
            } else {
                $cekTanggal  = $dt->isoFormat('dddd');
                if ($cekTanggal === 'Friday') {
                    $operationTimes = OperationTime::where('name_id', 2)->get();
                } else {
                    $operationTimes = OperationTime::where('name_id', 1)->get();
                }
            }
        } else {
            $operationTimes = OperationTime::where('name_id', $plans[0]->time_option)->get();
        }
        $newDataPlans = $this->newDataPlan($plans, $operationTimes);

        $logProductions = [];

        foreach ($newDataPlans as $key => $value) {
            $logProduction = Production::where('plan_id', $value->id)->where('created_at', '>=', $value->start_time)->get();
            $logProductions[] = $logProduction;
        }

        return view('components.product', compact('logProductions', 'newDataPlans'));
    }

    public function getData($tanggal)
    {
        $dateData = Carbon::parse($tanggal);
        $dataPlans = Plan::whereDate('date', $dateData)->get();

        if (count($dataPlans) === 0) {
            $dt = Carbon::now();
            if (request('date')) {
                $dt = Carbon::createFromFormat('Y-m-d', request('date'));
            }

            $cekTanggal  = $dt->toHijri()->isoFormat('MMMM');
            // Cek apakah bulan Hijriah adalah Ramadhan
            if ($cekTanggal === 'Ramadan') {
                $operationTimes = OperationTime::where('name_id', 3)->get();
            } else {
                $cekTanggal  = $dt->isoFormat('dddd');
                if ($cekTanggal === 'Friday') {
                    $operationTimes = OperationTime::where('name_id', 2)->get();
                } else {
                    $operationTimes = OperationTime::where('name_id', 1)->get();
                }
            }
        } else {
            $operationTimes = OperationTime::where('name_id', $dataPlans[0]->time_option)->get();
        }

        $newDataPlans = $this->newDataPlan($dataPlans, $operationTimes);


        // Deklarasi variabel untuk menyimpan nilai jam, menit, dan detik
        $jamArray = [];
        $menitArray = [];
        $detikArray = [];
        $dataProductions = [];
        // Variabel untuk menyimpan hasil
        $startDates = [];
        $endDates = [];

        foreach ($newDataPlans as $timeObject) {
            // Explode string waktu menjadi array jam, menit, dan detik
            $onlyTime = Carbon::parse($timeObject->start_time);
            // Simpan nilai jam, menit, dan detik ke dalam variabel

            $jamStart = Carbon::parse($operationTimes[0]->start);
            $hourStart = $jamStart->hour;
            $menitStart = $jamStart->minute;
            $secondStart = $jamStart->second;

            $jamArray[] = $hourStart;
            $menitArray[] = $menitStart;
            $detikArray[] = $secondStart;
        }

        foreach ($newDataPlans as $key => $value) {
            // echo $value->id . ' ' . $jamArray[$key] . '</br>';
            $jamStart = Carbon::parse($operationTimes[0]->start);
            $hourStart = $jamStart->hour;
            $menitStart = $jamStart->minute;
            $secondStart = $jamStart->second;

            $dataProduction = Production::where('plan_id', $value->id)->where('created_at', '>=', $value->start_time->startOfDay()->setHour($hourStart)->setMinutes($menitStart)->format('Y-m-d H:i:s'))->with(['plan'])->get();
            $dataProductions[] = $dataProduction;
        }


        // list($jam, $menit, $detik) = explode(':', $operationTimes[0]->start);
        // $planDate = Carbon::parse('2024-01-12');
        // $latestData = Production::where('plan_id', 13)
        //     ->where('created_at', '>=', $planDate->startOfDay()->setHour($operationTimes[0]->start)->format('Y-m-d H:i:s'))
        //     ->with(['plan'])
        //     ->get();


        // dd($dataProductions);




        // Dapatkan tanggal awal dan akhir dari data terbaru
        foreach ($dataProductions as $key => $data) {
            if (!$data->isEmpty()) {

                // Loop melalui setiap data terbaru
                // Ambil nilai jam, menit, dan detik dari data terbaru
                // Buat objek Carbon untuk startDate
                $lastData = $data->sortByDesc('created_at')->first();

                $startDate = Carbon::parse($data->max('created_at'))
                    ->setHour($jamArray[$key])
                    ->setMinute($menitArray[$key] - 5)
                    ->setSecond($detikArray[$key]);

                // Buat endDate menggunakan now() dan timestamp
                $endDate = Carbon::parse($data->max('created_at'));
                // Simpan startDate dan endDate dalam array
                $startDates[] = $startDate;
                $endDates[] = $endDate;
            } else {
                // Jika tidak ada data, gunakan tanggal dari plan atau tanggal default jika plan tidak ada
                $startOfDay = $newDataPlans[0]->date ? Carbon::parse($newDataPlans[0]->date)->startOfDay() : now()->startOfDay();
                $startDate = $startOfDay->setHour($jamArray[$key])->setMinute($menitArray[$key] - 5)->setSecond($detikArray[$key])->setMicrosecond(0);

                $endOfDay = $newDataPlans[0]->date ? Carbon::parse($newDataPlans[0]->date)->endOfDay() : now()->endOfDay();
                $endDate = $endOfDay->setHour($jamArray[$key])->setMinute($menitArray[$key])->setSecond($detikArray[$key])->setMicrosecond(0);
                // Jika tidak ada data, gunakan tanggal dari plan atau tanggal default jika plan tidak ada
                $startDates[] = $startDate;
                $endDates[] = $endDate;
            }
        }

        // dd($startDates);

        $allIntervalData = [];

        foreach ($dataProductions as $keyOuter => $dataOuter) {
            $intervalData = [];

            // Ambil nilai startTime dan endTime untuk setiap data
            $startTime = $startDates[$keyOuter]->timestamp;
            $endTime = $endDates[$keyOuter]->timestamp;

            // Iterasi melalui interval waktu
            while ($startTime < $endTime) {


                $interval = 5 * 60; // 5 menit dalam detik
                $intervalStart = date('H:i:s', $startTime);
                $intervalEnd = date('H:i:s', $startTime + $interval);

                if (!$dataOuter->isEmpty() && count($intervalData) == 0) {
                    $isStartTimeRounded = ($startTime % 300) === 0;

                    if (!$isStartTimeRounded) {
                        $roundedStartTime = ceil($startTime / 300) * 300;
                        $minutesToNextInterval = ($roundedStartTime - $startTime) / 60;
                        $interval = $minutesToNextInterval * 60;
                    }
                }
                // Hitung jumlah baris untuk interval waktu tertentu
                $rowCount = $dataOuter->filter(function ($item) use ($intervalStart, $intervalEnd) {
                    $createdAt = date('H:i:s', strtotime($item->created_at));
                    return $createdAt >= $intervalStart && $createdAt < $intervalEnd;
                })->count();

                // Jumlahkan jumlah baris dari interval sebelumnya
                if (!empty($intervalData)) {
                    $rowCount += end($intervalData)['y'];
                }

                // Simpan data interval dan jumlah baris ke dalam array
                $intervalData[] = [
                    'x' => Carbon::parse($startTime)->format('Y-m-d ') . $intervalEnd,
                    'y' => $rowCount,
                ];

                // Tambahkan 5 menit untuk iterasi selanjutnya
                $startTime += $interval;
            }

            // Simpan data interval ke dalam array utama menggunakan kunci $keyOuter
            $allIntervalData[$keyOuter] = $intervalData;
        }

        // dd($allIntervalData);

        // Inisialisasi array untuk menyimpan interval dan jumlah baris
        // $intervalData = [];

        // // Tentukan waktu awal dan akhir
        // $startTime = $startDate->timestamp;
        // $endTime = $endDate->timestamp;
        // $interval = 5 * 60; // 5 menit dalam detik

        // // Iterasi melalui interval waktu
        // while ($startTime < $endTime) {

        //     $intervalStart = date('H:i', $startTime);
        //     $intervalEnd = date('H:i', $startTime + $interval);

        //     // Hitung jumlah baris untuk interval waktu tertentu
        //     $rowCount = $latestData->filter(function ($item) use ($intervalStart, $intervalEnd) {
        //         $createdAt = date('H:i', strtotime($item->created_at));
        //         return $createdAt >= $intervalStart && $createdAt < $intervalEnd;
        //     })->count();

        //     // Jumlahkan jumlah baris dari interval sebelumnya
        //     if (!empty($intervalData)) {
        //         $rowCount += end($intervalData)['y'];
        //     }

        //     // Simpan data interval dan jumlah baris ke dalam array
        //     $intervalData[] = [
        //         'x' => Carbon::parse($startTime)->format('Y-m-d ') . $intervalEnd . ":00",
        //         'y' => $rowCount,
        //     ];

        //     // Tambahkan 5 menit untuk iterasi selanjutnya
        //     $startTime += $interval;
        // }

        return response()->json($allIntervalData);
    }
}
