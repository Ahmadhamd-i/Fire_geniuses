<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'site_id',
        'checked',
        'check_in',
        'check_out',
        'total_hours',
        'overtime', // new field for daily overtime
        'check_in_source',
        'check_in_location',
    ];

    protected $casts = [
        'checked' => 'boolean',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime' => 'decimal:2', // cast overtime as decimal
    ];

    // Relationship to employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Relationship to work site
    public function site()
    {
        return $this->belongsTo(WorkSite::class, 'site_id');
    }
}

