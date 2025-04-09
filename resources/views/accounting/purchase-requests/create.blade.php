<x-app-layout>
    <x-slot name="header">
        {{-- Title adjusted to match the intended module --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Purchase Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     {{-- CORRECTED ROUTE NAME HERE --}}
                    <form method="POST" action="{{ route('accounting.purchase-requests.store') }}" id="requisition-form">
                        @csrf

                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="department" :value="__('Department')" />
                                    {{-- Assuming this field is in your purchase_requests table --}}
                                    <x-text-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department')" />
                                    <x-input-error :messages="$errors->get('department')" class="mt-2" />
                                </div>

                                {{-- Removing Priority as it wasn't in the PurchaseRequest migration --}}
                                {{-- If you added it, uncomment this section
                                <div>
                                    <x-input-label for="priority" :value="__('Priority')" />
                                    <select id="priority" name="priority" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                                </div>
                                --}}

                                <div>
                                    <x-input-label for="date_requested" :value="__('Date Requested')" />
                                    <x-text-input id="date_requested" class="block mt-1 w-full" type="date" name="date_requested" :value="old('date_requested', now()->format('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('date_requested')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="date_required" :value="__('Required Date')" />
                                    <x-text-input id="date_required" class="block mt-1 w-full" type="date" name="date_required" :value="old('date_required')" />
                                    <x-input-error :messages="$errors->get('required_date')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Additional Information</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <x-input-label for="notes" :value="__('Notes / Purpose')" />
                                    <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                            {{-- Hidden fields needed by controller --}}
                            <input type="hidden" name="pr_number" value="{{ $prNumber ?? '' }}">
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">
                        </div>

                         {{-- Removed dynamic items section as the controller redirects to manage items after saving header --}}
                        {{-- You would add items on the purchase-requests.items view --}}

                        <div class="flex items-center justify-end mt-6">
                            {{-- Removed "Save as Draft" checkbox as controller forces draft status initially --}}
                            <a href="{{ route('accounting.purchase-requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button id="submit-btn">
                                {{-- Button text changed to reflect workflow --}}
                                {{ __('Save Header & Add Items') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Removed Item Template and related JavaScript as items are managed on a separate page --}}

</x-app-layout>