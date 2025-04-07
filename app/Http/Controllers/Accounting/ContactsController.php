<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact;
use App\Models\Student; // Ensure this namespace is correct for your Student model
use App\Models\School; // Assuming you need this for school_id context
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Log; // Optional: For logging errors
use Illuminate\Support\Facades\Schema; // To check if students table exists
use Illuminate\Validation\Rule; // Make sure Rule is imported for validation

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Consider filtering by school if applicable
        $school_id = Auth::user()?->school_id; // Get current user's school ID
        $contacts = Contact::when($school_id, function ($query, $school_id) {
                            return $query->where('school_id', $school_id);
                        })
                        ->orderBy('name')
                        ->get(); // Using get() instead of paginate for simplicity now

        return view('accounting.contacts.index', compact('contacts'));
    }

    /**
     * Display a listing of contacts by type.
     */
    public function indexByType($type)
    {
        $validTypes = ['customer', 'vendor', 'student'];
        if (!in_array($type, $validTypes)) {
            return redirect()->route('accounting.contacts.index')->with('error', 'Invalid contact type specified.');
        }

        $school_id = Auth::user()?->school_id;
        $contacts = Contact::where('contact_type', $type)
                        ->when($school_id, function ($query, $school_id) {
                            return $query->where('school_id', $school_id);
                        })
                        ->orderBy('name')
                        ->get(); // Using get() instead of paginate

        return view('accounting.contacts.index', compact('contacts', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = collect(); // Default to an empty collection
        try {
            // Check if students table exists before querying
            if (Schema::hasTable('students')) {
                 // Fetch only necessary columns and order them
                 $school_id = Auth::user()?->school_id;
                 $students = Student::when($school_id, function($query, $school_id) {
                                    return $query->where('school_id', $school_id);
                                })
                                ->orderBy('last_name') // Assuming student has last_name, first_name
                                ->orderBy('first_name')
                                ->where('is_active', true) // Usually only link active students
                                ->get(['id', 'first_name', 'last_name', 'student_number']); // Adjust columns as needed
            } else {
                 Log::warning('ContactsController@create: Students table not found.');
            }
        } catch (\Exception $e) {
            // Log error if query fails for any other reason
            Log::error('ContactsController@create: Failed to fetch students. Error: ' . $e->getMessage());
        }

        return view('accounting.contacts.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contact_type' => 'required|string|in:customer,vendor,student',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:accounting_contacts,email', // Consider making email unique
            'phone' => 'nullable|string|max:20', // Adjusted max length
            'address' => 'nullable|string', // Removed max length for text area
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            // *** MODIFIED VALIDATION: student_id is optional ***
            'student_id' => 'nullable|exists:students,id',
        ]);

        // Automatically assign school_id based on logged-in user
        $schoolId = Auth::user()?->school_id; // Use null-safe operator

        // Add school_id and default is_active to the validated data
        $validatedData['school_id'] = $schoolId;
        $validatedData['is_active'] = true; // Default new contacts to active

        // Ensure student_id is null if empty string is passed
         $validatedData['student_id'] = $request->filled('student_id') ? $validatedData['student_id'] : null;

        try {
            $contact = Contact::create($validatedData);

            return redirect()->route('accounting.contacts.index')
                ->with('success', 'Contact "' . $contact->name . '" created successfully.'); // Added contact name

        } catch (\Exception $e) {
             Log::error('ContactsController@store: Failed to create contact. Error: ' . $e->getMessage());
             return redirect()->back()
                ->withInput() // Send input back to form
                ->with('error', 'Failed to create contact. Please check the details and try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        // Optional: Add authorization check if users should only see contacts for their school
        // $this->authorizeSchoolAccess($contact); // Implement this method if needed

        $contact->load(['invoices' => function ($query) {
            $query->orderBy('issue_date', 'desc'); // Order invoices
        }, 'payments' => function ($query) {
            $query->orderBy('payment_date', 'desc'); // Order payments
        }, 'student']); // Eager load student if linked

        return view('accounting.contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
         // Optional: Add authorization check
         // $this->authorizeSchoolAccess($contact); // Implement this method if needed

        $students = collect(); // Default to empty
        try {
            if (Schema::hasTable('students')) {
                 $school_id = Auth::user()?->school_id;
                 $students = Student::when($school_id, function($query, $school_id) {
                                    return $query->where('school_id', $school_id);
                                })
                                ->orderBy('last_name')
                                ->orderBy('first_name')
                                // Show active students + the currently linked one even if inactive
                                ->where(function($query) use ($contact) {
                                    $query->where('is_active', true)
                                          ->orWhere('id', $contact->student_id);
                                })
                                ->get(['id', 'first_name', 'last_name', 'student_number']);
            } else {
                 Log::warning('ContactsController@edit: Students table not found.');
            }
        } catch (\Exception $e) {
            Log::error('ContactsController@edit: Failed to fetch students. Error: ' . $e->getMessage());
        }

        return view('accounting.contacts.edit', compact('contact', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        // Optional: Add authorization check
        // $this->authorizeSchoolAccess($contact); // Implement this method if needed

        $validatedData = $request->validate([
            'contact_type' => 'required|string|in:customer,vendor,student',
            'name' => 'required|string|max:255',
            // Ensure unique email, ignoring the current contact
            'email' => ['nullable', 'email', 'max:255', Rule::unique('accounting_contacts')->ignore($contact->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
             // *** MODIFIED VALIDATION: student_id is optional ***
            'student_id' => 'nullable|exists:students,id',
            'is_active' => 'nullable|boolean', // Validation for boolean type
        ]);

         // Handle boolean 'is_active' checkbox - it's only present if checked
        $validatedData['is_active'] = $request->has('is_active');

        // Ensure student_id is null if empty string or if type is not student
        if ($validatedData['contact_type'] !== 'student' || !$request->filled('student_id')) {
             $validatedData['student_id'] = null;
        } else {
             $validatedData['student_id'] = $request->student_id; // Keep validated value
        }


        try {
             $contact->update($validatedData);

             return redirect()->route('accounting.contacts.index') // Consider redirecting to show page: route('accounting.contacts.show', $contact)
                ->with('success', 'Contact "' . $contact->name . '" updated successfully.');

        } catch (\Exception $e) {
            Log::error('ContactsController@update: Failed to update contact ID ' . $contact->id . '. Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update contact. Please check the details and try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        // Optional: Add authorization check
        // $this->authorizeSchoolAccess($contact); // Implement this method if needed

        // More robust check: also check related financial transactions if applicable
        if ($contact->invoices()->exists() || $contact->payments()->exists()) { // Use exists() for efficiency
            return redirect()->route('accounting.contacts.index')
                ->with('error', 'Contact cannot be deleted because it has associated invoices or payments.');
        }

         try {
            $contactName = $contact->name; // Get name before deleting
            $contact->delete();

            return redirect()->route('accounting.contacts.index')
                ->with('success', 'Contact "' . $contactName . '" deleted successfully.');

        } catch (\Exception $e) {
             Log::error('ContactsController@destroy: Failed to delete contact ID ' . $contact->id . '. Error: ' . $e->getMessage());
            return redirect()->route('accounting.contacts.index')
                ->with('error', 'Failed to delete contact.');
        }
    }

    /**
     * Helper function for authorization (Example)
     * Implement proper authorization (Policies/Gates) for real applications.
     */
    // protected function authorizeSchoolAccess(Contact $contact)
    // {
    //     $userSchoolId = Auth::user()?->school_id;
    //     if ($userSchoolId && $contact->school_id !== $userSchoolId) {
    //         abort(403, 'Unauthorized action.'); // Or redirect with error
    //     }
    //     // Add more checks if needed (e.g., roles/permissions)
    // }
}