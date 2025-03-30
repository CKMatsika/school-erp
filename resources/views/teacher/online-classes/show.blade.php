<!-- resources/views/teacher/online-classes/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Online Class Details') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('teacher.online-classes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
                
                @if($onlineClass->status == 'scheduled' || $onlineClass->status == 'ongoing')
                <a href="{{ route('teacher.online-classes.edit', $onlineClass) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                
                @if($onlineClass->status == 'scheduled')
                <form action="{{ route('teacher.online-classes.start', $onlineClass) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        Start Class
                    </button>
                </form>
                @elseif($onlineClass->status == 'ongoing')
                <form action="{{ route('teacher.online-classes.end', $onlineClass) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                        </svg>
                        End Class
                    </button>
                </form>
                @endif
                @endif
                
                @if($onlineClass->status == 'scheduled' || $onlineClass->status == 'ongoing')
                <a href="{{ $onlineClass->meeting_link }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                    </svg>
                    Join Class
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
                            <h1 class="text-2xl font-bold text-gray-900">{{ $onlineClass->title }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $onlineClass->class->name ?? 'Unknown Class' }} | 
                                {{ $onlineClass->subject->name ?? 'Unknown Subject' }} | 
                                {{ ucfirst($onlineClass->platform) }}
                            </p>
                        </div>
                        <div class="flex items-center">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($onlineClass->status == 'scheduled') bg-blue-100 text-blue-800
                                @elseif($onlineClass->status == 'ongoing') bg-green-100 text-green-800
                                @elseif($onlineClass->status == 'completed') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($onlineClass->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Class Time Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Schedule</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Date</span>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-base">{{ $onlineClass->start_time->format('l, F j, Y') }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Time</span>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-base">
                                            {{ $onlineClass->start_time->format('h:i A') }} - {{ $onlineClass->end_time->format('h:i A') }}
                                            <span class="text-sm text-gray-500 ml-2">({{ $onlineClass->start_time->diffInMinutes($onlineClass->end_time) }} minutes)</span>
                                        </span>
                                    </div>
                                </div>

                                @if($onlineClass->recurring)
                                <div class="md:col-span-2">
                                    <span class="block text-sm font-medium text-gray-500">Recurrence</span>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span class="text-base">
                                            {{ ucfirst($onlineClass->recurrence_pattern) }} until {{ $onlineClass->recurrence_end_date->format('F j, Y') }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Meeting Information</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Meeting Link</span>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        <a href="{{ $onlineClass->meeting_link }}" class="text-indigo-600 hover:text-indigo-900 break-all" target="_blank">
                                            {{ $onlineClass->meeting_link }}
                                        </a>
                                    </div>
                                </div>

                                @if($onlineClass->password)
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Meeting Password</span>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="text-base">{{ $onlineClass->password }}</span>
                                    </div>
                                </div>
                                @endif

                                @if($onlineClass->recording_url && ($onlineClass->status == 'completed' || $onlineClass->status == 'ongoing'))
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Recording</span>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <a href="{{ $onlineClass->recording_url }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">
                                            View Recording
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($onlineClass->description)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Description</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-800 whitespace-pre-line">{{ $onlineClass->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Special Instructions -->
                    @if($onlineClass->instructions)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Special Instructions</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-800 whitespace-pre-line">{{ $onlineClass->instructions }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Attendees -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Attendees</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            @if($onlineClass->attendees->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Student
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Join Time
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Leave Time
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Duration
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($onlineClass->attendees as $attendee)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8">
                                                                <img class="h-8 w-8 rounded-full" src="{{ $attendee->student->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($attendee->student->user->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $attendee->student->user->name }}">
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900">
                                                                    {{ $attendee->student->user->name }}
                                                                </div>
                                                                <div class="text-sm text-gray-500">
                                                                    {{ $attendee->student->admission_number ?? 'N/A' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $attendee->join_time ? $attendee->join_time->format('h:i A') : 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $attendee->leave_time ? $attendee->leave_time->format('h:i A') : 'Still in class' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        @if($attendee->join_time && $attendee->leave_time)
                                                            {{ $attendee->join_time->diffInMinutes($attendee->leave_time) }} mins
                                                        @elseif($attendee->join_time && !$attendee->leave_time)
                                                            {{ $attendee->join_time->diffInMinutes(now()) }} mins and counting
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h4 class="text-lg font-medium text-gray-900">No attendees yet</h4>
                                    <p class="text-sm text-gray-500 mt-1">Students will appear here when they join the class.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Class Materials (if any) -->
                    @if($onlineClass->materials->count() > 0)
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Class Materials</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <ul class="divide-y divide-gray-200">
                                @foreach($onlineClass->materials as $material)
                                    <li class="py-3 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <a href="{{ asset('storage/' . $material->file_path) }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">
                                            {{ $material->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>