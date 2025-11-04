<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
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
            'start_work_date' => Carbon::now(),
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

        $employee->update($request->except('image'));

        // ✅ Handle image upload
        if ($request->hasFile('image')) {

            // Delete old image if exists
            if ($employee->image) {
                $oldPath = str_replace(url('storage') . '/', '', $employee->image);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new image in `storage/app/public/employee_images`
            $imagePath = $request->file('image')->store('employee_images', 'public');

            // ✅ Generate the correct absolute URL
            $employee->image = url('storage/' . str_replace('public/', '', $imagePath));
        }

        $employee->save();

        return ApiResponse::SendResponse(200, 'Profile updated successfully', $employee);
    }

    //create employee by admin
    public function createByAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'iqama_number' => 'nullable|string|max:255',
            'iqama_expiry_date' => 'nullable|date',
            'job_title' => 'nullable|string|max:255',
            'salary' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'start_work_date' => 'nullable|date',
            'email' => 'required|email|unique:employees,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'emer_phone_name' => 'nullable|string|max:255',
            'emergency_Phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
            'image' => 'nullable|image|max:4096', // up to 4MB
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        // Prepare employee data
        $data = $request->except('image');
        $data['password'] = Hash::make($request->password);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('employee_images', 'public');
            $data['image'] = asset('storage/' . $imagePath);
        }

        $employee = Employee::create($data);

        return ApiResponse::SendResponse(201, 'Employee created successfully by admin', $employee);
    }


    // Logout employee
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::SendResponse(200, 'Logged out successfully', '');
    }
}

