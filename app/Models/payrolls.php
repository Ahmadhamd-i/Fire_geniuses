<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    // Table name is inferred as 'payrolls', so no need to declare $table

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'basic_salary',
        'gross_pay',
        'allowances',
        'deductions',
        'net_pay',
        'is_paid',
        'paid_at',
        'payment_method',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'allowances' => 'array',
        'deductions' => 'array',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // Payroll belongs to an employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
