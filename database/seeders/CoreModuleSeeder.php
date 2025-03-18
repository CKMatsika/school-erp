// database/seeders/CoreModuleSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CoreModuleSeeder extends Seeder
{
    public function run()
    {
        // Create Core Module
        $coreModule = Module::create([
            'name' => 'Core',
            'key' => 'core',
            'description' => 'Core system functionality including authentication, user management, and module management',
            'is_active' => true,
            'is_core' => true,
            'version' => '1.0.0'
        ]);
        
        // Create Timetable Module
        $timetableModule = Module::create([
            'name' => 'Timetable',
            'key' => 'timetable',
            'description' => 'School timetable management system for class scheduling',
            'is_active' => true,
            'is_core' => false,
            'dependencies' => ['core'],
            'version' => '1.0.0'
        ]);
        
        // Create core permissions
        $permissions = [
            // User permissions
            ['name' => 'View users', 'slug' => 'view-users', 'module' => 'core'],
            ['name' => 'Create users', 'slug' => 'create-users', 'module' => 'core'],
            ['name' => 'Edit users', 'slug' => 'edit-users', 'module' => 'core'],
            ['name' => 'Delete users', 'slug' => 'delete-users', 'module' => 'core'],
            
            // Role permissions
            ['name' => 'View roles', 'slug' => 'view-roles', 'module' => 'core'],
            ['name' => 'Create roles', 'slug' => 'create-roles', 'module' => 'core'],
            ['name' => 'Edit roles', 'slug' => 'edit-roles', 'module' => 'core'],
            ['name' => 'Delete roles', 'slug' => 'delete-roles', 'module' => 'core'],
            
            // Module permissions
            ['name' => 'View modules', 'slug' => 'view-modules', 'module' => 'core'],
            ['name' => 'Manage modules', 'slug' => 'manage-modules', 'module' => 'core'],
            
            // School permissions
            ['name' => 'View school', 'slug' => 'view-school', 'module' => 'core'],
            ['name' => 'Edit school', 'slug' => 'edit-school', 'module' => 'core'],
        ];
        
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        
        // Create system roles
        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'System administrator with all permissions',
            'is_system' => true
        ]);
        
        $schoolAdminRole = Role::create([
            'name' => 'School Admin',
            'slug' => 'school-admin',
            'description' => 'School administrator with management permissions',
            'is_system' => true
        ]);
        
        // Assign all permissions to admin role
        $adminRole->permissions()->attach(Permission::all());
        
        // Assign school management permissions to school admin
        $schoolAdminRole->permissions()->attach(
            Permission::whereIn('slug', [
                'view-users', 'create-users', 'edit-users',
                'view-roles', 'create-roles', 'edit-roles',
                'view-modules', 'manage-modules',
                'view-school', 'edit-school'
            ])->get()
        );
    }
}