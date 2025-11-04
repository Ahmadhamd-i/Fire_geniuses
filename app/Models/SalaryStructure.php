<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'allowances',
        'overtime_rate',
        'cumulative_overtime', // new field
        'deductions',
        'salary_period',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'array',
        'overtime_rate' => 'decimal:2',
        'cumulative_overtime' => 'decimal:2', // cast as decimal
        'deductions' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
