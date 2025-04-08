<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Tender;
use App\Models\Accounting\TenderBid;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\AcademicYear;
use App\Models\Accounting\ProcurementContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;

class TenderController extends Controller
{
    /**
     * Display a listing of the tenders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Tender::with(['awardedSupplier', 'academicYear']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('tender_number', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Get results
        $tenders = $query->orderBy('closing_date', 'desc')
            ->paginate(15);
            
        // Get academic years for filter
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('accounting.tenders.index', compact('tenders', 'academicYears'));
    }

    /**
     * Show the form for creating a new tender.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get active academic year
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        // Generate tender number
        $latestTender = Tender::latest()->first();
        $nextId = $latestTender ? $latestTender->id + 1 : 1;
        $tenderNumber = 'TND-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        return view('accounting.tenders.create', compact('academicYear', 'tenderNumber'));
    }

    /**
     * Store a newly created tender in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tender_number' => 'required|string|max:50|unique:tenders',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'publication_date' => 'required|date',
            'closing_date' => 'required|date|after:publication_date',
            'estimated_value' => 'nullable|numeric|min:0',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'tender_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        
        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        
        // Handle file upload if provided
        $documentPath = null;
        if ($request->hasFile('tender_document')) {
            $file = $request->file('tender_document');
            $fileName = 'tender_' . Str::slug($validated['tender_number']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $documentPath = $file->storeAs('tenders', $fileName, 'public');
        }
        
        try {
            DB::beginTransaction();
            
            // Create the tender
            $tender = Tender::create($validated);
            
            // Store document path in the database if a file was uploaded
            if ($documentPath) {
                $tender->document_path = $documentPath;
                $tender->save();
            }
            
            DB::commit();
            
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('success', 'Tender created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete the uploaded file if there was an error
            if ($documentPath) {
                Storage::disk('public')->delete($documentPath);
            }
            
            return back()->withInput()->with('error', 'Failed to create tender: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified tender.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function show(Tender $tender)
    {
        // Load relationships
        $tender->load(['awardedSupplier', 'academicYear', 'creator', 'bids.supplier']);
        
        // Count bids
        $bidCount = $tender->bids->count();
        
        // Get related contracts
        $contracts = $tender->contracts()->with('supplier')->get();
        
        return view('accounting.tenders.show', compact('tender', 'bidCount', 'contracts'));
    }

    /**
     * Show the form for editing the specified tender.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function edit(Tender $tender)
    {
        // Only allow editing of draft or published tenders
        if (!in_array($tender->status, ['draft', 'published'])) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender cannot be edited.');
        }
        
        // Get academic years
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('accounting.tenders.edit', compact('tender', 'academicYears'));
    }

    /**
     * Update the specified tender in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tender $tender)
    {
        // Only allow editing of draft or published tenders
        if (!in_array($tender->status, ['draft', 'published'])) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender cannot be edited.');
        }
        
        $validated = $request->validate([
            'tender_number' => 'required|string|max:50|unique:tenders,tender_number,' . $tender->id,
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'publication_date' => 'required|date',
            'closing_date' => 'required|date|after:publication_date',
            'estimated_value' => 'nullable|numeric|min:0',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'tender_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'status' => 'sometimes|in:draft,published',
        ]);
        
        // Handle file upload if provided
        if ($request->hasFile('tender_document')) {
            // Delete old file if exists
            if ($tender->document_path) {
                Storage::disk('public')->delete($tender->document_path);
            }
            
            // Store new file
            $file = $request->file('tender_document');
            $fileName = 'tender_' . Str::slug($validated['tender_number']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $validated['document_path'] = $file->storeAs('tenders', $fileName, 'public');
        }
        
        // Only allow status change from draft to published
        if ($tender->status === 'draft' && $request->status === 'published') {
            $validated['status'] = 'published';
        } else {
            unset($validated['status']);
        }
        
        $tender->update($validated);
        
        return redirect()->route('accounting.tenders.show', $tender)
            ->with('success', 'Tender updated successfully.');
    }

    /**
     * Remove the specified tender from storage.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tender $tender)
    {
        // Only allow deletion of draft tenders
        if ($tender->status !== 'draft') {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'Only draft tenders can be deleted.');
        }
        
        // Check if there are bids
        if ($tender->bids()->exists()) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'Cannot delete tender with existing bids.');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete document if exists
            if ($tender->document_path) {
                Storage::disk('public')->delete($tender->document_path);
            }
            
            // Delete the tender
            $tender->delete();
            
            DB::commit();
            
            return redirect()->route('accounting.tenders.index')
                ->with('success', 'Tender deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete tender: ' . $e->getMessage());
        }
    }

    /**
     * Display the bids for a tender.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function bids(Tender $tender)
    {
        // Load bids with suppliers
        $tender->load(['bids.supplier']);
        
        // Get all active suppliers for adding new bids
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('accounting.tenders.bids', compact('tender', 'suppliers'));
    }

    /**
     * Award a tender to a supplier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function award(Request $request, Tender $tender)
    {
        // Check if tender can be awarded
        if (!$tender->canBeAwarded()) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender cannot be awarded.');
        }
        
        $validated = $request->validate([
            'awarded_to' => 'required|exists:suppliers,id',
            'award_date' => 'required|date',
            'award_notes' => 'nullable|string',
        ]);
        
        // Update tender
        $tender->update([
            'status' => 'awarded',
            'awarded_to' => $validated['awarded_to'],
            'award_date' => $validated['award_date'],
            'award_notes' => $validated['award_notes'],
        ]);
        
        return redirect()->route('accounting.tenders.show', $tender)
            ->with('success', 'Tender awarded successfully.');
    }

    /**
     * Download tender document.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\Response
     */
    public function download(Tender $tender)
    {
        if (!$tender->document_path || !Storage::disk('public')->exists($tender->document_path)) {
            return back()->with('error', 'Document not found.');
        }
        
        return Storage::disk('public')->download($tender->document_path, $tender->tender_number . '_document.' . pathinfo($tender->document_path, PATHINFO_EXTENSION));
    }
}