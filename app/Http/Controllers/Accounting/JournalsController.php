<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Contact;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $journals = Journal::orderBy('journal_date', 'desc')->get();
        return view('accounting.journals.index', compact('journals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $contacts = Contact::where('is_active', true)->get();
        
        return view('accounting.journals.create', compact('accounts', 'contacts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
            'journal_date' => 'required|date',
            'description' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:chart_of_accounts,id',
            'entries.*.debit' => 'required_without:entries.*.credit|nullable|numeric|min:0',
            'entries.*.credit' => 'required_without:entries.*.debit|nullable|numeric|min:0',
            'entries.*.description' => 'nullable|string',
            'entries.*.contact_id' => 'nullable|exists:accounting_contacts,id',
        ]);

        // Verify that debits equal credits
        $totalDebits = 0;
        $totalCredits = 0;
        
        foreach ($request->entries as $entry) {
            $totalDebits += (float) ($entry['debit'] ?? 0);
            $totalCredits += (float) ($entry['credit'] ?? 0);
        }
        
        if (abs($totalDebits - $totalCredits) > 0.01) {
            return redirect()->back()
                ->with('error', 'Journal entries must balance. Total debits must equal total credits.')
                ->withInput();
        }

        // Get school ID if available
        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the journal
            $journal = Journal::create([
                'school_id' => $schoolId,
                'reference' => $request->reference,
                'journal_date' => $request->journal_date,
                'description' => $request->description,
                'status' => 'posted',
                'created_by' => auth()->id(),
                'document_type' => 'manual',
            ]);

            // Create the journal entries
            foreach ($request->entries as $entry) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'] ?? 0,
                    'credit' => $entry['credit'] ?? 0,
                    'description' => $entry['description'] ?? null,
                    'contact_id' => $entry['contact_id'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('accounting.journals.index')
                ->with('success', 'Journal entry created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating journal entry: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Journal $journal)
    {
        $journal->load(['entries.account', 'entries.contact', 'creator']);
        return view('accounting.journals.show', compact('journal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Journal $journal)
    {
        // Don't allow editing non-manual journals
        if ($journal->document_type !== 'manual') {
            return redirect()->route('accounting.journals.show', $journal)
                ->with('error', 'Only manual journal entries can be edited.');
        }

        $journal->load(['entries.account', 'entries.contact']);
        
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $contacts = Contact::where('is_active', true)->get();
        
        return view('accounting.journals.edit', compact('journal', 'accounts', 'contacts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Journal $journal)
    {
        // Don't allow editing non-manual journals
        if ($journal->document_type !== 'manual') {
            return redirect()->route('accounting.journals.show', $journal)
                ->with('error', 'Only manual journal entries can be edited.');
        }

        $request->validate([
            'reference' => 'required|string',
            'journal_date' => 'required|date',
            'description' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.id' => 'nullable|exists:journal_entries,id',
            'entries.*.account_id' => 'required|exists:chart_of_accounts,id',
            'entries.*.debit' => 'required_without:entries.*.credit|nullable|numeric|min:0',
            'entries.*.credit' => 'required_without:entries.*.debit|nullable|numeric|min:0',
            'entries.*.description' => 'nullable|string',
            'entries.*.contact_id' => 'nullable|exists:accounting_contacts,id',
        ]);

        // Verify that debits equal credits
        $totalDebits = 0;
        $totalCredits = 0;
        
        foreach ($request->entries as $entry) {
            $totalDebits += (float) ($entry['debit'] ?? 0);
            $totalCredits += (float) ($entry['credit'] ?? 0);
        }
        
        if (abs($totalDebits - $totalCredits) > 0.01) {
            return redirect()->back()
                ->with('error', 'Journal entries must balance. Total debits must equal total credits.')
                ->withInput();
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the journal
            $journal->update([
                'reference' => $request->reference,
                'journal_date' => $request->journal_date,
                'description' => $request->description,
            ]);

            // Get the IDs of existing entries
            $existingEntryIds = $journal->entries->pluck('id')->toArray();
            $updatedEntryIds = [];

            // Update or create the journal entries
            foreach ($request->entries as $entryData) {
                if (isset($entryData['id']) && in_array($entryData['id'], $existingEntryIds)) {
                    // Update existing entry
                    $entry = JournalEntry::find($entryData['id']);
                    $entry->update([
                        'account_id' => $entryData['account_id'],
                        'debit' => $entryData['debit'] ?? 0,
                        'credit' => $entryData['credit'] ?? 0,
                        'description' => $entryData['description'] ?? null,
                        'contact_id' => $entryData['contact_id'] ?? null,
                    ]);
                    $updatedEntryIds[] = $entry->id;
                } else {
                    // Create new entry
                    $entry = JournalEntry::create([
                        'journal_id' => $journal->id,
                        'account_id' => $entryData['account_id'],
                        'debit' => $entryData['debit'] ?? 0,
                        'credit' => $entryData['credit'] ?? 0,
                        'description' => $entryData['description'] ?? null,
                        'contact_id' => $entryData['contact_id'] ?? null,
                    ]);
                    $updatedEntryIds[] = $entry->id;
                }
            }

            // Delete removed entries
            foreach ($existingEntryIds as $entryId) {
                if (!in_array($entryId, $updatedEntryIds)) {
                    JournalEntry::destroy($entryId);
                }
            }

            DB::commit();

            return redirect()->route('accounting.journals.show', $journal)
                ->with('success', 'Journal entry updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating journal entry: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Journal $journal)
    {
        // Don't allow deleting non-manual journals
        if ($journal->document_type !== 'manual') {
            return redirect()->route('accounting.journals.index')
                ->with('error', 'Only manual journal entries can be deleted.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Delete the journal entries
            $journal->entries()->delete();
            
            // Delete the journal
            $journal->delete();

            DB::commit();

            return redirect()->route('accounting.journals.index')
                ->with('success', 'Journal entry deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('accounting.journals.index')
                ->with('error', 'Error deleting journal entry: ' . $e->getMessage());
        }
    }
}