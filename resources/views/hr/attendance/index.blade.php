blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff Attendance Records') }}
            </h2>
            <div>
                {{-- @can('create', App\Models\HumanResources\AttendanceRecord::class) --}}
                 <a href="{{ route('hr.attendance.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    Manual Entry
                </a>
                {{-- @endcan --}}
                {{-- @can('viewReports', App\Models\HumanResources\AttendanceRecord::class) --}}
                 <a href="{{ route('hr.attendance.reports') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Reports
                </a>
                 {{-- @endcan --}}
            </div>
        </div>
    </x-slot>

     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             @include('components.flash-messages')

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('hr.attendance.index') }}" class="flex flex-wrap items-end gap-4">
                        {{-- Date From --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="date_from" :value="__('Date From')" />
                            <x-text-input id="date_from" class="block mt-1 w-full text-sm" type="date" name="date_from" :value="request('date_from', now()->startOfMonth()->format('Y-m-d'))" />
                        </div>
                        {{-- Date To --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="date_to" :value="__('Date To')" />
                            <x-text-input id="date_to" class="block mt-1 w-full text-sm" type="date" name="date_to" :value="request('date_to', now()->endOfMonth()->format('Y-m-d'))" />
                        </div>
                         {{-- Staff Member --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="staff_id" :value="__('Staff Member')" />
                            <select id="staff_id" name="staff_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Staff</option>
                                 {{-- Ensure controller passes $staffList --}}
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Status --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                 <option value="">All Statuses</option>
                                 <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                 <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                 <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late Arrival</option>
                                 <option value="early_departure" {{ request('status') == 'early_departure' ? 'selected' : '' }}>Early Departure</option>
                                 <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                 <option value="holiday" {{ request('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                            </select>
                        </div>
                         {{-- Action Buttons --}}
                        <div class="flex space-x-2">
                             <x-primary-button type="submit">
                                {{ __('Filter') }}
                            </x-primary-button>
                             @if(request()->hasAny(['staff_id', 'date_from', 'date_to', 'status']))
                                <a href="{{ route('hr.attendance.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

             {{-- Results Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-0 md:p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                     <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Duration (Hrs)</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    {{-- <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> --}}
                                </tr>
                            </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                 {{-- Ensure controller passes $attendanceRecords --}}
                                @forelse($attendanceRecords as $record)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $record->attendance_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="{{ route('hr.staff.show', $record->staff_id) }}" class="text-indigo-600 hover:underline">
                                                {{ $record->staff->full_name ?? 'N/A' }}
                                            </a>
                                        </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $record->clock_in_time ? \Carbon\Carbon::parse($record->clock_in_time)->format('H:i') : '-' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $record->clock_out_time ? \Carbon\Carbon::parse($record->clock_out_time)->format('H:i') : '-' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $record->work_duration ? number_format($record->work_duration, 2) : '-' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span @class([
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-green-100 text-green-800' => $record->status == 'present',
                                                'bg-red-100 text-red-800' => $record->status == 'absent',
                                                'bg-yellow-100 text-yellow-800' => $record->status == 'late' || $record->status == 'early_departure',
                                                'bg-blue-100 text-blue-800' => $record->status == 'on_leave',
                                                'bg-purple-100 text-purple-800' => $record->status == 'holiday',
                                                'bg-gray-100 text-gray-800' => !in_array($record->status, ['present', 'absent', 'late', 'early_departure', 'on_leave', 'holiday']),
                                            ])>
                                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                            </span>
                                        </td>
                                         <td class="px-6 py-4 whitespace-normal text-xs text-gray-500">{{ Str::limit($record->notes, 50) }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-400">{{ ucfirst($record->source) }}</td>
                                         {{-- Actions (e.g., edit manual entry) --}}
                                        {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            @if($record->source === 'manual')
                                                {{-- @can('update', $record) --}}
                                                {{-- <a href="{{ route('hr.attendance.edit', $record) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a> --}}
                                                {{-- @endcan --}}
                                            {{-- @endif
                                        </td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                         <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No attendance records found matching your criteria.</td>
                                    </tr>
                                @endforelse
                             </tbody>
                        </table>
                    </div>
                     {{-- Pagination --}}
                    <div class="mt-4 px-6 pb-4">
                        {{ $attendanceRecords->links() }}
                    </div>
                 </div>
            </div>

        </div>
    </div>
</x-app-layout>