<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherProfileController extends Controller
{
    /**
     * Display the teacher's profile.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        return view('teacher.profile.index', compact('teacher'));
    }

    /**
     * Show the form for editing the teacher's profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $teacher = Auth::user()->teacher;
        $departments = ['Mathematics', 'Science', 'English', 'Social Studies', 'Arts', 'Physical Education', 'Computer Science', 'Foreign Languages', 'Special Education'];
        
        return view('teacher.profile.edit', compact('teacher', 'departments'));
    }

    /**
     * Update the teacher's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'department' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'qualifications' => 'nullable|string|max:255',
            'experience' => 'nullable|numeric',
            'specialization' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        // Update user name and email
        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Update teacher profile
        $teacher = $user->teacher;
        $teacher->email = $request->email;
        $teacher->phone = $request->phone;
        $teacher->date_of_birth = $request->date_of_birth;
        $teacher->gender = $request->gender;
        $teacher->address = $request->address;
        $teacher->department = $request->department;
        $teacher->join_date = $request->join_date;
        $teacher->qualifications = $request->qualifications;
        $teacher->experience = $request->experience;
        $teacher->specialization = $request->specialization;
        $teacher->linkedin = $request->linkedin;
        $teacher->twitter = $request->twitter;
        $teacher->website = $request->website;
        $teacher->bio = $request->bio;
        $teacher->save();

        return redirect()->route('teacher.profile.index')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the teacher's profile photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $user = Auth::user();
        
        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->photo);
        }

        return redirect()->route('teacher.profile.index')->with('success', 'Profile photo updated successfully!');
    }

    /**
     * Update the teacher's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('teacher.profile.index')->with('success', 'Password updated successfully!');
    }
}