<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'basic_salary',
        'gross_pay',
        'net_pay',
        'previous_balance',
        'paid_amount',
        'remaining_balance',
        'is_paid',
        'paid_at',
        'payment_method',
        'meta',
    ];

    protected $casts = [
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'is_paid' => 'boolean',
        'meta' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function payments()
    {
        return $this->hasMany(PayrollPayment::class);
    }
}
