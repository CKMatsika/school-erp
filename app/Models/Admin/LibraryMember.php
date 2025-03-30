<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    protected $fillable = [
        'user_id', 
        'membership_number', 
        'membership_type', 
        'join_date', 
        'expiry_date', 
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}