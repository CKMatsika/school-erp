<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add SMS Gateway') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('components.flash-messages')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.sms.gateways.store') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Gateway Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700">Provider</label>
                            <select name="provider" id="provider" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Provider</option>
                                <option value="twilio" {{ old('provider') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                <option value="africas_talking" {{ old('provider') == 'africas_talking' ? 'selected' : '' }}>Africa's Talking</option>
                                <option value="infobip" {{ old('provider') == 'infobip' ? 'selected' : '' }}>Infobip</option>
                                <option value="custom" {{ old('provider') == 'custom' ? 'selected' : '' }}>Custom API</option>
                            </select>
                            @error('provider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="api_key" class="block text-sm font-medium text-gray-700">API Key / SID</label>
                                <input type="text" name="api_key" id="api_key" value="{{ old('api_key') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('api_key')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="api_secret" class="block text-sm font-medium text-gray-700">API Secret / Token</label>
                                <input type="password" name="api_secret" id="api_secret" value="{{ old('api_secret') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('api_secret')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="sender_id" class="block text-sm font-medium text-gray-700">Sender ID / From Number</label>
                                <input type="text" name="sender_id" id="sender_id" value="{{ old('sender_id') }}" placeholder="e.g., +12025550123 or YourSchool" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">May be a phone number or alphanumeric ID depending on provider</p>
                                @error('sender_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div id="api_endpoint_container">
                                <label for="api_endpoint" class="block text-sm font-medium text-gray-700">API Endpoint URL</label>
                                <input type="url" name="api_endpoint" id="api_endpoint" value="{{ old('api_endpoint') }}" placeholder="https://api.example.com/v1/messages" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Required for Custom API only</p>
                                @error('api_endpoint')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex items-center mt-4">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex items-center mt-4">
                                    <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default') == '1' ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_default" class="ml-2 block text-sm text-gray-700">Make Default Gateway</label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <a href="{{ route('accounting.sms.gateways') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Save Gateway
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide fields based on provider selection
        document.getElementById('provider').addEventListener('change', function() {
            const apiEndpointContainer = document.getElementById('api_endpoint_container');
            
            if (this.value === 'custom') {
                apiEndpointContainer.classList.remove('hidden');
            } else {
                apiEndpointContainer.classList.add('hidden');
            }
        });
        
        // Trigger change event on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('provider').dispatchEvent(new Event('change'));
        });
    </script>
</x-app-layout>