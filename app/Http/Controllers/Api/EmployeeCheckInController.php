<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmployeeCheckInController extends Controller
{
    // Check-in employee
    public function checkIn(Request $request)
    {
        $employee = auth()->user();

        // Prevent multiple check-ins for the same day
        $todayRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($todayRecord && $todayRecord->checked) {
            return ApiResponse::SendResponse(400, 'You already checked in today.', '');
        }

        $validator = Validator::make($request->all(), [
            'department' => 'nullable|string',
            'check_in_source' => 'nullable|string',
            'check_in_location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $attendance = AttendanceRecord::create([
            'employee_id' => $employee->id,
            'department' => $request->department,
            'checked' => true,
            'check_in' => now(),
            'check_in_source' => $request->check_in_source,
            'check_in_location' => $request->check_in_location,
        ]);

        return ApiResponse::SendResponse(200, 'Checked in successfully', $attendance);
    }

    // Check-out employee
    public function checkOut(Request $request)
    {
        $employee = auth()->user();

        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->checked || $attendance->check_out) {
            return ApiResponse::SendResponse(400, 'You have not checked in or already checked out today.', '');
        }

        $attendance->check_out = now();
        $attendance->total_hours = round(Carbon::parse($attendance->check_out)
                ->diffInMinutes(Carbon::parse($attendance->check_in)) / 60, 2);
        $attendance->save();

        return ApiResponse::SendResponse(200, 'Checked out successfully', $attendance);
    }

    // Get employee attendance records
    public function myAttendance(Request $request)
    {
        $employee = auth()->user();

        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::SendResponse(200, 'Attendance records retrieved', $records);
    }
}
