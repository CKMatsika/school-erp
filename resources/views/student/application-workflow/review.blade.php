<!-- resources/views/student/application-workflow/review.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Review Application') }}: {{ $application->application_number }}
            </h2>
            <div class="flex space-x-2">
                @if($application->status === 'accepted')
                    <a href="{{ route('student.application-workflow.enroll', $application) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Enroll Student
                    </a>
                @endif
                <a href="{{ route('student.application-workflow.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Application Status & Controls -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Application Status</h3>
                                <div class="flex items-center">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $application->getStatusColorClass() }} mr-2">
                                        {{ $application->getStatusLabel() }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Submitted {{ $application->submission_date->diffForHumans() }}
                                    </span>
                                </div>
                                
                                @if($application->status === 'interview_scheduled')
                                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8
                                                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-blue-700">
                                                    <strong>Interview Scheduled</strong><br>
                                                    @php
                                                        // Extract interview details from notes
                                                        $interviewDetails = '';
                                                        if (preg_match('/Interview scheduled for (.+?)(?:\n|$)/s', $application->notes ?? '', $matches)) {
                                                            $interviewDetails = $matches[1];
                                                        }
                                                    @endphp
                                                    {{ $interviewDetails ?: 'Details not available' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($application->processed_by)
                                    <div class="mt-2 text-sm text-gray-500">
                                        Processed by: {{ $application->processedBy->name ?? 'System' }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Application Controls -->
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">Application Actions</h3>
                                
                                @if(in_array($application->status, ['submitted', 'under_review', 'pending_documents', 'interview_scheduled']))
                                    <div class="space-y-3">
                                        <!-- Update Status Form -->
                                        <form action="{{ route('student.application-workflow.updateStatus', $application) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label for="status" class="block text-sm font-medium text-gray-700">Update Status</label>
                                                <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                                    <option value="pending_documents" {{ $application->status == 'pending_documents' ? 'selected' : '' }}>Pending Documents</option>
                                                    <option value="interview_scheduled" {{ $application->status == 'interview_scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                                    <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                                    <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    <option value="waitlisted" {{ $application->status == 'waitlisted' ? 'selected' : '' }}>Waitlisted</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                                <textarea name="notes" id="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                            </div>
                                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update Status
                                            </button>
                                        </form>
                                    
                                        <!-- Schedule Interview Button -->
                                        <button type="button" onclick="toggleModal('interview-modal')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Schedule Interview
                                        </button>
                                        
                                        <!-- Request Documents Button -->
                                        <button type="button" onclick="toggleModal('documents-modal')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Request Documents
                                        </button>
                                    </div>
                                @elseif($application->status === 'accepted')
                                    <div>
                                        <a href="{{ route('student.application-workflow.enroll', $application) }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Enroll Student
                                        </a>
                                        <p class="mt-2 text-sm text-gray-500 text-center">
                                            This application has been accepted. Click above to complete the enrollment process.
                                        </p>
                                    </div>
                                @elseif($application->status === 'enrolled')
                                    <div class="bg-green-50 border border-green-200 rounded-md p-4 text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-green-800">
                                            This student has been enrolled.
                                        </p>
                                        <a href="{{ route('student.students.show', $application->student_id) }}" class="mt-2 inline-flex items-center text-sm font-medium text-green-600 hover:text-green-500">
                                            View Student Record
                                            <svg class="ml-1 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-center">
                                        <p class="text-sm text-gray-700">
                                            This application has been {{ strtolower($application->getStatusLabel()) }}.
                                        </p>
                                        <form action="{{ route('student.application-workflow.updateStatus', $application) }}" method="POST" class="mt-3">
                                            @csrf
                                            <input type="hidden" name="status" value="under_review">
                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Reopen Application
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Required Documents Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Required Documents</h3>
                            
                            @if($missingDocuments->count() > 0)
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                <strong>Missing Documents:</strong> {{ $missingDocuments->count() }} required {{ Str::plural('document', $missingDocuments->count()) }} missing.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <ul class="space-y-2">
                                    @foreach($missingDocuments as $document)
                                        <li class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                <span class="text-sm text-gray-700">{{ $document->name }}</span>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Missing
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                <div class="mt-4">
                                    <button type="button" onclick="toggleModal('documents-modal')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Request Missing Documents
                                    </button>
                                </div>
                            @else
                                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-green-700">
                                                All required documents have been submitted.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="{{ route('student.documents.create', ['documentable_type' => 'application', 'documentable_id' => $application->id]) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Upload Additional Documents
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Application Notes -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Application Notes</h3>
                            
                            @if($application->notes)
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ $application->notes }}</pre>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic">No notes have been added to this application.</p>
                            @endif
                            
                            <!-- Quick Note Form -->
                            <form action="{{ route('student.application-workflow.updateStatus', $application) }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="status" value="{{ $application->status }}">
                                <div>
                                    <label for="quick_note" class="block text-sm font-medium text-gray-700">Add a note</label>
                                    <textarea name="notes" id="quick_note" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Add Note
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-gray-700">Applicant Information</h3>
                                <a href="{{ route('student.applications.edit', $application) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Edit Application</a>
                            </div>
                            
                            <div class="mt-4 flex items-center">
                                @if($application->photo)
                                    <div class="flex-shrink-0 h-24 w-24">
                                        <img class="h-24 w-24 rounded-full object-cover" 
                                            src="{{ asset('storage/' . $application->photo) }}" 
                                            alt="{{ $application->first_name }}">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="ml-6">
                                    <h4 class="text-xl font-medium text-gray-900">{{ $application->first_name }} {{ $application->last_name }}</h4>
                                    <div class="mt-1 flex items-center">
                                        <span class="text-sm text-gray-500 mr-3">
                                            <span class="font-medium">Application #:</span> {{ $application->application_number }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <span class="font-medium">Submitted:</span> {{ $application->submission_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Applying For Class</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->applyingForClass->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Academic Year</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->academicYear->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Gender</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($application->gender) }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date of Birth</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $application->date_of_birth->format('M d, Y') }}
                                        ({{ $application->date_of_birth->age }} years)
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->email ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->phone ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Address</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->address ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Boarding Status</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->is_boarder ? 'Boarder' : 'Day Scholar' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Parents/Guardians Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Parents/Guardians</h3>
                            
                            @if($application->parents->isEmpty())
                                <p class="text-sm text-gray-500 italic">No parent/guardian information available.</p>
                            @else
                                <div class="space-y-6">
                                    @foreach($application->parents as $parent)
                                        <div class="bg-gray-50 p-4 rounded-md">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="text-base font-medium text-gray-900">
                                                        {{ $parent->first_name }} {{ $parent->last_name }}
                                                    </h4>
                                                    <p class="text-sm text-gray-500">{{ ucfirst($parent->relationship) }}</p>
                                                </div>
                                                @if($parent->is_primary_contact)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Primary Contact
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-gray-500">Contact Information</p>
                                                    <p class="mt-1 text-gray-900">Phone: {{ $parent->phone_primary }}</p>
                                                    @if($parent->phone_secondary)
                                                        <p class="text-gray-900">Alt. Phone: {{ $parent->phone_secondary }}</p>
                                                    @endif
                                                    @if($parent->email)
                                                        <p class="text-gray-900">Email: {{ $parent->email }}</p>
                                                    @endif
                                                </div>
                                                
                                                <div>
                                                    <p class="font-medium text-gray-500">Additional Information</p>
                                                    @if($parent->occupation)
                                                        <p class="mt-1 text-gray-900">Occupation: {{ $parent->occupation }}</p>
                                                    @endif
                                                    @if($parent->address)
                                                        <p class="text-gray-900">Address: {{ $parent->address }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Uploaded Documents -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-700">Uploaded Documents</h3>
                                <a href="{{ route('student.documents.create', ['documentable_type' => 'application', 'documentable_id' => $application->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Upload New Document
                                </a>
                            </div>
                            
                            @if($application->documents->isEmpty())
                                <p class="text-sm text-gray-500 italic">No documents have been uploaded yet.</p>
                            @else
                                <ul class="divide-y divide-gray-200">
                                    @foreach($application->documents as $document)
                                        <li class="py-3 flex justify-between items-center">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $document->documentType->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        Uploaded: {{ $document->upload_date->format('M d, Y') }} | 
                                                        File: {{ $document->file_name }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                @if($document->is_verified)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Verified
                                                    </span>
                                                @else
                                                    <form action="{{ route('student.documents.verify', $document) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                                            Verify
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('student.documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                    Download
                                                </a>
                                            </div>
                                        <!-- resources/views/student/application-workflow/review.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Review Application') }}: {{ $application->application_number }}
            </h2>
            <div class="flex space-x-2">
                @if($application->status === 'accepted')
                    <a href="{{ route('student.application-workflow.enroll', $application) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Enroll Student
                    </a>
                @endif
                <a href="{{ route('student.application-workflow.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Application Status & Controls -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Application Status</h3>
                                <div class="flex items-center">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $application->getStatusColorClass() }} mr-2">
                                        {{ $application->getStatusLabel() }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Submitted {{ $application->submission_date->diffForHumans() }}
                                    </span>
                                </div>
                                
                                @if($application->status === 'interview_scheduled')
                                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8
                                                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-md p-3">
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview Scheduling Modal -->
    <div class="fixed inset-0 z-10 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" id="interview-modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('student.application-workflow.scheduleInterview', $application) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Schedule Interview
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Schedule an interview for {{ $application->first_name }} {{ $application->last_name }}.
                            </p>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <div>
                                <label for="interview_date" class="block text-sm font-medium text-gray-700">Interview Date</label>
                                <input type="date" name="interview_date" id="interview_date" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <div>
                                <label for="interview_time" class="block text-sm font-medium text-gray-700">Interview Time</label>
                                <input type="time" name="interview_time" id="interview_time" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <div>
                                <label for="interview_location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" name="interview_location" id="interview_location" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <div>
                                <label for="interviewer" class="block text-sm font-medium text-gray-700">Interviewer</label>
                                <input type="text" name="interviewer" id="interviewer" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="send_notification" name="send_notification" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="send_notification" class="font-medium text-gray-700">Send Notification</label>
                                    <p class="text-gray-500">Send email/SMS notification to the applicant about this interview.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Schedule Interview
                        </button>
                        <button type="button" onclick="toggleModal('interview-modal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Document Request Modal -->
    <div class="fixed inset-0 z-10 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" id="documents-modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('student.application-workflow.requestDocuments', $application) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Request Documents
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Request additional documents from the applicant.
                            </p>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Documents</label>
                            <div class="bg-gray-50 p-3 rounded-md max-h-48 overflow-y-auto">
                                @foreach($missingDocuments as $document)
                                    <div class="flex items-start mb-2">
                                        <div class="flex items-center h-5">
                                            <input id="document_{{ $document->id }}" name="document_types[]" value="{{ $document->id }}" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="document_{{ $document->id }}" class="font-medium text-gray-700">{{ $document->name }}</label>
                                            @if($document->description)
                                                <p class="text-gray-500">{{ $document->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4">
                                <label for="message" class="block text-sm font-medium text-gray-700">Message to Applicant</label>
                                <textarea name="message" id="message" rows="4" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                            
                            <div class="mt-4 flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="send_notification" name="send_notification" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="send_notification" class="font-medium text-gray-700">Send Notification</label>
                                    <p class="text-gray-500">Send email/SMS notification to the applicant about required documents.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Request Documents
                        </button>
                        <button type="button" onclick="toggleModal('documents-modal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout><div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-blue-700">
                                                    <strong>Interview Scheduled</strong><br>
                                                    @php
                                                        // Extract interview details from notes
                                                        $interviewDetails = '';
                                                        if (preg_match('/Interview scheduled for (.+?)(?:\n|$)/s', $application->notes ?? '', $matches)) {
                                                            $interviewDetails = $matches[1];
                                                        }
                                                    @endphp
                                                    {{ $interviewDetails ?: 'Details not available' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($application->processed_by)
                                    <div class="mt-2 text-sm text-gray-500">
                                        Processed by: {{ $application->processedBy->name ?? 'System' }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Application Controls -->
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-lg font-semibold text-gray-700 mb-3">Application Actions</h3>
                                
                                @if(in_array($application->status, ['submitted', 'under_review', 'pending_documents', 'interview_scheduled']))
                                    <div class="space-y-3">
                                        <!-- Update Status Form -->
                                        <form action="{{ route('student.application-workflow.updateStatus', $application) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label for="status" class="block text-sm font-medium text-gray-700">Update Status</label>
                                                <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                                    <option value="pending_documents" {{ $application->status == 'pending_documents' ? 'selected' : '' }}>Pending Documents</option>
                                                    <option value="interview_scheduled" {{ $application->status == 'interview_scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                                    <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                                    <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    <option value="waitlisted" {{ $application->status == 'waitlisted' ? 'selected' : '' }}>Waitlisted</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                                <textarea name="notes" id="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                            </div>
                                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Update Status
                                            </button>
                                        </form>
                                    
                                        <!-- Schedule Interview Button -->
                                        <button type="button" onclick="toggleModal('interview-modal')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Schedule Interview
                                        </button>
                                        
                                        <!-- Request Documents Button -->
                                        <button type="button" onclick="toggleModal('documents-modal')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Request Documents
                                        </button>
                                    </div>
                                @elseif($application->status === 'accepted')
                                    <div>
                                        <a href="{{ route('student.application-workflow.enroll', $application) }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Enroll Student
                                        </a>
                                        <p class="mt-2 text-sm text-gray-500 text-center">
                                            This application has been accepted. Click above to complete the enrollment process.
                                        </p>
                                    </div>
                                @elseif($application->status === 'enrolled')
                                    <div class="bg-green-50 border border-green-200 rounded-md p-4 text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-green-800">
                                            This student has been enrolled.
                                        </p>
                                        <a href="{{ route('student.students.show', $application->student_id) }}" class="mt-2 inline-flex items-center text-sm font-medium text-green-600 hover:text-green-500">
                                            View Student Record
                                            <svg class="ml-1 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-center">
                                        <p class="text-sm text-gray-700">
                                            This application has been {{ strtolower($application->getStatusLabel()) }}.
                                        </p>
                                        <form action="{{ route('student.application-workflow.updateStatus', $application) }}" method="POST" class="mt-3">
                                            @csrf
                                            <input type="hidden" name="status" value="under_review">
                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Reopen Application
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Required Documents Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Required Documents</h3>
                            
                            @if($missingDocuments->count() > 0)
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                <strong>Missing Documents:</strong> {{ $missingDocuments->count() }} required {{ Str::plural('document', $missingDocuments->count()) }} missing.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <ul class="space-y-2">
                                    @foreach($missingDocuments as $document)
                                        <li class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                <span class="text-sm text-gray-700">{{ $document->name }}</span>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Missing
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                <div class="mt-4">
                                    <button type="button" onclick="toggleModal('documents-modal')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Request Missing Documents
                                    </button>
                                </div>
                            @else
                                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-green-700">
                                                All required documents have been submitted.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="{{ route('student.documents.create', ['documentable_type' => 'application', 'documentable_id' => $application->id]) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Upload Additional Documents
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Application Notes -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Application Notes</h3>
                            
                            @if($application->notes)
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ $application->notes }}</pre>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic">No notes have been added to this application.</p>
                            @endif
                            
                            <!-- Quick Note Form -->
                            <form action="{{ route('student.application-workflow.updateStatus', $application) }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="status" value="{{ $application->status }}">
                                <div>
                                    <label for="quick_note" class="block text-sm font-medium text-gray-700">Add a note</label>
                                    <textarea name="notes" id="quick_note" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Add Note
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-gray-700">Applicant Information</h3>
                                <a href="{{ route('student.applications.edit', $application) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Edit Application</a>
                            </div>
                            
                            <div class="mt-4 flex items-center">
                                @if($application->photo)
                                    <div class="flex-shrink-0 h-24 w-24">
                                        <img class="h-24 w-24 rounded-full object-cover" 
                                            src="{{ asset('storage/' . $application->photo) }}" 
                                            alt="{{ $application->first_name }}">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="ml-6">
                                    <h4 class="text-xl font-medium text-gray-900">{{ $application->first_name }} {{ $application->last_name }}</h4>
                                    <div class="mt-1 flex items-center">
                                        <span class="text-sm text-gray-500 mr-3">
                                            <span class="font-medium">Application #:</span> {{ $application->application_number }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <span class="font-medium">Submitted:</span> {{ $application->submission_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Applying For Class</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->applyingForClass->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Academic Year</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->academicYear->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Gender</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($application->gender) }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date of Birth</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $application->date_of_birth->format('M d, Y') }}
                                        ({{ $application->date_of_birth->age }} years)
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->email ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->phone ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Address</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->address ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Boarding Status</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $application->is_boarder ? 'Boarder' : 'Day Scholar' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Parents/Guardians Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Parents/Guardians</h3>
                            
                            @if($application->parents->isEmpty())
                                <p class="text-sm text-gray-500 italic">No parent/guardian information available.</p>
                            @else
                                <div class="space-y-6">
                                    @foreach($application->parents as $parent)
                                        <div class="bg-gray-50 p-4 rounded-md">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h4 class="text-base font-medium text-gray-900">
                                                        {{ $parent->first_name }} {{ $parent->last_name }}
                                                    </h4>
                                                    <p class="text-sm text-gray-500">{{ ucfirst($parent->relationship) }}</p>
                                                </div>
                                                @if($parent->is_primary_contact)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Primary Contact
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-gray-500">Contact Information</p>
                                                    <p class="mt-1 text-gray-900">Phone: {{ $parent->phone_primary }}</p>
                                                    @if($parent->phone_secondary)
                                                        <p class="text-gray-900">Alt. Phone: {{ $parent->phone_secondary }}</p>
                                                    @endif
                                                    @if($parent->email)
                                                        <p class="text-gray-900">Email: {{ $parent->email }}</p>
                                                    @endif
                                                </div>
                                                
                                                <div>
                                                    <p class="font-medium text-gray-500">Additional Information</p>
                                                    @if($parent->occupation)
                                                        <p class="mt-1 text-gray-900">Occupation: {{ $parent->occupation }}</p>
                                                    @endif
                                                    @if($parent->address)
                                                        <p class="text-gray-900">Address: {{ $parent->address }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Uploaded Documents -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-700">Uploaded Documents</h3>
                                <a href="{{ route('student.documents.create', ['documentable_type' => 'application', 'documentable_id' => $application->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Upload New Document
                                </a>
                            </div>
                            
                            @if($application->documents->isEmpty())
                                <p class="text-sm text-gray-500 italic">No documents have been uploaded yet.</p>
                            @else
                                <ul class="divide-y divide-gray-200">
                                    @foreach($application->documents as $document)
                                        <li class="py-3 flex justify-between items-center">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $document->documentType->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        Uploaded: {{ $document->upload_date->format('M d, Y') }} | 
                                                        File: {{ $document->file_name }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                @if($document->is_verified)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Verified
                                                    </span>
                                                @else
                                                    <form action="{{ route('student.documents.verify', $document) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                                            Verify
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('student.documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                    Download
                                                </a>
                                            </div>
                                        <!-- resources/views/student/application-workflow/review.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Review Application') }}: {{ $application->application_number }}
            </h2>
            <div class="flex space-x-2">
                @if($application->status === 'accepted')
                    <a href="{{ route('student.application-workflow.enroll', $application) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Enroll Student
                    </a>
                @endif
                <a href="{{ route('student.application-workflow.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Application Status & Controls -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Application Status</h3>
                                <div class="flex items-center">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $application->getStatusColorClass() }} mr-2">
                                        {{ $application->getStatusLabel() }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Submitted {{ $application->submission_date->diffForHumans() }}
                                    </span>
                                </div>
                                
                                @if($application->status === 'interview_scheduled')
                                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8