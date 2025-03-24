<?php

namespace App\Models\Traits;

use App\Models\Activity;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    public function logActivity($action)
    {
        $description = $this->getActivityDescription($action);
        $subjectName = $this->getActivitySubjectName();

        Activity::create([
            'school_id' => $this->school_id ?? auth()->user()->school_id ?? null,
            'user_id' => auth()->id(),
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'action' => $action,
            'description' => $description,
            'subject_name' => $subjectName,
            'ip_address' => request()->ip(),
        ]);
    }

    protected function getActivityDescription($action)
    {
        $modelName = class_basename($this);
        
        return $action . ' ' . strtolower($modelName);
    }

    protected function getActivitySubjectName()
    {
        return $this->name ?? 
               $this->title ?? 
               ($this->first_name && $this->last_name ? $this->first_name . ' ' . $this->last_name : null) ?? 
               'ID: ' . $this->id;
    }
}