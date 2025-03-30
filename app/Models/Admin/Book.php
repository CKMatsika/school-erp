<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title', 
        'author', 
        'isbn', 
        'category', 
        'publication_year', 
        'publisher', 
        'quantity', 
        'location'
    ];

    public function circulations()
    {
        return $this->hasMany(BookCirculation::class);
    }
}