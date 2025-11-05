<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    // Table name is inferred as 'leave_requests', so no need to declare $table

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'leave_type',
        'reason',
        'status',
        'approved_by',
        'rejection_reason',
        'attachments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'attachments' => 'array',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // Leave request belongs to an employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Leave request may be approved by a user (admin)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
