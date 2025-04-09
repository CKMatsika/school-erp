// PosTerminalController.php
public function register(Request $request)
{
    $validated = $request->validate([
        'terminal_id' => 'required|string|unique:pos_terminals,terminal_id',
        'name' => 'required|string',
        'model' => 'required|string',
        'payment_processor' => 'required|string', // e.g., 'visa', 'mastercard', 'bank_name'
        'api_key' => 'nullable|string',
        'merchant_id' => 'required|string',
        'location' => 'nullable|string',
        'is_active' => 'boolean',
    ]);
    
    $validated['school_id'] = Auth::user()->school_id;
    $validated['is_active'] = $request->boolean('is_active', true);
    
    $terminal = PosTerminal::create($validated);
    
    return redirect()->route('accounting.pos.terminals.index')
        ->with('success', 'POS Terminal registered successfully.');
}