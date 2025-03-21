<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'sms_gateway_id',
        'sms_template_id',
        'recipient_number',
        'recipient_name',
        'student_id',
        'message_id',
        'message_content',
        'related_to',
        'related_id',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function gateway()
    {
        return $this->belongsTo(SmsGateway::class, 'sms_gateway_id');
    }

    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function relatedModel()
    {
        switch ($this->related_to) {
            case 'invoice':
                return $this->belongsTo(Invoice::class, 'related_id');
            case 'payment':
                return $this->belongsTo(Payment::class, 'related_id');
            // Add other related models as needed
            default:
                return null;
        }
    }
}