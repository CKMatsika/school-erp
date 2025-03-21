<?php

namespace App\Models\Accounting;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'template_id',
        'event',
        'recipient_type',
        'recipient_id',
        'channel',
        'to',
        'subject',
        'content',
        'status',
        'error_message',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function getRecipient()
    {
        if (!$this->recipient_type || !$this->recipient_id) {
            return null;
        }

        $model = 'App\\Models\\' . $this->recipient_type;
        if (class_exists($model)) {
            return $model::find($this->recipient_id);
        }

        return null;
    }
}