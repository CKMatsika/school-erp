<!-- resources/views/student/promotions/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Class Promotions') }}
            </h2>
            <a href="{{ route('student.promotions.graduate') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Graduation Process
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

            <!-- Promotion Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Promote Students</h3>
                    <p class="mb-4 text-sm text-gray-600">Use this form to promote students from one class to another for a new academic year.</p>
                    
                    <form action="{{ route('student.promotions.preview') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="from_class_id" class="block text-sm font-medium text-gray-700 mb-1">From Class</label>
                                <select name="from_class_id" id="from_class_id" required 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Current Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('from_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="to_class_id" class="block text-sm font-medium text-gray-700 mb-1">To Class</label>
                                <select name="to_class_id" id="to_class_id" required 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Target Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('to_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="current_academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">Current Academic Year</label>
                                <select name="current_academic_year_id" id="current_academic_year_id" required 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Current Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('current_academic_year_id') == $year->id ? 'selected' : ($year->is_current ? 'selected' : '') }}>
                                            {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="new_academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">New Academic Year</label>
                                <select name="new_academic_year_id" id="new_academic_year_id" required 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select New Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('new_academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Preview Students
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Promotion Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Promotion Guidelines</h3>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    The class promotion process will move students from one class to another for a new academic year. 
                                    This action updates existing enrollment records and creates new ones for the selected students.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-base font-medium text-gray-700">When to use this feature:</h4>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>End of academic year promotions</li>
                                <li>Moving students to the next grade level</li>
                                <li>Restructuring classes within an academic year</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="text-base font-medium text-gray-700">What happens during promotion:</h4>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Current enrollment records are marked as "completed"</li>
                                <li>New enrollment records are created for the new class and academic year</li>
                                <li>Student records are updated with the new current class</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="text-base font-medium text-gray-700">Important Notes:</h4>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>This action cannot be undone</li>
                                <li>Only students with "active" status will be included</li>
                                <li>You will have a chance to review and select specific students before confirming the promotion</li>
                                <li>For graduating students, use the "Graduation Process" instead</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>