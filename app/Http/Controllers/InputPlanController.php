<?php

namespace App\Http\Controllers;

use App\Models\BedModels;
use App\Models\Line;
use App\Models\OperationTime;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Imports\BedModelsImport;
use App\Imports\PlanImport;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\isEmpty;

class InputPlanController extends Controller
{
    public $message = '';

    public function index()
    {
        // $gregorianDate = '2024-04-01';
        // $dt = Carbon::createFromFormat('Y-m-d', $gregorianDate);

        $plans = $this->getDataPlan();
        if (count($plans) === 0) {
            $dt = Carbon::now();
            if (request('date')) {
                $dt = Carbon::createFromFormat('Y-m-d', request('date'));
            }

            $cekTanggal  = $dt->toHijri()->isoFormat('MMMM');
            // Cek apakah bulan Hijriah adalah Ramadhan
            if ($cekTanggal === 'Ramadan') {
                $operationTimes = OperationTime::where('option', 3)->get();
            } else {
                $cekTanggal  = $dt->isoFormat('dddd');
                if ($cekTanggal === 'Friday') {
                    $operationTimes = OperationTime::where('option', 2)->get();
                } else {
                    $operationTimes = OperationTime::where('option', 1)->get();
                }
            }
        } else {
            $operationTimes = OperationTime::where('option', $plans[0]->option_time)->get();
        }


        $lines = Line::all();
        $bedModels = BedModels::orderBy('name', 'asc')->get();
        return view('input-plan', [
            'plans' => $plans,
            'lines' => $lines,
            'bedModels' => $bedModels,
            'operationTimes' => $operationTimes,
        ]);
    }

    private function getDataPlan()
    {
        if (request('date')) {
            $date = request('date');
            $dataPlans = Plan::whereDate('date', $date)
                ->orderBy('queue', 'asc')
                ->get();
        } else {
            $today = now()->format('Y-m-d');
            $dataPlans = Plan::whereDate('date', $today)
                ->orderBy('queue', 'asc')
                ->get();
        }
        // dd($dataPlans);

        if (count($dataPlans) !== 0) {
            $operationTimes = OperationTime::where('option', $dataPlans[0]->time_option)->get();
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
                    'option_time' => $dataPlan->time_option,
                    'line_id' => $dataPlan->line_id,
                    'start_time' => $startOperation,
                    'end_time' => $estimatedEndTime,
                    'target_quantity' => $targetQuantity,
                    'bed_models' => $dataPlan->bed_models->name,
                    'bed_models_id' => $bedModelId,
                    'tact_time' => $dataPlan->bed_models->tact_time,
                    'date' => $dataPlan->date
                ];
            }
            // dd($resultSet);
            return $resultSet;
        } else {
            return $dataPlans;
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'row-count' => 'required|integer',
            'date' => 'required|date',
            'bed_models_id' => 'required|array',
            'bed_models_id.*' => 'exists:bed_models,id',
            'target_quantity' => 'required|array',
            'target_quantity.*' => 'integer',
            'queue' => 'required|array',
            'queue.*' => 'integer',
        ]);

        $resultSet = [];
        // dd($validatedData);
        $todayDataPlan = Plan::whereDate('date', $validatedData['date'])->latest()->get();
        // dd($todayDataPlan);
        if (count($todayDataPlan) !== 0) {
            return redirect('/input-plan')->with("gagal", "Data plan for that date is already exist! Please delete the data first."); // Ganti 'route_name' dengan nama rute yang sesuai.
        } else {
            foreach ($validatedData['bed_models_id'] as $key => $modelId) {
                $bedModelId = $modelId;
                $targetQuantity = $validatedData['target_quantity'][$key];
                $queue = $validatedData['queue'][$key];
                $dt = Carbon::createFromFormat('Y-m-d', $validatedData['date']);
                $cekTanggal  = $dt->toHijri()->isoFormat('MMMM');
                // Cek apakah bulan Hijriah adalah Ramadhan
                if ($cekTanggal === 'Ramadan') {
                    $timeOption = 3;
                } else {
                    $cekTanggal  = $dt->isoFormat('dddd');
                    if ($cekTanggal === 'Friday') {
                        $timeOption = 2;
                    } else {
                        $timeOption = 1;
                    }
                }
                $resultSet[] = [
                    'date' => $validatedData['date'],
                    'target_quantity' => $targetQuantity,
                    'bed_models_id' => $bedModelId,
                    'time_option' => $timeOption,
                    'queue' => $queue
                ];
            }

            // dd($resultSet);
            // Contoh menyimpan data ke dalam database menggunakan model
            foreach ($resultSet as $data) {
                Plan::create($data);
            }


            return redirect('/input-plan')->with('sukses', 'Insert plan production success!'); // Ganti 'route_name' dengan nama rute yang sesuai.
        }
    }

    public function storeOperationtime(Request $request)
    {
        $validatedData = $request->validate([
            'start' => 'required',
            'finish' => 'required',
            'status' => ['required'],
        ]);

        // dd($validatedData);
        OperationTime::create($validatedData);

        return redirect('/input-plan')->with('sukses', 'Insert operation time success!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function addMasterData(Request $request, $id)
    {

        if ($id == 101) {
            $bedModel = BedModels::whereNull('name')->limit(1)->first();
        } else {
            $bedModel = BedModels::findOrFail($id);
        }

        if ($bedModel === null) {
            return redirect('/input-plan')->with('gagal', 'Master data fully entered!'); // Ganti 'route_name' dengan nama rute yang sesuai.
        }
        // Validasi data input jika diperlukan
        $request->validate([
            'name' => 'required|string',
            'tact_time' => 'required'
            // Tambahkan validasi untuk field lainnya
        ]);

        // Update data
        $bedModel->update([
            'name' => $request->input('name'),
            'tact_time' => $request->input('tact_time'),
            // Update field lainnya
        ]);
        return redirect('/input-plan')->with('sukses', 'Master data updated successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }

    public function updateMasterData(Request $request)
    {
        $bedModel = BedModels::whereNull('name')->limit(1)->first();

        if ($bedModel === null) {
            return redirect('/input-plan')->with('gagal', 'Master data fully entered!'); // Ganti 'route_name' dengan nama rute yang sesuai.
        }
        // Validasi data input jika diperlukan
        $request->validate([
            'name' => 'required|string',
            'tact_time' => 'required'
            // Tambahkan validasi untuk field lainnya
        ]);

        // Update data
        $bedModel->update([
            'name' => $request->input('name'),
            'tact_time' => $request->input('tact_time'),
            // Update field lainnya
        ]);
        return redirect('/input-plan')->with('sukses', 'Master data updated successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }

    public function updatePlanData(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        // Validasi data input jika diperlukan
        $request->validate([
            'line_id' => 'required',
            'bed_models_id' => 'required|integer',
            'target_quantity' => 'required|integer',
            // Tambahkan validasi untuk field lainnya
        ]);

        // Update data
        $plan->update([
            'line_id' => $request->input('line_id'),
            'bed_models_id' => $request->input('bed_models_id'),
            'target_quantity' => $request->input('target_quantity'),
            // Update field lainnya
        ]);
        return redirect('/input-plan')->with('sukses', 'Plan production data updated successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }

    public function updateOperationTimeData(Request $request, $id)
    {
        $operationTimes = OperationTime::findOrFail($id);
        // Validasi data input jika diperlukan
        $request->validate([
            'start' => 'required',
            'finish' => 'required',
            'status' => 'required',
            // Tambahkan validasi untuk field lainnya
        ]);

        // Update data
        $operationTimes->update([
            'start' => $request->input('start'),
            'finish' => $request->input('finish'),
            'status' => $request->input('status')
            // Update field lainnya
        ]);
        return redirect('/input-plan')->with('sukses', 'Operation time data updated successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }

    public function destroyMasterData($id)
    {
        $bedModel = BedModels::findOrFail($id);

        // Update data
        $bedModel->update([
            'name' => null,
            'tact_time' => null,
            // Update field lainnya
        ]);
        return redirect('/input-plan')->with('sukses', 'Master data deleted successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function destroyPlanData($id)
    {
        $plan = Plan::findOrFail($id);

        // Update data
        $plan->delete();
        return redirect('/input-plan')->with('sukses', 'Data plan deleted successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }

    public function destroyOperationTimeData($id)
    {
        $operationTime = OperationTime::findOrFail($id);

        // Update data
        $operationTime->delete();
        return redirect('/input-plan')->with('sukses', 'Data plan deleted successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function bulkDestroyPlanData()
    {
        $today = Carbon::today();
        if (request('date')) {
            Plan::whereDate('date', Carbon::parse(request('date')))->delete();
        } else {
            Plan::whereDate('date', $today)->delete();
        }
        return redirect('/input-plan')->with('sukses', 'Data plan deleted successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function importMaster(Request $request)
    {

        $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls,csv'
        ]);

        // Menangani request jika validasi berhasil
        if ($request->hasFile('fileExcel')) {
            $file = $request->file('fileExcel');
            Excel::import(new BedModelsImport, $file);

            return redirect('/input-plan')->with('sukses', 'Bed Models Master updated!');
        }

        // Redirect back with error if file is not present
        return redirect()->back()->with('gagal', 'File tidak ditemukan.');
    }

    public function importPlan(Request $request)
    {

        $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls,csv'
        ]);

        // Menangani request jika validasi berhasil
        if ($request->hasFile('fileExcel')) {
            $file = $request->file('fileExcel');
            Excel::import(new PlanImport, $file);
            return redirect('/input-plan')->with('sukses', 'Plan Production Updated/Inserted!');
        }

        // Redirect back with error if file is not present
        return redirect()->back()->with('gagal', 'File tidak ditemukan.');
    }
}
