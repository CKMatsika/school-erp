<!-- resources/views/teacher/marking-schemes/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Marking Scheme Details') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('teacher.marking-schemes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
                
                @if($markingScheme->status != 'archived')
                <a href="{{ route('teacher.marking-schemes.edit', $markingScheme) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                @endif
                
                @if($markingScheme->status == 'draft')
                <form action="{{ route('teacher.marking-schemes.publish', $markingScheme) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Publish
                    </button>
                </form>
                @endif
                
                <a href="{{ route('teacher.marking-schemes.download', $markingScheme) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Download PDF
                </a>
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
                            <h1 class="text-2xl font-bold text-gray-900">{{ $markingScheme->title }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $markingScheme->class->name ?? 'Unknown Class' }} | 
                                {{ $markingScheme->subject->name ?? 'Unknown Subject' }} | 
                                {{ ucfirst($markingScheme->assessment_type) }}
                            </p>
                        </div>
                        <div class="flex items-center">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($markingScheme->status == 'draft') bg-gray-100 text-gray-800
                                @elseif($markingScheme->status == 'published') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($markingScheme->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Summary Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Summary</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Total Marks</span>
                                    <span class="block text-xl font-semibold">{{ $markingScheme->total_marks }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Passing Marks</span>
                                    <span class="block text-xl font-semibold">{{ $markingScheme->passing_marks }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Passing Percentage</span>
                                    <span class="block text-xl font-semibold">{{ round(($markingScheme->passing_marks / $markingScheme->total_marks) * 100, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($markingScheme->description)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Description</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-800 whitespace-pre-line">{{ $markingScheme->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Marking Criteria -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Marking Criteria</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Criterion
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Description
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Marks
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Weight
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($markingScheme->criteria as $criterion)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $criterion->name }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $criterion->description }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    {{ $criterion->marks }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    {{ $criterion->weight }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="2" class="px-6 py-3 text-right text-sm font-medium text-gray-500">
                                                Total
                                            </td>
                                            <td class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                                {{ $markingScheme->criteria->sum('marks') }}
                                            </td>
                                            <td class="px-6 py-3 text-center text-sm font-medium text-gray-700">
                                                {{ $markingScheme->criteria->sum('weight') }}%
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Materials -->
                    @if($markingScheme->file_path || $markingScheme->notes)
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Additional Materials</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            @if($markingScheme->file_path)
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Attached Document</h4>
                                    <a href="{{ asset('storage/' . $markingScheme->file_path) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Download Document
                                    </a>
                                </div>
                            @endif

                            @if($markingScheme->notes)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Additional Notes</h4>
                                    <p class="text-gray-800 whitespace-pre-line">{{ $markingScheme->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Metadata</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Created</span>
                                    <span class="block text-base">{{ $markingScheme->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Last Updated</span>
                                    <span class="block text-base">{{ $markingScheme->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Created By</span>
                                    <span class="block text-base">{{ $markingScheme->teacher->user->name ?? 'Unknown' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>