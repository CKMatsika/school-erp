<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact; // Assuming contacts are recipients
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient; // Import the Twilio SDK Client class
use Twilio\Exceptions\TwilioException; // Import Twilio Exception class

class SmsController extends Controller
{
    /**
     * Display SMS gateway information or configuration page.
     */
    public function gateways()
    {
        // TODO: Implement fetching gateway info
        $gateways = [];
        return view('accounting.sms.gateways', compact('gateways')); // Needs view
    }

    /**
     * Display SMS templates.
     */
    public function templates()
    {
        // TODO: Implement fetching templates
        $templates = [];
        return view('accounting.sms.templates', compact('templates')); // Needs view
    }

    /**
     * Display SMS sending logs.
     */
    public function logs()
    {
         // TODO: Implement fetching logs
        $logs = [];
        return view('accounting.sms.logs', compact('logs')); // Needs view
    }

    /**
     * Show the form for sending a new SMS message.
     */
    public function showSendForm()
    {
        $school_id = Auth::user()?->school_id;
        $contacts = Contact::where('is_active', true)
                           ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                           ->whereNotNull('phone')
                           ->where('phone', '!=', '')
                           ->orderBy('name')
                           ->get(['id', 'name', 'phone']);
        return view('accounting.sms.send', compact('contacts')); // Needs view
    }

    /**
     * Process the sending of the SMS message using Twilio.
     */
    public function processSend(Request $request)
    {
        $validated = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required', // Validate each recipient identifier
            'message' => 'required|string|max:1600',
        ]);

        $school_id = Auth::user()?->school_id;
        $recipientIdentifiers = $validated['recipients'];
        $messageBody = $validated['message'];
        $sentCount = 0;
        $failedRecipients = [];
        $phoneNumbers = []; // Key: contact ID, Value: E.164 Phone Number

        // 1. Resolve recipient identifiers (assuming IDs for now) to phone numbers
        $contacts = Contact::whereIn('id', $recipientIdentifiers)
                           ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                           ->whereNotNull('phone')
                           ->where('phone', '!=', '')
                           ->get(['id', 'name', 'phone']); // Include name for error messages

        foreach ($contacts as $contact) {
            // --- Phone Number Formatting (Basic Example - Improve This) ---
            // Attempt to format to E.164 (+CountryCodeNumber) which Twilio requires
            $number = preg_replace('/[^0-9+]/', '', $contact->phone); // Remove spaces, dashes, parens etc. except +
            if (strlen($number) > 0 && strpos($number, '+') !== 0) {
                // WARNING: Assumes local numbers need a default country code.
                // Replace '+1' with your country code if necessary.
                // A more robust solution uses a phone number parsing library (e.g., giggsey/libphonenumber-for-php)
                 $defaultCountryCode = '+1'; // Example for US/Canada
                 // $defaultCountryCode = '+44'; // Example for UK
                 // $defaultCountryCode = '+254'; // Example for Kenya
                $number = $defaultCountryCode . preg_replace('/[^0-9]/', '', $number); // Remove non-digits before prepending
            }
            // --- End Basic Formatting ---

            // Basic validation: check length after formatting (adjust min/max as needed)
            if (strlen($number) >= 11 && strlen($number) <= 15 && strpos($number, '+') === 0) {
                $phoneNumbers[$contact->id] = $number;
            } else {
                Log::warning("Invalid or unformattable phone number for Contact ID {$contact->id}: '{$contact->phone}' resulted in '{$number}'. Skipping.");
                $failedRecipients[] = $contact->name . ' (Invalid Number)';
            }
        }
        // TODO: Add logic if $request->recipients could contain raw phone numbers directly

        if (empty($phoneNumbers)) {
             return redirect()->back()->withInput()->with('error', 'No valid recipient phone numbers found or resolved.');
        }


        // 2. Initialize Twilio Client & Get Credentials
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        $twilioFrom = config('services.twilio.from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
             Log::error('Twilio credentials not configured in config/services.php or .env.');
             return redirect()->back()->withInput()->with('error', 'SMS sending is not configured correctly.');
        }

        Log::info("Attempting to send SMS via Twilio", ['count' => count($phoneNumbers), 'from' => $twilioFrom]);

        $twilio = new TwilioClient($twilioSid, $twilioToken);

        // 3. Loop through valid numbers and send SMS
        foreach ($phoneNumbers as $contactId => $number) {
            try {
                $sms = $twilio->messages->create(
                    $number, // To: E.164 format number
                    [
                        'from' => $twilioFrom, // From: Your Twilio Number or Messaging Service SID
                        'body' => $messageBody,
                        // Optional: Status Callback URL
                        // 'statusCallback' => route('sms.status-callback'),
                    ]
                );

                Log::info("SMS queued/sent via Twilio to {$number} (Contact ID: {$contactId}). SID: {$sms->sid}, Status: {$sms->status}");
                // TODO: Log successful send details (sid, status, etc.) to your sms_logs table
                $sentCount++;

            } catch (TwilioException $e) {
                 Log::error("Twilio SMS error sending to {$number} (Contact ID: {$contactId}): [{$e->getCode()}] {$e->getMessage()}");
                 $failedRecipients[] = $number . ' (API Error)'; // Don't expose detailed error codes to user
                 // TODO: Log failed send details to your sms_logs table
            } catch (\Exception $e) {
                Log::error("General error sending SMS to {$number} (Contact ID: {$contactId}): {$e->getMessage()}");
                 $failedRecipients[] = $number . ' (Failed)';
                  // TODO: Log failed send details
            }
        }

        // 4. Determine redirect message
        $messageLog = [];
        if ($sentCount > 0) { $messageLog[] = "{$sentCount} SMS message(s) successfully sent via Twilio."; }
        if (!empty($failedRecipients)) { $messageLog[] = "Failed to send to: " . implode(', ', $failedRecipients); }

        if ($sentCount > 0 && empty($failedRecipients)) {
            return redirect()->route('accounting.sms.send')->with('success', $messageLog[0]);
        } elseif ($sentCount > 0 && !empty($failedRecipients)) {
             return redirect()->route('accounting.sms.send')->with('warning', implode(' ', $messageLog));
        } elseif (!empty($failedRecipients)) {
             // If all failed, it's more of an error
             return redirect()->back()->withInput()->with('error', implode(' ', $messageLog));
        } else {
             // This case might happen if initial number resolution failed for all
             return redirect()->back()->withInput()->with('error', 'Could not send SMS to any recipients. Please check numbers.');
        }
    }

    // Placeholder for basic authorization check
     private function authorizeSchoolAccess($model) {
         $userSchoolId = Auth::user()?->school_id;
         if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
             abort(403, 'Unauthorized action.');
         }
     }
}