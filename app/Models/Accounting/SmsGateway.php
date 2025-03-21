<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsGateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'provider',
        'api_key',
        'api_secret',
        'sender_id',
        'api_endpoint',
        'configuration',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }
}