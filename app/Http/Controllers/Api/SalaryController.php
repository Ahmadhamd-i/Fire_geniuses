<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalaryController extends Controller
{
    // Add salary structure for an employee
    public function addSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'overtime_rate' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'salary_period' => 'nullable|string|in:monthly,biweekly,weekly',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $salary = SalaryStructure::updateOrCreate(
            ['employee_id' => $request->employee_id],
            [
                'basic_salary' => $request->basic_salary,
                'allowances' => $request->allowances,
                'overtime_rate' => $request->overtime_rate,
                'deductions' => $request->deductions,
                'salary_period' => $request->salary_period ?? 'monthly',
            ]
        );

        // Update the salary field in employees table
        $employee = Employee::find($request->employee_id);
        $employee->salary = $request->basic_salary;
        $employee->save();

        return ApiResponse::SendResponse(200, 'Salary structure saved successfully', $salary);
    }

    // Update salary structure
    public function updateSalary(Request $request, $employeeId)
    {
        $salary = SalaryStructure::where('employee_id', $employeeId)->first();

        if (!$salary) {
            return ApiResponse::SendResponse(404, 'Salary structure not found', '');
        }

        $validator = Validator::make($request->all(), [
            'basic_salary' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|array',
            'overtime_rate' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'salary_period' => 'nullable|string|in:monthly,biweekly,weekly',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $salary->update($request->only(['basic_salary', 'allowances', 'overtime_rate', 'deductions', 'salary_period']));

        // Update the salary field in employees table if basic_salary is provided
        if ($request->has('basic_salary')) {
            $employee = Employee::find($employeeId);
            $employee->salary = $request->basic_salary;
            $employee->save();
        }

        return ApiResponse::SendResponse(200, 'Salary structure updated successfully', $salary);
    }

    // Optional: get salary structure for an employee
    public function getSalary($employeeId)
    {
        $salary = SalaryStructure::where('employee_id', $employeeId)->first();

        if (!$salary) {
            return ApiResponse::SendResponse(404, 'Salary structure not found', '');
        }

        return ApiResponse::SendResponse(200, 'Salary structure retrieved', $salary);
    }
}
