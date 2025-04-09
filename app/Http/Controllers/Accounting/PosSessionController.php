// PosSessionController.php
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
    
    $session = PosSession::create($validated);
    
    return redirect()->route('accounting.pos.sessions.show', $session)
        ->with('success', 'POS Session started successfully.');
}

public function endSession(PosSession $session)
{
    if ($session->status !== 'active') {
        return back()->with('error', 'This session is not active.');
    }
    
    return view('accounting.pos.sessions.end', compact('session'));
}

public function closeSession(Request $request, PosSession $session)
{
    if ($session->status !== 'active') {
        return back()->with('error', 'This session is not active.');
    }
    
    $validated = $request->validate([
        'closing_balance' => 'required|numeric|min:0',
        'cash_counted' => 'required|numeric|min:0',
        'notes' => 'nullable|string',
    ]);
    
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
}