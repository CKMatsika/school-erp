<?php

namespace App\Models\Accounting; // Correct namespace as requested

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Add if using soft deletes
use App\Models\School; // Assuming School model exists here
use App\Models\HumanResources\Staff; // Import Staff model for relationship

class Subject extends Model
{
    use HasFactory, SoftDeletes; // Add SoftDeletes if using

    protected $fillable = [
        'school_id',
        'name',
        'subject_code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the school that owns the subject (if applicable).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * The staff members that teach this subject.
     */
    public function staff(): BelongsToMany
    {
        // Ensure the Staff model namespace is correct here
        return $this->belongsToMany(Staff::class, 'staff_subject', 'subject_id', 'staff_id')
                    ->withTimestamps(); // If pivot table has timestamps
    }
}