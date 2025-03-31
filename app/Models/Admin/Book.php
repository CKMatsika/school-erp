<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'isbn',
        'author',
        'category_id',
        'publisher',
        'publication_year',
        'edition',
        'pages',
        'price',
        'quantity',
        'available_quantity',
        'rack_number',
        'shelf_number',
        'description',
        'cover_image',
        'table_of_contents',
        'language',
        'tags',
        'status', // 'available', 'partially-available', 'unavailable'
        'added_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'publication_year' => 'integer',
        'pages' => 'integer',
        'quantity' => 'integer',
        'available_quantity' => 'integer',
        'tags' => 'array',
    ];

    /**
     * Get the category that owns the book.
     */
    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    /**
     * Get the staff member who added the book.
     */
    public function addedBy()
    {
        return $this->belongsTo(Staff::class, 'added_by');
    }

    /**
     * Get the book copies for the book.
     */
    public function copies()
    {
        return $this->hasMany(BookCopy::class);
    }

    /**
     * Get the circulations for the book.
     */
    public function circulations()
    {
        return $this->hasMany(BookCirculation::class);
    }

    /**
     * Get current active circulations.
     */
    public function activeCirculations()
    {
        return $this->circulations()->whereNull('return_date');
    }

    /**
     * Scope a query to only include books by category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include books by author.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $author
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAuthor($query, $author)
    {
        return $query->where('author', 'LIKE', "%{$author}%");
    }

    /**
     * Scope a query to only include books by language.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $language
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope a query to only include available books.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    /**
     * Check if book is available for circulation.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->available_quantity > 0;
    }

    /**
     * Update book availability status based on quantity.
     *
     * @return bool
     */
    public function updateStatus()
    {
        if ($this->available_quantity <= 0) {
            $this->status = 'unavailable';
        } elseif ($this->available_quantity < $this->quantity) {
            $this->status = 'partially-available';
        } else {
            $this->status = 'available';
        }
        
        return $this->save();
    }

    /**
     * Search books by title, author, ISBN, or publisher.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%")
                    ->orWhere('isbn', 'LIKE', "%{$search}%")
                    ->orWhere('publisher', 'LIKE', "%{$search}%");
    }

    /**
     * Get books with tag.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $tag
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->where('tags', 'LIKE', "%{$tag}%");
    }

    /**
     * Get circulation history count.
     *
     * @return int
     */
    public function getCirculationCountAttribute()
    {
        return $this->circulations()->count();
    }

    /**
     * Update available quantity when a book is issued or returned.
     *
     * @param  int  $quantity
     * @param  string  $operation 'issue' or 'return'
     * @return bool
     */
    public function updateAvailableQuantity($quantity, $operation)
    {
        if ($operation === 'issue') {
            $this->available_quantity -= $quantity;
        } elseif ($operation === 'return') {
            $this->available_quantity += $quantity;
        }
        
        // Ensure available_quantity doesn't exceed total quantity or go below 0
        $this->available_quantity = max(0, min($this->available_quantity, $this->quantity));
        
        $this->updateStatus();
        
        return $this->save();
    }
}