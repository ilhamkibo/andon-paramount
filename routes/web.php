<?php

use App\Http\Controllers\InputPlanController;
use App\Http\Controllers\LineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/line');
});

Route::get('/line', [LineController::class, 'index'])->name('production');
Route::get('/line/{tanggal}', [LineController::class, 'show'])->name('production_show');
Route::get('api/get-latest-data/{tanggal}', [LineController::class, 'getData'])->name('anjing');
Route::post('/import-models-file', [InputPlanController::class, 'importMaster'])->name('file.import-models');
Route::post('/import-plan-file', [InputPlanController::class, 'importPlan'])->name('file.import-plan');
Route::post('/import-time-file', [InputPlanController::class, 'importTime'])->name('file.import-time');
Route::get('/input-plan', [InputPlanController::class, 'index'])->name('input-plan');
Route::post('/store-data', [InputPlanController::class, 'store'])->name('store-data');
Route::post('/store-data-time', [InputPlanController::class, 'storeDataTime'])->name('store-data-time');
Route::post('/masterData/{id}', [InputPlanController::class, 'addMasterData'])->name('masterData.add');
Route::put('/masterData/{id}', [InputPlanController::class, 'destroyMasterData'])->name('masterData.destroy');
Route::post('/operationTimeData/{id}', [InputPlanController::class, 'updateOperationTimeData'])->name('OperationTimeData.update');
Route::delete('/operationTimeData/{id}', [InputPlanController::class, 'destroyOperationTimeData'])->name('OperationTimeData.destroy');
Route::post('/store-operation', [InputPlanController::class, 'storeOperationtime'])->name('store-operation');
Route::post('/planData/{id}', [InputPlanController::class, 'updatePlanData'])->name('planData.update');
Route::delete('/planData/{id}', [InputPlanController::class, 'destroyPlanData'])->name('planData.destroy');
Route::delete('/planData', [InputPlanController::class, 'bulkDestroyPlanData'])->name('planData.bulkDestroy');
Route::post('/operationTimePlan/{date}', [LineController::class, 'updateOperationTimePlanData'])->name('operationTimePlan.update');
