<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Student\Application;
use Illuminate\Http\Request;

class StudentSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        
        $students = Student::where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('admission_number', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })->where('school_id', auth()->user()->school_id)
          ->limit(20)
          ->get();
        
        $applications = Application::where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('application_number', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })->where('school_id', auth()->user()->school_id)
          ->limit(20)
          ->get();
        
        return view('student.search.results', [
            'query' => $query,
            'students' => $students,
            'applications' => $applications,
        ]);
    }
}