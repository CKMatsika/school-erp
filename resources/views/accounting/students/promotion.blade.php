<!-- resources/views/students/promotion.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Promotion') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
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

                    <form method="POST" action="{{ route('students.promotion.process') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-label for="from_class_id" :value="__('From Class')" />
                                <select id="from_class_id" name="from_class_id" required class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Select Class</option>
                                    @foreach($fromClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('from_class_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="to_class_id" :value="__('To Class')" />
                                <select id="to_class_id" name="to_class_id" required class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Select Class</option>
                                    @foreach($toClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('to_class_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="academic_year" :value="__('Academic Year')" />
                                <x-input id="academic_year" class="block mt-1 w-full" type="text" name="academic_year" :value="old('academic_year', date('Y'))" required />
                                @error('academic_year')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center">
                                <input id="generate_invoice" name="generate_invoice" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <label for="generate_invoice" class="ml-2 block text-sm text-gray-900">
                                    Generate Fee Invoices for Promoted Students
                                </label>
                            </div>
                        </div>

                        <div id="invoice_options" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-label for="fee_structure_id" :value="__('Fee Structure')" />
                                    <select id="fee_structure_id" name="fee_structure_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Select
                                        <option value="">Select Fee Structure</option>
                                        @foreach($feeStructures as $feeStructure)
                                            <option value="{{ $feeStructure->id }}">{{ $feeStructure->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('fee_structure_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="term" :value="__('Term')" />
                                    <select id="term" name="term" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Select Term</option>
                                        <option value="Term 1">Term 1</option>
                                        <option value="Term 2">Term 2</option>
                                        <option value="Term 3">Term 3</option>
                                    </select>
                                    @error('term')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="issue_date" :value="__('Issue Date')" />
                                    <x-input id="issue_date" class="block mt-1 w-full" type="date" name="issue_date" :value="old('issue_date')" />
                                    @error('issue_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="due_date" :value="__('Due Date')" />
                                    <x-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date')" />
                                    @error('due_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-button>
                                {{ __('Promote Students') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateInvoiceCheckbox = document.getElementById('generate_invoice');
            const invoiceOptions = document.getElementById('invoice_options');
            
            function toggleInvoiceOptions() {
                if (generateInvoiceCheckbox.checked) {
                    invoiceOptions.classList.remove('hidden');
                } else {
                    invoiceOptions.classList.add('hidden');
                }
            }
            
            generateInvoiceCheckbox.addEventListener('change', toggleInvoiceOptions);
            toggleInvoiceOptions(); // Initialize on page load
        });
    </script>
</x-app-layout>