<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::all();
        return view('core.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('core.schools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:schools,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'is_active' => 'boolean',
            'subscription_start' => 'nullable|date',
            'subscription_end' => 'nullable|date|after_or_equal:subscription_start',
        ]);

        $school = School::create($validated);

        return redirect()->route('schools.index')
            ->with('success', 'School created successfully.');
    }

    public function show(School $school)
    {
        return view('core.schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        return view('core.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:schools,code,' . $school->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'is_active' => 'boolean',
            'subscription_start' => 'nullable|date',
            'subscription_end' => 'nullable|date|after_or_equal:subscription_start',
        ]);

        $school->update($validated);

        return redirect()->route('schools.index')
            ->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {
        // Check if school has users
        if ($school->users()->count() > 0) {
            return redirect()->route('schools.index')
                ->with('error', 'Cannot delete school with users.');
        }

        $school->delete();

        return redirect()->route('schools.index')
            ->with('success', 'School deleted successfully.');
    }
}