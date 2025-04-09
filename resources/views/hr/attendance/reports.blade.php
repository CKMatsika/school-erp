```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Attendance Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 {{-- Ensure controller passes $staffList, $departments --}}
                <form method="GET" action="{{ route('hr.attendance.reports.generate') }}" target="_blank"> {{-- Open report in new tab --}}
                     @csrf {{-- Use GET, CSRF not strictly needed but harmless --}}
                     <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Select Report Criteria</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Report Type --}}
                             <div>
                                <x-input-label for="report_type" :value="__('Report Type')" class="required"/>
                                <select name="report_type" id="report_type" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="daily_summary" {{ old('report_type') == 'daily_summary' ? 'selected' : '' }}>Daily Summary</option>
                                    <option value="monthly_summary" {{ old('report_type') == 'monthly_summary' ? 'selected' : '' }}>Monthly Summary</option>
                                    <option value="exception_report" {{ old('report_type') == 'exception_report' ? 'selected' : '' }}>Exception Report (Late/Absent etc.)</option>
                                </select>
                                <x-input-error :messages="$errors->get('report_type')" class="mt-2" />
                            </div>
                             {{-- Date From --}}
                             <div>
                                <x-input-label for="date_from" :value="__('Start Date')" class="required"/>
                                <x-text-input id="date_from" class="block mt-1 w-full" type="date" name="date_from" :value="old('date_from', now()->startOfMonth()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date_from')" class="mt-2" />
                            </div>
                             {{-- Date To --}}
                             <div>
                                <x-input-label for="date_to" :value="__('End Date')" class="required"/>
                                <x-text-input id="date_to" class="block mt-1 w-full" type="date" name="date_to" :value="old('date_to', now()->endOfMonth()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date_to')" class="mt-2" />
                            </div>
                             {{-- Department --}}
                            <div>
                                <x-input-label for="department" :value="__('Department (Optional)')" />
                                <select id="department" name="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('department')" class="mt-2" />
                            </div>
                            {{-- Staff Member --}}
                            <div>
                                <x-input-label for="staff_id" :value="__('Specific Staff Member (Optional)')" />
                                <select id="staff_id" name="staff_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Staff</option>
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->full_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
                            </div>
                            {{-- Status (for exception report) --}}
                             <div id="status_filter_div" class="hidden"> {{-- Hidden by default --}}
                                <x-input-label for="status" :value="__('Exception Status')" />
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any Exception</option>
                                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late Arrival</option>
                                    <option value="early_departure" {{ old('status') == 'early_departure' ? 'selected' : '' }}>Early Departure</option>
                                    {{-- Add others if needed --}}
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                         </div>
                    </div>

                     <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                         <a href="{{ route('hr.attendance.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Generate Report') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
     <script>
        // Show/hide status filter based on report type
        document.addEventListener('DOMContentLoaded', function() {
            const reportTypeSelect = document.getElementById('report_type');
            const statusDiv = document.getElementById('status_filter_div');

            function toggleStatusFilter() {
                statusDiv.style.display = (reportTypeSelect.value === 'exception_report') ? '' : 'none';
            }

            reportTypeSelect.addEventListener('change', toggleStatusFilter);
            toggleStatusFilter(); // Initial check
        });
    </script>
</x-app-layout>