<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact;
use App\Models\Accounting\SmsGateway; // Ensure correct namespace
use App\Models\Accounting\SmsTemplate; // Ensure correct namespace
use App\Models\Accounting\SmsLog; // Use correct model namespace
use App\Models\Accounting\SchoolClass; // Use correct model namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Added for validation rules
use Twilio\Rest\Client as TwilioClient; // Example: If using Twilio
use Twilio\Exceptions\TwilioException; // Example: If using Twilio
use Carbon\Carbon; // For date filtering in logs

class SmsController extends Controller
{
    // Helper function for authorization (adapt if you have a different method)
    private function authorizeSchoolAccess($model = null)
    {
        if (!Auth::check()) abort(401); // Basic logged-in check
        $userSchoolId = Auth::user()?->school_id;
        // Check if model exists, has school_id, and if it matches user's school_id (if user has one)
        if ($userSchoolId && $model && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.');
        }
        // Add role/permission checks if necessary
        // e.g., if (!Auth::user()->can('manage sms gateways')) { abort(403); }
    }

    // =============================================
    // == SMS Gateway CRUD Methods ==
    // =============================================

    /**
     * Display a listing of the SMS gateways.
     * Maps to route 'accounting.sms-gateways.index'
     */
    public function gateways()
    {
        $this->authorize('viewAny', SmsGateway::class); // Example Policy check

        $school_id = Auth::user()?->school_id;
        $gateways = SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
                              ->orderBy('is_default', 'desc') // Show default first
                              ->orderBy('name')
                              ->paginate(15); // Use pagination

        // Correct view path
        return view('accounting.sms_gateways.index', compact('gateways'));
    }

    /**
     * Show the form for creating a new SMS gateway.
     * Maps to route 'accounting.sms-gateways.create'
     */
    public function createGateway()
    {
         $this->authorize('create', SmsGateway::class); // Example Policy check
        // Correct view path
        return view('accounting.sms_gateways.create');
    }

    /**
     * Store a newly created SMS gateway in storage.
     * Maps to route 'accounting.sms-gateways.store'
     */
    public function storeGateway(Request $request)
    {
         $this->authorize('create', SmsGateway::class); // Example Policy check

        $school_id = Auth::user()?->school_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sms_gateways')->where('school_id', $school_id)],
            'provider' => 'required|string|max:100', // Consider Rule::in([...]) if predefined
            'api_key' => 'nullable|string|max:255', // Made nullable to match model/form
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:100', // Made nullable to match model/form
            'api_endpoint' => 'nullable|url|max:255',
            'configuration' => 'nullable|json',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = $school_id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        DB::beginTransaction(); // Use transaction for default handling
        try {
            // Ensure only one default gateway per school
            if ($validated['is_default']) {
                SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
                          ->where('is_default', true) // Find the current default
                          ->update(['is_default' => false]); // Unset it
            }

            // Optional: Encrypt sensitive data before saving
            // if (!empty($validated['api_secret'])) {
            //     $validated['api_secret'] = encrypt($validated['api_secret']);
            // }
            // if (!empty($validated['api_key'])) {
            //     $validated['api_key'] = encrypt($validated['api_key']);
            // }

            $gateway = SmsGateway::create($validated);
            DB::commit();

            // Corrected redirect route name
            return redirect()->route('accounting.sms-gateways.index')
                       ->with('success', 'SMS Gateway created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SMS Gateway store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create SMS Gateway: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified SMS gateway (Optional - often not needed for config).
     * Maps to route 'accounting.sms-gateways.show'
     */
    // public function showGateway(SmsGateway $gateway) // Use Route Model Binding if route expects {gateway}
    // {
    //     $this->authorize('view', $gateway); // Example Policy check
    //     $this->authorizeSchoolAccess($gateway);
    //     // Be CAREFUL showing secrets/keys in views
    //     return view('accounting.sms_gateways.show', compact('gateway'));
    // }

    /**
     * Show the form for editing the specified SMS gateway.
     * Maps to route 'accounting.sms-gateways.edit'
     */
    public function editGateway(SmsGateway $gateway) // Use Route Model Binding
    {
         $this->authorize('update', $gateway); // Example Policy check
        $this->authorizeSchoolAccess($gateway);
        // Correct view path
        return view('accounting.sms_gateways.edit', compact('gateway'));
    }

    /**
     * Update the specified SMS gateway in storage.
     * Maps to route 'accounting.sms-gateways.update'
     */
    public function updateGateway(Request $request, SmsGateway $gateway) // Use Route Model Binding
    {
         $this->authorize('update', $gateway); // Example Policy check
        $this->authorizeSchoolAccess($gateway);
        $school_id = $gateway->school_id; // Use gateway's school_id

        $validated = $request->validate([
             // Update unique rule to ignore the current gateway ID
            'name' => ['required', 'string', 'max:255', Rule::unique('sms_gateways')->where('school_id', $school_id)->ignore($gateway->id)],
            'provider' => 'required|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:100',
            'api_endpoint' => 'nullable|url|max:255',
            'configuration' => 'nullable|json',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        // Handle potentially sensitive fields - don't update if left blank to avoid nulling them
        if (!$request->filled('api_key')) {
            unset($validated['api_key']);
        } else {
            // Optional: Encrypt if needed
            // $validated['api_key'] = encrypt($validated['api_key']);
        }
        if (!$request->filled('api_secret')) {
            unset($validated['api_secret']);
        } else {
             // Optional: Encrypt if needed
            // $validated['api_secret'] = encrypt($validated['api_secret']);
        }

        DB::beginTransaction(); // Use transaction for default handling
        try {
            // Ensure only one default gateway per school
            if ($validated['is_default']) {
                SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
                          ->where('id', '!=', $gateway->id) // Exclude self
                           ->where('is_default', true)
                          ->update(['is_default' => false]); // Unset current default(s)
            }
             // Note: If unsetting default, we currently allow having no default.
             // Add logic here if you need to enforce *at least one* default.

            $gateway->update($validated);
            DB::commit();

            // Corrected redirect route name
            return redirect()->route('accounting.sms-gateways.index')
                       ->with('success', 'SMS Gateway updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SMS Gateway update failed for ID {$gateway->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update SMS Gateway: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified SMS gateway from storage.
     * Maps to route 'accounting.sms-gateways.destroy'
     */
    public function destroyGateway(SmsGateway $gateway) // Use Route Model Binding
    {
        $this->authorize('delete', $gateway); // Example Policy check
        $this->authorizeSchoolAccess($gateway);

        // Optional: Add checks before deleting
        // if ($gateway->is_default) {
        //     return back()->with('error', 'Cannot delete the default gateway. Set another as default first.');
        // }
        // if ($gateway->smsLogs()->exists()) { // Check relation
        //     return back()->with('error', 'Cannot delete gateway with associated SMS logs.');
        // }

        try {
            $gateway->delete(); // Uses SoftDeletes trait from model
            // Corrected redirect route name
            return redirect()->route('accounting.sms-gateways.index')
                       ->with('success', 'SMS Gateway deleted successfully.');
        } catch (\Exception $e) {
             Log::error("SMS Gateway delete failed for ID {$gateway->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete SMS Gateway.');
        }
    }

    // =============================================
    // == SMS Template CRUD Methods ==
    // =============================================

    /**
     * Display SMS templates.
     * Maps to route 'accounting.sms.templates' (or 'accounting.sms-templates.index' if you renamed)
     */
    public function templates()
    {
        $school_id = Auth::user()?->school_id;
        $templates = SmsTemplate::when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')
            ->paginate(15); // Paginate templates

        return view('accounting.sms.templates', compact('templates')); // Ensure this view exists
    }

    /**
     * Show form to create a new SMS template.
     * Maps to route 'accounting.sms.templates.create' (or 'accounting.sms-templates.create')
     */
    public function createTemplate()
    {
        return view('accounting.sms.templates.create'); // Ensure this view exists
    }

    /**
     * Store a new SMS template.
     * Maps to route 'accounting.sms.templates.store' (or 'accounting.sms-templates.store')
     */
    public function storeTemplate(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sms_templates')->where('school_id', $school_id)],
            'type' => 'nullable|string|max:100', // Optional category/type
            'content' => 'required|string|max:1600', // Max length for multi-part SMS
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['school_id'] = $school_id;

        try {
            SmsTemplate::create($validated);
             // Adjust route name if you changed it
            return redirect()->route('accounting.sms.templates')->with('success', 'SMS Template created successfully.');
        } catch (\Exception $e) {
            Log::error("SMS Template store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create SMS Template: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit an SMS template.
     * Maps to route 'accounting.sms.templates.edit' (or 'accounting.sms-templates.edit')
     */
    public function editTemplate(SmsTemplate $template) // Route Model Binding
    {
        $this->authorizeSchoolAccess($template);
        return view('accounting.sms.templates.edit', compact('template')); // Ensure this view exists
    }

    /**
     * Update the specified SMS template.
     * Maps to route 'accounting.sms.templates.update' (or 'accounting.sms-templates.update')
     */
    public function updateTemplate(Request $request, SmsTemplate $template) // Route Model Binding
    {
        $this->authorizeSchoolAccess($template);
        $school_id = $template->school_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sms_templates')->where('school_id', $school_id)->ignore($template->id)],
            'type' => 'nullable|string|max:100',
            'content' => 'required|string|max:1600',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        try {
            $template->update($validated);
             // Adjust route name if you changed it
            return redirect()->route('accounting.sms.templates')->with('success', 'SMS Template updated successfully.');
        } catch (\Exception $e) {
             Log::error("SMS Template update failed for ID {$template->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update SMS Template: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified SMS template.
     * Maps to route 'accounting.sms.templates.destroy' (or 'accounting.sms-templates.destroy')
     */
    public function destroyTemplate(SmsTemplate $template) // Route Model Binding
    {
        $this->authorizeSchoolAccess($template);
        try {
            $template->delete(); // Assumes SoftDeletes if used on model
             // Adjust route name if you changed it
            return redirect()->route('accounting.sms.templates')->with('success', 'SMS Template deleted successfully.');
        } catch (\Exception $e) {
             Log::error("SMS Template delete failed for ID {$template->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete SMS Template.');
        }
    }

    // =============================================
    // == SMS Logs & Sending Methods ==
    // =============================================

    /**
     * Display SMS sending logs.
     * Maps to route 'accounting.sms.logs'
     */
    public function logs(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        $query = SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id));

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from)); // Use Carbon for safety
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
        if ($request->filled('status') && in_array($request->status, ['sent', 'delivered', 'failed', 'scheduled', 'pending'])) { // Validate status
            $query->where('status', $request->status);
        }

        $logs = $query->with('gateway') // Eager load gateway name maybe?
                      ->orderBy('created_at', 'desc')
                      ->paginate(25) // Increase pagination?
                      ->withQueryString(); // Keep filters on pagination links

        // Consider calculating summary in DB if performance is an issue
        $summary = [
            'today' => SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id))
                ->whereDate('created_at', Carbon::today())->count(),
            'month' => SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id))
                ->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'total' => SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id))->count(),
        ];

        return view('accounting.sms.logs', compact('logs', 'summary')); // Ensure this view exists
    }

    /**
     * Show the form for sending a new SMS message.
     * Maps to route 'accounting.sms.send'
     */
    public function showSendForm(Request $request)
    {
        $school_id = Auth::user()?->school_id;

        // Get contacts with valid phone numbers
        $contacts = Contact::where('is_active', true)
            ->when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->whereNotNull('phone')->where('phone', '!=', '')
            ->orderBy('name')->get(['id', 'name', 'phone']);

        // Get ACTIVE gateways
        $gateways = SmsGateway::where('is_active', true)
            ->when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('is_default', 'desc')->orderBy('name') // Default first
            ->get();

        // Get ACTIVE templates
        $templates = SmsTemplate::where('is_active', true)
            ->when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')->get();

        // Get school classes for class-based messaging
        $classes = SchoolClass::when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')->get(['id', 'name']); // Ensure this model & query works

        // Pre-select template if provided
        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            $selectedTemplate = SmsTemplate::when($school_id, fn($q) => $q->where('school_id', $school_id))
                                          ->find($request->template_id);
            // No need to authorize here if it's just pre-filling, authorize on send
        }

        return view('accounting.sms.send', compact('contacts', 'gateways', 'templates', 'classes', 'selectedTemplate')); // Ensure this view exists
    }

    /**
     * Process the sending of the SMS message.
     * Maps to route 'accounting.sms.process-send'
     */
    public function processSend(Request $request)
    {
         $validated = $request->validate([
            'recipient_type' => 'required|string|in:individual,parents,class,staff,debtors', // Add more types if needed
            'recipients' => 'required_if:recipient_type,individual|nullable|string', // Can be comma-separated numbers/contact IDs
            'class_id' => 'required_if:recipient_type,class|nullable|exists:school_classes,id',
            'gateway_id' => 'required|exists:sms_gateways,id',
            'template_id' => 'nullable|exists:sms_templates,id',
            'message' => 'required_without:template_id|nullable|string|max:1600', // Required if no template selected
            'scheduled_at' => 'nullable|date|after:now',
        ],[
             'message.required_without' => 'A message is required if no template is selected.'
         ]);

        $school_id = Auth::user()?->school_id;
        $messageBody = $validated['message'];
        $sentCount = 0;
        $failedRecipients = [];
        $phoneNumbers = []; // Final list of E.164 numbers

        // Get the selected gateway and authorize access
        $gateway = SmsGateway::where('is_active', true)->findOrFail($validated['gateway_id']);
        $this->authorizeSchoolAccess($gateway);

        // Get message content from template if selected
        if (!empty($validated['template_id'])) {
            $template = SmsTemplate::where('is_active', true)->find($validated['template_id']);
            if ($template) {
                $this->authorizeSchoolAccess($template);
                $messageBody = $template->content; // Overwrite message with template content
                $template->touch('last_used_at'); // Update timestamp
            } else {
                 return back()->withInput()->with('error', 'Selected SMS template is not active or not found.');
            }
        }

        // Make sure we have a message body
        if (empty($messageBody)) {
             return back()->withInput()->with('error', 'Message content cannot be empty.');
        }

        // Resolve recipients to phone numbers based on type
        // Wrap this in try-catch if queries might fail
        try {
            switch ($validated['recipient_type']) {
                case 'individual':
                    $rawNumbers = explode(',', $validated['recipients']);
                    foreach ($rawNumbers as $rawNumber) {
                        $formattedNumber = $this->formatPhoneNumber(trim($rawNumber));
                        if ($formattedNumber) {
                            $phoneNumbers[] = $formattedNumber;
                        } else {
                            $failedRecipients[] = $rawNumber . ' (Invalid Number)';
                        }
                    }
                    break;
                case 'parents':
                    // This assumes a contact_type 'parent' exists or similar logic
                    $contacts = Contact::where('is_active', true)
                        ->where('contact_type', 'parent') // Adjust type if needed
                        ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                        ->whereNotNull('phone')->where('phone', '!=', '')
                        ->pluck('phone', 'name'); // Pluck phone number, keyed by name for error reporting
                    foreach ($contacts as $name => $phone) {
                         $formattedNumber = $this->formatPhoneNumber($phone);
                         if ($formattedNumber) $phoneNumbers[] = $formattedNumber;
                         else $failedRecipients[] = $name . ' (Invalid Number)';
                    }
                    break;
                case 'class':
                     // This assumes Student model has 'contacts' relationship or similar
                     // And Contact model has 'student' relationship
                    $students = \App\Models\Student::where('school_class_id', $validated['class_id'])
                                ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                                ->with(['contacts' => function($q) { // Eager load active contacts with phones
                                    $q->where('is_active', true)->whereNotNull('phone')->where('phone', '!=', '');
                                }])->get();
                    foreach ($students as $student) {
                        foreach($student->contacts as $contact) {
                            $formattedNumber = $this->formatPhoneNumber($contact->phone);
                            if ($formattedNumber) $phoneNumbers[] = $formattedNumber;
                            else $failedRecipients[] = $contact->name . ' (Invalid Number)';
                        }
                    }
                    break;
                case 'staff':
                    $contacts = Contact::where('is_active', true)
                        ->where('contact_type', 'staff') // Adjust type if needed
                        ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                        ->whereNotNull('phone')->where('phone', '!=', '')
                        ->pluck('phone', 'name');
                    foreach ($contacts as $name => $phone) {
                         $formattedNumber = $this->formatPhoneNumber($phone);
                         if ($formattedNumber) $phoneNumbers[] = $formattedNumber;
                         else $failedRecipients[] = $name . ' (Invalid Number)';
                    }
                    break;
                case 'debtors':
                    // This requires robust balance calculation. Example using a scope:
                    // $contacts = Contact::where('is_active', true)
                    //     ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                    //     ->whereNotNull('phone')->where('phone', '!=', '')
                    //     ->havingBalance() // Assuming a scopeHavingBalance() exists on Contact model
                    //     ->pluck('phone', 'name');
                     // Placeholder: Get all customers for now
                      $contacts = Contact::where('is_active', true)
                        ->where('contact_type', 'customer')
                        ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                        ->whereNotNull('phone')->where('phone', '!=', '')
                        ->pluck('phone', 'name');
                    foreach ($contacts as $name => $phone) {
                         $formattedNumber = $this->formatPhoneNumber($phone);
                         if ($formattedNumber) $phoneNumbers[] = $formattedNumber;
                         else $failedRecipients[] = $name . ' (Invalid Number)';
                    }
                    break;
            }
        } catch (\Exception $e) {
             Log::error("Error resolving recipients for SMS: " . $e->getMessage());
             return back()->withInput()->with('error', 'Could not resolve recipients: ' . $e->getMessage());
        }

        // Remove duplicate numbers
        $phoneNumbers = array_unique($phoneNumbers);

        if (empty($phoneNumbers)) {
            $errorMsg = 'No valid recipient phone numbers found or resolved.';
            if (!empty($failedRecipients)) {
                 $errorMsg .= ' Failed numbers: ' . implode(', ', array_slice($failedRecipients, 0, 5)) . (count($failedRecipients) > 5 ? '...' : '');
            }
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        // If scheduled, save and redirect
        if ($request->filled('scheduled_at')) {
            try {
                $this->scheduleMessages($phoneNumbers, $messageBody, $gateway, $validated['scheduled_at']);
                return redirect()->route('accounting.sms.logs')->with('success', count($phoneNumbers) . ' SMS messages scheduled for ' . Carbon::parse($validated['scheduled_at'])->format('M d, Y H:i'));
            } catch (\Exception $e) {
                 Log::error("Error scheduling SMS: " . $e->getMessage());
                 return back()->withInput()->with('error', 'Failed to schedule messages: ' . $e->getMessage());
            }
        }

        // Handle immediate sending
        $result = ['sent' => 0, 'failed' => $failedRecipients]; // Initialize result
        try {
            switch (strtolower($gateway->provider)) { // Use strtolower for case-insensitivity
                case 'twilio':
                    $result = $this->sendViaTwilio($phoneNumbers, $messageBody, $gateway);
                    break;
                case 'africastalking': // Example name
                case 'africas_talking':
                    $result = $this->sendViaAfricasTalking($phoneNumbers, $messageBody, $gateway);
                    break;
                // Add case 'some_other_provider':
                //    $result = $this->sendViaSomeOtherProvider($phoneNumbers, $messageBody, $gateway);
                //    break;
                default:
                    Log::error("Unsupported SMS provider: {$gateway->provider} for Gateway ID: {$gateway->id}");
                    return redirect()->back()->withInput()->with('error', 'Configured SMS gateway provider (' . $gateway->provider . ') is not supported.');
            }
             // Merge initial failures (invalid numbers) with API failures
             $result['failed'] = array_unique(array_merge($failedRecipients, $result['failed']));
             $sentCount = $result['sent'];

        } catch (\Exception $e) {
             Log::error("SMS Sending failed completely via provider {$gateway->provider}: " . $e->getMessage());
             return back()->withInput()->with('error', 'Failed to send messages due to a gateway error: ' . $e->getMessage());
        }

        // Update gateway last used timestamp
        if ($sentCount > 0) {
            $gateway->touch('last_used_at');
        }

        // Prepare redirect message
        $finalMessage = '';
        $statusLevel = 'error';
        if ($sentCount > 0) {
            $finalMessage .= "{$sentCount} SMS message(s) dispatched successfully.";
            $statusLevel = 'success';
        }
        if (!empty($result['failed'])) {
            $finalMessage .= ($sentCount > 0 ? " " : "") . "Failed to send to " . count($result['failed']) . " recipient(s).";
            // Optionally list first few failed numbers if helpful and not too long
             $finalMessage .= " (Examples: " . implode(', ', array_slice($result['failed'], 0, 3)) . (count($result['failed']) > 3 ? '...' : '') . ")";
            // Adjust status level if there were failures
            $statusLevel = ($sentCount > 0) ? 'warning' : 'error';
        }
        if (empty($finalMessage)) {
             $finalMessage = 'No messages were sent.'; // Should ideally not happen if initial checks pass
        }

        // Redirect with appropriate status
        if ($statusLevel === 'error') {
             return redirect()->back()->withInput()->with('error', $finalMessage);
        } else {
            return redirect()->route('accounting.sms.logs')->with($statusLevel, $finalMessage);
        }
    }

    // =============================================
    // == Private Helper Methods ==
    // =============================================

    /**
     * Send messages via Twilio gateway (Example Implementation)
     */
    private function sendViaTwilio(array $phoneNumbers, string $messageBody, SmsGateway $gateway): array
    {
        $sentCount = 0;
        $failedRecipients = [];

        // Use config as fallback, but prioritize gateway settings
        $twilioSid = $gateway->api_key ?? config('services.twilio.sid');
        // Decrypt if stored encrypted: $twilioToken = decrypt($gateway->api_secret) ?? config('services.twilio.token');
        $twilioToken = $gateway->api_secret ?? config('services.twilio.token');
        $twilioFrom = $gateway->sender_id ?? config('services.twilio.from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            Log::error('Twilio credentials not configured for Gateway ID: ' . $gateway->id);
            return ['sent' => 0, 'failed' => ['All recipients (Twilio configuration error)']];
        }

        try {
            $twilio = new TwilioClient($twilioSid, $twilioToken);
        } catch (\Exception $e) {
             Log::error("Failed to initialize Twilio Client for Gateway ID {$gateway->id}: " . $e->getMessage());
             return ['sent' => 0, 'failed' => ['All recipients (Twilio client initialization error)']];
        }


        foreach ($phoneNumbers as $number) {
             $logData = [
                'recipient' => $number,
                'message' => $messageBody, // Log actual message sent
                'gateway_name' => $gateway->name,
                'gateway_id' => $gateway->id,
                'school_id' => Auth::user()?->school_id,
                'sent_by' => Auth::id(),
                'status' => 'failed', // Default status
                'reference' => null,
                'error_message' => null,
            ];

            try {
                $sms = $twilio->messages->create(
                    $number, // To
                    [
                        'from' => $twilioFrom, // From
                        'body' => $messageBody // Body
                        // Add other options like 'statusCallback' if needed
                    ]
                );

                // Log successful attempt (status might still change later e.g., delivered/undelivered)
                Log::info("SMS submitted via Twilio to {$number}. SID: {$sms->sid}, Status: {$sms->status}");
                $logData['status'] = $sms->status ?? 'sent'; // Use Twilio status
                $logData['reference'] = $sms->sid;
                SmsLog::create($logData);
                $sentCount++;

            } catch (TwilioException $e) {
                Log::error("Twilio SMS error sending to {$number}: [{$e->getCode()}] {$e->getMessage()}");
                $failedRecipients[] = $number . ' (API Error)';
                $logData['error_message'] = "[{$e->getCode()}] {$e->getMessage()}";
                SmsLog::create($logData); // Log failure

            } catch (\Exception $e) { // Catch other potential errors
                Log::error("General error sending SMS via Twilio to {$number}: {$e->getMessage()}");
                $failedRecipients[] = $number . ' (General Error)';
                $logData['error_message'] = $e->getMessage();
                SmsLog::create($logData); // Log failure
            }
        }

        return ['sent' => $sentCount, 'failed' => $failedRecipients];
    }

    /**
     * Send messages via Africa's Talking gateway (Placeholder)
     */
    private function sendViaAfricasTalking(array $phoneNumbers, string $messageBody, SmsGateway $gateway): array
    {
        // Requires installing the Africa's Talking SDK: composer require africastalking/africastalking
        // See: https://github.com/AfricasTalkingLtd/africastalking-php

        $sentCount = 0;
        $failedRecipients = [];

        $username = $gateway->api_key ?? config('services.africastalking.username');
        // Decrypt if needed: $apiKey = decrypt($gateway->api_secret) ?? config('services.africastalking.key');
        $apiKey = $gateway->api_secret ?? config('services.africastalking.key');
        $from = $gateway->sender_id ?? config('services.africastalking.from'); // Optional 'from'

        if (!$username || !$apiKey) {
            Log::error("Africa's Talking credentials not configured for Gateway ID: {$gateway->id}");
            return ['sent' => 0, 'failed' => ['All recipients (Africa\'s Talking configuration error)']];
        }

        try {
            // Initialize the SDK
            $AT = new \AfricasTalking\SDK\AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            // Send to possibly multiple recipients, API handles batching better
            $result = $sms->send([
                'to'      => implode(',', $phoneNumbers), // Comma-separated list
                'message' => $messageBody,
                'from'    => $from // Optional: Sender ID
            ]);

            // Process the result - Structure depends on SDK version
            // Log success/failure based on $result structure
            // Example (check SDK docs for exact structure):
             if (isset($result['status']) && $result['status'] === 'success' && isset($result['data']['SMSMessageData']['Recipients'])) {
                 $recipientsData = $result['data']['SMSMessageData']['Recipients'];
                 foreach($recipientsData as $recipient) {
                      $logData = [
                         'recipient' => $recipient['number'],
                         'message' => $messageBody,
                         'gateway_name' => $gateway->name,
                         'gateway_id' => $gateway->id,
                         'school_id' => Auth::user()?->school_id,
                         'sent_by' => Auth::id(),
                         'status' => strtolower($recipient['status']) ?? 'failed',
                         'reference' => $recipient['messageId'] ?? null,
                         'error_message' => ($recipient['status'] !== 'Success') ? $recipient['status'] : null, // Or costString if needed
                     ];
                     SmsLog::create($logData);
                     if ($recipient['status'] === 'Success') {
                         $sentCount++;
                     } else {
                          $failedRecipients[] = $recipient['number'] . ' (' . $recipient['status'] . ')';
                     }
                 }
                 Log::info("Africa's Talking Response: ", $result);

             } else {
                 Log::error("Africa's Talking SMS failed. Response: ", $result);
                 foreach($phoneNumbers as $num) { $failedRecipients[] = $num . ' (API Error)'; } // Mark all as failed if main call fails
                 // Log a general failure?
             }

        } catch (\Exception $e) {
            Log::error("Africa's Talking SMS error: {$e->getMessage()}");
             foreach($phoneNumbers as $num) { $failedRecipients[] = $num . ' (Gateway Error)'; }
             // Log general failure for all?
        }

        return ['sent' => $sentCount, 'failed' => $failedRecipients];
    }

    /**
     * Schedule messages for later sending by creating log entries.
     */
    private function scheduleMessages(array $phoneNumbers, string $messageBody, SmsGateway $gateway, string $scheduledTime): void
    {
        $school_id = Auth::user()?->school_id;
        $scheduledAtCarbon = Carbon::parse($scheduledTime); // Ensure it's a Carbon instance

        $logsToInsert = [];
        foreach ($phoneNumbers as $number) {
            $logsToInsert[] = [
                'recipient' => $number,
                'message' => $messageBody,
                'status' => 'scheduled',
                'gateway_name' => $gateway->name,
                'gateway_id' => $gateway->id,
                'scheduled_at' => $scheduledAtCarbon,
                'school_id' => $school_id,
                'sent_by' => Auth::id(),
                'created_at' => now(), // Add timestamps for bulk insert
                'updated_at' => now(),
            ];
        }

        // Bulk insert for efficiency
        if (!empty($logsToInsert)) {
             SmsLog::insert($logsToInsert);
             Log::info("Scheduled " . count($phoneNumbers) . " SMS messages for {$scheduledAtCarbon->toDateTimeString()} via Gateway ID {$gateway->id}");
        }
    }

    /**
     * Format a phone number to E.164 standard (e.g., +1234567890).
     * Adjust the default country code as needed.
     */
    private function formatPhoneNumber(?string $rawNumber): ?string
    {
        if (empty($rawNumber)) {
            return null;
        }

        // Remove all non-numeric characters except '+' at the beginning
        $number = preg_replace('/[^\d+]/', '', $rawNumber);
         // If '+' exists but not at the start, remove it
         if (strpos($number, '+') > 0) {
             $number = preg_replace('/\+/', '', $number);
         }
         // Trim again after potential internal '+' removal
         $number = trim($number);

        // If it already starts with '+', assume it's E.164 (basic check)
        if (str_starts_with($number, '+')) {
            // Basic length check for E.164 (adjust min/max if needed)
            if (strlen($number) >= 11 && strlen($number) <= 15) {
                return $number;
            } else {
                Log::warning("Potential E.164 number has invalid length: {$rawNumber} -> {$number}");
                return null; // Invalid length
            }
        }

        // If no '+', assume local format. Prepend default country code.
        // Remove leading '0' if present after stripping non-digits
        $number = preg_replace('/^0+/', '', $number);

        if (empty($number)) return null; // Was just '0' or empty after stripping

        // Replace with YOUR default country code
        $defaultCountryCode = '+254'; // Example: Kenya
        $formattedNumber = $defaultCountryCode . $number;

        // Final length check
        if (strlen($formattedNumber) >= 11 && strlen($formattedNumber) <= 15) {
            return $formattedNumber;
        } else {
            Log::warning("Formatted number has invalid length: {$rawNumber} -> {$formattedNumber}");
            return null;
        }
    }

} // End Class