<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ProcurementContract;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\Tender;
// Correct namespace if needed
// use App\Models\Settings\AcademicYear;
use App\Models\Accounting\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // <-- Import Auth
use Illuminate\Support\Facades\Log;   // <-- Import Log
use Illuminate\Support\Str;
use Carbon\Carbon; // <-- Import Carbon

class ProcurementContractController extends Controller
{
    /**
     * Display a listing of the procurement contracts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = ProcurementContract::with(['supplier', 'tender', 'academicYear']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('contract_number', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        // Get results
        $contracts = $query->orderBy('end_date')->paginate(15)->withQueryString();

        // Get suppliers and academic years for filter
        // Adjust if 'is_active' doesn't exist for suppliers
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        // $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Corrected: Order academic years by start_date
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        // $academicYears = AcademicYear::orderBy('year', 'desc')->get(); // Original incorrect line

        return view('accounting.procurement-contracts.index', compact('contracts', 'suppliers', 'academicYears'));
    }

    /**
     * Show the form for creating a new procurement contract.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        // Get suppliers
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']); // Fetch all or filter active if needed

        // Get current academic year
        $today = Carbon::today();
        $academicYear = AcademicYear::where('start_date', '<=', $today)
                                      ->where('end_date', '>=', $today)
                                      ->first();
        if (!$academicYear) {
             $academicYear = AcademicYear::orderBy('start_date', 'desc')->first(); // Fallback
              if(!$academicYear) {
                return redirect()->route('accounting.academic-years.index')
                    ->with('error', 'Please set up Academic Years before creating Contracts.');
             }
        }

        // Get potential Tenders to link
        $tenders = null;
        $selectedTender = null;
        $preSelectedSupplierId = null; // To pre-fill supplier if coming from Tender

        if ($request->has('tender_id')) {
            // If creating directly from a specific awarded tender
            $selectedTender = Tender::with('awardedSupplier')->find($request->tender_id);

            if (!$selectedTender || $selectedTender->status !== 'awarded' || !$selectedTender->awardedSupplier) {
                 return redirect()->route('accounting.tenders.index') // Redirect to tender list
                    ->with('error', 'Selected tender is not valid for contract creation (must be awarded to a supplier).');
            }
            $preSelectedSupplierId = $selectedTender->awarded_to; // Pre-select the awarded supplier

        } else {
            // If not coming from a specific tender, provide a list of awarded tenders without contracts
            $tenders = Tender::where('status', 'awarded')
                ->whereNotNull('awarded_to')
                ->whereDoesntHave('contracts') // Only show those without contracts yet
                ->with('awardedSupplier:id,name') // Load supplier name efficiently
                ->orderBy('award_date', 'desc')
                ->get(['id', 'tender_number', 'title', 'awarded_to']); // Select necessary columns
        }


        // Generate contract number
        $latestContract = ProcurementContract::latest('id')->first();
        $nextId = $latestContract ? $latestContract->id + 1 : 1;
        $contractNumber = 'CNT-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('accounting.procurement-contracts.create', compact(
            'suppliers',
            'academicYear',
            'tenders', // List of potential tenders (if not coming from specific one)
            'selectedTender', // The specific tender if creating from it
            'contractNumber',
            'preSelectedSupplierId' // Pass pre-selected supplier ID
        ));
    }

    /**
     * Store a newly created procurement contract in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_number' => 'required|string|max:50|unique:procurement_contracts',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'tender_id' => 'nullable|exists:tenders,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // Allow same day start/end
            'contract_value' => 'required|numeric|min:0',
            'terms_and_conditions' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id', // Make required as it's always passed now
            'contract_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB
        ]);

        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        $validated['tender_id'] = $validated['tender_id'] ?: null; // Ensure null if empty
        $validated['contract_value'] = $validated['contract_value'] ?? 0; // Default value

        // Handle file upload if provided
        $documentPath = null;
        if ($request->hasFile('contract_document')) {
            try {
                $file = $request->file('contract_document');
                $fileName = 'contract_doc_' . $validated['contract_number'] . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $documentPath = $file->storeAs('contract_documents', $fileName, 'public'); // Store in 'contract_documents'
                $validated['document_path'] = $documentPath;
            } catch (\Exception $e) {
                 Log::error("Contract Document Upload Failed: ".$e->getMessage());
                 return back()->withInput()->with('error', 'Failed to upload contract document.');
            }
        }

        try {
            // Check if selected tender allows contract creation (maybe already linked?)
            if($validated['tender_id']) {
                $tender = Tender::find($validated['tender_id']);
                if ($tender && $tender->contracts()->exists()) {
                     return back()->withInput()->with('error', 'The selected tender already has a contract linked.');
                }
                 // Optional: Check if supplier matches tender awardee
                if ($tender && $tender->awarded_to && $tender->awarded_to != $validated['supplier_id']) {
                    return back()->withInput()->with('error', 'The selected supplier does not match the supplier awarded for the chosen tender.');
                }
            }

            // Create the contract
            $contract = ProcurementContract::create($validated);

            return redirect()->route('accounting.procurement-contracts.show', $contract)
                ->with('success', 'Contract created successfully.');

        } catch (\Exception $e) {
            // Delete the uploaded file if database insertion failed
            if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }
            Log::error("Contract Creation Failed: ".$e->getMessage());
            return back()->withInput()->with('error', 'Failed to create contract. Please check logs.');
        }
    }

    /**
     * Display the specified procurement contract.
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Contracts\View\View
     */
    public function show(ProcurementContract $procurementContract)
    {
        // Load relationships
        $procurementContract->load(['supplier', 'tender', 'academicYear', 'creator']);

        return view('accounting.procurement-contracts.show', compact('procurementContract'));
    }

    /**
     * Show the form for editing the specified procurement contract.
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(ProcurementContract $procurementContract)
    {
        // Define editable statuses
        $editableStatuses = ['draft', 'active']; // Allow editing active contracts too? Adjust as needed.
        if (!in_array($procurementContract->status, $editableStatuses)) {
            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('error', 'Only contracts with status: ' . implode(', ', $editableStatuses) . ' can be edited.');
        }

        // Get suppliers
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        // Get academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get(); // Corrected order

        // Get awarded tenders (include the current one if linked, plus others without contracts)
        $tenders = Tender::where('status', 'awarded')
            ->whereNotNull('awarded_to')
            ->where(function($query) use ($procurementContract) {
                 $query->whereDoesntHave('contracts')
                       ->orWhere('id', $procurementContract->tender_id);
             })
            ->with('awardedSupplier:id,name')
            ->orderBy('award_date', 'desc')
            ->get(['id', 'tender_number', 'title', 'awarded_to']);

        return view('accounting.procurement-contracts.edit', compact(
            'procurementContract',
            'suppliers',
            'academicYears',
            'tenders'
        ));
    }

    /**
     * Update the specified procurement contract in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ProcurementContract $procurementContract)
    {
        $editableStatuses = ['draft', 'active']; // Define editable statuses
        if (!in_array($procurementContract->status, $editableStatuses)) {
            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('error', 'Only contracts with status: ' . implode(', ', $editableStatuses) . ' can be edited.');
        }

        $validated = $request->validate([
            'contract_number' => 'required|string|max:50|unique:procurement_contracts,contract_number,' . $procurementContract->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'tender_id' => 'nullable|exists:tenders,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // Allow same day
            'contract_value' => 'required|numeric|min:0',
            'terms_and_conditions' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id', // Make required
            'contract_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            // Allow changing status between draft/active? Or use separate actions?
            'status' => 'sometimes|in:draft,active,terminated', // Define allowed statuses here
        ]);

        $validated['tender_id'] = $validated['tender_id'] ?: null;
        $validated['academic_year_id'] = $validated['academic_year_id'] ?: null;
        $validated['contract_value'] = $validated['contract_value'] ?? 0;


        // Handle file upload if provided
        if ($request->hasFile('contract_document')) {
             try {
                // Delete old file if exists
                if ($procurementContract->document_path && Storage::disk('public')->exists($procurementContract->document_path)) {
                    Storage::disk('public')->delete($procurementContract->document_path);
                }
                // Store new file
                $file = $request->file('contract_document');
                $fileName = 'contract_doc_' . $validated['contract_number'] . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $validated['document_path'] = $file->storeAs('contract_documents', $fileName, 'public');
            } catch (\Exception $e) {
                 Log::error("Contract Document Update Upload Failed: ".$e->getMessage());
                 unset($validated['document_path']); // Don't update path if upload failed
                 session()->flash('warning', 'Failed to update contract document file.');
            }
        }

        // Handle status changes - add business logic checks if needed
        if ($request->has('status')) {
             // Example: Can only move from draft to active, or active to terminated
            if ($procurementContract->status === 'draft' && $validated['status'] === 'active') {
                // OK
            } elseif ($procurementContract->status === 'active' && $validated['status'] === 'terminated') {
                 // OK - maybe add terminated_at timestamp?
            } elseif ($validated['status'] === $procurementContract->status) {
                 // OK - No change
            } else {
                 unset($validated['status']); // Ignore invalid status change
                 session()->flash('warning', 'Invalid status transition attempted.');
            }
        }

        try {
            // Check tender/supplier consistency if tender_id is being changed/set
             if($request->filled('tender_id')) {
                $tender = Tender::find($validated['tender_id']);
                // Check if the new tender already has another contract
                if ($tender && $tender->contracts()->where('id', '!=', $procurementContract->id)->exists()) {
                     return back()->withInput()->with('error', 'The selected tender already has a contract linked.');
                }
                 // Check if supplier matches awardee
                if ($tender && $tender->awarded_to && $tender->awarded_to != $validated['supplier_id']) {
                    return back()->withInput()->with('error', 'The selected supplier does not match the supplier awarded for the chosen tender.');
                }
            }

            // Update contract
            $procurementContract->update($validated);

            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('success', 'Contract updated successfully.');

        } catch (\Exception $e) {
             Log::error("Contract Update Failed for ID {$procurementContract->id}: ".$e->getMessage());
             return back()->withInput()->with('error', 'Failed to update contract.');
        }
    }


    /**
     * Remove the specified procurement contract from storage.
     * CORRECTED Method
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ProcurementContract $procurementContract)
    {
        // Define deletable statuses
        $deletableStatuses = ['draft']; // Usually only drafts can be truly deleted
        if (!in_array($procurementContract->status, $deletableStatuses)) {
            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('error', 'Only contracts with status: ' . implode(', ', $deletableStatuses) . ' can be deleted.');
        }

        // Add Authorization check: Gate::authorize('delete', $procurementContract);

        // Optional: Add checks for related data (e.g., payments made against contract?)

        try {
            DB::beginTransaction();

            // Delete document if exists
            if ($procurementContract->document_path && Storage::disk('public')->exists($procurementContract->document_path)) {
                Storage::disk('public')->delete($procurementContract->document_path);
            }

            // Delete the contract
            $procurementContract->delete();

            DB::commit();

            return redirect()->route('accounting.procurement-contracts.index')
                ->with('success', 'Contract deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Contract Deletion Failed for ID {$procurementContract->id}: ".$e->getMessage());
            return back()->with('error', 'Failed to delete contract: ' . $e->getMessage());
        }
    }


    /**
     * Download contract document.
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(ProcurementContract $procurementContract)
    {
        if (!$procurementContract->document_path || !Storage::disk('public')->exists($procurementContract->document_path)) {
            return back()->with('error', 'Document not found for this contract.');
        }

        try {
            $path = $procurementContract->document_path;
            $fileName = $procurementContract->contract_number . '_document.' . pathinfo($path, PATHINFO_EXTENSION);
            return Storage::disk('public')->download($path, $fileName);
        } catch (\Exception $e) {
             Log::error("Contract Document Download Failed for ID {$procurementContract->id}: ".$e->getMessage());
             return back()->with('error', 'Could not download the document.');
        }
    }

} // End of ProcurementContractController class