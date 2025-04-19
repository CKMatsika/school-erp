<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\SmsGateway; // Use the correct model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // For unique rules

class SmsGatewayController extends Controller
{
    // Helper function for authorization (adapt if you have a different method)
    private function authorizeSchoolAccess($model = null)
    {
        if (!Auth::check()) abort(401);
        $userSchoolId = Auth::user()?->school_id;
        if ($userSchoolId && $model && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.');
        }
        // Add role/permission checks if necessary
    }

    /**
     * Display a listing of the SMS gateways.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $gateways = SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
                              ->orderBy('name')
                              ->paginate(15); // Paginate for potentially many gateways

        return view('accounting.sms_gateways.index', compact('gateways'));
    }

    /**
     * Show the form for creating a new SMS gateway.
     */
    public function create()
    {
        // Optionally pass lists of supported providers if needed
        // $providers = ['provider1', 'provider2'];
        return view('accounting.sms_gateways.create'); // Pass $providers if needed
    }

    /**
     * Store a newly created SMS gateway in storage.
     */
    public function store(Request $request)
    {
        $school_id = Auth::user()?->school_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sms_gateways')->where('school_id', $school_id)],
            'provider' => 'required|string|max:100', // Maybe Rule::in(['provider1', 'provider2'])
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255', // Often nullable or stored differently
            'sender_id' => 'required|string|max:100', // Validation rules depend on provider
            'api_endpoint' => 'nullable|url|max:255',
            'configuration' => 'nullable|json', // Validate specific JSON keys if needed
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = $school_id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        // Ensure only one default gateway per school
        if ($validated['is_default']) {
            SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
                      ->where('id', '!=', null) // Exclude self during potential update scenario
                      ->update(['is_default' => false]);
        }

        try {
            $gateway = SmsGateway::create($validated);
            return redirect()->route('accounting.sms-gateways.index')
                       ->with('success', 'SMS Gateway created successfully.');
        } catch (\Exception $e) {
            Log::error("SMS Gateway store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create SMS Gateway: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified SMS gateway. (Optional)
     */
    public function show(SmsGateway $smsGateway)
    {
        $this->authorizeSchoolAccess($smsGateway);
        // Usually you don't show API keys/secrets here for security
        return view('accounting.sms_gateways.show', compact('smsGateway'));
    }

    /**
     * Show the form for editing the specified SMS gateway.
     */
    public function edit(SmsGateway $smsGateway)
    {
        $this->authorizeSchoolAccess($smsGateway);
        // Optionally pass providers list again if needed
        return view('accounting.sms_gateways.edit', compact('smsGateway'));
    }

    /**
     * Update the specified SMS gateway in storage.
     */
    public function update(Request $request, SmsGateway $smsGateway)
    {
        $this->authorizeSchoolAccess($smsGateway);
        $school_id = Auth::user()?->school_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sms_gateways')->where('school_id', $school_id)->ignore($smsGateway->id)],
            'provider' => 'required|string|max:100',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'required|string|max:100',
            'api_endpoint' => 'nullable|url|max:255',
            'configuration' => 'nullable|json',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        // Ensure only one default gateway per school
        if ($validated['is_default']) {
            SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
                      ->where('id', '!=', $smsGateway->id) // Exclude self
                      ->update(['is_default' => false]);
        } elseif (!$validated['is_default'] && $smsGateway->is_default) {
             // If unsetting default, ensure *another* active one exists and make it default?
             // Or just allow no default? Simpler to allow no default for now.
             // Consider adding logic here if a default is strictly required.
             Log::info("Gateway ID {$smsGateway->id} is no longer the default.");
        }

        try {
            // Handle potentially sensitive fields like API secret - don't update if left blank?
            // Example: Only update secret if a new value is provided
            // if (!$request->filled('api_secret')) {
            //     unset($validated['api_secret']);
            // }

            $smsGateway->update($validated);
            return redirect()->route('accounting.sms-gateways.index')
                       ->with('success', 'SMS Gateway updated successfully.');
        } catch (\Exception $e) {
            Log::error("SMS Gateway update failed for ID {$smsGateway->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update SMS Gateway: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified SMS gateway from storage.
     */
    public function destroy(SmsGateway $smsGateway)
    {
        $this->authorizeSchoolAccess($smsGateway);

        // Add checks: Prevent deleting if it's the only active one? Or if used in logs?
        // if ($smsGateway->is_default && SmsGateway::where('school_id', $smsGateway->school_id)->where('is_active', true)->count() <= 1) {
        //      return back()->with('error', 'Cannot delete the only active default gateway.');
        // }

        try {
            // If it was default, maybe set another one as default? (complex logic)
            $smsGateway->delete(); // Soft delete
            return redirect()->route('accounting.sms-gateways.index')
                       ->with('success', 'SMS Gateway deleted successfully.');
        } catch (\Exception $e) {
             Log::error("SMS Gateway delete failed for ID {$smsGateway->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete SMS Gateway.');
        }
    }
}