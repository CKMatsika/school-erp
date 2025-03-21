<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPromotionController extends Controller
{
    public function index()
    {
        $fromClasses = SchoolClass::where('school_id', auth()->user()->school_id)->get();
        $toClasses = SchoolClass::where('school_id', auth()->user()->school_id)->get();
        $feeStructures = FeeStructure::where('school_id', auth()->user()->school_id)
            ->where('is_active', true)
            ->get();
            
        return view('students.promotion', compact('fromClasses', 'toClasses', 'feeStructures'));
    }

    public function promote(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:school_classes,id',
            'to_class_id' => 'required|exists:school_classes,id',
            'academic_year' => 'required|string',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'generate_invoice' => 'nullable|boolean',
            'issue_date' => 'required_if:generate_invoice,1|nullable|date',
            'due_date' => 'required_if:generate_invoice,1|nullable|date|after_or_equal:issue_date',
            'term' => 'required_if:generate_invoice,1|nullable|string',
        ]);
        
        // Get students from the source class
        $students = Student::where('class_id', $request->from_class_id)
            ->where('status', 'active')
            ->get();
            
        if ($students->isEmpty()) {
            return back()->with('error', 'No active students found in the selected class.');
        }
        
        // Check if this is a graduation class
        $toClass = SchoolClass::find($request->to_class_id);
        $isGraduationClass = $toClass->is_graduation_class ?? false;
            
        DB::beginTransaction();
        try {
            $count = 0;
            $invoiceCount = 0;
            
            foreach ($students as $student) {
                // Update student class
                $student->update([
                    'class_id' => $request->to_class_id,
                    'previous_class_id' => $request->from_class_id, 
                ]);
                
                // If this is a graduation class, mark as graduated
                if ($isGraduationClass) {
                    $student->update([
                        'status' => 'graduated',
                        'graduation_date' => now(),
                    ]);
                }
                
                // Generate new fee invoice if requested
                if ($request->generate_invoice && $request->fee_structure_id) {
                    $this->generateInvoice(
                        $student,
                        $request->fee_structure_id,
                        $request->issue_date,
                        $request->due_date,
                        $request->term,
                        $request->academic_year
                    );
                    $invoiceCount++;
                }
                
                $count++;
            }
            
            DB::commit();
            
            $message = "{$count} students promoted successfully.";
            if ($invoiceCount > 0) {
                $message .= " {$invoiceCount} invoices generated.";
            }
            
            return redirect()->route('students.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Error promoting students: {$e->getMessage()}");
        }
    }
    
    private function generateInvoice($student, $feeStructureId, $issueDate, $dueDate, $term, $academicYear)
    {
        // Get fee structure
        $feeStructure = FeeStructure::with('items')->findOrFail($feeStructureId);
        
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
            'reference' => "Fees {$term} {$academicYear} - Promotion",
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
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
        
        return $invoice;
    }
}