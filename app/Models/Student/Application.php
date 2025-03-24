<?php

namespace App\Models\Student;

use App\Models\Traits\LogsActivity; // Add this import
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Add LogsActivity trait here

    protected $fillable = [
        'school_id',
        'application_number',
        'first_name',
        'last_name',
        'other_names',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'address',
        'photo',
        'applying_for_class_id',
        'academic_year_id',
        'status',
        'is_boarder',
        'submission_date',
        'decision_date',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'submission_date' => 'date',
        'decision_date' => 'date',
        'is_boarder' => 'boolean',
    ];

    // Get the applicant's full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // School relationship
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Class applying for relationship
    public function applyingForClass()
    {
        return $this->belongsTo(\App\Models\TimetableSchoolClass::class, 'applying_for_class_id');
    }

    // Academic Year relationship
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Application Parents relationship
    public function parents()
    {
        return $this->hasMany(ApplicationParent::class);
    }

    // Documents relationship
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Processed by relationship
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Convert to student
    public function convertToStudent()
    {
        // Create a new student
        $student = Student::create([
            'school_id' => $this->school_id,
            'admission_number' => $this->application_number, // This might need a different format
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'other_names' => $this->other_names,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'photo' => $this->photo,
            'class_id' => $this->applying_for_class_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'is_boarder' => $this->is_boarder,
        ]);

        // Create enrollment
        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $this->applying_for_class_id,
            'academic_year_id' => $this->academic_year_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'notes' => 'Created from application #' . $this->application_number,
        ]);

        // Convert application parents to student guardians
        foreach ($this->parents as $parent) {
            $guardian = Guardian::create([
                'school_id' => $this->school_id,
                'first_name' => $parent->first_name,
                'last_name' => $parent->last_name,
                'relationship' => $parent->relationship,
                'email' => $parent->email,
                'phone_primary' => $parent->phone_primary,
                'phone_secondary' => $parent->phone_secondary,
                'address' => $parent->address,
                'occupation' => $parent->occupation,
                'is_primary_contact' => $parent->is_primary_contact,
                'receives_communication' => true,
            ]);

            // Link guardian to student
            $student->guardians()->attach($guardian->id, [
                'is_emergency_contact' => $parent->is_primary_contact,
            ]);
        }

        // Update application status
        $this->update([
            'status' => 'enrolled',
        ]);

        return $student;
    }
    
    /**
     * Get the status color class for display in UI.
     * 
     * @return string
     */
    public function getStatusColorClass()
    {
        $statusColors = [
            'submitted' => 'bg-blue-100 text-blue-800',
            'under_review' => 'bg-yellow-100 text-yellow-800',
            'pending_documents' => 'bg-orange-100 text-orange-800',
            'interview_scheduled' => 'bg-purple-100 text-purple-800',
            'accepted' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'enrolled' => 'bg-indigo-100 text-indigo-800',
            'waitlisted' => 'bg-gray-100 text-gray-800',
        ];
        
        return $statusColors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}