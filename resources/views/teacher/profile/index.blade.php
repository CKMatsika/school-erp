<!-- resources/views/teacher/profile/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Teacher Profile') }}
            </h2>
            <a href="{{ route('teacher.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Profile
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row">
                        <!-- Left Column - Profile Image and Basic Info -->
                        <div class="w-full md:w-1/3 mb-6 md:mb-0 md:pr-6">
                            <div class="bg-gray-50 p-6 rounded-lg flex flex-col items-center">
                                <div class="w-40 h-40 rounded-full overflow-hidden mb-4">
                                    <img src="{{ $teacher->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($teacher->user->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $teacher->user->name }}" class="w-full h-full object-cover">
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $teacher->user->name }}</h2>
                                <p class="text-sm text-gray-600 mb-4">Teacher ID: {{ $teacher->teacher_id }}</p>
                                <div class="flex space-x-2 mb-4">
                                    @if($teacher->email)
                                    <a href="mailto:{{ $teacher->email }}" class="text-gray-700 hover:text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                    </a>
                                    @endif
                                    
                                    @if($teacher->phone)
                                    <a href="tel:{{ $teacher->phone }}" class="text-gray-700 hover:text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                                
                                <div class="w-full border-t border-gray-200 pt-4">
                                    <div class="mb-3">
                                        <span class="block text-sm font-medium text-gray-500">Department</span>
                                        <span class="block text-base">{{ $teacher->department ?? 'Not Specified' }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="block text-sm font-medium text-gray-500">Joined Date</span>
                                        <span class="block text-base">{{ $teacher->join_date ? $teacher->join_date->format('M d, Y') : 'Not Specified' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-medium text-gray-500">Status</span>
                                        <span class="inline-flex px-2 py-1 mt-1 text-xs font-semibold rounded-full {{ $teacher->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($teacher->status ?? 'Unknown') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Detailed Information -->
                        <div class="w-full md:w-2/3">
                            <!-- Personal Information -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Personal Information</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-500">Email</span>
                                            <span class="block text-base">{{ $teacher->email ?? $teacher->user->email }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-500">Phone</span>
                                            <span class="block text-base">{{ $teacher->phone ?? 'Not Specified' }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-500">Date of Birth</span>
                                            <span class="block text-base">{{ $teacher->date_of_birth ? $teacher->date_of_birth->format('M d, Y') : 'Not Specified' }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-500">Gender</span>
                                            <span class="block text-base">{{ ucfirst($teacher->gender ?? 'Not Specified') }}</span>
                                        </div>
                                        <div class="md:col-span-2">
                                            <span class="block text-sm font-medium text-gray-500">Address</span>
                                            <span class="block text-base">{{ $teacher->address ?? 'Not Specified' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Professional Information -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Professional Information</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-500">Qualifications</span>
                                            <span class="block text-base">{{ $teacher->qualifications ?? 'Not Specified' }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-500">Experience (Years)</span>
                                            <span class="block text-base">{{ $teacher->experience ?? 'Not Specified' }}</span>
                                        </div>
                                        <div class="md:col-span-2">
                                            <span class="block text-sm font-medium text-gray-500">Specialization</span>
                                            <span class="block text-base">{{ $teacher->specialization ?? 'Not Specified' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Teaching Classes -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Teaching Classes</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    @if($teacher->classes->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($teacher->classes as $class)
                                                <div class="bg-white p-3 rounded-md border border-gray-200 flex justify-between items-center">
                                                    <div>
                                                        <span class="block font-medium text-gray-900">{{ $class->name }}</span>
                                                        <span class="text-sm text-gray-500">{{ $class->section ?? '' }}</span>
                                                    </div>
                                                    @if($class->pivot->is_class_teacher)
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Class Teacher</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-600">No classes assigned yet.</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Teaching Subjects -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Teaching Subjects</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    @if($teacher->subjects->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($teacher->subjects as $subject)
                                                <div class="bg-white p-3 rounded-md border border-gray-200">
                                                    <span class="block font-medium text-gray-900">{{ $subject->name }}</span>
                                                    <span class="text-sm text-gray-500">{{ $subject->code ?? '' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-600">No subjects assigned yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>