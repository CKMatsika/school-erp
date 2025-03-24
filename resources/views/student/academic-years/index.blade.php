<!-- resources/views/student/academic-years/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Academic Years') }}
            </h2>
            <a href="{{ route('student.academic-years.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Academic Year
            </a>
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

            <!-- Academic Years Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($academicYears as $academicYear)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $academicYear->name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $academicYear->start_date->format('M d, Y') }} - {{ $academicYear->end_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($academicYear->is_current)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-xs text-gray-500">Students</p>
                                    <p class="text-lg font-medium text-gray-900">{{ $academicYear->enrollments->count() }}</p>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-xs text-gray-500">Terms</p>
                                    <p class="text-lg font-medium text-gray-900">{{ $academicYear->terms->count() }}</p>
                                </div>
                            </div>

                            <!-- Terms List -->
                            @if($academicYear->terms->count() > 0)
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Terms</h4>
                                    <div class="space-y-2">
                                        @foreach($academicYear->terms as $term)
                                            <div class="flex justify-between items-center bg-gray-50 rounded px-3 py-2 text-sm">
                                                <div>
                                                    <span class="font-medium">{{ $term->name }}</span>
                                                    <span class="text-xs text-gray-500 block">
                                                        {{ $term->start_date->format('M d') }} - {{ $term->end_date->format('M d, Y') }}
                                                    </span>
                                                </div>
                                                @if($term->is_current)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Current
                                                    </span>
                                                @else
                                                    <form action="{{ route('student.academic-years.set-current-term', $term) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900">
                                                            Set as Current
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-end space-x-2 pt-2 border-t border-gray-200">
                                <a href="{{ route('student.academic-years.show', $academicYear) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </a>
                                <a href="{{ route('student.academic-years.edit', $academicYear) }}" class="text-sm text-yellow-600 hover:text-yellow-900">
                                    Edit
                                </a>
                                @if(!$academicYear->is_current && $academicYear->enrollments->count() === 0 && $academicYear->applications->count() === 0)
                                    <form action="{{ route('student.academic-years.destroy', $academicYear) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this academic year?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                                @if(!$academicYear->is_current)
                                    <form action="{{ route('student.academic-years.update', $academicYear) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="is_current" value="1">
                                        <input type="hidden" name="name" value="{{ $academicYear->name }}">
                                        <input type="hidden" name="start_date" value="{{ $academicYear->start_date->format('Y-m-d') }}">
                                        <input type="hidden" name="end_date" value="{{ $academicYear->end_date->format('Y-m-d') }}">
                                        <button type="submit" class="text-sm text-green-600 hover:text-green-900">
                                            Set as Current
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="text-center py-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No academic years</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new academic year.</p>
                                <div class="mt-6">
                                    <a href="{{ route('student.academic-years.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add New Academic Year
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>