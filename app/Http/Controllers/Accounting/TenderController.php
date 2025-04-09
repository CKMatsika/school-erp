<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Tender;
use App\Models\Accounting\TenderBid;
use App\Models\Accounting\Supplier;
use App\Models\Accounting\AcademicYear; // Assuming this is the correct namespace
use App\Models\Accounting\ProcurementContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // <-- Import Auth
use Illuminate\Support\Facades\Log;   // <-- Import Log
use Illuminate\Support\Str;
// use PDF; // <-- Uncomment if using PDF

class TenderController extends Controller
{
    /**
     * Display a listing of the tenders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Tender::with(['awardedSupplier', 'academicYear']); // Eager load relationships

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('tender_number', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        // Get results
        $tenders = $query->orderBy('closing_date', 'desc')->paginate(15)->withQueryString();

        // Get academic years for filter
        // ==================================================
        // == UPDATED ORDER BY CLAUSE ==
        // ==================================================
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get(); // Order by start_date
        // ==================================================


        return view('accounting.tenders.index', compact('tenders', 'academicYears'));
    }

    /**
     * Show the form for creating a new tender.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Get current academic year based on date
        $today = \Carbon\Carbon::today(); // Use Carbon for date handling
        $academicYear = AcademicYear::where('start_date', '<=', $today)
                                      ->where('end_date', '>=', $today)
                                      ->first();
        if (!$academicYear) {
             $academicYear = AcademicYear::orderBy('start_date', 'desc')->first(); // Fallback
             if (!$academicYear) {
                return redirect()->route('accounting.academic-years.index') // Adjust route
                    ->with('error', 'Please set up Academic Years before creating Tenders.');
             }
        }

        // Generate tender number
        $latestTender = Tender::latest('id')->first();
        $nextId = $latestTender ? $latestTender->id + 1 : 1;
        $tenderNumber = 'TND-' . date('Ym') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('accounting.tenders.create', compact('academicYear', 'tenderNumber'));
    }

    /**
     * Store a newly created tender in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tender_number' => 'required|string|max:50|unique:tenders',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'publication_date' => 'required|date',
            'closing_date' => 'required|date|after_or_equal:publication_date', // Ensure closing >= publication
            'estimated_value' => 'nullable|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id', // Made required as it's fetched in create
            'tender_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        // Set defaults
        $validated['status'] = 'draft';
        $validated['created_by'] = auth()->id();
        $validated['estimated_value'] = $validated['estimated_value'] ?? null; // Ensure null if empty

        // Handle file upload if provided
        $documentPath = null;
        if ($request->hasFile('tender_document')) {
            try {
                $file = $request->file('tender_document');
                // Use a more robust file name (avoiding potential conflicts with slugs)
                $fileName = 'tender_doc_' . $validated['tender_number'] . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $documentPath = $file->storeAs('tender_documents', $fileName, 'public'); // Store in 'tender_documents' folder
                $validated['document_path'] = $documentPath;
            } catch (\Exception $e) {
                 Log::error("Tender Document Upload Failed: ".$e->getMessage());
                 return back()->withInput()->with('error', 'Failed to upload tender document.');
            }
        }

        try {
            // Create the tender (document path is now included if uploaded)
            $tender = Tender::create($validated);

            return redirect()->route('accounting.tenders.show', $tender)
                ->with('success', 'Tender created successfully.');
        } catch (\Exception $e) {
            // Delete the uploaded file if database insertion failed
            if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }
            Log::error("Tender Creation Failed: ".$e->getMessage());
            return back()->withInput()->with('error', 'Failed to create tender. Please check logs.');
        }
    }

    /**
     * Display the specified tender.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Tender $tender)
    {
        // Load relationships needed for the view
        $tender->load([
            'awardedSupplier',
            'academicYear',
            'creator',
            'bids' => function ($q) { $q->with('supplier')->orderBy('total_score', 'desc')->orderBy('bid_amount', 'asc'); }, // Order bids
            'contracts.supplier' // Load related contracts and their suppliers
        ]);

        // Count bids (already loaded)
        $bidCount = $tender->bids->count();

        // contracts are already loaded
        // $contracts = $tender->contracts;

        return view('accounting.tenders.show', compact('tender', 'bidCount')); // Removed 'contracts' as it's on $tender now
    }

    /**
     * Show the form for editing the specified tender.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Tender $tender)
    {
        // Only allow editing of draft or published tenders (or adjust as needed)
        if (!in_array($tender->status, ['draft', 'published'])) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender status (' . $tender->status . ') does not allow editing.');
        }

        // Get academic years for dropdown
        // ==================================================
        // == UPDATED ORDER BY CLAUSE ==
        // ==================================================
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get(); // Order by start_date
        // ==================================================


        return view('accounting.tenders.edit', compact('tender', 'academicYears'));
    }

    /**
     * Update the specified tender in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Tender $tender)
    {
        if (!in_array($tender->status, ['draft', 'published'])) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender status (' . $tender->status . ') does not allow editing.');
        }

        $validated = $request->validate([
            'tender_number' => 'required|string|max:50|unique:tenders,tender_number,' . $tender->id,
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'publication_date' => 'required|date',
            'closing_date' => 'required|date|after_or_equal:publication_date', // Ensure closing >= publication
            'estimated_value' => 'nullable|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id', // Made required
            'tender_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            // Allow changing status from draft->published or published->draft? Or separate actions?
            'status' => 'sometimes|in:draft,published,closed', // Allow setting to closed maybe?
        ]);

        $validated['estimated_value'] = $validated['estimated_value'] ?? null; // Ensure null if empty
        $validated['academic_year_id'] = $validated['academic_year_id'] ?: null; // Ensure null if empty

        // Handle file upload if provided
        if ($request->hasFile('tender_document')) {
             try {
                // Delete old file if exists
                if ($tender->document_path && Storage::disk('public')->exists($tender->document_path)) {
                    Storage::disk('public')->delete($tender->document_path);
                }
                // Store new file
                $file = $request->file('tender_document');
                 $fileName = 'tender_doc_' . $validated['tender_number'] . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $validated['document_path'] = $file->storeAs('tender_documents', $fileName, 'public');
            } catch (\Exception $e) {
                 Log::error("Tender Document Update Upload Failed: ".$e->getMessage());
                 // Don't fail the whole update, just skip file and maybe warn?
                 // return back()->withInput()->with('error', 'Failed to upload tender document.');
                 unset($validated['document_path']); // Don't update path if upload failed
                 session()->flash('warning', 'Failed to update tender document file.'); // Inform user
            }
        }

        // Handle status changes carefully based on workflow rules
        if ($request->has('status')) {
             // Example: Only allow draft -> published, or published -> closed (not backwards easily)
            if ($tender->status === 'draft' && $validated['status'] === 'published') {
                 // Allowed
            } elseif ($tender->status === 'published' && $validated['status'] === 'closed') {
                // Allowed
            } elseif ($tender->status === 'published' && $validated['status'] === 'draft') {
                 // Maybe allowed? Depends on rules.
            } else if ($validated['status'] === $tender->status) {
                // No actual change, allowed
            }
            else {
                // Disallow other transitions via edit form
                 unset($validated['status']); // Ignore the requested status change
                 session()->flash('warning', 'Invalid status transition attempted.');
            }
        }


        try {
            $tender->update($validated);
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('success', 'Tender updated successfully.');
        } catch (\Exception $e) {
             Log::error("Tender Update Failed for ID {$tender->id}: ".$e->getMessage());
             return back()->withInput()->with('error', 'Failed to update tender.');
        }

    }

    /**
     * Remove the specified tender from storage.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Tender $tender)
    {
        // Add Authorization: Gate::authorize('delete', $tender);

        // Define deletable statuses
        $deletableStatuses = ['draft', 'cancelled']; // Adjust as needed

        if (!in_array($tender->status, $deletableStatuses)) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'Only tenders with status: ' . implode(', ', $deletableStatuses) . ' can be deleted.');
        }

        // Check for related bids or contracts (prevent deletion if linked)
        if ($tender->bids()->exists() || $tender->contracts()->exists()) {
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'Cannot delete tender with existing bids or contracts.');
        }

        try {
            DB::beginTransaction();

            // Delete document if exists
            if ($tender->document_path && Storage::disk('public')->exists($tender->document_path)) {
                Storage::disk('public')->delete($tender->document_path);
            }

            $tender->delete(); // Deleting the tender

            DB::commit();

            return redirect()->route('accounting.tenders.index')
                ->with('success', 'Tender deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
             Log::error("Tender Deletion Failed for ID {$tender->id}: ".$e->getMessage());
            return back()->with('error', 'Failed to delete tender: ' . $e->getMessage());
        }
    }

    /**
     * Display the bids for a tender.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Contracts\View\View
     */
    public function bids(Tender $tender)
    {
        // Load bids with suppliers, ordered
        $tender->load(['bids' => function ($q) {
             $q->with('supplier')->orderBy('total_score', 'desc')->orderBy('bid_amount', 'asc');
        }]);

        // Get active suppliers for adding new bids (assuming is_active exists or fetch all)
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        // $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('accounting.tenders.bids', compact('tender', 'suppliers'));
    }

     /**
     * Store a new bid for a tender.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBid(Request $request, Tender $tender)
    {
        // Check if tender is open for bidding
        if (!in_array($tender->status, ['published'])) { // Only allow bidding on published tenders
            return redirect()->route('accounting.tenders.bids', $tender)
                 ->with('error', 'Bids can only be submitted for published tenders.');
        }
        // Optional: Check closing date
        // if (Carbon::now()->gt($tender->closing_date)) {
        //      return redirect()->route('accounting.tenders.bids', $tender)
        //          ->with('error', 'The closing date for this tender has passed.');
        // }


        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                // Rule to ensure unique bid per supplier for this tender
                 \Illuminate\Validation\Rule::unique('tender_bids')->where(function ($query) use ($tender) {
                     return $query->where('tender_id', $tender->id);
                 }),
            ],
            'bid_date' => 'required|date',
            'bid_amount' => 'required|numeric|min:0',
            'technical_score' => 'nullable|integer|min:0',
            'financial_score' => 'nullable|integer|min:0',
            // 'total_score' // Usually calculated, not submitted
            'is_compliant' => 'boolean',
            'notes' => 'nullable|string',
            'bid_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480', // 20MB max for bids?
        ]);

        $validated['is_compliant'] = $request->has('is_compliant'); // Set boolean from checkbox
        // Calculate total score if components are present
        $validated['total_score'] = ($validated['technical_score'] ?? 0) + ($validated['financial_score'] ?? 0);


        // Handle file upload
        $documentPath = null;
        if ($request->hasFile('bid_document')) {
             try {
                $file = $request->file('bid_document');
                $fileName = 'bid_' . $tender->tender_number . '_supp' . $validated['supplier_id'] . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $documentPath = $file->storeAs('tender_bids/' . $tender->id, $fileName, 'public'); // Store in tender-specific folder
                $validated['document_path'] = $documentPath;
            } catch (\Exception $e) {
                 Log::error("Tender Bid Document Upload Failed: ".$e->getMessage());
                 return back()->withInput()->with('error', 'Failed to upload bid document.');
            }
        }

        try {
            $tender->bids()->create($validated); // Create using relationship

             return redirect()->route('accounting.tenders.bids', $tender)
                 ->with('success', 'Bid submitted successfully.');

        } catch (\Exception $e) {
             if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }
             Log::error("Tender Bid Store Failed: ".$e->getMessage());
             return back()->withInput()->with('error', 'Failed to store bid.');
        }

    }

     /**
     * Destroy a bid. (Use with caution!)
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @param  \App\Models\Accounting\TenderBid  $bid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBid(Tender $tender, TenderBid $bid)
    {
        // Add Authorization: Gate::authorize('delete', $bid);

        // Check if tender status allows bid deletion (e.g., only if not awarded/closed)
         if (!in_array($tender->status, ['published', 'closed'])) {
             return redirect()->route('accounting.tenders.bids', $tender)
                 ->with('error', 'Bids cannot be deleted at this stage.');
         }

        try {
             // Delete document if exists
             if ($bid->document_path && Storage::disk('public')->exists($bid->document_path)) {
                 Storage::disk('public')->delete($bid->document_path);
             }
             $bid->delete();
             return redirect()->route('accounting.tenders.bids', $tender)
                 ->with('success', 'Bid deleted successfully.');
        } catch (\Exception $e) {
             Log::error("Tender Bid Delete Failed for Bid ID {$bid->id}: ".$e->getMessage());
             return back()->with('error', 'Failed to delete bid.');
        }
    }


    /**
     * Award a tender to a supplier (based on bids, likely).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Illuminate\Http\RedirectResponse
     */
    public function award(Request $request, Tender $tender)
    {
        // Add Authorization: Gate::authorize('award', $tender);

        // Check if tender can be awarded (e.g., must be 'closed' or maybe 'published' if direct award)
        if (!in_array($tender->status, ['closed', 'published'])) { // Adjust allowed statuses
            return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender cannot be awarded at its current status.');
        }
         if ($tender->awarded_to) {
             return redirect()->route('accounting.tenders.show', $tender)
                ->with('error', 'This tender has already been awarded.');
         }


        $validated = $request->validate([
            // Often awarded based on a specific bid
            'tender_bid_id' => 'required_without:awarded_to|nullable|exists:tender_bids,id',
            // Allow direct award without a bid?
            'awarded_to' => 'required_without:tender_bid_id|nullable|exists:suppliers,id',
            'award_date' => 'required|date',
            'award_notes' => 'nullable|string',
        ],[
            'tender_bid_id.required_without' => 'Either a winning bid or a supplier must be selected.',
            'awarded_to.required_without' => 'Either a winning bid or a supplier must be selected.',
        ]);

        $supplierId = null;
        if($request->filled('tender_bid_id')) {
            $winningBid = TenderBid::find($validated['tender_bid_id']);
            // Ensure bid belongs to this tender
             if (!$winningBid || $winningBid->tender_id !== $tender->id) {
                 return back()->withInput()->with('error', 'Invalid winning bid selected.');
             }
            $supplierId = $winningBid->supplier_id;
        } else {
            $supplierId = $validated['awarded_to'];
        }

         if (!$supplierId) {
              return back()->withInput()->with('error', 'Could not determine the supplier to award to.');
         }


        try {
            $tender->update([
                'status' => 'awarded',
                'awarded_to' => $supplierId,
                'award_date' => $validated['award_date'],
                'award_notes' => $validated['award_notes'],
            ]);

            // TODO: Potentially create a contract record automatically?
            // ProcurementContract::create([...]);

            return redirect()->route('accounting.tenders.show', $tender)
                ->with('success', 'Tender awarded successfully.');

        } catch (\Exception $e) {
             Log::error("Tender Award Failed for ID {$tender->id}: ".$e->getMessage());
             return back()->withInput()->with('error', 'Failed to award tender.');
        }
    }

    /**
     * Download tender document.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(Tender $tender)
    {
        if (!$tender->document_path || !Storage::disk('public')->exists($tender->document_path)) {
            return back()->with('error', 'Document not found for this tender.');
        }

        try {
            $path = $tender->document_path;
            $fileName = $tender->tender_number . '_document.' . pathinfo($path, PATHINFO_EXTENSION);
            return Storage::disk('public')->download($path, $fileName);
        } catch (\Exception $e) {
             Log::error("Tender Document Download Failed for ID {$tender->id}: ".$e->getMessage());
             return back()->with('error', 'Could not download the document.');
        }
    }

     /**
     * Download bid document.
     *
     * @param  \App\Models\Accounting\Tender  $tender
     * @param  \App\Models\Accounting\TenderBid  $bid
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadBid(Tender $tender, TenderBid $bid)
    {
        // Ensure bid belongs to tender
         if ($bid->tender_id !== $tender->id) {
             abort(404);
         }

         if (!$bid->document_path || !Storage::disk('public')->exists($bid->document_path)) {
            return back()->with('error', 'Document not found for this bid.');
        }

        try {
            $path = $bid->document_path;
            $supplierName = Str::slug($bid->supplier->name ?? 'supplier_'.$bid->supplier_id);
            $fileName = 'bid_' . $tender->tender_number . '_' . $supplierName . '.' . pathinfo($path, PATHINFO_EXTENSION);
            return Storage::disk('public')->download($path, $fileName);
        } catch (\Exception $e) {
             Log::error("Tender Bid Document Download Failed for Bid ID {$bid->id}: ".$e->getMessage());
             return back()->with('error', 'Could not download the bid document.');
        }
    }


} // End of TenderController class