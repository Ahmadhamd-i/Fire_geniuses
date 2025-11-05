<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helper\ApiResponse;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminDashboardController extends Controller
{
    public function getDashboardStats()
    {
        // 1️⃣ Total number of employees
        $totalEmployees = Employee::count();

        // 2️⃣ Number of present employees today
        $today = now()->toDateString();
        $presentToday = AttendanceRecord::whereDate('created_at', $today)
            ->where('checked', true)
            ->count();

        // 3️⃣ Number of pending leave requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        // 4️⃣ Total amount of salaries
        $totalSalaries = SalaryStructure::sum('basic_salary');

        $data = [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'pending_leaves' => $pendingLeaves,
            'total_salaries' => $totalSalaries,
        ];

        return ApiResponse::SendResponse(200, 'Admin dashboard stats retrieved successfully', $data);
    }

    //Set vacation for employee
    public function setVacation(Request $request, $employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return ApiResponse::SendResponse(404, 'Employee not found', '');
        }

        $validator = Validator::make($request->all(), [
            'vacation_available' => 'required|numeric|min:21|max:30',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $employee->vacation_available = $request->vacation_available;
        $employee->save();

        return ApiResponse::SendResponse(200, 'Vacation days updated successfully', [
            'employee_id' => $employee->id,
            'vacation_available' => $employee->vacation_available,
        ]);
    }
}
