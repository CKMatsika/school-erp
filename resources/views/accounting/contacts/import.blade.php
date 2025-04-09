<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Contacts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages for General Success/Error --}}
            @include('components.flash-messages')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('accounting.contacts.import.handle') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Upload File</h3>

                        <p class="text-sm text-gray-600">
                            Upload an Excel (.xlsx, .xls) or CSV (.csv) file with contact data.
                            The first row should be the header row with column names matching the template.
                        </p>

                        {{-- Download Template Link --}}
                        <div>
                             <a href="{{ route('accounting.contacts.import.template') }}" class="text-sm text-indigo-600 hover:text-indigo-900 underline">
                                Download Template File (.csv)
                            </a>
                            <p class="text-xs text-gray-500 mt-1">Required columns: `name`, `contact_type`. Recommended: `email`. Column names must match exactly.</p>
                            <p class="text-xs text-gray-500 mt-1">Allowed `contact_type` values: customer, vendor, student.</p>
                        </div>


                        {{-- File Input --}}
                        <div>
                            <x-input-label for="contact_file" :value="__('Select File')" class="required"/>
                             <input type="file" id="contact_file" name="contact_file" required accept=".xlsx,.xls,.csv"
                                   class="block w-full mt-1 text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100"/>
                            <x-input-error :messages="$errors->get('contact_file')" class="mt-2" />
                        </div>

                        {{-- Display Import Errors --}}
                        @if(session('import_errors'))
                            <div class="mt-4 border-l-4 border-red-400 bg-red-50 p-4 max-h-60 overflow-y-auto">
                                <h4 class="font-bold text-red-800">Import Errors Found:</h4>
                                <ul class="list-disc list-inside mt-2 text-sm text-red-700 space-y-1">
                                    @foreach(session('import_errors') as $error)
                                        <li>
                                            <strong>Row {{ $error['row'] ?? 'N/A' }}:</strong>
                                            @if(isset($error['attribute']) && isset($error['errors']))
                                                 {{ $error['attribute'] }} - {{ implode(', ', $error['errors']) }}
                                                <span class="text-xs block text-red-600"> (Data: {{ json_encode($error['data'] ?? []) }})</span>
                                            @elseif(isset($error['errors']))
                                                {{ implode(', ', $error['errors']) }}
                                                 <span class="text-xs block text-red-600"> (Data: {{ json_encode($error['data'] ?? []) }})</span>
                                            @else
                                                Unknown error.
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('accounting.contacts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Cancel
                        </a>
                         {{-- Add spinner or disable on submit via JS if desired --}}
                        <x-primary-button type="submit">
                            Import Contacts
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>