<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationParent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'first_name',
        'last_name',
        'relationship',
        'email',
        'phone_primary',
        'phone_secondary',
        'address',
        'occupation',
        'is_primary_contact',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary_contact' => 'boolean',
    ];

    /**
     * Get the application that owns the parent.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the parent's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check if this parent is a primary contact.
     *
     * @return bool
     */
    public function isPrimaryContact()
    {
        return $this->is_primary_contact;
    }

    /**
     * Scope a query to only include primary contacts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimaryContacts($query)
    {
        return $query->where('is_primary_contact', true);
    }

    /**
     * Scope a query to filter by relationship type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $relationship
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRelationship($query, $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    /**
     * Scope a query to search parent information.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone_primary', 'like', "%{$search}%")
                ->orWhere('occupation', 'like', "%{$search}%");
        });
    }
}