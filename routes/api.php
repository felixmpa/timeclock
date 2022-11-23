<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TimeTrackerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/time-tracker', [TimeTrackerController::class, 'index']);
Route::middleware('auth:sanctum')->post('/time-tracker', [TimeTrackerController::class, 'store']);
Route::middleware('auth:sanctum')->put('/time-tracker/{id}', [TimeTrackerController::class, 'edit']);

Route::middleware('auth:sanctum')->get('/report/time-tracker', [TimeTrackerController::class, 'report']);


