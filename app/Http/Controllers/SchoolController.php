<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     */
    public function index()
    {
        $schools = DB::table('schools')->orderBy('name')->get();
        return view('schools.index', compact('schools'));
    }

    /**
     * Show the form for creating a new school.
     */
    public function create()
    {
        return view('schools.create');
    }

    /**
     * Store a newly created school in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            // Add other fields as needed
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('schools.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::table('schools')->insert([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            // Add other fields as needed
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('schools.index')
            ->with('success', 'School created successfully');
    }

    /**
     * Display the specified school.
     */
    public function show($id)
    {
        $school = DB::table('schools')->where('id', $id)->first();
        
        if (!$school) {
            return redirect()->route('schools.index')
                ->with('error', 'School not found');
        }
        
        return view('schools.show', compact('school'));
    }

    /**
     * Show the form for editing the specified school.
     */
    public function edit($id)
    {
        $school = DB::table('schools')->where('id', $id)->first();
        
        if (!$school) {
            return redirect()->route('schools.index')
                ->with('error', 'School not found');
        }
        
        return view('schools.edit', compact('school'));
    }

    /**
     * Update the specified school in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            // Add other fields as needed
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('schools.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $school = DB::table('schools')->where('id', $id)->first();
        
        if (!$school) {
            return redirect()->route('schools.index')
                ->with('error', 'School not found');
        }
        
        DB::table('schools')->where('id', $id)->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            // Add other fields as needed
            'updated_at' => now()
        ]);
        
        return redirect()->route('schools.index')
            ->with('success', 'School updated successfully');
    }

    /**
     * Remove the specified school from storage.
     */
    public function destroy($id)
    {
        $school = DB::table('schools')->where('id', $id)->first();
        
        if (!$school) {
            return redirect()->route('schools.index')
                ->with('error', 'School not found');
        }
        
        // Check if school is being used by any users, etc. before deleting
        
        DB::table('schools')->where('id', $id)->delete();
        
        return redirect()->route('schools.index')
            ->with('success', 'School deleted successfully');
    }
}