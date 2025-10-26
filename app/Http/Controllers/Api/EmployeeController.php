<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EmployeeAuthController extends Controller
{
    // Register employee
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $employee = Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $employee->createToken('employee_token')->plainTextToken;

        return ApiResponse::SendResponse(201, 'Employee registered successfully', [
            'employee' => $employee,
            'token' => $token,
        ]);
    }

    // Login employee
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $employee = Employee::where('email', $request->email)->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return ApiResponse::SendResponse(401, 'Invalid credentials', '');
        }

        $token = $employee->createToken('employee_token')->plainTextToken;

        return ApiResponse::SendResponse(200, 'Login successful', [
            'employee' => $employee,
            'token' => $token,
        ]);
    }

    // Complete / update profile
    public function completeProfile(Request $request)
    {
        $employee = auth()->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'nationality' => 'nullable|string',
            'iqama_number' => 'nullable|string',
            'iqama_expiry_date' => 'nullable|date',
            'job_title' => 'nullable|string',
            'salary' => 'nullable|string',
            'department' => 'nullable|string',
            'start_work_date' => 'nullable|date',
            'phone' => 'nullable|string',
            'emer_phone_name' => 'nullable|string',
            'emergency_Phone' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        // Update other fields first
        $employee->update($request->except('image'));

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($employee->image) {
                $oldPath = str_replace(asset('storage') . '/', '', $employee->image);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new image in employee_images folder
            $imagePath = $request->file('image')->store('employee_images', 'public');
            $employee->image = asset('storage/' . $imagePath);
        }

        $employee->save();

        return ApiResponse::SendResponse(200, 'Profile updated successfully', $employee);
    }

    // Logout employee
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::SendResponse(200, 'Logged out successfully', '');
    }
}

