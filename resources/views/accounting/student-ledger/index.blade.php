<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Ledger') }}
            </h2>
            {{-- Optional: Add other buttons if needed --}}
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-6">

                    <!-- Session Messages -->
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                    @if(session('warning_email'))
                        <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p>{{ session('warning_email') }}</p>
                        </div>
                    @endif
                     @if(session('warning_sms'))
                        <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p>{{ session('warning_sms') }}</p>
                        </div>
                    @endif

                    <!-- Validation Error Summary Block -->
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Whoops! Something went wrong.</strong>
                            <span class="block sm:inline">Please check the errors below.</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Student Selection Form -->
                    <form method="GET" action="{{ route('accounting.student-ledger.index') }}">
                        <div class="flex items-end space-x-4">
                            <div class="flex-grow">
                                <label for="student_id_select" class="block text-sm font-medium text-gray-700">Select Student</label> {{-- Changed ID slightly to avoid clash if using select2 --}}
                                <select id="student_id_select" name="student_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">-- Select a Student --</option>
                                    {{-- Ensure $students variable exists and is iterable --}}
                                    @isset($students)
                                        @if($students->count() > 0) {{-- Use count() method on collection --}}
                                            @foreach($students as $studentOption)
                                                {{-- Check if studentOption and its properties exist --}}
                                                @if(isset($studentOption) && isset($studentOption->id) && isset($studentOption->name))
                                                    <option value="{{ $studentOption->id }}"
                                                        {{-- Check if selectedStudentId matches --}}
                                                        @if(isset($selectedStudentId) && $selectedStudentId == $studentOption->id) selected @endif >
                                                        {{ $studentOption->name }} (ID: {{ $studentOption->id }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        @else
                                             <option value="" disabled>No active students found matching criteria.</option>
                                        @endif
                                    @else
                                        <option value="" disabled>Error: Student list not available.</option>
                                    @endisset
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    View Ledger
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Transaction Ledger Display (Conditional) -->
                    @isset($selectedStudent)
                        @if($selectedStudent) {{-- Check if $selectedStudent object exists --}}
                            <hr class="my-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                Ledger for: {{ $selectedStudent->name }} (ID: {{ $selectedStudent->id }})
                            </h3>

                            {{-- Statement Generation Form --}}
                            <div class="mb-4 p-4 border rounded-md bg-gray-50">
                                <h4 class="font-medium text-gray-700 mb-2">Generate Statement</h4>
                                <form method="GET" action="{{ route('accounting.student-ledger.statement', $selectedStudent->id) }}" target="_blank" class="space-y-2">
                                    <div class="flex flex-wrap items-end space-x-4">
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                            <input type="date" name="start_date" id="start_date" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('start_date') border-red-500 @enderror" value="{{ old('start_date') }}">
                                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('end_date') border-red-500 @enderror">
                                            @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="output" class="block text-sm font-medium text-gray-700">Output</label>
                                            <select name="output" id="output" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="html" {{ old('output', 'html') == 'html' ? 'selected' : '' }}>View in Browser</option>
                                                <option value="pdf" {{ old('output') == 'pdf' ? 'selected' : '' }}>Download PDF</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Generate
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Ledger Table --}}
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit (+)</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit (-)</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Running Balance</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @isset($transactions)
                                            @forelse($transactions as $transaction)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $transaction['date'] instanceof \Carbon\Carbon ? $transaction['date']->format(config('app.date_format', 'Y-m-d')) : ($transaction['date'] ?? 'N/A') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($transaction['type'] ?? 'N/A') }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $transaction['description'] ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                        {{ isset($transaction['debit']) && $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                        {{ isset($transaction['credit']) && $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 text-right">
                                                        {{ isset($transaction['running_balance']) ? number_format($transaction['running_balance'], 2) : 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        @if(isset($transaction['status']))
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            @switch($transaction['status'])
                                                                @case('paid') @case('completed') class="bg-green-100 text-green-800" @break
                                                                @case('partial') class="bg-yellow-100 text-yellow-800" @break
                                                                @case('overdue') class="bg-red-100 text-red-800" @break
                                                                @case('unpaid') class="bg-blue-100 text-blue-800" @break
                                                                @case('sent') class="bg-indigo-100 text-indigo-800" @break
                                                                @case('draft') class="bg-gray-100 text-gray-800" @break
                                                                @default class="bg-gray-100 text-gray-600"
                                                            @endswitch">
                                                            {{ ucfirst(str_replace('_', ' ', $transaction['status'])) }}
                                                        </span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                         @if(isset($transaction['type']) && isset($transaction['reference_id']))
                                                            <a href="{{ route('accounting.student-ledger.show', [strtolower($transaction['type']), $transaction['reference_id']]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                                View
                                                            </a>
                                                         @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No transactions found for this student in the selected period (if any).</td>
                                                </tr>
                                            @endforelse
                                        @else
                                             <tr>
                                                 <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Transactions not available.</td>
                                             </tr>
                                        @endisset
                                    </tbody>
                                     @isset($transactions)
                                        @if($transactions->isNotEmpty())
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-700 uppercase tracking-wider">Current Balance:</td>
                                                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                                         @isset($currentBalance)
                                                             {{ number_format($currentBalance, 2) }}
                                                         @else
                                                             N/A
                                                         @endisset
                                                     </td>
                                                    <td colspan="2"></td> {{-- Empty cells for status/action columns --}}
                                                </tr>
                                            </tfoot>
                                        @endif
                                     @endisset
                                </table>
                            </div>
                        @endif
                    @endisset {{-- End check for $selectedStudent --}}

                    {{-- Message if student ID was in URL but student not found --}}
                    @isset($selectedStudentId)
                         @if(!$selectedStudent && $selectedStudentId) {{-- Only show if ID was provided but student is null --}}
                            <hr class="my-6">
                            <p class="text-center text-red-600">Selected student (ID: {{ $selectedStudentId }}) not found or is inactive.</p>
                         @endif
                    @endisset
                    {{-- End of Conditional Transaction Display --}}


                </div> <!-- End p-6 -->
            </div> <!-- End bg-white -->
        </div> <!-- End max-w-7xl -->
    </div> <!-- End py-12 -->
</x-app-layout>