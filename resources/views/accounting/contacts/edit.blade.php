<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Contact') }}: {{ $contact->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Whoops!</strong>
                            <span class="block sm:inline">Something went wrong.</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounting.contacts.update', $contact->id) }}">
                        @csrf
                        @method('PUT') {{-- Or PATCH --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Contact Type --}}
                            <div class="md:col-span-2">
                                <x-input-label for="contact_type" :value="__('Contact Type *')" />
                                <div class="mt-2 space-x-4">
                                     <label class="inline-flex items-center">
                                        <input type="radio" name="contact_type" value="customer" class="form-radio text-indigo-600" {{ old('contact_type', $contact->contact_type) == 'customer' ? 'checked' : '' }} required>
                                        <span class="ml-2">{{ __('Customer') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="contact_type" value="vendor" class="form-radio text-indigo-600" {{ old('contact_type', $contact->contact_type) == 'vendor' ? 'checked' : '' }}>
                                        <span class="ml-2">{{ __('Vendor') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="contact_type" value="student" class="form-radio text-indigo-600" {{ old('contact_type', $contact->contact_type) == 'student' ? 'checked' : '' }}>
                                        <span class="ml-2">{{ __('Student') }}</span>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('contact_type')" class="mt-2" />
                            </div>

                            {{-- Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Name *')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $contact->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- Email --}}
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $contact->email)" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            {{-- Phone --}}
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $contact->phone)" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                             {{-- Tax Number --}}
                             <div>
                                <x-input-label for="tax_number" :value="__('Tax / ID Number')" />
                                <x-text-input id="tax_number" class="block mt-1 w-full" type="text" name="tax_number" :value="old('tax_number', $contact->tax_number)" />
                                <x-input-error :messages="$errors->get('tax_number')" class="mt-2" />
                            </div>

                            {{-- Address --}}
                            <div class="md:col-span-2">
                                <x-input-label for="address" :value="__('Address')" />
                                <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('address', $contact->address) }}</textarea>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            {{-- City --}}
                            <div>
                                <x-input-label for="city" :value="__('City')" />
                                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $contact->city)" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>

                            {{-- State --}}
                            <div>
                                <x-input-label for="state" :value="__('State / Province')" />
                                <x-text-input id="state" class="block mt-1 w-full" type="text" name="state" :value="old('state', $contact->state)" />
                                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                            </div>

                            {{-- Postal Code --}}
                            <div>
                                <x-input-label for="postal_code" :value="__('Postal Code')" />
                                <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $contact->postal_code)" />
                                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                            </div>

                            {{-- Country --}}
                            <div>
                                <x-input-label for="country" :value="__('Country')" />
                                <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country', $contact->country)" />
                                <x-input-error :messages="$errors->get('country')" class="mt-2" />
                            </div>

                            {{-- Student Select (Conditional) --}}
                             <div id="student_select_div" class="md:col-span-2" style="display: {{ old('contact_type', $contact->contact_type) == 'student' ? 'block' : 'none' }};">
                                {{-- Modified Label --}}
                                <x-input-label for="student_id" :value="__('Link to Student (Optional)')" />
                                <select id="student_id" name="student_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">-- Select Student --</option>
                                     @isset($students)
                                        @foreach ($students as $student)
                                            {{-- Ensure $contact->student_id is compared correctly --}}
                                            <option value="{{ $student->id }}" {{ old('student_id', $contact->student_id) == $student->id ? 'selected' : '' }}>
                                                {{ $student->last_name ?? '' }}, {{ $student->first_name ?? '' }} {{ $student->middle_name ?? '' }} (ID: {{ $student->student_number ?? $student->id }})
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                                <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                                <p class="mt-1 text-xs text-gray-500">Link this accounting contact to an existing student record (optional).</p>
                            </div>


                            {{-- Is Active --}}
                            <div class="md:col-span-2">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_active', $contact->is_active) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                </label>
                                 <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('accounting.contacts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Contact') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

     {{-- *** UPDATED SCRIPT *** --}}
     <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contactTypeRadios = document.querySelectorAll('input[name="contact_type"]');
            const studentSelectDiv = document.getElementById('student_select_div');
            const studentSelect = document.getElementById('student_id');

            function toggleStudentSelect() {
                let show = false;
                contactTypeRadios.forEach(radio => {
                    if (radio.checked && radio.value === 'student') {
                        show = true;
                    }
                });

                if (show) {
                    studentSelectDiv.style.display = 'block';
                    // studentSelect.required = true; // <-- REMOVED required attribute logic
                } else {
                    studentSelectDiv.style.display = 'none';
                    // studentSelect.required = false; // <-- REMOVED required attribute logic
                    // Do not reset value here on edit, keep the linked student if applicable
                }
            }

            // Initial check on page load
            toggleStudentSelect();

            // Add event listener
            contactTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleStudentSelect);
            });
        });
    </script>
     {{-- *** END OF UPDATED SCRIPT *** --}}
</x-app-layout>