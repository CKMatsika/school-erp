// app/Http/Middleware/ModuleAccessMiddleware.php
<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Support\Facades\Auth;

class ModuleAccessMiddleware
{
    protected $moduleService;
    
    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }
    
    public function handle($request, Closure $next, $moduleKey)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        $schoolId = $user->school_id;
        
        // Check if module is active for this school
        if (!$this->moduleService->isModuleActive($moduleKey, $schoolId)) {
            return redirect()->route('dashboard')
                ->with('error', 'This module is not active for your school.');
        }
        
        return $next($request);
    }
}