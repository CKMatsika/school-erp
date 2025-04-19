<?php

// Ensure this namespace is correct for your project structure
namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\School; // Assuming correct namespace
use App\Models\Student; // <-- Uncommented - Assuming correct namespace
use App\Models\HumanResources\Staff; // <-- Added for relationship

class SchoolClass extends Model
{
    // Use SoftDeletes only if the 'deleted_at' column exists in your table
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     * Explicitly set because the class name doesn't match the table name convention.
     *
     * @var string
     */
    protected $table = 'timetable_school_classes';

    /**
     * The attributes that are mass assignable.
     * Ensure these match the actual columns in your 'timetable_school_classes' table.
     * Removed 'home_room', 'notes' as they weren't in the base migration - add back if needed.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'level',
        'capacity',
        // 'home_room', // Add back if this column exists
        // 'notes',     // Add back if this column exists
        'is_active',
        // Add other columns like 'academic_year_id', 'term_id', 'teacher_id' if they exist
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    // ======================================================================
    // Relationships
    // ======================================================================

    /**
     * Get the school that owns the class (if applicable).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the students currently assigned to this class.
     * Assumes 'students' table has a 'current_class_id' foreign key. Adjust if needed.
     */
    public function students(): HasMany
    {
         // Use the imported Student class
         // Make sure 'current_class_id' is the correct foreign key on the students table
         return $this->hasMany(Student::class, 'current_class_id');
    }

    /**
     * The staff members assigned to teach this class.
     * Assumes a pivot table named 'staff_school_class'.
     */
    public function staff(): BelongsToMany
    {
        // Assumes pivot table: staff_school_class
        // Foreign keys: school_class_id, staff_id
        // Related keys: id (on school_classes), id (on staff)
        return $this->belongsToMany(Staff::class, 'staff_school_class', 'school_class_id', 'staff_id')
                    ->withPivot('academic_year_id') // Include pivot data if assignments are per year
                    ->withTimestamps(); // If pivot table has timestamps
    }

    // Add other relationships as needed (e.g., to AcademicYear, Term, Subjects taught in this class)

}