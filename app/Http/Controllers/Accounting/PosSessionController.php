<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\PosTerminal;
use App\Models\Accounting\PosSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PosSessionController extends Controller
{
    public function index()
    {
        $sessions = PosSession::where('school_id', Auth::user()->school_id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('accounting.pos.sessions.index', compact('sessions'));
    }
    
    public function create()
    {
        // Check if user is authenticated and has school_id
        if (!Auth::check() || !Auth::user()->school_id) {
            return redirect()->route('accounting.dashboard')
                ->with('error', 'Your account is not properly set up. Please contact an administrator.');
        }

        try {
            // Get terminals
            $terminals = PosTerminal::where('school_id', Auth::user()->school_id)
                ->where('is_active', true)
                ->get();
            
            // If no terminals exist, redirect with a message
            if ($terminals->isEmpty()) {
                return redirect()->route('accounting.dashboard')
                    ->with('warning', 'No active terminals found. Please set up POS terminals first.');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching terminals: ' . $e->getMessage());
            return redirect()->route('accounting.dashboard')
                ->with('error', 'There was an error retrieving the terminals: ' . $e->getMessage());
        }
        
        try {
            // Get cashiers (users with cashier role)
            $cashiers = User::where('school_id', Auth::user()->school_id)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'cashier');
                })
                ->get();
            
            // If no cashiers exist, redirect with a message
            if ($cashiers->isEmpty()) {
                return redirect()->route('accounting.dashboard')
                    ->with('warning', 'No cashiers found. Please assign the cashier role to at least one user.');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching cashiers: ' . $e->getMessage());
            return redirect()->route('accounting.dashboard')
                ->with('error', 'There was an error retrieving the cashiers: ' . $e->getMessage());
        }
        
        return view('accounting.pos.sessions.create', compact('terminals', 'cashiers'));
    }
    
    public function show(PosSession $session)
    {
        // Check if the session belongs to the user's school
        if ($session->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        $transactions = $session->transactions()
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('accounting.pos.sessions.show', compact('session', 'transactions'));
    }
    
    public function startSession(Request $request)
    {
        $validated = $request->validate([
            'pos_terminal_id' => 'required|exists:pos_terminals,id',
            'opening_balance' => 'required|numeric|min:0',
            'cashier_id' => 'required|exists:users,id',
        ]);
        
        // Check if there's already an active session for this terminal
        $activeSession = PosSession::where('pos_terminal_id', $validated['pos_terminal_id'])
            ->where('status', 'active')
            ->first();
            
        if ($activeSession) {
            return back()->with('error', 'This terminal already has an active session.');
        }
        
        $validated['started_at'] = now();
        $validated['status'] = 'active';
        $validated['school_id'] = Auth::user()->school_id;
        $validated['started_by'] = Auth::id();
        
        try {
            $session = PosSession::create($validated);
            return redirect()->route('accounting.pos.sessions.show', $session)
                ->with('success', 'POS Session started successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating session: ' . $e->getMessage());
            return back()->with('error', 'Failed to create session: ' . $e->getMessage());
        }
    }

    public function endSession(PosSession $session)
    {
        // Check if the session belongs to the user's school
        if ($session->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        if ($session->status !== 'active') {
            return back()->with('error', 'This session is not active.');
        }
        
        return view('accounting.pos.sessions.end', compact('session'));
    }
    
    public function closeSession(Request $request, PosSession $session)
    {
        // Check if the session belongs to the user's school
        if ($session->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
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
            // Calculate totals from transactions
            $transactions = $session->transactions;
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
            
            return redirect()->route('accounting.pos.sessions.index')
                ->with('success', 'POS Session closed successfully.');
        } catch (\Exception $e) {
            Log::error('Error closing session: ' . $e->getMessage());
            return back()->with('error', 'Failed to close session: ' . $e->getMessage());
        }
    }
}