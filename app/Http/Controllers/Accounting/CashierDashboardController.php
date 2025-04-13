<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\PosTerminal;
use App\Models\Accounting\PosSession;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CashierDashboardController extends Controller
{
    public function index()
    {
        // Check if user has a school_id
        $user = Auth::user();
        
        if (!$user->school_id) {
            // Check if there's only one school in the system
            $schools = School::all();
            
            if ($schools->count() == 1) {
                // Automatically assign the school to the user
                $user->school_id = $schools->first()->id;
                $user->save();
                
                // Log this auto-assignment
                Log::info("Auto-assigned school ID {$user->school_id} to user {$user->id}");
            } else {
                // Return view with schools dropdown to select
                return view('accounting.cashier.dashboard', [
                    'needsSchoolAssignment' => true,
                    'schools' => $schools,
                    'activeSession' => null
                ]);
            }
        }
        
        // Check for an active session for this user
        // Inspect the database structure - we need to use the correct column names
        // If your table doesn't have a cashier_id column, use what you have (e.g. 'started_by')
        try {
            $activeSession = PosSession::where(function($query) {
                // Try multiple possible column names that might link to the user
                $query->where('started_by', Auth::id())
                      ->orWhere('user_id', Auth::id())
                      ->orWhere('cashier_user_id', Auth::id());
            })
            ->where('status', 'active')
            ->first();
        } catch (\Exception $e) {
            Log::error("Error looking for active session: " . $e->getMessage());
            // Try looking at the schema
            $columns = \Schema::getColumnListing('pos_sessions');
            Log::info("Pos_sessions columns: " . implode(', ', $columns));
            $activeSession = null;
        }
        
        // Get terminals for new session form
        try {
            $terminals = PosTerminal::where('school_id', Auth::user()->school_id)
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error("Error fetching terminals: " . $e->getMessage());
            $terminals = collect([]);
        }
        
        return view('accounting.cashier.dashboard', [
            'activeSession' => $activeSession,
            'terminals' => $terminals,
            'needsSchoolAssignment' => false
        ]);
    }
    
    public function startSession(Request $request)
    {
        // Validate school assignment if needed
        if (!Auth::user()->school_id && $request->has('school_id')) {
            $request->validate([
                'school_id' => 'required|exists:schools,id'
            ]);
            
            $user = Auth::user();
            $user->school_id = $request->school_id;
            $user->save();
            
            return redirect()->route('accounting.cashier.dashboard')
                ->with('success', 'School assigned successfully.');
        }
        
        try {
            // Validate session data
            $validated = $request->validate([
                'terminal_id' => 'required|exists:pos_terminals,id', // Using terminal_id based on your PosTerminal model
                'opening_balance' => 'required|numeric|min:0',
            ]);
            
            // Check if there's already an active session for this terminal
            $activeTerminalSession = PosSession::where('terminal_id', $validated['terminal_id'])
                ->where('status', 'active')
                ->first();
                
            if ($activeTerminalSession) {
                return back()->with('error', 'This terminal already has an active session.');
            }
            
            // Create a new session - update field names as needed based on your table structure
            $session = new PosSession();
            $session->terminal_id = $validated['terminal_id']; // Changed from pos_terminal_id based on your model
            $session->opening_balance = $validated['opening_balance'];
            $session->started_by = Auth::id(); // Using started_by instead of cashier_id
            $session->started_at = now();
            $session->status = 'active';
            $session->school_id = Auth::user()->school_id;
            $session->save();
            
            return redirect()->route('accounting.cashier.dashboard')
                ->with('success', 'POS Session started successfully.');
        } catch (\Exception $e) {
            Log::error("Error starting session: " . $e->getMessage());
            return back()->with('error', 'Failed to start session: ' . $e->getMessage());
        }
    }
    
    public function endSession(PosSession $session)
    {
        // Check if the session belongs to the user
        if ($session->started_by != Auth::id() && $session->cashier_user_id != Auth::id() && $session->user_id != Auth::id()) {
            return back()->with('error', 'You do not have permission to end this session.');
        }
        
        if ($session->status !== 'active') {
            return back()->with('error', 'This session is not active.');
        }
        
        // Return the end session view
        return view('accounting.cashier.end_session', compact('session'));
    }
    
    public function closeSession(Request $request, PosSession $session)
    {
        // Check if the session belongs to the user
        if ($session->started_by != Auth::id() && $session->cashier_user_id != Auth::id() && $session->user_id != Auth::id()) {
            return back()->with('error', 'You do not have permission to close this session.');
        }
        
        if ($session->status !== 'active') {
            return back()->with('error', 'This session is not active.');
        }
        
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'cash_counted' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        try {
            // Calculate totals from transactions - adjust based on your relationships
            $transactions = $session->transactions ?? collect([]);
            $calculatedTotal = $transactions->sum('amount');
            $expectedClosingBalance = $session->opening_balance + $calculatedTotal;
            
            $session->closing_balance = $validated['closing_balance'];
            $session->cash_counted = $validated['cash_counted'];
            $session->expected_closing_balance = $expectedClosingBalance;
            $session->discrepancy = $validated['cash_counted'] - $expectedClosingBalance;
            $session->notes = $validated['notes'];
            $session->ended_at = now();
            $session->ended_by = Auth::id();
            $session->status = 'closed';
            $session->save();
            
            return redirect()->route('accounting.cashier.dashboard')
                ->with('success', 'POS Session closed successfully.');
        } catch (\Exception $e) {
            Log::error("Error closing session: " . $e->getMessage());
            return back()->with('error', 'Failed to close session: ' . $e->getMessage());
        }
    }
    
    public function sessionReport(PosSession $session)
    {
        // Check if the session belongs to the user's school
        if ($session->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        // Generate session report
        $transactions = $session->transactions ?? collect([]);
        
        return view('accounting.cashier.session_report', compact('session', 'transactions'));
    }
}