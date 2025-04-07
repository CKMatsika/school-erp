<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Academic Year') }}: {{ $academicYear->name }}
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('accounting.academic-years.edit', $academicYear) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Edit Year
                </a>
                 <a href="{{ route('accounting.academic-years.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

             {{-- Year Details Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Year Information</h3>
                     <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                         {{-- Display Year Name, Dates, Status --}}
                         <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Name</dt><dd class="mt-1 text-sm text-gray-900">{{ $academicYear->name }}</dd></div>
                         <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Start Date</dt><dd class="mt-1 text-sm text-gray-900">{{ optional($academicYear->start_date)->format('M d, Y') }}</dd></div>
                         <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">End Date</dt><dd class="mt-1 text-sm text-gray-900">{{ optional($academicYear->end_date)->format('M d, Y') }}</dd></div>
                          <div class="sm:col-span-1"><dt class="text-sm font-medium text-gray-500">Is Current?</dt><dd class="mt-1 text-sm text-gray-900">{{ $academicYear->is_current ? 'Yes' : 'No' }}</dd></div>
                     </dl>
                </div>
            </div>

             {{-- Terms Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Terms / Semesters</h3>

                     @if(session('term_success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('term_success') }}</p></div>
                     @endif
                      @if(session('term_error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>{{ session('term_error') }}</p></div>
                     @endif
                      @if ($errors->store_term && $errors->store_term->any()) {{-- Target errors for term form --}}
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong class="font-bold">Error adding term:</strong>
                            <ul> @foreach ($errors->store_term->all() as $error) <li>{{ $error }}</li> @endforeach </ul>
                        </div>
                    @endif

                     {{-- Table of Existing Terms --}}
                     <div class="overflow-x-auto mb-6 border border-gray-200 sm:rounded-lg">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                             </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($academicYear->terms as $term)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $term->name }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($term->start_date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($term->end_date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right">
                                             <form action="{{ route('accounting.academic-years.terms.destroy', [$academicYear, $term]) }}" method="POST" onsubmit="return confirm('Delete this term?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">No terms added yet for this academic year.</td></tr>
                                @endforelse
                            </tbody>
                         </table>
                     </div>

                      {{-- Add New Term Form --}}
                     <h4 class="text-md font-semibold text-gray-700 mb-3 pt-4 border-t">Add New Term</h4>
                      <form method="POST" action="{{ route('accounting.academic-years.terms.store', $academicYear) }}">
                         @csrf
                         <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="term_name" value="Term Name *" />
                                <x-text-input id="term_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            </div>
                             <div>
                                <x-input-label for="term_start_date" value="Start Date *" />
                                <x-text-input id="term_start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                            </div>
                            <div>
                                <x-input-label for="term_end_date" value="End Date *" />
                                <x-text-input id="term_end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" required />
                            </div>
                             <div>
                                 <x-primary-button type="submit">Add Term</x-primary-button>
                             </div>
                         </div>
                         {{-- Add is_active checkbox if needed --}}
                     </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>