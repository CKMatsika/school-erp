<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Application;
use App\Models\Student\AcademicYear;
use App\Models\Student\ApplicationParent;
use App\Models\Student\DocumentType;
use App\Models\TimetableSchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::where('school_id', auth()->user()->school_id)
                                  ->orderByDesc('submission_date')
                                  ->paginate(20);
        
        $statusCounts = Application::where('school_id', auth()->user()->school_id)
                                  ->selectRaw('status, count(*) as count')
                                  ->groupBy('status')
                                  ->pluck('count', 'status')
                                  ->toArray();

        // Get applications grouped by status
        // For pending applications
        $pendingApplications = Application::where('school_id', auth()->user()->school_id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_documents', 'interview_scheduled'])
            ->get();
            
        // For accepted applications
        $acceptedApplications = Application::where('school_id', auth()->user()->school_id)
            ->whereIn('status', ['accepted', 'waitlisted'])
            ->orderByDesc('decision_date')
            ->get();
            
        // For rejected applications
        $rejectedApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'rejected')
            ->orderByDesc('decision_date')
            ->get();
            
        // For enrolled applications
        $enrolledApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'enrolled')
            ->orderByDesc('decision_date')
            ->get();
            
        // For all other possible statuses
        $waitlistedApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'waitlisted')
            ->orderByDesc('decision_date')
            ->get();
            
        $submittedApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'submitted')
            ->orderByDesc('submission_date')
            ->get();
            
        $underReviewApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'under_review')
            ->orderByDesc('submission_date')
            ->get();
            
        $pendingDocumentsApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'pending_documents')
            ->orderByDesc('submission_date')
            ->get();
            
        $interviewScheduledApplications = Application::where('school_id', auth()->user()->school_id)
            ->where('status', 'interview_scheduled')
            ->orderByDesc('submission_date')
            ->get();
        
        // Pass all variables to the view
        return view('student.application-workflow.index', compact(
            'applications', 
            'statusCounts', 
            'pendingApplications', 
            'acceptedApplications',
            'rejectedApplications',
            'enrolledApplications',
            'waitlistedApplications',
            'submittedApplications',
            'underReviewApplications',
            'pendingDocumentsApplications',
            'interviewScheduledApplications'
        ));
    }

    public function create()
    {
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();
        $documentTypes = DocumentType::where('school_id', auth()->user()->school_id)
                                    ->where(function ($query) {
                                        $query->where('applies_to', 'application')
                                              ->orWhere('applies_to', 'both');
                                    })
                                    ->where('is_active', true)
                                    ->get();
        
        return view('student.application-workflow.create', compact('classes', 'academicYears', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'applying_for_class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            'is_boarder' => 'nullable|boolean',
            
            // Parent information (at least one parent required)
            'parent_first_name' => 'required|array|min:1',
            'parent_first_name.*' => 'required|string|max:255',
            'parent_last_name' => 'required|array|min:1',
            'parent_last_name.*' => 'required|string|max:255',
            'parent_relationship' => 'required|array|min:1',
            'parent_relationship.*' => 'required|in:father,mother,guardian,other',
            'parent_phone_primary' => 'required|array|min:1',
            'parent_phone_primary.*' => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('application-photos', 'public');
            }
            
            // Generate application number
            $applicationNumber = 'APP-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // Create application
            $application = Application::create([
                'school_id' => auth()->user()->school_id,
                'application_number' => $applicationNumber,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'applying_for_class_id' => $request->applying_for_class_id,
                'academic_year_id' => $request->academic_year_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $photoPath,
                'is_boarder' => $request->has('is_boarder'),
                'status' => 'submitted',
                'submission_date' => now(),
                'notes' => $request->notes,
            ]);
            
            // Create parents
            foreach ($request->parent_first_name as $index => $firstName) {
                ApplicationParent::create([
                    'application_id' => $application->id,
                    'first_name' => $firstName,
                    'last_name' => $request->parent_last_name[$index],
                    'relationship' => $request->parent_relationship[$index],
                    'email' => $request->parent_email[$index] ?? null,
                    'phone_primary' => $request->parent_phone_primary[$index],
                    'phone_secondary' => $request->parent_phone_secondary[$index] ?? null,
                    'address' => $request->parent_address[$index] ?? null,
                    'occupation' => $request->parent_occupation[$index] ?? null,
                    'is_primary_contact' => isset($request->parent_is_primary_contact[$index]),
                ]);
            }
            
            // Handle documents if uploaded
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $typeId => $file) {
                    $documentPath = $file->store('application-documents', 'public');
                    
                    $application->documents()->create([
                        'document_type_id' => $typeId,
                        'file_path' => $documentPath,
                        'file_name' => $file->getClientOriginalName(),
                        'upload_date' => now(),
                        'is_verified' => false,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('student.application-workflow.show', $application)
                ->with('success', 'Application submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            
            return back()->withInput()
                ->with('error', 'Error submitting application: ' . $e->getMessage());
        }
    }

    public function show(Application $application)
    {
        $application->load(['parents', 'documents.documentType', 'applyingForClass', 'academicYear']);
        
        $requiredDocuments = DocumentType::where('school_id', auth()->user()->school_id)
                                        ->where(function ($query) {
                                            $query->where('applies_to', 'application')
                                                  ->orWhere('applies_to', 'both');
                                        })
                                        ->where('is_required', true)
                                        ->get();
                                        
        $uploadedDocumentTypes = $application->documents->pluck('document_type_id')->toArray();
        $missingDocuments = $requiredDocuments->filter(function ($document) use ($uploadedDocumentTypes) {
            return !in_array($document->id, $uploadedDocumentTypes);
        });
        
        return view('student.application-workflow.show', compact('application', 'missingDocuments'));
    }

    public function edit(Application $application)
    {
        // Only allow editing if application is not enrolled
        if ($application->status === 'enrolled') {
            return redirect()->route('student.applications.show', $application)
                ->with('error', 'Cannot edit application after enrollment.');
        }
        
        $classes = TimetableSchoolClass::where('school_id', auth()->user()->school_id)->get();
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();
        $documentTypes = DocumentType::where('school_id', auth()->user()->school_id)
                                    ->where(function ($query) {
                                        $query->where('applies_to', 'application')
                                              ->orWhere('applies_to', 'both');
                                    })
                                    ->where('is_active', true)
                                    ->get();
        
        return view('student.application-workflow.edit', compact('application', 'classes', 'academicYears', 'documentTypes'));
    }

    public function update(Request $request, Application $application)
    {
        // Only allow updating if application is not enrolled
        if ($application->status === 'enrolled') {
            return redirect()->route('student.application-workflow.show', $application)
                ->with('error', 'Cannot update application after enrollment.');
        }
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'applying_for_class_id' => 'required|exists:timetable_school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            'is_boarder' => 'nullable|boolean',
            'status' => 'required|in:submitted,under_review,pending_documents,interview_scheduled,accepted,rejected,waitlisted',
        ]);

        DB::beginTransaction();
        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($application->photo && Storage::disk('public')->exists($application->photo)) {
                    Storage::disk('public')->delete($application->photo);
                }
                
                $photoPath = $request->file('photo')->store('application-photos', 'public');
            }
            
            // Update application
            $application->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'applying_for_class_id' => $request->applying_for_class_id,
                'academic_year_id' => $request->academic_year_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $request->hasFile('photo') ? $photoPath : $application->photo,
                'is_boarder' => $request->has('is_boarder'),
                'status' => $request->status,
                'notes' => $request->notes,
                'decision_date' => in_array($request->status, ['accepted', 'rejected', 'waitlisted']) ? now() : null,
                'processed_by' => in_array($request->status, ['accepted', 'rejected', 'waitlisted']) ? auth()->id() : null,
            ]);
            
            // Handle documents if uploaded
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $typeId => $file) {
                    $documentPath = $file->store('application-documents', 'public');
                    
                    $application->documents()->create([
                        'document_type_id' => $typeId,
                        'file_path' => $documentPath,
                        'file_name' => $file->getClientOriginalName(),
                        'upload_date' => now(),
                        'is_verified' => false,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('student.application-workflow.show', $application)
                ->with('success', 'Application updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Error updating application: ' . $e->getMessage());
        }
    }

    public function destroy(Application $application)
    {
        // Don't allow deleting submitted applications
        if ($application->status !== 'submitted') {
            return back()->with('error', 'Cannot delete application that has been processed.');
        }
        
        try {
            // Delete associated parents
            $application->parents()->delete();
            
            // Delete documents
            foreach ($application->documents as $document) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }
            
            // Delete photo
            if ($application->photo && Storage::disk('public')->exists($application->photo)) {
                Storage::disk('public')->delete($application->photo);
            }
            
            // Delete application
            $application->delete();
            
            return redirect()->route('student.applications.index')
                ->with('success', 'Application deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting application: ' . $e->getMessage());
        }
    }
    
    public function enroll(Application $application)
    {
        // Only allow enrollment for accepted applications
        if ($application->status !== 'accepted') {
            return redirect()->route('student.applicationworkflow.show', $application)
                ->with('error', 'Only accepted applications can be enrolled.');
        }
        
        try {
            // Create student from application
            $student = $application->convertToStudent();
            
            // Update application status
            $application->update([
                'status' => 'enrolled',
                'processed_by' => auth()->id(),
            ]);
            
            return redirect()->route('student.students.show', $student)
                ->with('success', 'Application enrolled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error enrolling application: ' . $e->getMessage());
        }
    }
    
    public function updateStatus(Request $request, Application $application)
    {
        $request->validate([
            'status' => 'required|in:under_review,pending_documents,interview_scheduled,accepted,rejected,waitlisted',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $application->update([
                'status' => $request->status,
                'notes' => $request->notes ? $application->notes . "\n\n" . now()->format('Y-m-d H:i') . " - " . $request->notes : $application->notes,
                'decision_date' => in_array($request->status, ['accepted', 'rejected', 'waitlisted']) ? now() : null,
                'processed_by' => auth()->id(),
            ]);
            
            return redirect()->route('student.application-workflow.show', $application)
                ->with('success', 'Application status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating application status: ' . $e->getMessage());
        }
    }
}