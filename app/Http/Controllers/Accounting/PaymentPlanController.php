<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentPlan;          // Adjust namespace if needed
use App\Models\Student;             // Adjust namespace if needed
use App\Models\Accounting\Invoice;  // Adjust namespace if needed
use App\Models\Accounting\Contact;  // Need Contacts to select student
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $school_id = Auth::user()?->school_id;
        $paymentPlans = PaymentPlan::with(['student', 'invoice', 'contact']) // Eager load basic relations
                                  ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                  ->orderByDesc('created_at')
                                  ->get(); // Consider pagination

        return view('accounting.payment-plans.index', compact('paymentPlans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $school_id = Auth::user()?->school_id;

        // Fetch contacts of type student
        $studentContacts = Contact::where('contact_type', 'student')
                                ->where('is_active', true)
                                ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                ->orderBy('name')
                                ->get(['id', 'name', 'student_id']); // Include student_id for potential linking

        // Fetch outstanding invoices (optional, could be selected after contact)
        $invoices = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                          ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                          ->orderBy('invoice_number')
                          ->get(['id', 'invoice_number', 'total', 'amount_paid', 'contact_id']);

        // Pre-select if invoice_id is passed
        $selectedInvoiceId = $request->query('invoice_id');
        $selectedContactId = null;
        if($selectedInvoiceId){
            $inv = $invoices->firstWhere('id', $selectedInvoiceId);
            $selectedContactId = $inv?->contact_id;
        }


        return view('accounting.payment-plans.create', compact('studentContacts', 'invoices', 'selectedInvoiceId', 'selectedContactId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_id' => 'required|exists:invoices,id', // Make invoice required for a plan
            'name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01',
            'number_of_installments' => 'required|integer|min:2', // Must have at least 2 installments
            'start_date' => 'required|date',
            'interval' => ['required', 'string', Rule::in(['daily', 'weekly', 'biweekly', 'monthly', 'quarterly'])],
            'notes' => 'nullable|string',
        ]);

        $school_id = Auth::user()?->school_id;

        // Validate invoice belongs to contact and school
        $invoice = Invoice::where('id', $validated['invoice_id'])
                         ->where('contact_id', $validated['contact_id'])
                         ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                         ->first();

        if (!$invoice) {
            return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact.');
        }

        // Optionally validate if total_amount matches invoice balance due
        $balanceDue = $invoice->total - $invoice->amount_paid;
        if (abs($validated['total_amount'] - $balanceDue) > 0.01) {
             // Decide action: allow different amount, warn, or fail? For now, let's allow but log.
             Log::info("Payment plan amount ({$validated['total_amount']}) differs from invoice balance ({$balanceDue}) for Invoice ID {$invoice->id}.");
        }


        DB::beginTransaction();
        try {
            $paymentPlan = PaymentPlan::create([
                'school_id' => $school_id,
                'contact_id' => $validated['contact_id'],
                 // Get student_id from linked Contact, assuming Contact model has student relationship
                'student_id' => Contact::find($validated['contact_id'])?->student_id,
                'invoice_id' => $validated['invoice_id'],
                'name' => $validated['name'],
                'total_amount' => $validated['total_amount'],
                'paid_amount' => 0, // Initially 0
                'remaining_amount' => $validated['total_amount'], // Initially full amount
                'number_of_installments' => $validated['number_of_installments'],
                'start_date' => $validated['start_date'],
                'end_date' => null, // Will be set by generateSchedule
                'interval' => $validated['interval'],
                'status' => 'draft', // Start as draft, needs approval/activation
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Generate the initial schedule
            $paymentPlan->generateSchedule(); // Call the model method

            // Recalculate end date based on last installment (optional but good)
            $lastInstallment = $paymentPlan->paymentSchedules()->orderBy('due_date', 'desc')->first();
            if($lastInstallment) {
                $paymentPlan->end_date = $lastInstallment->due_date;
                $paymentPlan->save();
            }


            DB::commit();

             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                   ->with('success', 'Payment Plan created successfully. Review and approve the schedule.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating payment plan: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Error creating payment plan: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentPlan $paymentPlan) // Use route model binding
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($paymentPlan);

        $paymentPlan->load(['student', 'invoice', 'contact', 'paymentSchedules', 'creator', 'approver']);

        return view('accounting.payment-plans.show', compact('paymentPlan'));
    }

    /**
     * Show the form for editing the specified resource.
     * NOTE: Editing active/complex plans can be tricky. Often limited fields are editable.
     */
    public function edit(PaymentPlan $paymentPlan)
    {
        // TODO: Authorization
         $this->authorizeSchoolAccess($paymentPlan);

        // Usually only allow editing 'draft' plans before approval/payments
         if ($paymentPlan->status !== 'draft') {
             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                    ->with('error', 'Only draft payment plans can be fully edited.');
         }

        $school_id = Auth::user()?->school_id;
        $studentContacts = Contact::where('contact_type', 'student') ->where('is_active', true) ->when($school_id, fn($q, $id) => $q->where('school_id', $id))->orderBy('name')->get(['id', 'name', 'student_id']);
        $invoices = Invoice::where('contact_id', $paymentPlan->contact_id) // Show invoices for current contact
                          ->whereIn('status', ['sent', 'partial', 'overdue'])
                          ->orWhere('id', $paymentPlan->invoice_id) // Include the currently linked invoice
                          ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                          ->orderBy('invoice_number')
                          ->get(['id', 'invoice_number', 'total', 'amount_paid', 'contact_id']);

        return view('accounting.payment-plans.edit', compact('paymentPlan', 'studentContacts', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     * Be careful about regenerating schedules if installments already paid.
     */
    public function update(Request $request, PaymentPlan $paymentPlan)
    {
        // TODO: Authorization
        $this->authorizeSchoolAccess($paymentPlan);

        // Limit editing based on status
        if (!in_array($paymentPlan->status, ['draft'])) { // Maybe allow editing notes/name on active?
             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                    ->with('error', 'Only draft payment plans can be fully edited.');
        }

        $validated = $request->validate([
            'contact_id' => 'required|exists:accounting_contacts,id',
            'invoice_id' => 'required|exists:invoices,id',
            'name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01',
            'number_of_installments' => 'required|integer|min:2',
            'start_date' => 'required|date',
            'interval' => ['required', 'string', Rule::in(['daily', 'weekly', 'biweekly', 'monthly', 'quarterly'])],
            'notes' => 'nullable|string',
            'status' => ['required', Rule::in(['draft', 'active', 'completed', 'cancelled'])] // Allow status update? Maybe separate action.
        ]);

        $school_id = Auth::user()?->school_id;
        // Validate invoice belongs to contact and school
        $invoice = Invoice::where('id', $validated['invoice_id'])->where('contact_id', $validated['contact_id'])->when($school_id, fn($q, $id) => $q->where('school_id', $id))->first();
        if (!$invoice) return redirect()->back()->withInput()->with('error', 'Selected invoice does not match the selected contact.');

        DB::beginTransaction();
        try {
             // Update main plan details
            $paymentPlan->update([
                 'contact_id' => $validated['contact_id'],
                 'student_id' => Contact::find($validated['contact_id'])?->student_id,
                 'invoice_id' => $validated['invoice_id'],
                 'name' => $validated['name'],
                 'total_amount' => $validated['total_amount'],
                 'number_of_installments' => $validated['number_of_installments'],
                 'start_date' => $validated['start_date'],
                 'interval' => $validated['interval'],
                 'notes' => $validated['notes'],
                 'status' => $validated['status'], // Allow status update? Requires care.
                 // Recalculate remaining based on current paid amount
                 'remaining_amount' => $validated['total_amount'] - $paymentPlan->paid_amount,
            ]);

            // **Crucial Decision:** Regenerate schedule only if certain fields change and NO payments made?
            // If fields like amount, installments, start date, interval change, the schedule is invalid.
            // Check if key fields have changed:
            $keyFieldsChanged = $paymentPlan->wasChanged(['total_amount', 'number_of_installments', 'start_date', 'interval']);
            // Check if any installments have been paid (requires PaymentSchedule model/relation)
            // $hasPaidInstallments = $paymentPlan->paymentSchedules()->where('status', 'paid')->exists();

            // Simplified: Regenerate if draft and key fields changed. Add check for paid installments later.
            if ($paymentPlan->status === 'draft' && $keyFieldsChanged) {
                 Log::info("Regenerating schedule for Payment Plan ID {$paymentPlan->id} due to changes.");
                 $paymentPlan->paymentSchedules()->delete(); // Delete old schedule items
                 $paymentPlan->generateSchedule();          // Generate new ones
                 // Recalculate end date
                 $lastInstallment = $paymentPlan->paymentSchedules()->orderBy('due_date', 'desc')->first();
                 $paymentPlan->end_date = $lastInstallment?->due_date;
                 $paymentPlan->save(); // Save updated end date
            } else if ($keyFieldsChanged && $paymentPlan->status !== 'draft') {
                 // Changed key fields on an active plan - this is problematic!
                 DB::rollBack(); // Prevent saving changes
                 return redirect()->back()->withInput()->with('error', 'Cannot change amount, installments, start date, or interval on an active plan.');
            }

            // Recalculate/Update status based on amounts (call model method)
            $paymentPlan->updateStatus();


            DB::commit();

             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                   ->with('success', 'Payment Plan updated successfully.');

        } catch (\Exception $e) {
             DB::rollBack();
            Log::error("Error updating payment plan {$paymentPlan->id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Error updating payment plan: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentPlan $paymentPlan)
    {
        // TODO: Authorization
         $this->authorizeSchoolAccess($paymentPlan);

        // Prevent deleting if active or completed or has payments applied to schedule
        if ($paymentPlan->status !== 'draft') { // Allow deleting only drafts?
            return redirect()->route('accounting.payment-plans.index')
                   ->with('error', 'Cannot delete an active or completed payment plan.');
        }
        // Add check for paid installments: if ($paymentPlan->paymentSchedules()->where('status', 'paid')->exists()) ...

        DB::beginTransaction();
        try {
            $planName = $paymentPlan->name;
            // Cascade delete should handle schedules if FK is set up correctly
            $paymentPlan->delete(); // Soft delete if enabled
            DB::commit();
             return redirect()->route('accounting.payment-plans.index')
                   ->with('success', "Payment Plan '{$planName}' deleted successfully.");
        } catch(\Exception $e) {
            DB::rollBack();
             Log::error("Error deleting payment plan {$paymentPlan->id}: " . $e->getMessage());
              return redirect()->route('accounting.payment-plans.index')
                   ->with('error', 'Failed to delete payment plan.');
        }
    }

     // --- Additional Methods from Routes ---

     /**
      * View the generated payment schedule.
      */
     public function viewSchedule(PaymentPlan $paymentPlan) // Changed parameter name to match route/binding
     {
         // TODO: Authorization
         $this->authorizeSchoolAccess($paymentPlan);

         $paymentPlan->load('paymentSchedules'); // Eager load schedule items
         // Needs view: resources/views/accounting/payment-plans/schedule.blade.php
         return view('accounting.payment-plans.schedule', compact('paymentPlan'));
     }

     /**
      * Manually trigger schedule generation (e.g., if it needs recalculation).
      */
     public function generateSchedule(Request $request, PaymentPlan $paymentPlan) // Changed parameter name
     {
          // TODO: Authorization
          $this->authorizeSchoolAccess($paymentPlan);

         // Add checks - maybe only allow regeneration for 'draft' status?
         if ($paymentPlan->status != 'draft') {
             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                   ->with('error', 'Cannot regenerate schedule for non-draft plans.');
         }
         // Add check for paid installments?

         DB::beginTransaction();
         try {
             Log::info("Manually regenerating schedule for Payment Plan ID {$paymentPlan->id}.");
             $paymentPlan->paymentSchedules()->delete(); // Delete old schedule items
             $paymentPlan->generateSchedule();          // Generate new ones
             // Recalculate end date
             $lastInstallment = $paymentPlan->paymentSchedules()->orderBy('due_date', 'desc')->first();
             $paymentPlan->end_date = $lastInstallment?->due_date;
             $paymentPlan->save();
             DB::commit();
             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                   ->with('success', 'Payment schedule regenerated successfully.');
         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error regenerating schedule for payment plan {$paymentPlan->id}: " . $e->getMessage());
             return redirect()->route('accounting.payment-plans.show', $paymentPlan)
                   ->with('error', 'Error regenerating schedule: '.$e->getMessage());
         }
     }


     // Basic authorization helper
     private function authorizeSchoolAccess($model) {
         $userSchoolId = Auth::user()?->school_id;
         if ($userSchoolId && isset($model->school_id) && $model->school_id !== $userSchoolId) {
             abort(403, 'Unauthorized action.');
         }
     }

}