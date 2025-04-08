<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDebtController extends Controller
{
    /**
     * Display age analysis of student debts.
     */
    public function ageAnalysis()
    {
        $school_id = Auth::user()?->school_id;
        
        // You'll need to update this query based on your actual database structure
        $debtAnalysis = DB::table('student_accounts')
            ->select(
                DB::raw('SUM(CASE WHEN due_date >= NOW() THEN amount ELSE 0 END) as current_amount'),
                DB::raw('SUM(CASE WHEN due_date < NOW() AND due_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as thirty_days'),
                DB::raw('SUM(CASE WHEN due_date < DATE_SUB(NOW(), INTERVAL 30 DAY) AND due_date >= DATE_SUB(NOW(), INTERVAL 60 DAY) THEN amount ELSE 0 END) as sixty_days'),
                DB::raw('SUM(CASE WHEN due_date < DATE_SUB(NOW(), INTERVAL 60 DAY) AND due_date >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN amount ELSE 0 END) as ninety_days'),
                DB::raw('SUM(CASE WHEN due_date < DATE_SUB(NOW(), INTERVAL 90 DAY) THEN amount ELSE 0 END) as older_than_ninety')
            )
            ->when($school_id, fn($query) => $query->where('school_id', $school_id))
            ->first();
            
        return view('accounting.student-debts.age-analysis', compact('debtAnalysis'));
    }
    
    /**
     * Display a listing of student debts.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        
        // Basic query to get student debts - adjust according to your database structure
        $studentDebts = DB::table('student_accounts')
            ->join('students', 'student_accounts.student_id', '=', 'students.id')
            ->select('student_accounts.*', 'students.name as student_name')
            ->when($school_id, fn($query) => $query->where('student_accounts.school_id', $school_id))
            ->orderBy('due_date')
            ->paginate(15);
            
        return view('accounting.student-debts.index', compact('studentDebts'));
    }
    
    /**
     * Show the details of a specific student's debt.
     */
    public function show($id)
    {
        $school_id = Auth::user()?->school_id;
        
        // Fetch student account details - adjust according to your database structure
        $studentAccount = DB::table('student_accounts')
            ->join('students', 'student_accounts.student_id', '=', 'students.id')
            ->select('student_accounts.*', 'students.name as student_name')
            ->where('student_accounts.id', $id)
            ->when($school_id, fn($query) => $query->where('student_accounts.school_id', $school_id))
            ->first();
            
        if (!$studentAccount) {
            abort(404, 'Student account not found');
        }
        
        return view('accounting.student-debts.show', compact('studentAccount'));
    }
}