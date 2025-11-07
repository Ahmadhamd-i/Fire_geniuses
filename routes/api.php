<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeCheckInController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\SalaryController;
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
        //create employee -------------------------------------------------------------------------------
        Route::post('employees', [EmployeeController::class, 'createByAdmin']);
        //Edit Salary ----------------------------------------------------------------------------------------
        Route::post('salary', [SalaryController::class, 'addSalary']); // Add salary structure
        Route::Post('salary/{employeeId}', [SalaryController::class, 'updateSalary']); // Update salary
        Route::get('salary/{employeeId}', [SalaryController::class, 'getSalary']); // Get salary structure
        //Vaacations------------------------------------------------------------------------------------
        Route::get('leave/all', [LeaveRequestController::class, 'allRequests']); // Admin
        Route::post('leave/{id}/approve', [LeaveRequestController::class, 'approve']); // Admin approve/reject
        //Dashboard---------------------------------------------------------------------------------------------
        Route::get('dashboard', [AdminDashboardController::class, 'getDashboardStats']);
        //Set Vacation
        Route::Post('setvacation/{employeeId}', [AdminDashboardController::class, 'setVacation']);
        //Payrolls Salary
        Route::post('payroll/pay/{employeeID}', [PayrollController::class, 'paysalary']);
        Route::get('payroll/{employeeId}', [PayrollController::class, 'employeePayrolls']);
        //Monthley Salary brief
        Route::post('generate-monthly', [PayrollController::class, 'calculateMonthlyUnpaidPayrolls']);
        Route::get('monthly', [PayrollController::class, 'getMonthlyPayrolls']);
        Route::get('monthly-summary', [PayrollController::class, 'getMonthlySummary']);
    });
});

Route::prefix('employee')->group(function () {

    // ✅ Public routes (no token needed)
    Route::post('register', [EmployeeController::class, 'register']);
    Route::post('login', [EmployeeController::class, 'login']);

    // ✅ Protected routes (employee token required)
    Route::middleware('auth:employee')->group(function () {
        Route::post('complete-profile', [EmployeeController::class, 'completeProfile']);
        Route::post('logout', [EmployeeController::class, 'logout']);
        //Check in---------------------------------------------------------------------------------
        Route::post('check-in', [EmployeeCheckInController::class, 'checkIn']);
        Route::post('check-out', [EmployeeCheckInController::class, 'checkOut']);
        Route::get('attendance', [EmployeeCheckInController::class, 'myAttendance']);
        //Vacations-----------------------------------------------------------------------------------
        Route::post('leave/apply', [LeaveRequestController::class, 'apply']); // Employee
        Route::get('leave/my', [LeaveRequestController::class, 'myRequests']); // Employee
        //Payroll
        Route::get('payroll/{employeeId}', [PayrollController::class, 'employeePayrolls']);
    });
    Route::post('payroll/generate-monthly', [PayrollController::class, 'generateMonthlyPayroll']);
//b
});



