<!-- resources/views/teacher/assignments/submission.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Submission') }}
            </h2>
            <a href="{{ route('teacher.assignments.submissions', $assignment) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Submissions
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Assignment Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Assignment</h3>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $assignment->title }}</h2>
                            <p class="text-sm text-gray-600">
                                {{ $assignment->class->name ?? 'Unknown Class' }} | 
                                {{ $assignment->subject->name ?? 'Unknown Subject' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Due: {{ \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') }}
                                @if($assignment->due_time)
                                at {{ \Carbon\Carbon::parse($assignment->due_time)->format('h:i A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Max Score: {{ $assignment->max_score }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Student Submission -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-700">Student Submission</h3>
                                    <p class="text-md font-medium text-gray-900 mt-1">{{ $submission->student->name ?? 'Unknown Student' }}</p>
                                    <p class="text-sm text-gray-600">
                                        Submitted: {{ \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y h:i A') }}
                                    </p>
                                    @if($assignment->due_date && $submission->submitted_at)
                                        @php
                                            $dueDateTime = $assignment->due_time 
                                                ? \Carbon\Carbon::parse($assignment->due_date . ' ' . $assignment->due_time)
                                                : \Carbon\Carbon::parse($assignment->due_date)->endOfDay();
                                            $isLate = \Carbon\Carbon::parse($submission->submitted_at)->gt($dueDateTime);
                                        @endphp
                                        @if($isLate)
                                            <p class="text-sm text-red-600 font-medium">Submitted Late</p>
                                        @else
                                            <p class="text-sm text-green-600 font-medium">Submitted On Time</p>
                                        @endif
                                    @endif
                                </div>
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    @if($submission->status == 'submitted') bg-yellow-100 text-yellow-800
                                    @elseif($submission->status == 'graded') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </div>

                            <!-- Submission Text -->
                            @if($submission->submission_text)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Submission Text</h4>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 whitespace-pre-line">{{ $submission->submission_text }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Submission File -->
                            @if($submission->file_path)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Submission File</h4>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <a href="{{ asset('storage/' . $submission->file_path) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Download Submission
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Grading Section -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Grading</h3>
                            
                            @if($submission->status == 'graded' || $submission->status == 'returned')
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                                    <div class="mb-2">
                                        <span class="block text-sm font-medium text-gray-500">Score</span>
                                        <span class="block text-xl font-bold text-gray-900">{{ $submission->score }} / {{ $assignment->max_score }}</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($submission->score / $assignment->max_score) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    
                                    @if($submission->feedback)
                                    <div class="mt-4">
                                        <span class="block text-sm font-medium text-gray-500 mb-1">Feedback</span>
                                        <p class="text-gray-800 whitespace-pre-line">{{ $submission->feedback }}</p>
                                    </div>
                                    @endif
                                    
                                    <div class="mt-4">
                                        <span class="block text-sm font-medium text-gray-500 mb-1">Graded By</span>
                                        <p class="text-gray-800">{{ $submission->gradedBy->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ $submission->graded_at ? \Carbon\Carbon::parse($submission->graded_at)->format('M d, Y h:i A') : '' }}</p>
                                    </div>
                                </div>
                                
                                <form action="{{ route('teacher.assignments.grade', ['assignment' => $assignment, 'submission' => $submission]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label for="score" class="block text-sm font-medium text-gray-700">Update Score</label>
                                            <input type="number" id="score" name="score" min="0" max="{{ $assignment->max_score }}" value="{{ old('score', $submission->score) }}" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                        
                                        <div>
                                            <label for="feedback" class="block text-sm font-medium text-gray-700">Update Feedback</label>
                                            <textarea id="feedback" name="feedback" rows="4" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('feedback', $submission->feedback) }}</textarea>
                                        </div>
                                        
                                        <div class="flex items-center justify-end">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                Update Grading
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <form action="{{ route('teacher.assignments.grade', ['assignment' => $assignment, 'submission' => $submission]) }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label for="score" class="block text-sm font-medium text-gray-700">Score</label>
                                            <input type="number" id="score" name="score" min="0" max="{{ $assignment->max_score }}" value="{{ old('score', 0) }}" required class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <p class="text-xs text-gray-500 mt-1">Out of {{ $assignment->max_score }} points</p>
                                        </div>
                                        
                                        <div>
                                            <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                                            <textarea id="feedback" name="feedback" rows="6" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('feedback') }}</textarea>
                                        </div>
                                        
                                        <div class="flex items-center justify-end">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                Submit Grading
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>