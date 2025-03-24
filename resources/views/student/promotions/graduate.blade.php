<!-- resources/views/student/promotions/graduate.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Graduation Process') }}
            </h2>
            <a href="{{ route('student.promotions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Promotions
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

            <!-- Graduation Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Process Graduation</h3>
                    <p class="mb-4 text-sm text-gray-600">Use this form to mark students as graduated. This will update their status and create appropriate records.</p>
                    
                    <form action="{{ route('student.promotions.previewGraduation') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Graduating Class</label>
                                <select name="class_id" id="class_id" required 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Graduating Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" required 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : ($year->is_current ? 'selected' : '') }}>
                                            {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Preview Graduating Students
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Graduation Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Graduation Process Information</h3>
                    
                    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-purple-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-purple-700">
                                    The graduation process is used for students completing their final year at the school. This will mark students as graduated
                                    and close their active enrollments.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-base font-medium text-gray-700">What happens during graduation:</h4>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Student status is changed to "graduated"</li>
                                <li>A graduation date is recorded on the student record</li>
                                <li>Current enrollment records are marked as "completed"</li>
                                <li>Student will no longer appear in active student lists</li>
                                <li>Student records remain in the system for alumni tracking</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="text-base font-medium text-gray-700">Post-Graduation:</h4>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Graduation certificates can be generated for graduated students</li>
                                <li>Transcripts will show the student as graduated</li>
                                <li>Students can be included in alumni databases</li>
                                <li>Additional documents can be uploaded to student records</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="text-base font-medium text-gray-700">Important Notes:</h4>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>This action cannot be undone easily</li>
                                <li>Only students with "active" status will be included</li>
                                <li>You will have a chance to review and select specific students before confirming</li>
                                <li>For regular class promotions, use the "Class Promotion" feature instead</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>