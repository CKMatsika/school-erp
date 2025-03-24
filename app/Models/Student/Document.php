<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'documentable_id',
        'documentable_type',
        'document_type_id',
        'file_path',
        'file_name',
        'upload_date',
        'expiry_date',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Polymorphic relationship (works with both students and applications)
    public function documentable()
    {
        return $this->morphTo();
    }

    // Document Type relationship
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    // Verified by relationship
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Check if document is expired
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}