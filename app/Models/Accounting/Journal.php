<?php

namespace App\Models\Accounting;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'reference',
        'journal_date',
        'description',
        'status',
        'created_by',
        'document_type',
        'document_id',
    ];

    protected $casts = [
        'journal_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDocument()
    {
        if (!$this->document_type || !$this->document_id) {
            return null;
        }

        switch ($this->document_type) {
            case 'invoice':
                return Invoice::find($this->document_id);
            case 'payment':
                return Payment::find($this->document_id);
            default:
                return null;
        }
    }

    public function isBalanced()
    {
        $totalDebits = $this->entries->sum('debit');
        $totalCredits = $this->entries->sum('credit');
        
        return abs($totalDebits - $totalCredits) < 0.01;
    }
}