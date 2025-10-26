<?php

use App\Http\Controllers\Api\EmployeeAuthController;
use App\Http\Controllers\Api\EmployeeCheckInController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;

Route::prefix('admin')->group(function () {

    // ✅ Public routes (no token needed)
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);

    // ✅ Protected routes (admin token required)
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::post('change-password', [AdminAuthController::class, 'changePassword']);
    });
});

Route::prefix('employee')->group(function () {

    // ✅ Public routes (no token needed)
    Route::post('register', [EmployeeAuthController::class, 'register']);
    Route::post('login', [EmployeeAuthController::class, 'login']);

    // ✅ Protected routes (employee token required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('complete-profile', [EmployeeAuthController::class, 'completeProfile']);
        Route::post('logout', [EmployeeAuthController::class, 'logout']);
    });
});
//Checkin
{
    Route::prefix('employee')->middleware('auth:sanctum')->group(function () {
        Route::post('check-in', [EmployeeCheckInController::class, 'checkIn']);
        Route::post('check-out', [EmployeeCheckInController::class, 'checkOut']);
        Route::get('attendance', [EmployeeCheckInController::class, 'myAttendance']);
    });

}
