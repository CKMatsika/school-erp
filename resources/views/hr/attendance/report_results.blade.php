```blade
{{-- This is a very basic results view. You'd replace this with a proper report layout or PDF/Excel export --}}
<x-app-layout>
     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $reportTitle ?? 'Attendance Report Results' }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Report Criteria
                     </h3>
                     <div class="text-sm mb-6">
                        <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $validated['report_type'] ?? 'N/A')) }}</p>
                        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($validated['date_from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($validated['date_to'])->format('M d, Y') }}</p>
                        @if(isset($validated['department']))<p><strong>Department:</strong> {{ $validated['department'] }}</p>@endif
                        @if(isset($validated['staff_id']))<p><strong>Staff:</strong> {{ \App\Models\HumanResources\Staff::find($validated['staff_id'])->full_name ?? 'N/A' }}</p>@endif
                        @if(isset($validated['status']))<p><strong>Status:</strong> {{ ucfirst($validated['status']) }}</p>@endif
                    </div>

                     <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6 border-t pt-4">
                        Report Data
                     </h3>
                    @if($reportData->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        {{-- Adjust columns based on actual report type/data --}}
                                        <th class="px-4 py-2 text-left">Date</th>
                                        <th class="px-4 py-2 text-left">Staff</th>
                                        <th class="px-4 py-2 text-center">Status</th>
                                        <th class="px-4 py-2 text-center">Clock In</th>
                                        <th class="px-4 py-2 text-center">Clock Out</th>
                                        <th class="px-4 py-2 text-right">Duration</th>
                                        <th class="px-4 py-2 text-left">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData as $record)
                                     <tr>
                                        <td class="px-4 py-2">{{ $record->attendance_date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2">{{ $record->staff->full_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-center">{{ ucfirst(str_replace('_',' ',$record->status)) }}</td>
                                        <td class="px-4 py-2 text-center">{{ $record->clock_in_time ? \Carbon\Carbon::parse($record->clock_in_time)->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-2 text-center">{{ $record->clock_out_time ? \Carbon\Carbon::parse($record->clock_out_time)->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-2 text-right">{{ $record->work_duration ? number_format($record->work_duration, 2) : '-' }}</td>
                                         <td class="px-4 py-2 whitespace-normal">{{ $record->notes }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                         <p class="text-gray-500 italic">No records found for the selected criteria.</p>
                    @endif
                    <div class="mt-6 text-right">
                         <button onclick="window.print();" class="text-sm text-indigo-600 hover:text-indigo-900 mr-4">Print Report</button>
                         <a href="{{ route('hr.attendance.reports') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Report Selection</a>
                     </div>
                 </div>
            </div>
        </div>
    </div>
</x-app-layout>