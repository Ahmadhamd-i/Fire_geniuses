<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPayrollSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'total_employees',
        'total_gross_pay',
        'total_deductions',
        'total_net_pay',
        'total_paid',
        'total_remaining',
    ];
}
