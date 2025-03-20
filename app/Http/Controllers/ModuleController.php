<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\SchoolModule;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    protected $moduleService;
    
    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }
    
    /**
     * Display a listing of available modules
     */
    public function index()
    {
        // Remove this line for now: $this->authorize('manage-modules');
        
        $user = Auth::user();
        $modules = $this->moduleService->getAvailableModules($user->school_id);
        
        return view('core.modules.index', compact('modules'));
    }
    
    /**
     * Toggle module activation for a school
     */
    public function toggle(Request $request, $moduleKey)
    {
        // Remove this line for now: $this->authorize('manage-modules');
        
        $user = Auth::user();
        $schoolId = $user->school_id;
        $module = Module::where('key', $moduleKey)->firstOrFail();
        
        try {
            $schoolModule = SchoolModule::firstOrNew([
                'school_id' => $schoolId,
                'module_id' => $module->id
            ]);
            
            // Toggle active status
            $currentStatus = $schoolModule->is_active ?? false;
            
            if ($currentStatus) {
                $this->moduleService->deactivateModule($moduleKey, $schoolId);
                $message = "Module '{$module->name}' has been deactivated.";
            } else {
                $this->moduleService->activateModule($moduleKey, $schoolId);
                $message = "Module '{$module->name}' has been activated.";
            }
            
            return redirect()->route('modules.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('modules.index')->with('error', $e->getMessage());
        }
    }
}