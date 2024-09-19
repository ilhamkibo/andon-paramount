<?php

namespace App\Http\Controllers;

use App\Models\BedModels;
use App\Models\Line;
use App\Models\OperationTime;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Imports\BedModelsImport;
use App\Imports\OperationTimeImport;
use App\Imports\PlanImport;
use App\Models\Note;
use App\Models\OperationName;
use App\Models\Production;
use App\Models\Timer;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\Engine\Operands\Operand;

use function PHPUnit\Framework\isEmpty;

class InputPlanController extends Controller
{
    public $message = '';

    private function getDataPlan($today)
    {
        if (request('date') || request('line_id')) {
            return $this->handleDateLineRequest();
        }

        $dataPlans = Plan::whereDate('date', $today)
            ->with(['bed_models'])
            ->orderBy('queue', 'asc')
            ->get();

        if ($dataPlans->isEmpty()) {
            $this->message = 'The data on ' . $today->format('F, j Y') . ' could not be found.';
        }

        return $dataPlans;
    }

    private function handleDateLineRequest()
    {
        $today = Carbon::parse(request('date'))->setHour(8);
        $dataPlans = Plan::where('line_id', 1)
            ->with(['bed_models'])
            ->whereDate('date', $today)
            ->orderby('queue', 'asc')
            ->get();

        if ($dataPlans->isEmpty()) {
            $this->message = 'The data on ' . $today->format('F, j Y') . ' could not be found.';
        }
        return $dataPlans;
    }

    private function calculateBreakDuration($operationTimes, $startOperation, $estimatedEndTime, $planDate)
    {
        foreach ($operationTimes as $operationTime) {
            if ($operationTime->status != 2) continue;

            $breakStartTime = Carbon::parse($operationTime->start)->setDate($planDate->year, $planDate->month, $planDate->day);
            $breakEndTime = Carbon::parse($operationTime->finish)->setDate($planDate->year, $planDate->month, $planDate->day);

            if ($breakStartTime->gt($startOperation) && $estimatedEndTime->gt($breakStartTime)) {
                $breakDuration = $breakEndTime->diffInMinutes($breakStartTime);
                $estimatedEndTime->addMinutes(ceil($breakDuration));
            }
        }
        return $estimatedEndTime;
    }

    private function adjustForBreaks($operationTimes, $previousOperationEnd, $planDate)
    {
        foreach ($operationTimes as $value) {
            if ($value->status != 2) continue;

            $breakStartTime = Carbon::parse($value->start)->setDate($planDate->year, $planDate->month, $planDate->day);
            $breakEndTime = Carbon::parse($value->finish)->setDate($planDate->year, $planDate->month, $planDate->day);

            if ($previousOperationEnd->between($breakStartTime, $breakEndTime)) {
                $breakDuration = $breakEndTime->diffInMinutes($breakStartTime);
                $previousOperationEnd->addMinutes(ceil($breakDuration));
            }
        }
        return $previousOperationEnd;
    }

    private function newDataPlan($dataPlans, $operationTimes)
    {
        $resultSet = [];
        $previousOperationEnd = '';

        foreach ($dataPlans as $key => $dataPlan) {
            $bedModel = BedModels::find($dataPlan->bed_models_id);
            $estimatedSpendTime = number_format(($bedModel->tact_time * $dataPlan->target_quantity) / 60, 2);
            $planDate = Carbon::parse($dataPlan->date);

            $startOperation = ($key == 0)
                ? Carbon::parse($operationTimes[0]->start)->copy()->setDate($planDate->year, $planDate->month, $planDate->day)
                : Carbon::parse($previousOperationEnd);

            $estimatedEndTime = $startOperation->copy()->addMinutes(ceil($estimatedSpendTime * 60));
            $estimatedEndTime = $this->calculateBreakDuration($operationTimes, $startOperation, $estimatedEndTime, $planDate);
            $previousOperationEnd = $this->adjustForBreaks($operationTimes, $estimatedEndTime, $planDate)->addSeconds(ceil($bedModel->setting_time * 60));

            $resultSet[] = (object)[
                'id' => $dataPlan->id,
                'line_id' => $dataPlan->line_id,
                'start_time' => $startOperation,
                'end_time' => $estimatedEndTime,
                'target_quantity' => $dataPlan->target_quantity,
                'bed_models' => $dataPlan->bed_models->name,
                'bed_models_id' => $dataPlan->bed_models_id,
                'tact_time' => floatval($dataPlan->bed_models->tact_time),
                'date' => $dataPlan->date
            ];
        }
        return $resultSet;
    }

    public function index()
    {
        $timer = Timer::first();
        $today = Carbon::today();
        $dataPlans = $this->getDataPlan($today);

        $operationTimesNewPlans = $dataPlans->isEmpty()
            ? OperationTime::where('name_id', 1)->get()
            : OperationTime::where('name_id', $dataPlans[0]->time_option)->whereNotNull('start')->get();

        $plans = $this->newDataPlan($dataPlans, $operationTimesNewPlans);
        $operationTimes = OperationTime::orderBy('name_id', 'asc')->whereNotNull('start')->with('operation_name')->get();

        return view('input-plan', [
            'plans' => $plans,
            'lines' => Line::all(),
            'bedModels' => BedModels::all(),
            'operationTimes' => $operationTimes,
            'operationNames' => OperationName::all(),
            'timer' => $timer,
        ]);
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

    public function storeNewOperationTime(Request $request)
    {
        $lastId = OperationName::orderBy('id', 'desc')->pluck('id')->first();
        $lastIdOt = OperationTime::orderBy('id', 'desc')->pluck('id')->first() + 1;

        if ($lastId >= 50) {
            return redirect('/input-plan')->with('gagal', 'Operation time fully entered (Max 50 rows)! Edit existed operation time.'); // Ganti 'route_name' dengan nama rute yang sesuai.
        } else {
            $lastId = $lastId + 1;
        }

        $validated = $request->validate([
            'operation_name' => 'required|unique:operation_names,name',
            'number' => 'required|array',
            'start' => 'required|array',
            'finish' => 'required|array',
            'status' => 'required|array'
        ]);

        // dd($validated);

        OperationName::updateOrInsert([
            'id' => $lastId,
        ], [
            'name' => $validated['operation_name'],
            'created_at' => now(),
        ]);


        for ($i = 0; $i <= 19; $i++) {
            $id = $lastIdOt + $i;

            OperationTime::updateOrInsert(
                [
                    'id' => $id
                ],
                [
                    'name_id' => $lastId,
                    'start' => $validated['start'][$i] ?? null,
                    'finish' => $validated['finish'][$i] ?? null,
                    'status' => $validated['status'][$i] ?? null,
                    'created_at' => now(),
                ]
            );
        }

        return redirect('/input-plan')->with('sukses', 'Insert new operation time success!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }
    public function insertNewLineOperationTime(Request $request)
    {
        $validatedData = $request->validate([
            'start' => 'required',
            'finish' => 'required',
            'status' => ['required'],
            'opTime' => ['required'],
        ]);

        $lastId = OperationTime::where('name_id', $validatedData['opTime'])
            ->whereNull('start')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->first();

        if (!$lastId) {
            return redirect('/input-plan')->with('gagal', 'Line on choosen operation time data is fully used!'); // Ganti 'route_name' dengan nama rute yang sesuai.
        }

        OperationTime::updateOrInsert(
            [
                'id' => $lastId
            ],
            [
                'name_id' => $validatedData['opTime'],
                'start' => $validatedData['start'],
                'finish' => $validatedData['finish'],
                'status' => $validatedData['status'],
                'created_at' => now(),
            ]
        );


        return redirect('/input-plan')->with('sukses', 'Insert operation time success!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function updateTimer(Request $request, $id)
    {
        $time = Timer::findOrFail($id);
        $request->validate([
            'timer' => 'required|numeric',
        ]);

        $time->update([
            'timer' => $request->input('timer'),
        ]);

        // dd($request->all());
        return redirect('/input-plan')->with('sukses', 'Update timer success!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function updateOperationTimeData(Request $request, $id)
    {

        $validatedData = $request->validate([
            'nama_operation' => 'required|string|unique:operation_names,name,' . $id,
            'start.*' => 'required',
            'finish.*' => 'required',
            'status.*' => 'required',
        ]);

        // dd($validatedData);
        // Ambil operationNames dan operationTimes
        $operationNames = OperationName::findOrFail($id);

        $operationNames->update([
            'name' => $validatedData['nama_operation'],
        ]);

        $operationTimes = OperationTime::where('name_id', $id)->whereNotNull('start')->get();
        // Iterasi melalui operationTimes untuk update masing-masing record
        foreach ($operationTimes as $index => $operationTime) {
            $updatedData = [
                'start' => $validatedData['start'][$index],
                'finish' => $validatedData['finish'][$index],
                'status' => $validatedData['status'][$index],
            ];

            // Update data
            $operationTime->update($updatedData);
        }

        // Redirect dengan pesan sukses
        return redirect('/input-plan')->with('sukses', 'Operation time data updated successfully!');
    }

    public function destroyOperationTimeData($id)
    {
        $operationTime = OperationTime::findOrFail($id);
        // dd($operationTime);
        // Update data
        // $operationTime->delete();
        // Set start and finish to null
        $operationTime->start = null;
        $operationTime->finish = null;
        $operationTime->status = null;

        // Save the changes
        $operationTime->save();
        return redirect('/input-plan')->with('gagal', 'Data plan deleted successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.
    }

    public function addMasterData(Request $request)
    {

        $lastId = BedModels::orderBy('id', 'desc')->pluck('id')->first();

        if ($lastId >= 2000) {
            return redirect('/input-plan')->with('gagal', 'Master data fully entered (Max 2000 rows)! Edit existed master data.'); // Ganti 'route_name' dengan nama rute yang sesuai.
        } else {
            $lastId = $lastId + 1;
        }

        // Validasi data input jika diperlukan
        $validatedData = $request->validate([
            'name' => 'required|string|unique:bed_models,name',
            'tact_time' => 'required',
            'setting_time' => 'required',
            // Tambahkan validasi untuk field lainnya
        ]);

        // Update data
        BedModels::updateOrInsert(
            [
                'id' => $lastId
            ],
            [
                'name' => $validatedData['name'],
                'tact_time' => $validatedData['tact_time'],
                'setting_time' => $validatedData['setting_time'],
            ]
        );
        return redirect('/input-plan')->with('sukses', 'Master data inserted successfully!'); // Ganti 'route_name' dengan nama rute yang sesuai.

    }

    public function updateMasterData(Request $request, $id)
    {
        $bedModel = BedModels::find($id);

        if ($bedModel === null) {
            return redirect('/input-plan')->with('gagal', 'Master data not found!'); // Ganti 'route_name' dengan nama rute yang sesuai.
        }
        // Validasi data input jika diperlukan
        $request->validate([
            'name' => 'required|string|unique:bed_models,name,' . $id,
            'tact_time' => 'required',
            'setting_time' => 'required'
            // Tambahkan validasi untuk field lainnya
        ]);
        // dd($bedModel, $request->all());
        // Update data
        $bedModel->update([
            'name' => $request->input('name'),
            'tact_time' => $request->input('tact_time'),
            'setting_time' => $request->input('setting_time'),
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


    public function bulkDestroyPlanData()
    {
        $today = Carbon::today();
        $date = request('date') ? Carbon::parse(request('date')) : $today;

        // Retrieve the Plan records to be deleted
        $plans = Plan::whereDate('date', $date)->get();

        // Extract the plan IDs
        $planIds = $plans->pluck('id');

        // Retrieve the Production records associated with the Plan IDs
        $productions = Production::whereIn('plan_id', $planIds)->get();

        // Extract the production IDs
        $productionIds = $productions->pluck('id');

        // Delete the associated notes
        Note::whereIn('production_id', $productionIds)->delete();

        // Delete the associated productions
        Production::whereIn('id', $productionIds)->delete();

        // Delete the plans
        Plan::whereIn('id', $planIds)->delete();

        return redirect('/input-plan')->with('sukses', 'Data plan deleted successfully!');
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
        // Validate the request to ensure a file is provided and it's of the correct type
        $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls,csv'
        ]);

        // Check if the file is present in the request
        if ($request->hasFile('fileExcel')) {
            $file = $request->file('fileExcel');

            try {
                // Import the file using the PlanImport class
                Excel::import(new PlanImport, $file);

                // Redirect to the input plan route with a success message
                return redirect('/input-plan')->with('sukses', 'Plan Production Updated/Inserted!');
            } catch (\Exception $e) {
                // Redirect back with an error message if there's an exception during import
                return redirect()->back()->with('gagal', 'Error during file import: ' . $e->getMessage());
            }
        }

        // Redirect back with an error message if the file is not found
        return redirect()->back()->with('gagal', 'File tidak ditemukan.');
    }


    public function importTime(Request $request)
    {

        $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls,csv'
        ]);

        // Menangani request jika validasi berhasil
        if ($request->hasFile('fileExcel')) {
            $file = $request->file('fileExcel');
            Excel::import(new OperationTimeImport, $file);
            return redirect('/input-plan')->with('sukses', 'Plan Production Updated/Inserted from Imported File!');
        }

        // Redirect back with error if file is not present
        return redirect()->back()->with('gagal', 'File tidak ditemukan.');
    }

    public function action(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->get('query');
            if ($query != '') {
                $data = BedModels::where('name', 'like', '%' . $query . '%')
                    ->orderBy('id', 'desc')
                    ->paginate(5);
            } else {
                $data = BedModels::orderBy('id', 'asc')->paginate(5);
            }

            $table_data = '';
            if ($data->count() > 0) {
                foreach ($data as $key => $item) {
                    $table_data .= '
                    <tr>
                        <td class="py-1 text-center align-middle">' . ($data->currentPage() - 1) * $data->perPage() + $key + 1 . '</td>
                        <td class="py-1 text-center align-middle">' . $item->name . '</td>
                        <td class="py-1 text-center align-middle">' . $item->tact_time . '</td>
                        <td class="py-1 text-center align-middle">' . $item->setting_time . '</td>
                        <td class="py-1 text-center ">
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editMasterModal' . $item->id . '">Edit</button>
                            
                            <div class="modal fade" id="editMasterModal' . $item->id . '" tabindex="-1" role="dialog" aria-labelledby="editMasterModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editMasterModalLabel">Edit Data</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="' . route('masterData.update', $item->id) . '" method="POST">
                                            ' . csrf_field() . '
                                            ' . method_field('POST') . '
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input required type="text" class="form-control" id="name" value="' . $item->name . '" name="name">
                                                </div>
                                                <div class="form-group">
                                                    <label for="tact_time">Tact Time</label>
                                                    <input required type="number" step="0.01" class="form-control" id="tact_time" value="' . $item->tact_time . '" name="tact_time">
                                                </div>
                                                <div class="form-group">
                                                    <label for="setting_time">Setting Time</label>
                                                    <input required type="number" step="0.01" class="form-control" id="setting_time" value="' . $item->setting_time . '" name="setting_time">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>';
                }
            } else {
                $table_data = '<tr><td colspan="5" class="text-center">No data available</td></tr>';
            }

            return response()->json([
                'table_data' => $table_data,
                'pagination' => (string) $data->links(),
            ]);
        }
    }
}
