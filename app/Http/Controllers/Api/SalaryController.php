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
            'basic_salary' => 'required|numeric',
            'allowances' => 'nullable|numeric',
            'overtime_rate' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'salary_period' => 'nullable|string|in:monthly,biweekly,weekly',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $salary = SalaryStructure::updateOrCreate(
            ['employee_id' => $request->employee_id],
            [
                'basic_salary' => (float)$request->basic_salary,
                'allowances' => $request->allowances ? (float)$request->allowances : null,
                'overtime_rate' => $request->overtime_rate ? (float)$request->overtime_rate : null,
                'deductions' => $request->deductions ? (float)$request->deductions : null,
                'salary_period' => $request->salary_period ?? 'monthly',
            ]
        );

        // Update the salary field in employees table
        $employee = Employee::find($request->employee_id);
        $employee->salary = (float)$request->basic_salary;
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
            'allowances' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'salary_period' => 'nullable|string|in:monthly,biweekly,weekly',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        // Explicitly cast all decimal fields to float
        $updateData = [];
        if ($request->has('basic_salary')) $updateData['basic_salary'] = (float)$request->basic_salary;
        if ($request->has('allowances')) $updateData['allowances'] = (float)$request->allowances;
        if ($request->has('overtime_rate')) $updateData['overtime_rate'] = (float)$request->overtime_rate;
        if ($request->has('deductions')) $updateData['deductions'] = (float)$request->deductions;
        if ($request->has('salary_period')) $updateData['salary_period'] = $request->salary_period;

        if (empty($updateData)) {
            return ApiResponse::SendResponse(400, 'No valid fields provided for update', '');
        }

        // Update salary structure
        $salary->update($updateData);

        // Update employee's base salary if changed
        if (isset($updateData['basic_salary'])) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                $employee->salary = $updateData['basic_salary'];
                $employee->save();
            }
        }

        return ApiResponse::SendResponse(200, 'Salary structure updated successfully', $salary->fresh());
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
