<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Helper\ApiResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|min:6',
        ]);

        $admin = Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin->createToken('admin_token', ['admin'])->plainTextToken;

        return ApiResponse::SendResponse(201, 'Registration successful', [
            'admin' => $admin,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return ApiResponse::SendResponse(401, 'Invalid credentials');
        }

        $token = $admin->createToken('admin_token', ['admin'])->plainTextToken;

        return ApiResponse::SendResponse(200, 'Login successful', [
            'admin' => $admin,
            'token' => $token
        ]);
    }

    public function me(Request $request)
    {
        $admin = $request->user('sanctum');
        $token = $admin->currentAccessToken()->plainTextToken ?? null;

        return ApiResponse::SendResponse(200, 'Admin data', [
            'admin' => $admin,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user('sanctum')->currentAccessToken()->delete();

        return ApiResponse::SendResponse(200, 'Logged out successfully', [
            'admin' => null,
            'token' => null
        ]);
    }
    public function changePassword(Request $request)
    {
        $admin = $request->user(); // Authenticated admin

        // Validate input
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        // Update password
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ]);
    }
}
