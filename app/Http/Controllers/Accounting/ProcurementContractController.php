<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ProcurementContract;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\Tender;
use App\Models\Accounting\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcurementContractController extends Controller
{
    /**
     * Display a listing of the procurement contracts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ProcurementContract::with(['supplier', 'tender', 'academicYear']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('contract_number', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Get results
        $contracts = $query->orderBy('end_date')
            ->paginate(15);
            
        // Get suppliers and academic years for filter
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('accounting.procurement-contracts.index', compact('contracts', 'suppliers', 'academicYears'));
    }

    /**
     * Show the form for creating a new procurement contract.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Get all active suppliers
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Get active academic year
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        // Get tenders that have been awarded
        $tenders = null;
        
        // If creating from a tender
        $selectedTender = null;
        if ($request->has('tender_id')) {
            $selectedTender = Tender::with('awardedSupplier')
                ->findOrFail($request->tender_id);
                
            // Verify the tender is awarded
            if ($selectedTender->status !== 'awarded' || !$selectedTender->awardedSupplier) {
                return redirect()->route('accounting.tenders.show', $selectedTender)
                    ->with('error', 'This tender has not been awarded yet.');
            }
        } else {
            // Get all awarded tenders without contracts
            $tenders = Tender::where('status', 'awarded')
                ->whereNotNull('awarded_to')
                ->whereDoesntHave('contracts')
                ->with('awardedSupplier')
                ->get();
        }
        
        // Generate contract number
        $latestContract = ProcurementContract::latest()->first();
        $nextId = $latestContract ? $latestContract->id + 1 : 1;
        $contractNumber = 'CNT-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        return view('accounting.procurement-contracts.create', compact(
            'suppliers', 
            'academicYear', 
            'tenders',
            'selectedTender',
            'contractNumber'
        ));
    }

    /**
     * Store a newly created procurement contract in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            'end_date' => 'required|date|after:start_date',
            'contract_value' => 'required|numeric|min:0',
            'terms_and_conditions' => 'nullable|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'contract_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        
        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        
        // Handle file upload if provided
        $documentPath = null;
        if ($request->hasFile('contract_document')) {
            $file = $request->file('contract_document');
            $fileName = 'contract_' . Str::slug($validated['contract_number']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $documentPath = $file->storeAs('contracts', $fileName, 'public');
            $validated['document_path'] = $documentPath;
        }
        
        // Create the contract
        $contract = ProcurementContract::create($validated);
        
        return redirect()->route('accounting.procurement-contracts.show', $contract)
            ->with('success', 'Contract created successfully.');
    }

    /**
     * Display the specified procurement contract.
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function edit(ProcurementContract $procurementContract)
    {
        // Only allow editing of draft contracts
        if ($procurementContract->status !== 'draft') {
            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('error', 'Only draft contracts can be edited.');
        }
        
        // Get all active suppliers
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Get academic years
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        // Get awarded tenders
        $tenders = Tender::where('status', 'awarded')
            ->whereNotNull('awarded_to')
            ->with('awardedSupplier')
            ->get();
        
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProcurementContract $procurementContract)
    {
        // Only allow editing of draft contracts
        if ($procurementContract->status !== 'draft') {
            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('error', 'Only draft contracts can be edited.');
        }
        
        $validated = $request->validate([
            'contract_number' => 'required|string|max:50|unique:procurement_contracts,contract_number,' . $procurementContract->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'tender_id' => 'nullable|exists:tenders,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'contract_value' => 'required|numeric|min:0',
            'terms_and_conditions' => 'nullable|string',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'contract_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'status' => 'sometimes|in:draft,active',
        ]);
        
        // Handle file upload if provided
        if ($request->hasFile('contract_document')) {
            // Delete old file if exists
            if ($procurementContract->document_path) {
                Storage::disk('public')->delete($procurementContract->document_path);
            }
            
            // Store new file
            $file = $request->file('contract_document');
            $fileName = 'contract_' . Str::slug($validated['contract_number']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $validated['document_path'] = $file->storeAs('contracts', $fileName, 'public');
        }
        
        // Update contract
        $procurementContract->update($validated);
        
        return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
            ->with('success', 'Contract updated successfully.');
    }

    /**
     * Remove the specified procurement contract from storage.
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProcurementContract $procurementContract)
    {
        // Only allow deletion of draft contracts
        if ($procurementContract->status !== 'draft
        public function destroy(ProcurementContract $procurementContract)
    {
        // Only allow deletion of draft contracts
        if ($procurementContract->status !== 'draft') {
            return redirect()->route('accounting.procurement-contracts.show', $procurementContract)
                ->with('error', 'Only draft contracts can be deleted.');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete document if exists
            if ($procurementContract->document_path) {
                Storage::disk('public')->delete($procurementContract->document_path);
            }
            
            // Delete the contract
            $procurementContract->delete();
            
            DB::commit();
            
            return redirect()->route('accounting.procurement-contracts.index')
                ->with('success', 'Contract deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete contract: ' . $e->getMessage());
        }
    }

    /**
     * Download contract document.
     *
     * @param  \App\Models\Accounting\ProcurementContract  $procurementContract
     * @return \Illuminate\Http\Response
     */
    public function download(ProcurementContract $procurementContract)
    {
        if (!$procurementContract->document_path || !Storage::disk('public')->exists($procurementContract->document_path)) {
            return back()->with('error', 'Document not found.');
        }
        
        return Storage::disk('public')->download(
            $procurementContract->document_path, 
            $procurementContract->contract_number . '_document.' . pathinfo($procurementContract->document_path, PATHINFO_EXTENSION)
        );
    }
}