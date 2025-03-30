<!-- resources/views/teacher/lessons/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lesson Plan Details') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('teacher.lessons.edit', $lessonPlan) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                @if($lessonPlan->status == 'ready')
                <form action="{{ route('teacher.lessons.update', $lessonPlan) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="conducted">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Mark as Conducted
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
                            <h1 class="text-2xl font-bold text-gray-900">{{ $lessonPlan->title }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $lessonPlan->class->name ?? 'Unknown Class' }} | 
                                {{ $lessonPlan->subject->name ?? 'Unknown Subject' }}
                            </p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($lessonPlan->status == 'draft') bg-gray-100 text-gray-800
                            @elseif($lessonPlan->status == 'ready') bg-green-100 text-green-800
                            @elseif($lessonPlan->status == 'conducted') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($lessonPlan->status) }}
                        </span>
                    </div>

                    <!-- Schedule -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Schedule</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Date</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($lessonPlan->lesson_date)->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-medium text-gray-500">Start Time</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($lessonPlan->start_time)->format('h:i A') }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-medium text-gray-500">End Time</span>
                                <span class="block text-base">{{ \Carbon\Carbon::parse($lessonPlan->end_time)->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson Details -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Learning Objectives</h3>
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <p class="text-gray-800 whitespace-pre-line">{{ $lessonPlan->objectives }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Resources</h3>
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <p class="text-gray-800 whitespace-pre-line">{{ $lessonPlan->resources ?: 'No resources specified.' }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Lesson Activities</h3>
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <p class="text-gray-800 whitespace-pre-line">{{ $lessonPlan->activities }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Assessment</h3>
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <p class="text-gray-800 whitespace-pre-line">{{ $lessonPlan->assessment ?: 'No assessment specified.' }}</p>
                            </div>
                        </div>

                        @if($lessonPlan->status == 'conducted')
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Reflection</h3>
                            @if($lessonPlan->reflection)
                                <div class="bg-white p-4 rounded-lg border border-gray-200">
                                    <p class="text-gray-800 whitespace-pre-line">{{ $lessonPlan->reflection }}</p>
                                </div>
                            @else
                                <form action="{{ route('teacher.lessons.update', $lessonPlan) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="reflection" rows="4" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Add your reflection here..."></textarea>
                                    <div class="mt-2 flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Save Reflection
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Associated Scheme of Work -->
            @if($lessonPlan->schemeOfWork)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Part of Scheme of Work</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $lessonPlan->schemeOfWork->title }}</h4>
                                <p class="text-sm text-gray-600">{{ $lessonPlan->schemeOfWork->term }} - {{ $lessonPlan->schemeOfWork->academic_year }}</p>
                            </div>
                            <a href="{{ route('teacher.schemes.show', $lessonPlan->schemeOfWork) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View Scheme</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>