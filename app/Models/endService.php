<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EndOfServiceRecord extends Model
{
    use HasFactory, SoftDeletes;

    // Table name is inferred as 'end_of_service_records'

    protected $fillable = [
        'employee_id',
        'termination_date',
        'total_service_years',
        'amount',
        'notes',
    ];

    protected $casts = [
        'termination_date' => 'date',
        'total_service_years' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // EOS record belongs to an employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
