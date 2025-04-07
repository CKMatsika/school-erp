<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\FeeStructure;
// *** Use the consolidated SchoolClass model ***
use App\Models\Accounting\SchoolClass; // <-- CORRECTED MODEL
use App\Models\Student;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceItem;
use App\Models\Accounting\ChartOfAccount; // Needed if fee items link here
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BulkInvoiceController extends Controller
{
    public function index()
    {
        $school_id = Auth::user()?->school_id;

        // *** Use the correct model name here ***
        $classes = SchoolClass::when($school_id, fn($q, $id)=>$q->where('school_id', $id))
                              ->where('is_active', true)
                              ->orderBy('name')
                              ->get();

        $feeStructures = FeeStructure::when($school_id, fn($q, $id)=>$q->where('school_id', $id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ensure the view name is correct
        return view('accounting.bulk-invoice.index', compact('classes', 'feeStructures'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            // *** Use the correct table name here ('timetable_school_classes') ***
            'class_id' => 'required|exists:timetable_school_classes,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ]);

        $school_id = Auth::user()?->school_id;
        $classId = $validated['class_id'];
        $feeStructureId = $validated['fee_structure_id'];

        // *** Use the correct model name here ***
        $classExists = SchoolClass::where('id', $classId)
                                  ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                  ->exists();
        $feeStructure = FeeStructure::with('items.incomeAccount')
                                     ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                                     ->find($feeStructureId);

        if (!$classExists || !$feeStructure) {
            return redirect()->back()->with('error', 'Invalid class or fee structure selected.');
        }

        // Find students - Ensure 'current_class_id' is correct foreign key on Student model
        $students = Student::where('current_class_id', $classId)
                           ->where('is_active', true)
                           ->when($school_id, fn($q, $id) => $q->where('school_id', $id))
                           ->whereHas('accountingContact', function($q){
                                $q->where('is_active', true)->where('contact_type', 'student');
                           })
                           ->with('accountingContact')
                           ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'No active students with accounting contacts found in the selected class.');
        }
        if ($feeStructure->items->isEmpty()) {
             return redirect()->back()->with('error', 'Selected fee structure has no items defined.');
        }

        $invoicesCreated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                if (!$student->accountingContact) continue;

                 $invoiceNumber = $this->generateNextInvoiceNumber($school_id); // Use the helper

                 $totalSubtotal = 0; $totalTax = 0;
                 foreach($feeStructure->items as $item) { $totalSubtotal += $item->amount; }
                 $totalAmount = $totalSubtotal + $totalTax; // Add tax logic if needed

                $invoice = Invoice::create([
                    'school_id' => $school_id,
                    'contact_id' => $student->accountingContact->id,
                    'invoice_number' => $invoiceNumber,
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'],
                    'subtotal' => $totalSubtotal,
                    'tax_amount' => $totalTax,
                    'total' => $totalAmount,
                    'amount_paid' => 0,
                    'status' => 'draft', // Or 'sent' if creating journals immediately
                    'type' => 'bulk_fee', // Need 'type' column added to invoices table via migration
                    'created_by' => Auth::id(),
                    'fee_schedule_id' => $feeStructureId, // Need 'fee_schedule_id' added to invoices table
                ]);

                 foreach($feeStructure->items as $feeItem) {
                     if (!$feeItem->incomeAccount) {
                         Log::warning("Skipping fee item '{$feeItem->name}' for invoice {$invoice->id}: No income account linked.");
                         continue;
                     }
                     InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $feeItem->name,
                        'quantity' => 1,
                        'unit_price' => $feeItem->amount,
                        'subtotal' => $feeItem->amount,
                        'tax_rate_id' => null, // Add tax logic here
                        'tax_amount' => 0,     // Add tax logic here
                        'account_id' => $feeItem->incomeAccount->id,
                    ]);
                 }

                // Optional: Create Journal Entry if status is set to 'sent'
                // if ($invoice->status === 'sent') {
                //     try {
                //         app(InvoicesController::class)->createJournalForInvoice($invoice); // Requires InvoicesController in 'use'
                //     } catch (\Exception $journalError) {
                //          Log::error("Failed to create journal for bulk invoice {$invoice->id}: ".$journalError->getMessage());
                //          // Decide how to handle - maybe add to $errors array?
                //     }
                // }

                $invoicesCreated++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during bulk invoice generation: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', 'An error occurred during bulk invoice generation: ' . $e->getMessage());
        }

        if ($invoicesCreated > 0) {
             return redirect()->route('accounting.invoices.index')
               ->with('success', "Successfully generated {$invoicesCreated} draft invoices for the selected class.");
        } else {
             return redirect()->back()->with('error', 'No invoices were generated. Please check student data and fee structure.');
        }
    }

     /**
     * Generate the next invoice number (Helper).
     * Ensure this logic is consistent with InvoicesController or centralized.
     */
    private function generateNextInvoiceNumber($schoolId = null)
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $query = Invoice::where('invoice_number', 'LIKE', $prefix . '%')
                      ->when($schoolId, fn($q, $id) => $q->where('school_id', $id))
                      ->orderBy('invoice_number', 'desc');
        $lastInvoice = $query->first();
        $nextNum = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastNumPart = end($parts);
            if (is_numeric($lastNumPart)) $nextNum = intval($lastNumPart) + 1;
        }
        return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
}