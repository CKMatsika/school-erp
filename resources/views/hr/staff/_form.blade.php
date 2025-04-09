@csrf
{{-- Use PUT/PATCH method spoofing for the update route --}}
@isset($staff)
    @method('PUT') {{-- Or PATCH --}}
@endisset

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Personal Details --}}
    <div class="md:col-span-3"><h4 class="text-md font-semibold text-gray-700 border-b pb-2 mb-4">Personal Details</h4></div>

    <div>
        <x-input-label for="staff_number" :value="__('Staff Number')" />
        <x-text-input id="staff_number" class="block mt-1 w-full bg-gray-100" type="text" name="staff_number" :value="old('staff_number', $staffNumber ?? $staff->staff_number ?? '')" required readonly />
        <x-input-error :messages="$errors->get('staff_number')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="first_name" :value="__('First Name')" class="required" />
        <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $staff->first_name ?? '')" required />
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="last_name" :value="__('Last Name')" class="required" />
        <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $staff->last_name ?? '')" required />
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="middle_name" :value="__('Middle Name (Optional)')" />
        <x-text-input id="middle_name" class="block mt-1 w-full" type="text" name="middle_name" :value="old('middle_name', $staff->middle_name ?? '')" />
        <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="gender" :value="__('Gender')" />
        <select id="gender" name="gender" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select Gender</option>
            <option value="male" {{ old('gender', $staff->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender', $staff->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ old('gender', $staff->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
        <x-text-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth', isset($staff->date_of_birth) ? $staff->date_of_birth->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
    </div>

    {{-- Contact Details --}}
     <div class="md:col-span-3"><h4 class="text-md font-semibold text-gray-700 border-b pb-2 mb-4 mt-6">Contact Details</h4></div>
     <div>
        <x-input-label for="email" :value="__('Email Address')" class="required" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $staff->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
        @isset($staff) {{-- Only show on edit --}}
        <div class="mt-1 flex items-center">
            <input type="checkbox" id="update_user_email" name="update_user_email" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="update_user_email" class="ml-2 text-xs text-gray-600">{{ __('Also update linked user account email?') }}</label>
        </div>
        @endisset
    </div>
     <div>
        <x-input-label for="phone_number" :value="__('Phone Number')" />
        <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number" :value="old('phone_number', $staff->phone_number ?? '')" />
        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
    </div>
     <div class="md:col-span-3">
        <x-input-label for="address" :value="__('Address')" />
        <textarea id="address" name="address" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('address', $staff->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    {{-- Employment Details --}}
    <div class="md:col-span-3"><h4 class="text-md font-semibold text-gray-700 border-b pb-2 mb-4 mt-6">Employment Details</h4></div>
     <div>
        <x-input-label for="date_joined" :value="__('Date Joined')" class="required" />
        <x-text-input id="date_joined" class="block mt-1 w-full" type="date" name="date_joined" :value="old('date_joined', isset($staff->date_joined) ? $staff->date_joined->format('Y-m-d') : '')" required />
        <x-input-error :messages="$errors->get('date_joined')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="employment_type" :value="__('Employment Type')" class="required"/>
        <select id="employment_type" name="employment_type" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select Type</option>
            <option value="permanent" {{ old('employment_type', $staff->employment_type ?? '') == 'permanent' ? 'selected' : '' }}>Permanent</option>
            <option value="contract" {{ old('employment_type', $staff->employment_type ?? '') == 'contract' ? 'selected' : '' }}>Contract</option>
            <option value="temporary" {{ old('employment_type', $staff->employment_type ?? '') == 'temporary' ? 'selected' : '' }}>Temporary</option>
            <option value="intern" {{ old('employment_type', $staff->employment_type ?? '') == 'intern' ? 'selected' : '' }}>Intern</option>
        </select>
        <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="staff_type" :value="__('Staff Type')" class="required"/>
        <select id="staff_type" name="staff_type" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Select Type</option>
            <option value="teaching" {{ old('staff_type', $staff->staff_type ?? '') == 'teaching' ? 'selected' : '' }}>Teaching</option>
            <option value="non-teaching" {{ old('staff_type', $staff->staff_type ?? '') == 'non-teaching' ? 'selected' : '' }}>Non-Teaching</option>
            <option value="admin" {{ old('staff_type', $staff->staff_type ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <x-input-error :messages="$errors->get('staff_type')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="job_title" :value="__('Job Title')" class="required"/>
        <x-text-input id="job_title" class="block mt-1 w-full" type="text" name="job_title" :value="old('job_title', $staff->job_title ?? '')" required />
        <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="department" :value="__('Department (Optional)')" />
        <x-text-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department', $staff->department ?? '')" list="department-list" />
         {{-- Datalist for suggestions --}}
        <datalist id="department-list">
            @foreach($departments ?? [] as $dept)
                <option value="{{ $dept }}">
            @endforeach
        </datalist>
        <x-input-error :messages="$errors->get('department')" class="mt-2" />
    </div>
     <div>
        <x-input-label for="basic_salary" :value="__('Basic Salary (Optional)')" />
        <x-text-input id="basic_salary" class="block mt-1 w-full" type="number" step="0.01" min="0" name="basic_salary" :value="old('basic_salary', $staff->basic_salary ?? '')" />
        <x-input-error :messages="$errors->get('basic_salary')" class="mt-2" />
    </div>

     {{-- Status --}}
     <div class="md:col-span-3">
        <label for="is_active" class="inline-flex items-center">
            <input type="hidden" name="is_active" value="0"> {{-- Value if checkbox is unchecked --}}
            <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50" name="is_active" value="1" {{ old('is_active', $staff->is_active ?? true) ? 'checked' : '' }}>
            <span class="ml-2 text-sm text-gray-600">{{ __('Staff Member is Active') }}</span>
        </label>
     </div>

     {{-- Notes --}}
    <div class="md:col-span-3">
        <x-input-label for="notes" :value="__('Notes (Optional)')" />
        <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', $staff->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

     {{-- User Account --}}
     @if(!isset($staff) || !$staff->user) {{-- Only show on create, or if user doesn't exist yet on edit --}}
        <div class="md:col-span-3 mt-6 border-t pt-6">
            <h4 class="text-md font-semibold text-gray-700 mb-4">User Account</h4>
            <div class="flex items-start">
                <div class="flex items-center h-5">
                     <input type="hidden" name="create_user_account" value="0">
                     <input id="create_user_account" name="create_user_account" type="checkbox" value="1" {{ old('create_user_account') ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="create_user_account" class="font-medium text-gray-700">{{ __('Create User Account?') }}</label>
                    <p class="text-gray-500 text-xs">If checked, a user login will be created using the email address above. A random password will be generated (inform the user).</p>
                </div>
            </div>
            {{-- Role Selection (Example using Spatie Roles) --}}
            {{-- @if(isset($roles))
            <div id="user-role-div" class="{{ old('create_user_account') ? '' : 'hidden' }} mt-4">
                 <x-input-label for="user_role" :value="__('Assign Role')" />
                 <select name="user_role" id="user_role" class="block mt-1 w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                     <option value="">-- Select Role --</option>
                     @foreach($roles as $role)
                         <option value="{{ $role }}" {{ old('user_role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                     @endforeach
                 </select>
                 <x-input-error :messages="$errors->get('user_role')" class="mt-2" />
            </div>
            @endif --}}
        </div>
     @elseif(isset($staff) && isset($user) && isset($roles)) {{-- Show role on edit if user exists --}}
        <div class="md:col-span-3 mt-6 border-t pt-6">
             <h4 class="text-md font-semibold text-gray-700 mb-4">User Account Role</h4>
             <p class="text-sm text-gray-600 mb-2">Linked User: {{ $user->email }}</p>
              <div>
                 <x-input-label for="user_role" :value="__('Assign Role')" />
                 <select name="user_role" id="user_role" class="block mt-1 w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                     <option value="">-- No Role --</option>
                     @foreach($roles as $role)
                         {{-- Check if user has role --}}
                         <option value="{{ $role }}" {{ old('user_role', $user->hasRole($role) ? $role : '') == $role ? 'selected' : '' }}>
                             {{ $role }}
                         </option>
                     @endforeach
                 </select>
                 <x-input-error :messages="$errors->get('user_role')" class="mt-2" />
            </div>
        </div>
     @endif

</div>

@if(!isset($staff) || !$staff->user) {{-- Script for create form --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const createUserCheckbox = document.getElementById('create_user_account');
        const roleDiv = document.getElementById('user-role-div');

        function toggleRoleDiv() {
            if (roleDiv) {
                 roleDiv.classList.toggle('hidden', !createUserCheckbox.checked);
            }
        }

        if(createUserCheckbox) {
            createUserCheckbox.addEventListener('change', toggleRoleDiv);
            // Initial check
            toggleRoleDiv();
        }
    });
</script>
@endif