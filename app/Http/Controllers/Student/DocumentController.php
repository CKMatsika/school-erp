<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\Document;
use App\Models\Student\DocumentType;
use App\Models\Student\Student;
use App\Models\Student\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::where('school_id', auth()->user()->school_id)
                                    ->with('documents')
                                    ->get();
        
        $documents = Document::with(['documentable', 'documentType'])
                           ->whereHas('documentType', function ($query) {
                               $query->where('school_id', auth()->user()->school_id);
                           })
                           ->orderByDesc('created_at')
                           ->paginate(20);
        
        return view('student.documents.index', compact('documentTypes', 'documents'));
    }

    public function create()
    {
        $documentTypes = DocumentType::where('school_id', auth()->user()->school_id)
                                    ->where('is_active', true)
                                    ->get();
        
        $students = Student::where('school_id', auth()->user()->school_id)
                          ->where('status', 'active')
                          ->orderBy('last_name')
                          ->orderBy('first_name')
                          ->get();
        
        $applications = Application::where('school_id', auth()->user()->school_id)
                                 ->whereIn('status', ['submitted', 'under_review', 'pending_documents', 'interview_scheduled', 'accepted'])
                                 ->orderByDesc('submission_date')
                                 ->get();
        
        return view('student.documents.create', compact('documentTypes', 'students', 'applications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'documentable_type' => 'required|in:student,application',
            'documentable_id' => 'required',
            'file' => 'required|file|max:10240', // 10MB max file size
            'expiry_date' => 'nullable|date',
            'is_verified' => 'nullable|boolean',
        ]);

        try {
            // Determine the documentable model
            $documentableClass = $request->documentable_type === 'student' ? Student::class : Application::class;
            $documentable = $documentableClass::findOrFail($request->documentable_id);
            
            // Store the file
            $file = $request->file('file');
            $filePath = $file->store("{$request->documentable_type}-documents", 'public');
            
            // Create document
            $document = new Document([
                'document_type_id' => $request->document_type_id,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'upload_date' => now(),
                'expiry_date' => $request->expiry_date,
                'is_verified' => $request->has('is_verified'),
                'verified_by' => $request->has('is_verified') ? auth()->id() : null,
                'verified_at' => $request->has('is_verified') ? now() : null,
            ]);
            
            $documentable->documents()->save($document);
            
            // If this is an application in 'pending_documents' status and all required documents are now uploaded,
            // update status to 'under_review'
            if ($request->documentable_type === 'application' && $documentable->status === 'pending_documents') {
                $requiredDocumentTypes = DocumentType::where('school_id', auth()->user()->school_id)
                                                   ->where('is_required', true)
                                                   ->where(function ($query) {
                                                       $query->where('applies_to', 'application')
                                                             ->orWhere('applies_to', 'both');
                                                   })
                                                   ->pluck('id')
                                                   ->toArray();
                
                $uploadedDocumentTypes = $documentable->documents->pluck('document_type_id')->toArray();
                $missingRequiredDocuments = array_diff($requiredDocumentTypes, $uploadedDocumentTypes);
                
                if (empty($missingRequiredDocuments)) {
                    $documentable->update([
                        'status' => 'under_review',
                        'notes' => ($documentable->notes ? $documentable->notes . "\n\n" : '') . 
                                  now()->format('Y-m-d H:i') . " - System: All required documents uploaded. Status changed to Under Review.",
                    ]);
                }
            }
            
            return redirect()->route('student.documents.show', $document)
                ->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    public function show(Document $document)
    {
        return view('student.documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $documentTypes = DocumentType::where('school_id', auth()->user()->school_id)
                                    ->where('is_active', true)
                                    ->get();
        
        return view('student.documents.edit', compact('document', 'documentTypes'));
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'file' => 'nullable|file|max:10240', // 10MB max file size
            'expiry_date' => 'nullable|date',
            'is_verified' => 'nullable|boolean',
        ]);

        try {
            // Update document properties
            $document->document_type_id = $request->document_type_id;
            $document->expiry_date = $request->expiry_date;
            
            // Handle file replacement if provided
            if ($request->hasFile('file')) {
                // Delete old file
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                
                // Store new file
                $file = $request->file('file');
                $documentableType = $document->documentable_type === Student::class ? 'student' : 'application';
                $filePath = $file->store("{$documentableType}-documents", 'public');
                
                $document->file_path = $filePath;
                $document->file_name = $file->getClientOriginalName();
                $document->upload_date = now();
            }
            
            // Handle verification
            if ($request->has('is_verified') && !$document->is_verified) {
                $document->is_verified = true;
                $document->verified_by = auth()->id();
                $document->verified_at = now();
            } elseif (!$request->has('is_verified') && $document->is_verified) {
                $document->is_verified = false;
                $document->verified_by = null;
                $document->verified_at = null;
            }
            
            $document->save();
            
            return redirect()->route('student.documents.show', $document)
                ->with('success', 'Document updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating document: ' . $e->getMessage());
        }
    }

    public function destroy(Document $document)
    {
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            // Delete document record
            $document->delete();
            
            return redirect()->route('student.documents.index')
                ->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }
    
    public function download(Document $document)
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Document file not found.');
        }
        
        // Log the download activity
        // activity()->performedOn($document)->log('downloaded');
        
        // Return the file for download
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
    
    public function verify(Document $document)
    {
        try {
            $document->update([
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
            
            return redirect()->route('student.documents.show', $document)
                ->with('success', 'Document verified successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error verifying document: ' . $e->getMessage());
        }
    }
    
    public function types()
    {
        $documentTypes = DocumentType::where('school_id', auth()->user()->school_id)->get();
        
        return view('student.documents.types', compact('documentTypes'));
    }
    
    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'applies_to' => 'required|in:application,enrollment,both',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $documentType = DocumentType::create([
                'school_id' => auth()->user()->school_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_required' => $request->has('is_required'),
                'applies_to' => $request->applies_to,
                'is_active' => $request->has('is_active'),
            ]);
            
            return redirect()->route('student.documents.types')
                ->with('success', 'Document type created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating document type: ' . $e->getMessage());
        }
    }
    
    public function updateType(Request $request, DocumentType $documentType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'applies_to' => 'required|in:application,enrollment,both',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $documentType->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_required' => $request->has('is_required'),
                'applies_to' => $request->applies_to,
                'is_active' => $request->has('is_active'),
            ]);
            
            return redirect()->route('student.documents.types')
                ->with('success', 'Document type updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating document type: ' . $e->getMessage());
        }
    }
    
    public function destroyType(DocumentType $documentType)
    {
        // Check if there are documents using this type
        if ($documentType->documents()->count() > 0) {
            return back()->with('error', 'Cannot delete document type with associated documents.');
        }
        
        try {
            $documentType->delete();
            
            return redirect()->route('student.documents.types')
                ->with('success', 'Document type deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting document type: ' . $e->getMessage());
        }
    }
}