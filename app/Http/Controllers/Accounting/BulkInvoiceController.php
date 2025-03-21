<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\FeeStructure;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkInvoiceController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::where('school_id', auth()->user()->school_id)->get();
        $feeStructures = FeeStructure::where('school_id', auth()->user()->school_id)
            ->where('is_active', true)
            ->get();
        
        return view('accounting.bulk-invoice.index', compact('classes', 'feeStructures'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'term' => 'required|string',
            'academic_year' => 'required|string',
        ]);
        
        // Get all students in the class
        $students = Student::where('class_id', $request->class_id)
            ->where('status', 'active')
            ->get();
        
        if ($students->isEmpty()) {
            return back()->with('error', 'No active students found in the selected class.');
        }
        
        // Get fee structure
        $feeStructure = FeeStructure::with('items')->findOrFail($request->fee_structure_id);
        
        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($students as $student) {
                // Generate invoice number
                $prefix = 'INV';
                $year = date('Y');
                $month = date('m');
                $lastInvoice = Invoice::where('school_id', auth()->user()->school_id)
                    ->where('invoice_number', 'like', "{$prefix}-{$year}{$month}%")
                    ->orderBy('invoice_number', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($lastInvoice) {
                    $parts = explode('-', $lastInvoice->invoice_number);
                    if (count($parts) > 1) {
                        $lastNumber = substr($parts[1], 6); // Extract number after YYYYMM
                        $nextNumber = intval($lastNumber) + 1;
                    }
                }
                
                $invoiceNumber = $prefix . '-' . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                
                // Create invoice
                $invoice = Invoice::create([
                    'school_id' => auth()->user()->school_id,
                    'student_id' => $student->id,
                    'invoice_number' => $invoiceNumber,
                    'reference' => "Fees {$request->term} {$request->academic_year}",
                    'issue_date' => $request->issue_date,
                    'due_date' => $request->due_date,
                    'type' => 'student_fee',
                    'status' => 'unpaid',
                    'created_by' => auth()->id(),
                ]);
                
                // Add fee items
                foreach ($feeStructure->items as $item) {
                    $invoice->items()->create([
                        'description' => $item->description,
                        'quantity' => 1,
                        'price' => $item->amount,
                        'tax_rate_id' => $item->tax_rate_id,
                        'tax_amount' => $item->tax_amount ?? 0,
                        'total' => $item->amount + ($item->tax_amount ?? 0),
                    ]);
                }
                
                $invoice->calculateTotals();
                $invoice->createStudentDebt();
                $count++;
            }
            
            DB::commit();
            return redirect()->route('accounting.invoices.index')
                ->with('success', "{$count} invoices created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Error creating invoices: {$e->getMessage()}");
        }
    }
}