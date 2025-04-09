<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage; // <-- Import Storage facade
use App\Models\Role; // Assuming Role model is in App\Models
use App\Models\Module; // Assuming Module model is in App\Models

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        // 'logo', // <-- REMOVED: Assuming 'logo_path' replaces this
        'logo_path', // <-- ADDED: Path to the stored logo file
        'principal_name', // Added example, ensure all relevant fields are here
        'is_active',
        'subscription_start',
        'subscription_end'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Note: Roles are typically associated with Users, not directly with Schools,
    // unless you have school-specific roles. This relationship might need review.
    // If using spatie/laravel-permission, roles are usually global or team-based.
    public function roles()
    {
        // Consider if this relationship is needed or correctly defined
        // If roles are global, this relationship might not belong here.
        // If using teams (e.g., one school = one team), roles might be linked via teams/users.
        // return $this->hasMany(Role::class); // Keeping as placeholder based on original code
        // Or maybe you meant permissions granted to the school?
        return $this->morphToMany(\Spatie\Permission\Models\Role::class, 'model', 'model_has_roles'); // Example if School itself can have roles via Spatie
    }


    public function modules()
    {
        // Ensure 'school_modules' pivot table and Module model exist
        return $this->belongsToMany(Module::class, 'school_modules')
            ->withPivot('is_active', 'settings') // Assuming settings is stored as JSON/text
            ->withTimestamps()
            ->using(SchoolModule::class); // Optional: Use a custom pivot model if needed
    }

    // Check if subscription is active
    public function isSubscriptionActive(): bool // Added return type hint
    {
        if (!$this->subscription_end) {
            return true; // No end date means unlimited or perpetually active
        }

        // Ensure subscription_end is a Carbon instance for comparison
        return $this->subscription_end instanceof \Carbon\Carbon ?
               $this->subscription_end->isFuture() :
               false; // Or handle non-Carbon date appropriately
    }


    // =============================================
    // == ADDED LOGO URL ACCESSOR ==
    // =============================================
    /**
     * Get the full public URL for the school logo.
     * Access via $school->logo_url
     *
     * @return string|null
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            // Generate URL using the 'public' disk configuration
            return Storage::disk('public')->url($this->logo_path);
        }
        // Optional: Return a default placeholder logo URL if needed
        // return asset('images/default_logo.png');
        return null; // Return null if no logo path or file doesn't exist
    }
    // =============================================

} // End of School class

// Optional Pivot Model (if you need extra logic on the pivot table)
// Create this file if needed: app/Models/SchoolModule.php
// use Illuminate\Database\Eloquent\Relations\Pivot;
// class SchoolModule extends Pivot {
//     protected $casts = ['settings' => 'array']; // Example cast
// }