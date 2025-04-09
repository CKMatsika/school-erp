<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $school_id = $user->school_id;
        
        // Get active session for the current user
        $activeSession = PosSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();
            
        // Get transaction statistics for the active session
        $stats = [];
        $recentTransactions = [];
        
        if ($activeSession) {
            // Calculate session statistics
            $transactions = $activeSession->transactions;
            
            $stats = [
                'transactions_count' => $transactions->count(),
                'total_amount' => $transactions->sum('amount'),
                'cash_amount' => $transactions->where('payment_method', 'cash')->sum('amount'),
                'card_amount' => $transactions->where('payment_method', 'card')->sum('amount'),
            ];
            
            // Get recent transactions for this session
            $recentTransactions = PosTransaction::where('pos_session_id', $activeSession->id)
                ->with('payment.contact')
                ->latest()
                ->take(5)
                ->get();
        }
        
        return view('accounting.cashier.dashboard', compact(
            'activeSession',
            'stats',
            'recentTransactions'
        ));
    }
    
    public function startSession(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);
        
        $user = Auth::user();
        
        try {
            // Check if user already has an active session
            $existingSession = PosSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();
                
            if ($existingSession) {
                return back()->with('error', 'You already have an active session.');
            }
            
            // Start a new session
            $session = PosSession::startSession(
                $validated['terminal_id'],
                $user->id,
                $validated['opening_balance']
            );
            
            return redirect()->route('accounting.cashier.dashboard')
                ->with('success', 'Session started successfully.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start session: ' . $e->getMessage());
        }
    }
    
    public function endSession(Request $request, PosSession $session)
    {
        // Check authorization
        if ($session->user_id !== Auth::id()) {
            return back()->with('error', 'You can only end your own sessions.');
        }
        
        if ($session->status !== 'open') {
            return back()->with('error', 'This session is already closed.');
        }
        
        return view('accounting.cashier.end_session', compact('session'));
    }
    
    public function closeSession(Request $request, PosSession $session)
    {
        // Check authorization
        if ($session->user_id !== Auth::id()) {
            return back()->with('error', 'You can only close your own sessions.');
        }
        
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $session->closeSession(
                $validated['closing_balance'],
                $validated['notes'],
                Auth::id()
            );
            
            return redirect()->route('accounting.cashier.dashboard')
                ->with('success', 'Session closed successfully.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to close session: ' . $e->getMessage());
        }
    }
    
    public function sessionReport(PosSession $session)
    {
        // Check authorization (cashiers should only see their own reports)
        if ($session->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return back()->with('error', 'You can only view your own session reports.');
        }
        
        // Generate Z-reading report
        $report = $session->generateZReading();
        
        return view('accounting.cashier.session_report', compact('session', 'report'));
    }
}