<!-- resources/views/teacher/assignments/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assignment Details') }}
            </h2>
            <div class="flex gap-2">
                @if($assignment->status == 'draft')
                <a href="{{ route('teacher.assignments.edit', $assignment) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                @endif
                
                @if($assignment->status == 'published')
                <a href="{{ route('teacher.assignments.submissions', $assignment) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                    View Submissions
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Title and Status -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $assignment->class->name ?? 'Unknown Class' }} | 
                                {{ $assignment->subject->name ?? 'Unknown Subject' }}
                            </p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($assignment->status == 'draft') bg-gray-100 text-gray-800
                            @elseif($assignment->status == 'published') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($assignment->status) }}
                        </span>
                    </div>

                    <!-- Due Date -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Due Date</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Date</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') }}</span>
                            </div>
                            @if($assignment->due_time)
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Time</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($assignment->due_time)->format('h:i A') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Description</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-800 whitespace-pre-line">{{ $assignment->description }}</p>
                        </div>
                    </div>

                    <!-- Instructions -->
                    @if($assignment->instructions)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Instructions</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-800 whitespace-pre-line">{{ $assignment->instructions }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Submission Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Submission Information</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Submission Type</span>
                                    <span class="block text-base">{{ ucfirst($assignment->submission_type) }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Maximum Score</span>
                                    <span class="block text-base">{{ $assignment->max_score }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Marking Scheme</span>
                                    <span class="block text-base">
                                        @if($assignment->markingScheme)
                                            {{ $assignment->markingScheme->title }}
                                        @else
                                            None
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment -->
                    @if($assignment->attachment_path)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Attachment</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <a href="{{ asset('storage/' . $assignment->attachment_path) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download Attachment
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Submissions Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Submissions Summary</h3>
                    
                    @if($assignment->status == 'draft')
                        <p class="text-gray-500">Submit this assignment to allow students to view and submit their work.</p>
                    @elseif($assignment->submissions->isEmpty())
                        <p class="text-gray-500">No submissions yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted At</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($assignment->submissions->take(5) as $submission)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $submission->student->name ?? 'Unknown Student' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($submission->status == 'submitted') bg-yellow-100 text-yellow-800
                                                @elseif($submission->status == 'graded') bg-green-100 text-green-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($submission->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($submission->status == 'graded')
                                                    {{ $submission->score }} / {{ $assignment->max_score }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('teacher.assignments.submission', ['assignment' => $assignment, 'submission' => $submission]) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('teacher.assignments.submissions', $assignment) }}" class="text-indigo-600 hover:text-indigo-900">View All Submissions</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>