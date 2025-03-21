<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Contact;
use App\Models\Student;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::all();
        return view('accounting.contacts.index', compact('contacts'));
    }

    /**
     * Display a listing of contacts by type.
     */
    public function indexByType($type)
    {
        $contacts = Contact::where('contact_type', $type)->get();
        return view('accounting.contacts.index', compact('contacts', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::all();
        return view('accounting.contacts.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_type' => 'required|string|in:customer,vendor,student',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'student_id' => 'nullable|exists:students,id|required_if:contact_type,student',
        ]);

        $schoolId = null;
        if (auth()->user()->school) {
            $schoolId = auth()->user()->school->id;
        }

        $contact = Contact::create([
            'school_id' => $schoolId,
            'contact_type' => $request->contact_type,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'tax_number' => $request->tax_number,
            'is_active' => true,
            'student_id' => $request->student_id,
        ]);

        return redirect()->route('accounting.contacts.index')
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['invoices', 'payments']);
        return view('accounting.contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        $students = Student::all();
        return view('accounting.contacts.edit', compact('contact', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'contact_type' => 'required|string|in:customer,vendor,student',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'student_id' => 'nullable|exists:students,id|required_if:contact_type,student',
            'is_active' => 'nullable|boolean',
        ]);

        $contact->update([
            'contact_type' => $request->contact_type,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'tax_number' => $request->tax_number,
            'is_active' => $request->has('is_active'),
            'student_id' => $request->student_id,
        ]);

        return redirect()->route('accounting.contacts.index')
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        // Check if contact has invoices or payments
        if ($contact->invoices()->count() > 0 || $contact->payments()->count() > 0) {
            return redirect()->route('accounting.contacts.index')
                ->with('error', 'Contact cannot be deleted because it has associated invoices or payments.');
        }

        $contact->delete();

        return redirect()->route('accounting.contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}
