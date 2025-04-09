```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Process Payroll') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
             @include('components.flash-messages')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Ensure controller passes $staffGroups --}}
                 <form method="POST" action="{{ route('hr.payroll.process.run') }}" onsubmit="return confirm('Are you sure you want to process payroll for the selected period/group? This action cannot be easily undone.');">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Select Period and Group</h3>

                         <div>
                            <x-input-label for="pay_period_month" :value="__('Pay Period (Month/Year)')" class="required"/>
                            {{-- Use month input type --}}
                            <x-text-input id="pay_period_month" name="pay_period_month" type="month" class="mt-1 block w-full" :value="old('pay_period_month', now()->format('Y-m'))" required />
                            <x-input-error :messages="$errors->get('pay_period_month')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Select the month and year for which to process payroll.</p>
                        </div>

                        <div>
                            <x-input-label for="department" :value="__('Department (Optional)')" />
                            <select id="department" name="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Process All Departments --</option>
                                @foreach($staffGroups as $group)
                                    <option value="{{ $group->department }}" {{ old('department') == $group->department ? 'selected' : '' }}>
                                        {{ $group->department }} ({{ $group->count }} Staff)
                                    </option>
                                @endforeach
                            </select>
                             <x-input-error :messages="$errors->get('department')" class="mt-2" />
                              <p class="text-xs text-gray-500 mt-1">Leave blank to process payroll for all eligible staff in the selected period.</p>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                            <p class="text-sm text-yellow-700">
                                <span class="font-bold">Warning:</span> Running payroll will generate payslips for all eligible staff in the selected period/department who haven't already been processed. Please ensure previous periods are closed and data is accurate.
                            </p>
                        </div>

                    </div>
                     <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                        <x-primary-button type="submit" class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:ring-red-500">
                            {{ __('Run Payroll Process') }}
                        </x-primary-button>
                    </div>
                 </form>
            </div>
        </div>
    </div>
</x-app-layout>