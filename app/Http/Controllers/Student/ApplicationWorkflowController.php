<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Application;
use App\Models\Student\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationWorkflowController extends Controller
{
    public function index()
    {
        $pendingApplications = Application::where('school_id', auth()->user()->school_id)
                                        ->whereIn('status', ['submitted', 'under_review', 'pending_documents', 'interview_scheduled'])
                                        ->orderByDesc('submission_date')
                                        ->paginate(10);
        
        $acceptedApplications = Application::where('school_id', auth()->user()->school_id)
                                         ->where('status', 'accepted')
                                         ->orderByDesc('decision_date')
                                         ->paginate(10);
        
        $rejectedApplications = Application::where('school_id', auth()->user()->school_id)
                                         ->whereIn('status', ['rejected', 'waitlisted'])
                                         ->orderByDesc('decision_date')
                                         ->paginate(10);
        
        return view('student.application-workflow.index', compact('pendingApplications', 'acceptedApplications', 'rejectedApplications'));
    }
    
    public function review(Application $application)
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
        
        return view('student.application-workflow.review', compact('application', 'missingDocuments'));
    }
    
    public function updateStatus(Request $request, Application $application)
    {
        $request->validate([
            'status' => 'required|in:under_review,pending_documents,interview_scheduled,accepted,rejected,waitlisted',
            'notes' => 'nullable|string',
        ]);
        
        try {
            // Add timestamp to notes
            $notesWithTimestamp = now()->format('Y-m-d H:i') . " - " . auth()->user()->name . ": " . $request->notes;
            $updatedNotes = $application->notes ? $application->notes . "\n\n" . $notesWithTimestamp : $notesWithTimestamp;
            
            $application->update([
                'status' => $request->status,
                'notes' => $updatedNotes,
                'decision_date' => in_array($request->status, ['accepted', 'rejected', 'waitlisted']) ? now() : $application->decision_date,
                'processed_by' => auth()->id(),
            ]);
            
            return redirect()->route('student.application-workflow.review', $application)
                ->with('success', 'Application status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating application status: ' . $e->getMessage());
        }
    }
    
    public function scheduleInterview(Request $request, Application $application)
    {
        $request->validate([
            'interview_date' => 'required|date|after:today',
            'interview_time' => 'required',
            'interview_location' => 'required|string',
            'interviewer' => 'required|string',
            'send_notification' => 'nullable|boolean',
        ]);
        
        try {
            // Format interview details
            $interviewDetails = "Interview scheduled for " . date('F j, Y', strtotime($request->interview_date)) . 
                               " at " . $request->interview_time . 
                               " in " . $request->interview_location . 
                               " with " . $request->interviewer;
            
            // Add timestamp to notes
            $notesWithTimestamp = now()->format('Y-m-d H:i') . " - " . auth()->user()->name . ": " . $interviewDetails;
            $updatedNotes = $application->notes ? $application->notes . "\n\n" . $notesWithTimestamp : $notesWithTimestamp;
            
            $application->update([
                'status' => 'interview_scheduled',
                'notes' => $updatedNotes,
                'processed_by' => auth()->id(),
            ]);
            
            // If send notification is checked, send email/SMS notification (would need to implement this)
            if ($request->has('send_notification')) {
                // Placeholder for notification logic
                // You could integrate with your notification system here
            }
            
            return redirect()->route('student.application-workflow.review', $application)
                ->with('success', 'Interview scheduled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error scheduling interview: ' . $e->getMessage());
        }
    }
    
    public function requestDocuments(Request $request, Application $application)
    {
        $request->validate([
            'document_types' => 'required|array',
            'document_types.*' => 'exists:document_types,id',
            'message' => 'required|string',
            'send_notification' => 'nullable|boolean',
        ]);
        
        try {
            // Get document type names
            $documentTypes = DocumentType::whereIn('id', $request->document_types)->pluck('name')->toArray();
            $documentList = implode(", ", $documentTypes);
            
            // Format documents request
            $documentsRequest = "Documents requested: " . $documentList . "\n\nMessage: " . $request->message;
            
            // Add timestamp to notes
            $notesWithTimestamp = now()->format('Y-m-d H:i') . " - " . auth()->user()->name . ": " . $documentsRequest;
            $updatedNotes = $application->notes ? $application->notes . "\n\n" . $notesWithTimestamp : $notesWithTimestamp;
            
            $application->update([
                'status' => 'pending_documents',
                'notes' => $updatedNotes,
                'processed_by' => auth()->id(),
            ]);
            
            // If send notification is checked, send email/SMS notification (would need to implement this)
            if ($request->has('send_notification')) {
                // Placeholder for notification logic
                // You could integrate with your notification system here
            }
            
            return redirect()->route('student.application-workflow.review', $application)
                ->with('success', 'Document request sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error requesting documents: ' . $e->getMessage());
        }
    }
    
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
            'status' => 'required|in:under_review,pending_documents,interview_scheduled,accepted,rejected,waitlisted',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            $applications = Application::whereIn('id', $request->application_ids)->get();
            
            foreach ($applications as $application) {
                // Add timestamp to notes
                $notesWithTimestamp = now()->format('Y-m-d H:i') . " - " . auth()->user()->name . ": " . $request->notes;
                $updatedNotes = $application->notes ? $application->notes . "\n\n" . $notesWithTimestamp : $notesWithTimestamp;
                
                $application->update([
                    'status' => $request->status,
                    'notes' => $updatedNotes,
                    'decision_date' => in_array($request->status, ['accepted', 'rejected', 'waitlisted']) ? now() : $application->decision_date,
                    'processed_by' => auth()->id(),
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('student.application-workflow.index')
                ->with('success', count($applications) . ' applications updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error updating applications: ' . $e->getMessage());
        }
    }
    
    public function enroll(Application $application)
    {
        // Only allow enrollment for accepted applications
        if ($application->status !== 'accepted') {
            return redirect()->route('student.application-workflow.review', $application)
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
                ->with('success', 'Student enrolled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error enrolling student: ' . $e->getMessage());
        }
    }
}