<!-- resources/views/teacher/schemes/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Scheme of Work Details') }}
            </h2>
            <div class="flex gap-2">
                @if($scheme->status == 'draft' || $scheme->status == 'rejected')
                <a href="{{ route('teacher.schemes.edit', $scheme) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                @endif
                
                @if($scheme->status == 'draft')
                <form action="{{ route('teacher.schemes.submit-for-approval', $scheme) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                        </svg>
                        Submit for Approval
                    </button>
                </form>
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
                            <h1 class="text-2xl font-bold text-gray-900">{{ $scheme->title }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $scheme->class->name ?? 'Unknown Class' }} | 
                                {{ $scheme->subject->name ?? 'Unknown Subject' }}
                            </p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($scheme->status == 'draft') bg-gray-100 text-gray-800
                            @elseif($scheme->status == 'submitted') bg-yellow-100 text-yellow-800
                            @elseif($scheme->status == 'approved') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($scheme->status) }}
                        </span>
                    </div>

                    <!-- Term and Date Range -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Term and Duration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Term</span>
                                <span class="block text-base">{{ $scheme->term }} - {{ $scheme->academic_year }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Start Date</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($scheme->start_date)->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-medium text-gray-500">End Date</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($scheme->end_date)->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Description</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-800 whitespace-pre-line">{{ $scheme->description ?: 'No description provided.' }}</p>
                        </div>
                    </div>

                    <!-- Approval Info -->
                    @if($scheme->status == 'approved' || $scheme->status == 'rejected')
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Approval Information</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="mb-2">
                                <span class="block text-sm font-medium text-gray-500">Approved/Rejected By</span>
                                <span class="block text-base">{{ $scheme->approvedBy->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="block text-sm font-medium text-gray-500">Date</span>
                                <span class="block text-base">{{ $scheme->approved_at ? \Carbon\Carbon::parse($scheme->approved_at)->format('M d, Y') : 'N/A' }}</span>
                            </div>
                            @if($scheme->feedback)
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Feedback</span>
                                <p class="text-gray-800 whitespace-pre-line mt-1">{{ $scheme->feedback }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Lesson Plans -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-medium text-gray-700">Lesson Plans</h3>
                            <a href="{{ route('teacher.lessons.create', ['scheme_id' => $scheme->id]) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Add Lesson Plan
                            </a>
                        </div>
                        
                        @if($lessonPlans->isEmpty())
                            <p class="text-gray-500 text-center py-4 bg-gray-50 rounded-lg">No lesson plans added yet.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($lessonPlans as $lesson)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $lesson->title }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($lesson->lesson_date)->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($lesson->start_time)->format('h:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($lesson->end_time)->format('h:i A') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($lesson->status == 'draft') bg-gray-100 text-gray-800
                                                    @elseif($lesson->status == 'ready') bg-green-100 text-green-800
                                                    @elseif($lesson->status == 'conducted') bg-blue-100 text-blue-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst($lesson->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('teacher.lessons.show', $lesson) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>