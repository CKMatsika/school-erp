<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact; // Assuming contacts are recipients
use App\Models\Accounting\SmsGateway; // Updated to match your actual model namespace
use App\Models\Accounting\SmsTemplate; // Updated to match your actual model namespace
use App\Models\SmsLog; // Updated to match your actual model namespace
use App\Models\School\SchoolClass; // For class selection in send form
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient; // Import the Twilio SDK Client class
use Twilio\Exceptions\TwilioException; // Import Twilio Exception class
use Carbon\Carbon;

class SmsController extends Controller
{
    /**
     * Display SMS gateway information or configuration page.
     */
    public function gateways()
    {
        $school_id = Auth::user()?->school_id;
        
        // Fetch SMS gateways from the database
        $gateways = SmsGateway::when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')
            ->get();
            
        return view('accounting.sms.gateways', compact('gateways'));
    }

    /**
     * Show form to create a new SMS gateway.
     */
    public function createGateway()
    {
        return view('accounting.sms.gateways.create');
    }

    /**
     * Store a new SMS gateway.
     */
    public function storeGateway(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:100',
            'api_endpoint' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');
        $validated['school_id'] = Auth::user()?->school_id;
        
        // If this is set as default, unset others
        if ($validated['is_default']) {
            SmsGateway::when($validated['school_id'], fn($q) => $q->where('school_id', $validated['school_id']))
                ->update(['is_default' => false]);
        }
        
        // Create the gateway
        SmsGateway::create($validated);
        
        return redirect()->route('accounting.sms.gateways')->with('success', 'SMS Gateway added successfully.');
    }

    /**
     * Show form to edit an SMS gateway.
     */
    public function editGateway(SmsGateway $gateway)
    {
        $this->authorizeSchoolAccess($gateway);
        return view('accounting.sms.gateways.edit', compact('gateway'));
    }

    /**
     * Update the specified SMS gateway.
     */
    public function updateGateway(Request $request, SmsGateway $gateway)
    {
        $this->authorizeSchoolAccess($gateway);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:100',
            'api_endpoint' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');
        
        // Only update API secret if provided
        if (empty($validated['api_secret'])) {
            unset($validated['api_secret']);
        }
        
        // If this is set as default, unset others
        if ($validated['is_default']) {
            SmsGateway::where('id', '!=', $gateway->id)
                ->when($gateway->school_id, fn($q) => $q->where('school_id', $gateway->school_id))
                ->update(['is_default' => false]);
        }
        
        // Update the gateway
        $gateway->update($validated);
        
        return redirect()->route('accounting.sms.gateways')->with('success', 'SMS Gateway updated successfully.');
    }

    /**
     * Delete the specified SMS gateway.
     */
    public function destroyGateway(SmsGateway $gateway)
    {
        $this->authorizeSchoolAccess($gateway);
        
        // Check if it's in use
        $hasLogs = $gateway->smsLogs()->exists();
        
        if ($hasLogs) {
            return redirect()->route('accounting.sms.gateways')->with('error', 'Cannot delete this gateway as it has associated logs.');
        }
        
        $gateway->delete();
        
        return redirect()->route('accounting.sms.gateways')->with('success', 'SMS Gateway deleted successfully.');
    }

    /**
     * Display SMS templates.
     */
    public function templates()
    {
        $school_id = Auth::user()?->school_id;
        
        // Fetch SMS templates from the database
        $templates = SmsTemplate::when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')
            ->get();
            
        return view('accounting.sms.templates', compact('templates'));
    }

    /**
     * Show form to create a new SMS template.
     */
    public function createTemplate()
    {
        return view('accounting.sms.templates.create');
    }

    /**
     * Store a new SMS template.
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['school_id'] = Auth::user()?->school_id;
        
        // Create the template
        SmsTemplate::create($validated);
        
        return redirect()->route('accounting.sms.templates')->with('success', 'SMS Template created successfully.');
    }

    /**
     * Show form to edit an SMS template.
     */
    public function editTemplate(SmsTemplate $template)
    {
        $this->authorizeSchoolAccess($template);
        return view('accounting.sms.templates.edit', compact('template'));
    }

    /**
     * Update the specified SMS template.
     */
    public function updateTemplate(Request $request, SmsTemplate $template)
    {
        $this->authorizeSchoolAccess($template);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        
        // Update the template
        $template->update($validated);
        
        return redirect()->route('accounting.sms.templates')->with('success', 'SMS Template updated successfully.');
    }

    /**
     * Delete the specified SMS template.
     */
    public function destroyTemplate(SmsTemplate $template)
    {
        $this->authorizeSchoolAccess($template);
        
        $template->delete();
        
        return redirect()->route('accounting.sms.templates')->with('success', 'SMS Template deleted successfully.');
    }

    /**
     * Display SMS sending logs.
     */
    public function logs(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        
        // Apply filters from request
        $query = SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id));
        
        // Apply date range filter if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Get paginated results
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
            
        // Generate summary statistics
        $summary = [
            'today' => SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id))
                ->whereDate('created_at', Carbon::today())
                ->count(),
                
            'month' => SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id))
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
                
            'total' => SmsLog::when($school_id, fn($q) => $q->where('school_id', $school_id))
                ->count(),
        ];
        
        return view('accounting.sms.logs', compact('logs', 'summary'));
    }

    /**
     * Show the form for sending a new SMS message.
     */
    public function showSendForm(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        
        // Get contacts with valid phone numbers
        $contacts = Contact::where('is_active', true)
            ->when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
            
        // Get available gateways
        $gateways = SmsGateway::where('is_active', true)
            ->when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')
            ->get();
            
        // Get SMS templates
        $templates = SmsTemplate::when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')
            ->get();
            
        // Get school classes for class-based messaging
        $classes = SchoolClass::when($school_id, fn($q) => $q->where('school_id', $school_id))
            ->orderBy('name')
            ->get(['id', 'name']);
            
        // Pre-select template if provided in query string
        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            $selectedTemplate = SmsTemplate::find($request->template_id);
            $this->authorizeSchoolAccess($selectedTemplate);
        }
            
        return view('accounting.sms.send', compact('contacts', 'gateways', 'templates', 'classes', 'selectedTemplate'));
    }

    /**
     * Process the sending of the SMS message using Twilio.
     */
    public function processSend(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|string|in:individual,parents,class,staff,debtors',
            'recipients' => 'required_if:recipient_type,individual|nullable|string',
            'class_id' => 'required_if:recipient_type,class|nullable|exists:school_classes,id',
            'gateway_id' => 'required|exists:sms_gateways,id',
            'template_id' => 'nullable|exists:sms_templates,id',
            'message' => 'required|string|max:1600',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $school_id = Auth::user()?->school_id;
        $messageBody = $validated['message'];
        $sentCount = 0;
        $failedRecipients = [];
        $phoneNumbers = []; // Will store formatted phone numbers
        
        // Get the selected gateway
        $gateway = SmsGateway::findOrFail($validated['gateway_id']);
        $this->authorizeSchoolAccess($gateway);
        
        // Update template last_used_at if a template was used
        if (!empty($validated['template_id'])) {
            $template = SmsTemplate::find($validated['template_id']);
            if ($template) {
                $template->last_used_at = now();
                $template->save();
            }
        }
        
        // Process different recipient types
        switch ($validated['recipient_type']) {
            case 'individual':
                // Parse comma-separated phone numbers
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
                // Get all active parent contacts with phone numbers
                $contacts = Contact::where('is_active', true)
                    ->where('type', 'parent')
                    ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get(['id', 'name', 'phone']);
                    
                foreach ($contacts as $contact) {
                    $formattedNumber = $this->formatPhoneNumber($contact->phone);
                    if ($formattedNumber) {
                        $phoneNumbers[] = $formattedNumber;
                    } else {
                        $failedRecipients[] = $contact->name . ' (Invalid Number)';
                    }
                }
                break;
                
            case 'class':
                // Get all parents of students in the selected class
                $classId = $validated['class_id'];
                $contacts = Contact::where('is_active', true)
                    ->whereHas('students', function($q) use ($classId) {
                        $q->where('class_id', $classId);
                    })
                    ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get(['id', 'name', 'phone']);
                    
                foreach ($contacts as $contact) {
                    $formattedNumber = $this->formatPhoneNumber($contact->phone);
                    if ($formattedNumber) {
                        $phoneNumbers[] = $formattedNumber;
                    } else {
                        $failedRecipients[] = $contact->name . ' (Invalid Number)';
                    }
                }
                break;
                
            case 'staff':
                // Get all active staff with phone numbers
                $contacts = Contact::where('is_active', true)
                    ->where('type', 'staff')
                    ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get(['id', 'name', 'phone']);
                    
                foreach ($contacts as $contact) {
                    $formattedNumber = $this->formatPhoneNumber($contact->phone);
                    if ($formattedNumber) {
                        $phoneNumbers[] = $formattedNumber;
                    } else {
                        $failedRecipients[] = $contact->name . ' (Invalid Number)';
                    }
                }
                break;
                
            case 'debtors':
                // Get contacts with outstanding balances
                $contacts = Contact::where('is_active', true)
                    ->whereHas('invoices', function($q) {
                        $q->where('status', 'issued')
                            ->whereRaw('total_amount > paid_amount');
                    })
                    ->when($school_id, fn($q) => $q->where('school_id', $school_id))
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get(['id', 'name', 'phone']);
                    
                foreach ($contacts as $contact) {
                    $formattedNumber = $this->formatPhoneNumber($contact->phone);
                    if ($formattedNumber) {
                        $phoneNumbers[] = $formattedNumber;
                    } else {
                        $failedRecipients[] = $contact->name . ' (Invalid Number)';
                    }
                }
                break;
        }

        if (empty($phoneNumbers)) {
            return redirect()->back()->withInput()->with('error', 'No valid recipient phone numbers found or resolved.');
        }

        // If this is a scheduled message, save it for later processing
        if ($request->filled('scheduled_at')) {
            $this->scheduleMessages($phoneNumbers, $messageBody, $gateway, $validated['scheduled_at']);
            return redirect()->route('accounting.sms.logs')->with('success', count($phoneNumbers) . ' SMS messages scheduled for ' . $validated['scheduled_at']);
        }

        // Handle immediate sending based on gateway type
        switch ($gateway->provider) {
            case 'twilio':
                $result = $this->sendViaTwilio($phoneNumbers, $messageBody, $gateway);
                $sentCount = $result['sent'];
                $failedRecipients = array_merge($failedRecipients, $result['failed']);
                break;
                
            case 'africas_talking':
                $result = $this->sendViaAfricasTalking($phoneNumbers, $messageBody, $gateway);
                $sentCount = $result['sent'];
                $failedRecipients = array_merge($failedRecipients, $result['failed']);
                break;
                
            // Add other providers as needed
            default:
                return redirect()->back()->withInput()->with('error', 'Unsupported SMS gateway provider: ' . $gateway->provider);
        }

        // Update gateway last_used_at if any messages were sent
        if ($sentCount > 0) {
            $gateway->last_used_at = now();
            $gateway->save();
        }

        // Determine redirect message
        $messageLog = [];
        if ($sentCount > 0) { $messageLog[] = "{$sentCount} SMS message(s) successfully sent."; }
        if (!empty($failedRecipients)) { $messageLog[] = "Failed to send to: " . implode(', ', $failedRecipients); }

        if ($sentCount > 0 && empty($failedRecipients)) {
            return redirect()->route('accounting.sms.logs')->with('success', $messageLog[0]);
        } elseif ($sentCount > 0 && !empty($failedRecipients)) {
            return redirect()->route('accounting.sms.logs')->with('warning', implode(' ', $messageLog));
        } else {
            return redirect()->back()->withInput()->with('error', implode(' ', $messageLog) ?: 'Failed to send any messages.');
        }
    }
    
    /**
     * Send messages via Twilio gateway
     */
    private function sendViaTwilio($phoneNumbers, $messageBody, $gateway)
    {
        $sentCount = 0;
        $failedRecipients = [];
        
        // Get Twilio credentials from gateway or config
        $twilioSid = $gateway->api_key ?? config('services.twilio.sid');
        $twilioToken = $gateway->api_secret ?? config('services.twilio.token');
        $twilioFrom = $gateway->sender_id ?? config('services.twilio.from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            Log::error('Twilio credentials not configured properly');
            return ['sent' => 0, 'failed' => ['All recipients (Twilio configuration error)']];
        }

        $twilio = new TwilioClient($twilioSid, $twilioToken);

        foreach ($phoneNumbers as $number) {
            try {
                $sms = $twilio->messages->create(
                    $number,
                    [
                        'from' => $twilioFrom,
                        'body' => $messageBody,
                    ]
                );

                Log::info("SMS sent via Twilio to {$number}. SID: {$sms->sid}, Status: {$sms->status}");
                
                // Log successful send
                SmsLog::create([
                    'recipient' => $number,
                    'message' => $messageBody,
                    'status' => $sms->status,
                    'gateway_name' => $gateway->name,
                    'gateway_id' => $gateway->id,
                    'reference' => $sms->sid,
                    'school_id' => Auth::user()?->school_id,
                    'sent_by' => Auth::id(),
                ]);
                
                $sentCount++;

            } catch (TwilioException $e) {
                Log::error("Twilio SMS error sending to {$number}: [{$e->getCode()}] {$e->getMessage()}");
                $failedRecipients[] = $number . ' (API Error)';
                
                // Log failed send
                SmsLog::create([
                    'recipient' => $number,
                    'message' => $messageBody,
                    'status' => 'failed',
                    'gateway_name' => $gateway->name,
                    'gateway_id' => $gateway->id,
                    'error_message' => $e->getMessage(),
                    'school_id' => Auth::user()?->school_id,
                    'sent_by' => Auth::id(),
                ]);
            } catch (\Exception $e) {
                Log::error("General error sending SMS to {$number}: {$e->getMessage()}");
                $failedRecipients[] = $number . ' (Failed)';
                
                // Log failed send
                SmsLog::create([
                    'recipient' => $number,
                    'message' => $messageBody,
                    'status' => 'failed',
                    'gateway_name' => $gateway->name,
                    'gateway_id' => $gateway->id,
                    'error_message' => $e->getMessage(),
                    'school_id' => Auth::user()?->school_id,
                    'sent_by' => Auth::id(),
                ]);
            }
        }

        return [
            'sent' => $sentCount,
            'failed' => $failedRecipients
        ];
    }
    
    /**
     * Send messages via Africa's Talking gateway
     */
    private function sendViaAfricasTalking($phoneNumbers, $messageBody, $gateway)
    {
        // Implement Africa's Talking API integration
        // This is a placeholder - you would need to implement the actual integration
        
        Log::info("Africa's Talking integration not yet implemented");
        return [
            'sent' => 0,
            'failed' => ['All recipients (Africa\'s Talking integration not implemented)']
        ];
    }
    
    /**
     * Schedule messages for later sending
     */
    private function scheduleMessages($phoneNumbers, $messageBody, $gateway, $scheduledTime)
    {
        $school_id = Auth::user()?->school_id;
        
        foreach ($phoneNumbers as $number) {
            // Create a scheduled SMS record
            SmsLog::create([
                'recipient' => $number,
                'message' => $messageBody,
                'status' => 'scheduled',
                'gateway_name' => $gateway->name,
                'gateway_id' => $gateway->id,
                'scheduled_at' => $scheduledTime,
                'school_id' => $school_id,
                'sent_by' => Auth::id(),
            ]);
        }
        
        Log::info("Scheduled " . count($phoneNumbers) . " SMS messages for {$scheduledTime}");
    }
    
    /**
     * Format a phone number to E.164 format required by SMS gateways
     */
    private function formatPhoneNumber($rawNumber)
    {
        // Basic phone number formatting
        $number = preg_replace('/[^0-9+]/', '', $rawNumber);
        
        if (strlen($number) > 0 && strpos($number, '+') !== 0) {
            // Replace with your default country code
            $defaultCountryCode = '+254'; // Example for Kenya
            $number = $defaultCountryCode . preg_replace('/^0/', '', preg_replace('/[^0-9]/', '', $number));
        }
        
        // Basic validation: check length after formatting
        if (strlen($number) >= 11 && strlen($number) <= 15 && strpos($number, '+') === 0) {
            return $number;
        }
        
        return null;
    }

    /**
     * Authorization check for school access
     */
    private function authorizeSchoolAccess($model) {
        $userSchoolId = Auth::user()?->school_id;
        if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
            abort(403, 'Unauthorized action.');
        }
    }
}