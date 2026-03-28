<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk melihat data diri sendiri (berguna untuk frontend)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Nanti rute absen masuk & keluar kita taruh di sini!
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
});
