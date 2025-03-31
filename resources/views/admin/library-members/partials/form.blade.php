<div>
    <form method="POST" action="{{ isset($member) ? route('library-members.update', $member) : route('library-members.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($member))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Basic Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', isset($member) ? $member->name : '')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Member ID -->
                    <div>
                        <x-input-label for="member_id" :value="__('Member ID')" />
                        <x-text-input id="member_id" class="block mt-1 w-full" type="text" name="member_id" :value="old('member_id', isset($member) ? $member->member_id : $memberId)" />
                        <x-input-error :messages="$errors->get('member_id')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', isset($member) ? $member->email : '')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <x-input-label for="contact_no" :value="__('Contact Number')" />
                        <x-text-input id="contact_no" class="block mt-1 w-full" type="text" name="contact_no" :value="old('contact_no', isset($member) ? $member->contact_no : '')" />
                        <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
                    </div>

                    <!-- Member Type -->
                    <div>
                        <x-input-label for="member_type" :value="__('Member Type')" />
                        <select id="member_type" name="member_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Type') }}</option>
                            <option value="Student" {{ (old('member_type', isset($member) ? $member->member_type : '')) == 'Student' ? 'selected' : '' }}>{{ __('Student') }}</option>
                            <option value="Staff" {{ (old('member_type', isset($member) ? $member->member_type : '')) == 'Staff' ? 'selected' : '' }}>{{ __('Staff') }}</option>
                            <option value="External" {{ (old('member_type', isset($member) ? $member->member_type : '')) == 'External' ? 'selected' : '' }}>{{ __('External') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('member_type')" class="mt-2" />
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                        <x-text-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth', isset($member) && $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Membership Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Membership Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Membership Date -->
                    <div>
                        <x-input-label for="membership_date" :value="__('Membership Date')" />
                        <x-text-input id="membership_date" class="block mt-1 w-full" type="date" name="membership_date" :value="old('membership_date', isset($member) && $member->membership_date ? $member->membership_date->format('Y-m-d') : now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('membership_date')" class="mt-2" />
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <x-input-label for="expiry_date" :value="__('Expiry Date')" />
                        <x-text-input id="expiry_date" class="block mt-1 w-full" type="date" name="expiry_date" :value="old('expiry_date', isset($member) && $member->expiry_date ? $member->expiry_date->format('Y-m-d') : now()->addYear()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                    </div>

                    <!-- Max Books Allowed -->
                    <div>
                        <x-input-label for="max_books_allowed" :value="__('Max Books Allowed')" />
                        <x-text-input id="max_books_allowed" class="block mt-1 w-full" type="number" min="1" name="max_books_allowed" :value="old('max_books_allowed', isset($member) ? $member->max_books_allowed : '3')" required />
                        <x-input-error :messages="$errors->get('max_books_allowed')" class="mt-2" />
                    </div>

                    <!-- Is Active -->
                    <div>
                        <x-input-label for="is_active" :value="__('Active Status')" />
                        <select id="is_active" name="is_active" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="1" {{ (old('is_active', isset($member) ? $member->is_active : '1')) == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="0" {{ (old('is_active', isset($member) ? $member->is_active : '')) === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                    </div>

                    <!-- Is Blocked -->
                    <div>
                        <x-input-label for="is_blocked" :value="__('Block Status')" />
                        <select id="is_blocked" name="is_blocked" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="0" {{ (old('is_blocked', isset($member) ? $member->is_blocked : '')) === '0' || old('is_blocked', isset($member) ? $member->is_blocked : '') === false ? 'selected' : '' }}>{{ __('Not Blocked') }}</option>
                            <option value="1" {{ (old('is_blocked', isset($member) ? $member->is_blocked : '')) == '1' ? 'selected' : '' }}>{{ __('Blocked') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('is_blocked')" class="mt-2" />
                    </div>

                    <!-- Block Reason (shown only when is_blocked is true) -->
                    <div id="block_reason_container" class="{{ (old('is_blocked', isset($member) ? $member->is_blocked : '')) == '1' ? '' : 'hidden' }}">
                        <x-input-label for="block_reason" :value="__('Block Reason')" />
                        <x-text-input id="block_reason" class="block mt-1 w-full" type="text" name="block_reason" :value="old('block_reason', isset($member) ? $member->block_reason : '')" />
                        <x-input-error :messages="$errors->get('block_reason')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Additional Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Address -->
                    <div>
                        <x-input-label for="address" :value="__('Address')" />
                        <textarea id="address" name="address" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('address', isset($member) ? $member->address : '') }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', isset($member) ? $member->notes : '') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <!-- Member Photo -->
                    <div>
                        <x-input-label for="photo" :value="__('Member Photo')" />
                        <input id="photo" name="photo" type="file" accept="image/*" class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        
                        @if(isset($member) && $member->photo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}" class="w-32 h-32 object-cover rounded">
                            </div>
                        @endif
                    </div>

                    <!-- ID Proof -->
                    <div>
                        <x-input-label for="id_proof" :value="__('ID Proof')" />
                        <input id="id_proof" name="id_proof" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('id_proof')" class="mt-2" />
                        
                        @if(isset($member) && $member->id_proof)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $member->id_proof) }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ __('View ID Proof') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($member) ? __('Update Member') : __('Create Member') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate member ID based on name and member type
        const nameInput = document.getElementById('name');
        const memberTypeSelect = document.getElementById('member_type');
        const memberIdInput = document.getElementById('member_id');
        
        if (nameInput && memberTypeSelect && memberIdInput) {
            // Only generate ID if it's not already set (for new members)
            if (!memberIdInput.value || memberIdInput.value === '{{ $memberId ?? '' }}') {
                const generateMemberId = function() {
                    if (nameInput.value && memberTypeSelect.value) {
                        const name = nameInput.value.trim();
                        const type = memberTypeSelect.value;
                        
                        // Take first letter of name + first letter of last name
                        let code = name.charAt(0).toUpperCase();
                        
                        // Get last name
                        const nameWords = name.split(' ');
                        if (nameWords.length > 1) {
                            code += nameWords[nameWords.length - 1].charAt(0).toUpperCase();
                        }
                        
                        // Add first two letters of type
                        code += type.substring(0, 2).toUpperCase();
                        
                        // Add current year and random number
                        const year = new Date().getFullYear().toString().substr(2);
                        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                        
                        code += year + random;
                        
                        memberIdInput.value = code;
                    }
                };
                
                nameInput.addEventListener('blur', generateMemberId);
                memberTypeSelect.addEventListener('change', generateMemberId);
            }
        }
        
        // Handle is_blocked change to show/hide block reason
        const isBlockedSelect = document.getElementById('is_blocked');
        const blockReasonContainer = document.getElementById('block_reason_container');
        
        if (isBlockedSelect && blockReasonContainer) {
            isBlockedSelect.addEventListener('change', function() {
                if (this.value === '1') {
                    blockReasonContainer.classList.remove('hidden');
                } else {
                    blockReasonContainer.classList.add('hidden');
                }
            });
        }
        
        // Set max allowed books based on member type
        const maxBooksInput = document.getElementById('max_books_allowed');
        
        if (memberTypeSelect && maxBooksInput) {
            memberTypeSelect.addEventListener('change', function() {
                // Don't change if user has already set a value
                if (maxBooksInput.value == '3') { // Default value
                    if (this.value === 'Student') {
                        maxBooksInput.value = '3';
                    } else if (this.value === 'Staff') {
                        maxBooksInput.value = '5';
                    } else if (this.value === 'External') {
                        maxBooksInput.value = '2';
                    }
                }
            });
        }
    });
</script>
@endpush