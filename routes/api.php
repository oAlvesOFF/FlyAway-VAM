<?php

use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\FlightStreamController;
use App\Http\Controllers\Api\FlightTrackingController;
use App\Http\Controllers\Api\PirepController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CommonController;
use Illuminate\Support\Facades\Route;

Route::get('flights/active', [FlightTrackingController::class, 'index']);
Route::get('flights/stream', [FlightStreamController::class, 'stream']);
Route::get('airports', [CommonController::class, 'airports']);

Route::middleware('api.auth')->group(function () {
    Route::get('user', [UserController::class, 'me']);
    Route::get('me', [UserController::class, 'me']);
    Route::apiResource('pireps', PirepController::class)->only(['index', 'store', 'show']);
    Route::post('pireps/{pirep}/approve', [PirepController::class, 'approve']);
    Route::post('pireps/{pirep}/reject', [PirepController::class, 'reject']);
    Route::get('aircraft', [FlightController::class, 'aircraft']);
    Route::get('schedules', [FlightController::class, 'schedules']);
    Route::get('schedules/my-reservations', [FlightController::class, 'myReservations']);
    Route::post('flights/track', [FlightTrackingController::class, 'track']);
    Route::post('flights/{flight}/complete', [FlightTrackingController::class, 'complete']);
});
