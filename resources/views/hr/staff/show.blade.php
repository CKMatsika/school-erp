<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Staff Details: {{ $staff->full_name }} ({{ $staff->staff_number }})
                <span @class([
                    'ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    'bg-green-100 text-green-800' => $staff->is_active,
                    'bg-red-100 text-red-800' => !$staff->is_active,
                 ])>
                    {{ $staff->is_active ? 'Active' : 'Inactive' }}
                </span>
            </h2>
            <div class="flex space-x-2">
                {{-- @can('update', $staff) --}}
                <a href="{{ route('hr.staff.edit', $staff) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Edit
                </a>
                {{-- @endcan --}}
                 {{-- Add other actions like Assign Subjects/Classes --}}
                 @if($staff->staff_type === 'teaching')
                    <a href="{{ route('hr.staff.assign-subjects.form', $staff) }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Assign Subjects
                    </a>
                     <a href="{{ route('hr.staff.assign-classes.form', $staff) }}" class="inline-flex items-center px-4 py-2 bg-teal-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-600 active:bg-teal-700 focus:outline-none focus:border-teal-700 focus:ring ring-teal-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Assign Classes
                    </a>
                 @endif
                 {{-- Payroll Assignments --}}
                 @if($staff->staff_type !== 'teaching') {{-- Or based on permissions --}}
                      <a href="{{ route('hr.payroll.assignments.staff', $staff) }}" class="inline-flex items-center px-4 py-2 bg-rose-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-600 active:bg-rose-700 focus:outline-none focus:border-rose-700 focus:ring ring-rose-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Payroll Elements
                    </a>
                 @endif

                 {{-- @can('delete', $staff) --}}
                 <form action="{{ route('hr.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate/delete this staff member?');">
                     @csrf
                     @method('DELETE')
                     <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                         {{ $staff->deleted_at ? 'Force Delete' : 'Deactivate' }} {{-- Adjust based on soft delete usage --}}
                     </button>
                 </form>
                 {{-- @endcan --}}
            </div>
         </div>
    </x-slot>

     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             @include('components.flash-messages')

            {{-- Staff Details --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                         {{-- Personal Info --}}
                         <div class="font-semibold text-gray-600">Staff Number:</div><div class="md:col-span-2">{{ $staff->staff_number }}</div>
                         <div class="font-semibold text-gray-600">Full Name:</div><div class="md:col-span-2">{{ $staff->full_name }}</div>
                         <div class="font-semibold text-gray-600">Gender:</div><div class="md:col-span-2">{{ ucfirst($staff->gender ?? 'N/A') }}</div>
                         <div class="font-semibold text-gray-600">Date of Birth:</div><div class="md:col-span-2">{{ $staff->date_of_birth ? $staff->date_of_birth->format('M d, Y') : 'N/A' }}</div>
                         <div class="font-semibold text-gray-600">Email:</div><div class="md:col-span-2">{{ $staff->email }}</div>
                         <div class="font-semibold text-gray-600">Phone:</div><div class="md:col-span-2">{{ $staff->phone_number ?? 'N/A' }}</div>
                         <div class="font-semibold text-gray-600">Address:</div><div class="md:col-span-2 whitespace-pre-wrap">{{ $staff->address ?? 'N/A' }}</div>

                         {{-- Employment Info --}}
                         <div class="font-semibold text-gray-600 pt-4 border-t md:border-t-0">Date Joined:</div><div class="md:col-span-2 pt-4 border-t md:border-t-0">{{ $staff->date_joined->format('M d, Y') }}</div>
                         <div class="font-semibold text-gray-600">Job Title:</div><div class="md:col-span-2">{{ $staff->job_title }}</div>
                         <div class="font-semibold text-gray-600">Staff Type:</div><div class="md:col-span-2">{{ ucfirst($staff->staff_type) }}</div>
                         <div class="font-semibold text-gray-600">Employment Type:</div><div class="md:col-span-2">{{ ucfirst($staff->employment_type) }}</div>
                         <div class="font-semibold text-gray-600">Department:</div><div class="md:col-span-2">{{ $staff->department ?? 'N/A' }}</div>
                         <div class="font-semibold text-gray-600">Basic Salary:</div><div class="md:col-span-2">{{ $staff->basic_salary ? number_format($staff->basic_salary, 2) : 'N/A' }}</div>
                         <div class="font-semibold text-gray-600">Status:</div><div class="md:col-span-2">{{ $staff->is_active ? 'Active' : 'Inactive' }}</div>
                          <div class="font-semibold text-gray-600">Notes:</div><div class="md:col-span-2 whitespace-pre-wrap">{{ $staff->notes ?? 'N/A' }}</div>
                         <div class="font-semibold text-gray-600">User Account:</div><div class="md:col-span-2">{{ $staff->user->email ?? 'Not Linked' }}</div>

                    </div>
                 </div>
            </div>

             {{-- Assigned Subjects (for Teachers) --}}
            @if($staff->staff_type === 'teaching' && $staff->subjects->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                      <h3 class="text-lg font-medium text-gray-900 mb-4">Assigned Subjects</h3>
                      <ul class="list-disc list-inside space-y-1">
                          @foreach($staff->subjects as $subject)
                            <li class="text-sm text-gray-700">{{ $subject->name }} {{ $subject->subject_code ? '('.$subject->subject_code.')' : '' }}</li>
                          @endforeach
                      </ul>
                 </div>
            </div>
            @endif

            {{-- Assigned Classes (for Teachers) --}}
             @if($staff->staff_type === 'teaching' && $staff->classes->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                      <h3 class="text-lg font-medium text-gray-900 mb-4">Assigned Classes</h3>
                      <ul class="list-disc list-inside space-y-1">
                           {{-- Group by academic year if pivot data is loaded --}}
                           @foreach($staff->classes->groupBy('pivot.academic_year_id') as $yearId => $classesInYear)
                                @php
                                    $year = $classesInYear->first()->academicYear; // Get year details from first item
                                @endphp
                                <li class="text-sm font-semibold text-gray-800 mt-2">
                                    {{ $year->name ?? ($year->start_date->format('Y').'/'.($year->start_date->year + 1)) ?? 'Unknown Year' }}
                                    <ul class="list-disc list-inside pl-4 mt-1 space-y-1">
                                         @foreach($classesInYear as $class)
                                            <li class="text-sm text-gray-700">{{ $class->name }}</li> {{-- Assuming SchoolClass has 'name' --}}
                                         @endforeach
                                    </ul>
                                </li>
                           @endforeach
                      </ul>
                 </div>
            </div>
            @endif

             {{-- Add links/sections for Attendance History, Payroll History etc. later --}}

             <div class="mt-6 text-right">
                 <a href="{{ route('hr.staff.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                     ← Back to Staff List
                 </a>
             </div>

        </div>
    </div>
</x-app-layout>