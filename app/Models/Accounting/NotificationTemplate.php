<?php

namespace App\Models\Accounting;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'type',
        'event',
        'subject',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function logs()
    {
        return $this->hasMany(NotificationLog::class, 'template_id');
    }

    public function renderContent($params = [])
    {
        $content = $this->content;
        
        foreach ($params as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }
        
        return $content;
    }

    public function renderSubject($params = [])
    {
        if (!$this->subject) {
            return null;
        }
        
        $subject = $this->subject;
        
        foreach ($params as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', $value, $subject);
        }
        
        return $subject;
    }
}