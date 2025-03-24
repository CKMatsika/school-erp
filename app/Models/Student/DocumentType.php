<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'is_required',
        'applies_to',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    // School relationship
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Documents relationship
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}