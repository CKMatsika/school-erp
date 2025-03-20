<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        // Remove authorization check for now
        // $this->authorize('view-roles');
        
        $user = Auth::user();
        $query = Role::query();
        
        // If not super admin, show only school-specific roles or system roles
        // Temporarily comment this out until you have super-admin role properly set up
        /*
        if (!$user->hasRole('super-admin')) {
            $query->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhere('is_system', true);
            });
        }
        */
        
        $roles = $query->withCount('users')->get();
        
        return view('core.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        // Remove authorization check for now
        // $this->authorize('create-roles');
        
        $permissions = Permission::all()->groupBy('module');
        
        return view('core.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        // Remove authorization check for now
        // $this->authorize('create-roles');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'is_system' => ['boolean']
        ]);
        
        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);
        
        // Ensure slug is unique
        $count = 0;
        $originalSlug = $validated['slug'];
        while (Role::where('slug', $validated['slug'])->exists()) {
            $count++;
            $validated['slug'] = $originalSlug . '-' . $count;
        }
        
        // Assign school_id if not a system role
        if (!isset($validated['is_system']) || !$validated['is_system']) {
            $validated['school_id'] = Auth::user()->school_id;
        } else {
            $validated['school_id'] = null;
        }
        
        $role = Role::create($validated);
        
        // Attach permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }
        
        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        // Remove authorization check for now
        // $this->authorize('view-roles');
        
        // Temporarily disable role checking
        /*
        // Check if user can view this specific role
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && !$role->is_system && $user->school_id !== $role->school_id) {
            abort(403, 'Unauthorized action.');
        }
        */
        
        $role->load('permissions', 'users');
        
        return view('core.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        // Remove authorization check for now
        // $this->authorize('edit-roles');
        
        // Temporarily disable role checking
        /*
        // Check if user can edit this specific role
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && !$role->is_system && $user->school_id !== $role->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Don't allow editing of the super-admin role
        if ($role->slug === 'super-admin' && !$user->hasRole('super-admin')) {
            abort(403, 'You cannot edit the super admin role.');
        }
        */
        
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('core.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Remove authorization check for now
        // $this->authorize('edit-roles');
        
        // Temporarily disable role checking
        /*
        // Check if user can update this specific role
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && !$role->is_system && $user->school_id !== $role->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Don't allow editing of the super-admin role
        if ($role->slug === 'super-admin' && !$user->hasRole('super-admin')) {
            abort(403, 'You cannot edit the super admin role.');
        }
        */
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'is_system' => ['boolean']
        ]);
        
        // Don't allow changing system status of built-in roles
        if ($role->is_system) {
            $validated['is_system'] = true;
        }
        
        // Update school_id if is_system changes
        if (isset($validated['is_system']) && $validated['is_system']) {
            $validated['school_id'] = null;
        } elseif (isset($validated['is_system']) && !$validated['is_system'] && $role->is_system) {
            $validated['school_id'] = Auth::user()->school_id;
        }
        
        $role->update($validated);
        
        // Update permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        } else {
            $role->permissions()->detach();
        }
        
        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Remove authorization check for now
        // $this->authorize('delete-roles');
        
        // Temporarily disable role checking
        /*
        // Check if user can delete this specific role
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && !$role->is_system && $user->school_id !== $role->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Don't allow deletion of built-in roles
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }
        */
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role with assigned users.');
        }
        
        $role->permissions()->detach();
        $role->delete();
        
        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}