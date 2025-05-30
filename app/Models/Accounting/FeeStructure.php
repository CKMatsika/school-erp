<?php

namespace App\Models\Accounting;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'academic_year',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    /**
     * Get the academic year that this fee structure belongs to.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year', 'id');
    }

    public function getTotalAmount()
    {
        return $this->items->sum('amount');
    }
}