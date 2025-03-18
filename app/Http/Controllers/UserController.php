<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('view-users');
        
        $user = Auth::user();
        $query = User::query();
        
        // Super admin can see all users, others only see their school's users
        if (!$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        } elseif ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        
        // Filter by user type
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $status = $request->status == 'active';
            $query->where('is_active', $status);
        }
        
        $users = $query->with('roles')->paginate(15);
        $schools = School::all();
        
        return view('core.users.index', compact('users', 'schools'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        $this->authorize('create-users');
        
        $schools = School::where('is_active', true)->get();
        $roles = Role::all();
        $selectedSchoolId = $request->school_id;
        
        return view('core.users.create', compact('schools', 'roles', 'selectedSchoolId'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'school_id' => ['nullable', 'exists:schools,id'],
            'user_type' => ['required', 'string', 'in:admin,staff,teacher,student,parent'],
            'is_active' => ['boolean'],
            'roles' => ['nullable', 'array']
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        
        $user = User::create($validated);
        
        // Assign roles
        if (isset($validated['roles'])) {
            $user->roles()->attach($validated['roles']);
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view-users');
        
        // Check if user can view this specific user
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('super-admin') && $currentUser->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $user->load('roles', 'school');
        
        return view('core.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('edit-users');
        
        // Check if user can edit this specific user
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('super-admin') && $currentUser->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $schools = School::where('is_active', true)->get();
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('core.users.edit', compact('user', 'schools', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('edit-users');
        
        // Check if user can update this specific user
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('super-admin') && $currentUser->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Rules\Password::defaults()],
            'school_id' => ['nullable', 'exists:schools,id'],
            'user_type' => ['required', 'string', 'in:admin,staff,teacher,student,parent'],
            'is_active' => ['boolean'],
            'roles' => ['nullable', 'array']
        ]);
        
        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        $user->update($validated);
        
        // Update roles
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        } else {
            $user->roles()->detach();
        }
        
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete-users');
        
        // Check if user can delete this specific user
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('super-admin') && $currentUser->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        $user->roles()->detach();
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('edit-users');
        
        // Check if user can update this specific user
        $currentUser = Auth::user();
        if (!$currentUser->hasRole('super-admin') && $currentUser->school_id !== $user->school_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Prevent self-deactivation
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot change your own status.');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('users.index')
            ->with('success', "User has been {$status} successfully.");
    }
}