<?php

namespace App\Models\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class BookCirculation extends Model
{
    protected $fillable = [
        'book_id', 
        'member_id', 
        'borrow_date', 
        'due_date', 
        'return_date', 
        'status'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}