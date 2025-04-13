<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\PosTerminal; // Updated namespace
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PosTerminalController extends Controller
{
    public function index()
    {
        $terminals = PosTerminal::where('school_id', Auth::user()->school_id)
            ->orderBy('terminal_name') // Using terminal_name based on your model
            ->paginate(15);
            
        return view('accounting.pos.terminals.index', compact('terminals'));
    }
    
    public function create()
    {
        return view('accounting.pos.terminals.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|string|unique:pos_terminals,terminal_id',
            'terminal_name' => 'required|string', // Updated field name
            'device_model' => 'required|string', // Updated field name
            'payment_processor' => 'required|string',
            'api_key' => 'nullable|string',
            'merchant_id' => 'required|string',
            'location' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['school_id'] = Auth::user()->school_id;
        $validated['is_active'] = $request->boolean('is_active', true);
        
        try {
            $terminal = PosTerminal::create($validated);
            
            return redirect()->route('accounting.pos.terminals.index')
                ->with('success', 'POS Terminal registered successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating terminal: ' . $e->getMessage());
            return back()->with('error', 'Failed to create terminal: ' . $e->getMessage());
        }
    }
    
    public function show(PosTerminal $terminal)
    {
        // Check if the terminal belongs to the user's school
        if ($terminal->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        $recentSessions = $terminal->sessions()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        return view('accounting.pos.terminals.show', compact('terminal', 'recentSessions'));
    }
    
    public function edit(PosTerminal $terminal)
    {
        // Check if the terminal belongs to the user's school
        if ($terminal->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        return view('accounting.pos.terminals.edit', compact('terminal'));
    }
    
    public function update(Request $request, PosTerminal $terminal)
    {
        // Check if the terminal belongs to the user's school
        if ($terminal->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'terminal_id' => 'required|string|unique:pos_terminals,terminal_id,' . $terminal->id,
            'terminal_name' => 'required|string', // Updated field name
            'device_model' => 'required|string', // Updated field name
            'payment_processor' => 'required|string',
            'api_key' => 'nullable|string',
            'merchant_id' => 'required|string',
            'location' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active', true);
        
        try {
            $terminal->update($validated);
            
            return redirect()->route('accounting.pos.terminals.index')
                ->with('success', 'POS Terminal updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating terminal: ' . $e->getMessage());
            return back()->with('error', 'Failed to update terminal: ' . $e->getMessage());
        }
    }
    
    public function destroy(PosTerminal $terminal)
    {
        // Check if the terminal belongs to the user's school
        if ($terminal->school_id !== Auth::user()->school_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        // Check if there are any sessions using this terminal
        if ($terminal->sessions()->count() > 0) {
            return back()->with('error', 'Cannot delete this terminal as it has sessions associated with it.');
        }
        
        try {
            $terminal->delete();
            
            return redirect()->route('accounting.pos.terminals.index')
                ->with('success', 'POS Terminal deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting terminal: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete terminal: ' . $e->getMessage());
        }
    }
    
    public function setupTest()
    {
        // Check if user is authenticated and has a school ID
        if (!Auth::check()) {
            return "Please log in first";
        }
        
        if (!Auth::user()->school_id) {
            return "Your account doesn't have a school ID assigned";
        }
        
        // Create a new terminal
        try {
            $terminal = new PosTerminal();
            $terminal->school_id = Auth::user()->school_id;
            $terminal->terminal_name = "Main Counter";
            $terminal->terminal_id = "POS-" . rand(1000, 9999);
            $terminal->location = "Main Office";
            $terminal->is_active = true;
            $terminal->payment_processor = "cash";
            $terminal->device_model = "Standard POS";
            $terminal->merchant_id = "MERCHANT-" . rand(1000, 9999);
            $terminal->save();
            
            return redirect()->route('accounting.cashier.dashboard')
                ->with('success', 'Test terminal created successfully! ID: ' . $terminal->id);
        } catch (\Exception $e) {
            Log::error('Error creating test terminal: ' . $e->getMessage());
            return "Error creating terminal: " . $e->getMessage();
        }
    }
}