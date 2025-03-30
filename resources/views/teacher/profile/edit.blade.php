<!-- resources/views/teacher/profile/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Profile') }}
            </h2>
            <a href="{{ route('teacher.profile.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Profile Photo -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-4">Profile Photo</h3>
                        <div class="flex items-center">
                            <div class="mr-6">
                                <div class="w-24 h-24 rounded-full overflow-hidden">
                                    <img src="{{ $teacher->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($teacher->user->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $teacher->user->name }}" class="w-full h-full object-cover">
                                </div>
                            </div>
                            
                            <form action="{{ route('teacher.profile.update-photo') }}" method="POST" enctype="multipart/form-data" class="flex-1">
                                @csrf
                                <div class="flex flex-col md:flex-row md:items-center">
                                    <div class="flex-1">
                                        <input type="file" name="photo" id="photo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100" required>
                                    </div>
                                    <button type="submit" class="mt-2 md:mt-0 md:ml-2 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Update Photo
                                    </button>
                                </div>
                                @error('photo')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('teacher.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Personal Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Personal Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Name -->
                                    <div>
                                        <x-label for="name" :value="__('Full Name')" />
                                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $teacher->user->name)" required autofocus />
                                        @error('name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <x-label for="email" :value="__('Email')" />
                                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $teacher->email ?? $teacher->user->email)" required />
                                        @error('email')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Phone -->
                                    <div>
                                        <x-label for="phone" :value="__('Phone')" />
                                        <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $teacher->phone)" />
                                        @error('phone')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Date of Birth -->
                                    <div>
                                        <x-label for="date_of_birth" :value="__('Date of Birth')" />
                                        <x-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth', $teacher->date_of_birth ? $teacher->date_of_birth->format('Y-m-d') : '')" />
                                        @error('date_of_birth')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Gender -->
                                    <div>
                                        <x-label for="gender" :value="__('Gender')" />
                                        <select id="gender" name="gender" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender', $teacher->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $teacher->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender', $teacher->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Address -->
                                    <div class="md:col-span-2">
                                        <x-label for="address" :value="__('Address')" />
                                        <textarea id="address" name="address" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('address', $teacher->address) }}</textarea>
                                        @error('address')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Professional Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Professional Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Department -->
                                    <div>
                                        <x-label for="department" :value="__('Department')" />
                                        <select id="department" name="department" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept }}" {{ old('department', $teacher->department) === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Join Date -->
                                    <div>
                                        <x-label for="join_date" :value="__('Join Date')" />
                                        <x-input id="join_date" class="block mt-1 w-full" type="date" name="join_date" :value="old('join_date', $teacher->join_date ? $teacher->join_date->format('Y-m-d') : '')" />
                                        @error('join_date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Qualifications -->
                                    <div>
                                        <x-label for="qualifications" :value="__('Qualifications')" />
                                        <x-input id="qualifications" class="block mt-1 w-full" type="text" name="qualifications" :value="old('qualifications', $teacher->qualifications)" />
                                        @error('qualifications')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Experience -->
                                    <div>
                                        <x-label for="experience" :value="__('Experience (Years)')" />
                                        <x-input id="experience" class="block mt-1 w-full" type="number" name="experience" :value="old('experience', $teacher->experience)" min="0" step="0.5" />
                                        @error('experience')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Specialization -->
                                    <div class="md:col-span-2">
                                        <x-label for="specialization" :value="__('Specialization')" />
                                        <x-input id="specialization" class="block mt-1 w-full" type="text" name="specialization" :value="old('specialization', $teacher->specialization)" />
                                        @error('specialization')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Media Links (Optional) -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Social Media Links (Optional)</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- LinkedIn -->
                                    <div>
                                        <x-label for="linkedin" :value="__('LinkedIn Profile')" />
                                        <x-input id="linkedin" class="block mt-1 w-full" type="url" name="linkedin" :value="old('linkedin', $teacher->linkedin)" placeholder="https://linkedin.com/in/yourprofile" />
                                        @error('linkedin')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Twitter -->
                                    <div>
                                        <x-label for="twitter" :value="__('Twitter Profile')" />
                                        <x-input id="twitter" class="block mt-1 w-full" type="url" name="twitter" :value="old('twitter', $teacher->twitter)" placeholder="https://twitter.com/yourusername" />
                                        @error('twitter')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Personal Website -->
                                    <div>
                                        <x-label for="website" :value="__('Personal Website')" />
                                        <x-input id="website" class="block mt-1 w-full" type="url" name="website" :value="old('website', $teacher->website)" placeholder="https://yourwebsite.com" />
                                        @error('website')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bio -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Bio</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <x-label for="bio" :value="__('Professional Biography')" />
                                    <textarea id="bio" name="bio" rows="4" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" placeholder="Tell us about your teaching philosophy, experience, and interests...">{{ old('bio', $teacher->bio) }}</textarea>
                                    @error('bio')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('teacher.profile.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Change Password -->
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Change Password</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <form method="POST" action="{{ route('teacher.profile.update-password') }}">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Current Password -->
                                <div>
                                    <x-label for="current_password" :value="__('Current Password')" />
                                    <x-input id="current_password" class="block mt-1 w-full" type="password" name="current_password" required />
                                    @error('current_password')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="hidden md:block"></div>
                                
                                <!-- New Password -->
                                <div>
                                    <x-label for="password" :value="__('New Password')" />
                                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                                    @error('password')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Confirm Password -->
                                <div>
                                    <x-label for="password_confirmation" :value="__('Confirm New Password')" />
                                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                </div>
                            </div>
                            
                            <div class="flex justify-end mt-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>