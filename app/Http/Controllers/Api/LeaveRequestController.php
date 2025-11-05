<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    // Employee submits a leave request
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_type' => 'nullable|in:annual,sick,unpaid,maternity,other',
            'reason' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,png|max:4096',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('leave_attachments', 'public');
            }
        }

        $leave = LeaveRequest::create([
            'employee_id' => Auth::id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'leave_type' => $request->leave_type ?? 'annual',
            'reason' => $request->reason,
            'attachments' => $attachments,
        ]);

        return ApiResponse::SendResponse(200, 'Leave request submitted', $leave);
    }

    // Admin lists all requests
    public function allRequests()
    {
        $requests = LeaveRequest::with('employee')->orderBy('created_at', 'desc')->get();
        return ApiResponse::SendResponse(200, 'Leave requests retrieved', $requests);
    }

    // Admin approves or rejects
    public function approve(Request $request, $id)
    {
        $leave = LeaveRequest::find($id);
        if (!$leave) {
            return ApiResponse::SendResponse(404, 'Leave request not found', '');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::SendResponse(400, $validator->errors(), '');
        }

        $admin = Auth::user(); // current admin

        if (!$admin) {
            return ApiResponse::SendResponse(400, 'Admin not found', '');
        }

        $leave->status = $request->status;
        $leave->approved_by = $admin->id;

        if ($request->status === 'rejected') {
            $leave->rejection_reason = $request->rejection_reason ?? 'No reason provided';
        } else {
            $leave->rejection_reason = null;
        }

        $leave->save();

        return ApiResponse::SendResponse(200, 'Leave request updated successfully', $leave);
    }


    // Employee sees their leave requests
    public function myRequests()
    {
        $leaves = LeaveRequest::where('employee_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return ApiResponse::SendResponse(200, 'Your leave requests', $leaves);
    }
}

