<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helper\ApiResponse;
use App\Models\MonthlyPayrollSummary;
use App\Models\Payroll;
use App\Models\PayrollPayment;
use App\Models\SalaryStructure;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    /**
     * Generate payroll for an employee
     */
    public function generatePayroll(Request $request, $employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return ApiResponse::SendResponse(404, 'Employee not found', '');
        }

        $salaryStruct = SalaryStructure::where('employee_id', $employeeId)->first();
        if (!$salaryStruct) {
            return ApiResponse::SendResponse(400, 'Salary structure not defined for this employee', '');
        }

        // Previous unpaid balance
        $lastPayroll = Payroll::where('employee_id', $employeeId)
            ->latest('period_end')
            ->first();
        $previousBalance = $lastPayroll ? $lastPayroll->remaining_balance : 0;

        // Overtime pay
        $overtimePay = ($salaryStruct->cumulative_overtime ?? 0) * ($salaryStruct->overtime_rate ?? 0);

        // Gross pay
        $grossPay = $salaryStruct->basic_salary + $overtimePay + $previousBalance;

        // Total deductions
        $totalDeductions = $salaryStruct->deductions ?? 0;

        // Net pay
        $netPay = $grossPay - $totalDeductions;

        $payroll = Payroll::create([
            'employee_id' => $employeeId,
            'period_start' => $request->period_start ?? now()->startOfMonth(),
            'period_end' => $request->period_end ?? now()->endOfMonth(),
            'basic_salary' => $salaryStruct->basic_salary,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'previous_balance' => $previousBalance,
            'paid_amount' => 0,
            'remaining_balance' => $netPay,
            'is_paid' => false,
        ]);

        return ApiResponse::SendResponse(200, 'Payroll generated successfully', $payroll);
    }


    /**
     * Automatic Generate payroll for employees
     */
    public function generateMonthlyPayroll()
    {
        $periodStart = now()->startOfMonth()->toDateString();
        $periodEnd = now()->endOfMonth()->toDateString();

        $employees = Employee::all();

        DB::transaction(function () use ($employees, $periodStart, $periodEnd) {
            foreach ($employees as $employee) {
                $salaryStruct = SalaryStructure::where('employee_id', $employee->id)->first();
                if (!$salaryStruct) continue;

                // Last payroll for this employee
                $lastPayroll = Payroll::where('employee_id', $employee->id)
                    ->latest('period_end')
                    ->first();

                $previousOvertime = $lastPayroll ? ($lastPayroll->meta['overtime_paid'] ?? 0) : 0;
                $newOvertimeHours = max(($salaryStruct->cumulative_overtime ?? 0) - $previousOvertime, 0);
                $overtimePay = $newOvertimeHours * ($salaryStruct->overtime_rate ?? 0);

                $basic = $salaryStruct->basic_salary ?? 0;
                $allowances = $salaryStruct->allowances ?? 0;
                $deductions = $salaryStruct->deductions ?? 0;

                $grossPay = $basic + $allowances + $overtimePay;
                $netPay = $grossPay - $deductions;

                // Carry over unpaid amounts from last payroll
                $carryOver = $lastPayroll ? ($lastPayroll->remaining_balance ?? 0) : 0;
                $netPay += $carryOver;

                Payroll::create([
                    'employee_id' => $employee->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'basic_salary' => $basic,
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'paid_amount' => 0,
                    'remaining_balance' => $netPay,
                    'meta' => ['overtime_paid' => $salaryStruct->cumulative_overtime],
                    'is_paid' => false,
                ]);

                // ✅ Reset accumulative fields in salary structure
                $salaryStruct->cumulative_overtime = 0;
                $salaryStruct->allowances = 0;
                $salaryStruct->deductions = 0;
                $salaryStruct->save();
            }
        });

        return response()->json(['status' => 200, 'msg' => 'Monthly payroll generated and accumulative fields reset successfully']);
    }


    /**
     * Pay salary (partial or full)
     */
    public function paySalary(Request $request, $employeeId)
    {
        // 1️⃣ Get the employee’s latest unpaid payroll
        $payroll = Payroll::where('employee_id', $employeeId)
            ->where('is_paid', false)
            ->latest('period_end')
            ->first();

        if (!$payroll) {
            return ApiResponse::SendResponse(404, 'No unpaid payroll found for this employee', '');
        }

        // 2️⃣ Validate input
        $validator = Validator::make($request->all(), [
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $paidAmount = (float)$request->paid_amount;

        // 3️⃣ Prevent overpayment
        if ($paidAmount > $payroll->remaining_balance) {
            return ApiResponse::SendResponse(400, 'Paid amount exceeds remaining balance', '');
        }

        // 4️⃣ Record this payment in payroll_payments table
        PayrollPayment::create([
            'payroll_id' => $payroll->id,
            'amount' => $paidAmount,
            'payment_method' => $request->payment_method ?? 'cash',
            'paid_at' => now(),
        ]);

        // 5️⃣ Update payroll totals
        $payroll->paid_amount += $paidAmount;
        $payroll->remaining_balance -= $paidAmount;

        if ($payroll->remaining_balance <= 0) {
            $payroll->is_paid = true;
            $payroll->paid_at = now();
        }

        $payroll->payment_method = $request->payment_method ?? $payroll->payment_method;
        $payroll->save();

        return ApiResponse::SendResponse(200, 'Salary payment recorded successfully', $payroll);
    }



    /**
     * View employee payrolls
     */
    public function employeePayrolls($employeeId)
    {
        $payrolls = Payroll::with('payments')
            ->where('employee_id', $employeeId)
            ->orderBy('period_end', 'desc')
            ->get();

        return ApiResponse::SendResponse(200, 'Payrolls retrieved', $payrolls);
    }

    /**
     * summery for payrolles every month
     */
    public function calculateMonthlyUnpaidPayrolls()
    {
        $employees = Employee::all();
        $periodStart = now()->startOfMonth()->toDateString();
        $periodEnd = now()->endOfMonth()->toDateString();

        $month = now()->month;
        $year = now()->year;

        // Skip if summary already exists (payroll already generated this month)
        $existingSummary = MonthlyPayrollSummary::where('month', $month)->where('year', $year)->first();
        if ($existingSummary) {
            return ApiResponse::SendResponse(400, 'Payroll already generated for this month', '');
        }

        $totalGross = 0;
        $totalDeduct = 0;
        $totalNet = 0;

        foreach ($employees as $employee) {
            $salaryStruct = SalaryStructure::where('employee_id', $employee->id)->first();
            if (!$salaryStruct) continue;

            // Skip if payroll for this month already exists
            $exists = Payroll::where('employee_id', $employee->id)
                ->whereBetween('period_start', [now()->startOfMonth(), now()->endOfMonth()])
                ->exists();
            if ($exists) continue;

            $lastPayroll = Payroll::where('employee_id', $employee->id)->latest('period_end')->first();
            $previousBalance = $lastPayroll ? $lastPayroll->remaining_balance : 0;

            $overtimePay = ($salaryStruct->cumulative_overtime ?? 0) * ($salaryStruct->overtime_rate ?? 0);
            $grossPay = $salaryStruct->basic_salary + ($salaryStruct->allowances ?? 0) + $overtimePay + $previousBalance;
            $totalDeductions = $salaryStruct->deductions ?? 0;
            $netPay = $grossPay - $totalDeductions;

            Payroll::create([
                'employee_id' => $employee->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'basic_salary' => $salaryStruct->basic_salary,
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
                'paid_amount' => 0,
                'remaining_balance' => $netPay,
                'is_paid' => false,
            ]);

            // Update totals
            $totalGross += $grossPay;
            $totalDeduct += $totalDeductions;
            $totalNet += $netPay;

            // Reset accumulative values
            $salaryStruct->update([
                'cumulative_overtime' => 0,
                'allowances' => 0,
                'deductions' => 0,
            ]);
        }

        // Save summary
        MonthlyPayrollSummary::create([
            'month' => $month,
            'year' => $year,
            'total_employees' => $employees->count(),
            'total_gross_pay' => $totalGross,
            'total_deductions' => $totalDeduct,
            'total_net_pay' => $totalNet,
            'total_paid' => 0,
            'total_remaining' => $totalNet,
        ]);

        return ApiResponse::SendResponse(200, 'Monthly payroll and summary generated successfully', '');
    }

    public function getMonthlySummary(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $summary = MonthlyPayrollSummary::where('month', $month)->where('year', $year)->first();

        if (!$summary) {
            return ApiResponse::SendResponse(404, 'No summary found for this month', '');
        }

        return ApiResponse::SendResponse(200, "Summary for $month/$year retrieved", $summary);
    }

}
