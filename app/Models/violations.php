<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use HasFactory, SoftDeletes;

    // Table name inferred as 'violations', no need to specify

    protected $fillable = [
        'employee_id',
        'type',
        'description',
        'amount',
        'date',
        'evidence',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'evidence' => 'array',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // Violation belongs to an employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
