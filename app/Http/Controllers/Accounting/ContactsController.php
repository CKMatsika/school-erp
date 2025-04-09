<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact;
use App\Models\Student; // Ensure this namespace is correct
// use App\Models\School; // Assuming not needed unless filtering schools differently
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel; // <-- Import Excel Facade
use App\Imports\ContactsImport;      // <-- Import your Import class
use Maatwebsite\Excel\Validators\ValidationException; // <-- Import ValidationException
use Illuminate\Support\Facades\Response; // <-- For template download
use Illuminate\Support\Facades\Validator; // <-- Import Validator Facade

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        $query = Contact::when($school_id, fn($q, $id) => $q->where('school_id', $id))
                        ->when($request->filled('search'), function ($q) use ($request) {
                             $term = '%' . $request->search . '%';
                             $q->where(function($subq) use ($term){
                                 $subq->where('name', 'like', $term)
                                      ->orWhere('email', 'like', $term)
                                      ->orWhere('phone', 'like', $term)
                                      ->orWhere('tax_number', 'like', $term);
                             });
                        });

        // Filter by type if provided in route parameter OR request query
        $type = $request->route()->parameter('type') ?? $request->input('type');
         $validTypes = ['customer', 'vendor', 'student'];
         if ($type && in_array($type, $validTypes)) {
             $query->where('contact_type', $type);
         }

        $contacts = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('accounting.contacts.index', compact('contacts', 'type'));
    }

    /**
     * Display a listing of contacts by type.
     * (Redirects to the main index method with type filter)
     */
    public function indexByType($type)
    {
        // Redirect to the index method, passing the type as a query parameter
        return redirect()->route('accounting.contacts.index', ['type' => $type]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = collect(); // Initialize as empty collection
        // Attempt to load students only if the table likely exists and relationship is needed
        // You might want to load this via AJAX instead if the list is very large
        // try {
        //     if (Schema::hasTable('students')) {
        //          $school_id = Auth::user()?->school_id;
        //          $students = Student::when($school_id, fn($q, $id) => $q->where('school_id', $id))
        //                         ->orderBy('last_name')->orderBy('first_name')
        //                         // ->where('is_active', true) // Uncomment if student model has is_active
        //                         ->whereDoesntHave('accountingContact') // Only show students NOT already linked
        //                         ->get(['id', 'first_name', 'last_name', 'middle_name', 'student_number']); // Select needed fields
        //     }
        // } catch (\Exception $e) {
        //     Log::error("Error fetching students for contact form: " . $e->getMessage());
        //     // Continue without students list, maybe show a warning
        // }

        return view('accounting.contacts.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $school_id = Auth::user()?->school_id;
        $validated = $request->validate([
            'contact_type' => ['required', Rule::in(['customer', 'vendor', 'student'])],
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                 Rule::unique('contacts')->where(function ($query) use ($school_id) {
                     return $query->where('school_id', $school_id); // Scope uniqueness by school if needed
                 })
             ],
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'student_id' => 'nullable|required_if:contact_type,student|exists:students,id', // Required if type is student
        ],[
            'student_id.required_if' => 'Please select the corresponding student record when creating a student contact.'
        ]);

        // Add school ID if applicable
        if ($school_id) {
            $validated['school_id'] = $school_id;
        }
        $validated['is_active'] = true; // Default to active

        try {
            $contact = Contact::create($validated);

            // If student type and student_id is set, potentially update student record too? (Or just link)
            // Be careful about data synchronization if you duplicate info.

            return redirect()->route('accounting.contacts.show', $contact)
                       ->with('success', ucfirst($validated['contact_type']) . ' contact created successfully.');

        } catch (\Exception $e) {
            Log::error("Contact Store failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create contact.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        // Add authorization check if needed: Gate::authorize('view', $contact);
        $contact->load('student', 'invoices', 'payments'); // Eager load relationships
        return view('accounting.contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
         // Add authorization check if needed: Gate::authorize('update', $contact);
        $students = collect();
        // Load students only if editing a student contact
        // if ($contact->contact_type === 'student') {
        //     try {
        //         if (Schema::hasTable('students')) {
        //             $school_id = Auth::user()?->school_id;
        //             $students = Student::when($school_id, fn($q, $id) => $q->where('school_id', $id))
        //                             ->orderBy('last_name')->orderBy('first_name')
        //                             // ->where('is_active', true)
        //                             // Show currently linked student + unlinked students
        //                             ->where(function($q) use ($contact) {
        //                                 $q->whereDoesntHave('accountingContact')
        //                                   ->orWhere('id', $contact->student_id);
        //                             })
        //                             ->get(['id', 'first_name', 'last_name', 'middle_name', 'student_number']);
        //         }
        //     } catch (\Exception $e) {
        //         Log::error("Error fetching students for contact edit: " . $e->getMessage());
        //     }
        // }

        return view('accounting.contacts.edit', compact('contact', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
         // Add authorization check if needed: Gate::authorize('update', $contact);
         $school_id = Auth::user()?->school_id;

         $validated = $request->validate([
            'contact_type' => ['required', Rule::in(['customer', 'vendor', 'student'])],
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                 Rule::unique('contacts')->ignore($contact->id)->where(function ($query) use ($school_id) {
                     return $query->where('school_id', $school_id); // Scope uniqueness by school
                 })
             ],
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'student_id' => 'nullable|required_if:contact_type,student|exists:students,id',
             'is_active' => 'boolean', // Allow updating status
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        // Ensure student_id is null if contact_type is not student
        if ($validated['contact_type'] !== 'student') {
             $validated['student_id'] = null;
        }

        try {
            $contact->update($validated);
             return redirect()->route('accounting.contacts.show', $contact)
                       ->with('success', 'Contact updated successfully.');
        } catch (\Exception $e) {
            Log::error("Contact Update failed for ID {$contact->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update contact.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        // Add authorization check if needed: Gate::authorize('delete', $contact);

        // Prevent deletion if contact has transactions
        if ($contact->invoices()->exists() || $contact->payments()->exists()) {
            return redirect()->route('accounting.contacts.index')
                       ->with('error', 'Cannot delete contact with existing invoices or payments.');
        }

        try {
            $contact->delete(); // Assuming Soft Deletes, otherwise use forceDelete()
             return redirect()->route('accounting.contacts.index')
                       ->with('success', 'Contact deleted successfully.');
        } catch (\Exception $e) {
             Log::error("Contact Delete failed for ID {$contact->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete contact.');
        }
    }

    // =============================================
    // == Bulk Import Methods ==
    // =============================================

    /**
     * Show the form for importing contacts.
     */
    public function showImportForm()
    {
        // Gate::authorize('import', Contact::class); // Add authorization check
        return view('accounting.contacts.import');
    }

    /**
     * Handle the uploaded contact file.
     */
    public function handleImport(Request $request)
    {
        // Gate::authorize('import', Contact::class);

        $request->validate([
            'contact_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB Max
        ]);

        $file = $request->file('contact_file');
        $import = new ContactsImport(); // Instantiate the import class

        try {
            Excel::import($import, $file);

            $importedCount = $import->getImportedCount();
            $skippedRows = $import->getSkippedRows();
            $totalProcessed = $import->getRowCount(); // This counts rows read

            $message = "Import finished. Processed: {$totalProcessed} rows. Imported: {$importedCount} contacts successfully.";
            $statusLevel = 'success';

            if (!empty($skippedRows)) {
                $message .= " Skipped: " . count($skippedRows) . " rows due to errors.";
                session()->flash('import_errors', $skippedRows); // Store detailed errors
                 // Change status level if there were skips
                 $statusLevel = ($importedCount > 0) ? 'warning' : 'error';
                 // Redirect back to form to show errors
                 return redirect()->route('accounting.contacts.import.form')->with($statusLevel, $message); // CORRECTED: Added ');'
            }

            // If no errors, redirect to index
            return redirect()->route('accounting.contacts.index')->with($statusLevel, $message); // CORRECTED: Use $statusLevel

        } catch (ValidationException $e) {
            // Catch validation exceptions from Laravel Excel
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                 $errorMessages[] = [
                     'row' => $failure->row(),
                     'attribute' => $failure->attribute(),
                     'errors' => $failure->errors(),
                     'data' => $failure->values()
                 ];
            }
             session()->flash('import_errors', $errorMessages);
             return redirect()->route('accounting.contacts.import.form')
                        ->with('error', 'Import failed due to validation errors in the file. Please check details below.');

        } catch (\Exception $e) {
            // Catch any other general exceptions during import
             Log::error("Contact Import Failed: " . $e->getMessage());
             return redirect()->route('accounting.contacts.import.form')
                        ->with('error', 'An unexpected error occurred during import: ' . $e->getMessage());
        }
    }

    /**
     * Download a template file (CSV) for contact import.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadTemplate()
    {
        $headers = [
            'name', 'contact_type', 'email', 'phone', 'tax_number',
            'address', 'city', 'state', 'postal_code', 'country',
            // 'student_identifier' // Example if using this for student linking
        ];
        $example = [
            'Sample Customer Inc.', 'customer', 'customer@example.com', '555-1234', 'VAT123',
            '456 Business Ave', 'Busytown', 'BizState', '54321', 'Sample Country',
            // '' // Leave student identifier blank for non-student
        ];
         $example2 = [
            'Sample Vendor Ltd.', 'vendor', 'vendor@example.com', '555-5678', 'VEN987',
            '789 Supply Rd', 'Metropolis', 'MetroState', '98765', 'Sample Country',
            // ''
        ];
         $example3 = [
            'Student Name Example', 'student', 'student.contact@example.com', '555-1122', '',
            '1 School Lane', 'Anytown', 'Anystate', '12345', 'Sample Country',
             // 'S-001' // Add Example Student ID/Number if using linking
        ];

        $filename = "contact_import_template.csv";

        $callback = function() use ($headers, $example, $example2, $example3) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $example);
            fputcsv($handle, $example2);
            fputcsv($handle, $example3);
            fclose($handle);
        };

        // Use Response facade directly
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

} // End of ContactsController class