<?php

namespace App\Services;

use App\Models\Module;
use App\Models\School;
use App\Models\SchoolModule;
use Illuminate\Support\Facades\Config;

class ModuleService
{
    /**
     * Check if a module is active for a specific school
     */
    public function isModuleActive($moduleKey, $schoolId = null)
    {
        // Get the module
        $module = Module::where('key', $moduleKey)->first();
        
        if (!$module || !$module->is_active) {
            return false;
        }
        
        // Core modules are always active
        if ($module->is_core) {
            return true;
        }
        
        // If no school specified, check global activation only
        if (!$schoolId) {
            return $module->is_active;
        }
        
        // Check school-specific activation
        $schoolModule = SchoolModule::where('module_id', $module->id)
            ->where('school_id', $schoolId)
            ->first();
            
        return $schoolModule && $schoolModule->is_active;
    }
    
    /**
     * Activate a module for a specific school
     */
    public function activateModule($moduleKey, $schoolId)
    {
        $module = Module::where('key', $moduleKey)->firstOrFail();
        $school = School::findOrFail($schoolId);
        
        // Check dependencies
        if ($module->dependencies) {
            foreach ($module->dependencies as $dependency) {
                if (!$this->isModuleActive($dependency, $schoolId)) {
                    throw new \Exception("Required dependency module '$dependency' is not active");
                }
            }
        }
        
        // Create or update the school module record
        $schoolModule = SchoolModule::firstOrNew([
            'school_id' => $schoolId,
            'module_id' => $module->id
        ]);
        
        $schoolModule->is_active = true;
        $schoolModule->save();
        
        return $schoolModule;
    }
    
    /**
     * Deactivate a module for a specific school
     */
    public function deactivateModule($moduleKey, $schoolId)
    {
        $module = Module::where('key', $moduleKey)->firstOrFail();
        
        // Cannot deactivate core modules
        if ($module->is_core) {
            throw new \Exception("Cannot deactivate core module");
        }
        
        // Check if other modules depend on this one
        $dependentModules = Module::whereJsonContains('dependencies', $moduleKey)
            ->get();
            
        foreach ($dependentModules as $dependentModule) {
            $schoolModule = SchoolModule::where('module_id', $dependentModule->id)
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->first();
                
            if ($schoolModule) {
                throw new \Exception("Module '{$dependentModule->name}' depends on this module and is active");
            }
        }
        
        // Deactivate the module
        $schoolModule = SchoolModule::where('module_id', $module->id)
            ->where('school_id', $schoolId)
            ->first();
            
        if ($schoolModule) {
            $schoolModule->is_active = false;
            $schoolModule->save();
        }
        
        return true;
    }
    
    /**
     * Get available modules for a school
     */
    public function getAvailableModules($schoolId = null)
    {
        $modules = Module::where('is_active', true)->get();
        
        if (!$schoolId) {
            return $modules;
        }
        
        // Add school-specific activation status
        foreach ($modules as $module) {
            $schoolModule = SchoolModule::where('module_id', $module->id)
                ->where('school_id', $schoolId)
                ->first();
                
            $module->is_active_for_school = $schoolModule ? $schoolModule->is_active : false;
        }
        
        return $modules;
    }
    
    /**
     * Get module configuration
     */
    public function getModuleConfig($moduleKey)
    {
        return Config::get("modules.{$moduleKey}");
    }
    
    /**
     * Check if a module is installed
     */
    public function isModuleInstalled($moduleKey)
    {
        $config = $this->getModuleConfig($moduleKey);
        return $config && file_exists($config['path']);
    }
}