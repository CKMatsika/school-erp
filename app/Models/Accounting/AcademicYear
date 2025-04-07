<?php

namespace App\Models\Accounting; // Or App\Models; if you prefer

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\School;
use App\Models\Accounting\Term; // Adjust if Term model is elsewhere

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_years';

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function terms()
    {
        // Assuming Term model is in App\Models\Accounting
        return $this->hasMany(Term::class)->orderBy('start_date'); // Order terms chronologically
    }

    // Add other relationships like enrollments, fee structures later if needed
}