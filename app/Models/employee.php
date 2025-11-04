<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, SoftDeletes, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'address', 'image', 'nationality',
        'iqama_number', 'iqama_expiry_date', 'job_title', 'salary',
        'department', 'start_work_date', 'email', 'password', 'phone',
        'status', 'emer_phone_name', 'emergency_Phone',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'iqama_expiry_date' => 'date',
        'start_work_date' => 'date',
    ];
    public function getTotalWorkDaysAttribute()
    {
        return $this->attendanceRecords()
            ->whereNotNull('check_in')
            ->selectRaw('DATE(check_in) as work_date')
            ->distinct()
            ->count('work_date');
    }
    public function salaryStructure()
    {
        return $this->hasOne(SalaryStructure::class);
    }

}
