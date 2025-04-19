{{-- resources/views/accounting/sms_gateways/_form.blade.php --}}
@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Gateway Name *') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $gateway->name ?? '') }}" required
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">A descriptive name (e.g., "Primary Twilio", "BulkProvider").</p>
    </div>

    {{-- Provider --}}
    <div>
        <label for="provider" class="block text-sm font-medium text-gray-700">{{ __('Provider *') }}</label>
        {{-- Consider changing to a <select> if you have a fixed list of providers --}}
        <input type="text" name="provider" id="provider" value="{{ old('provider', $gateway->provider ?? '') }}" required
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('provider') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Name of the SMS provider (e.g., Twilio, Vonage, Custom).</p>
    </div>

    {{-- Sender ID --}}
    <div>
        <label for="sender_id" class="block text-sm font-medium text-gray-700">{{ __('Sender ID / From Number *') }}</label>
        <input type="text" name="sender_id" id="sender_id" value="{{ old('sender_id', $gateway->sender_id ?? '') }}" required
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('sender_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">The phone number or alphanumeric ID messages will appear from.</p>
    </div>

    {{-- API Key --}}
    <div>
        <label for="api_key" class="block text-sm font-medium text-gray-700">{{ __('API Key / Username *') }}</label>
        {{-- Consider type="password" --}}
        <input type="text" name="api_key" id="api_key" value="{{ old('api_key', $gateway->api_key ?? '') }}" required
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('api_key') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- API Secret / Token --}}
    <div>
        <label for="api_secret" class="block text-sm font-medium text-gray-700">{{ __('API Secret / Token / Password') }}</label>
         {{-- Consider type="password" --}}
        <input type="text" name="api_secret" id="api_secret" value="{{ old('api_secret', $gateway->api_secret ?? '') }}"
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('api_secret') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Leave blank if not applicable or if you don't want to change it (on edit).</p>
    </div>

    {{-- API Endpoint --}}
    <div>
        <label for="api_endpoint" class="block text-sm font-medium text-gray-700">{{ __('API Endpoint (Optional)') }}</label>
        <input type="url" name="api_endpoint" id="api_endpoint" value="{{ old('api_endpoint', $gateway->api_endpoint ?? '') }}" placeholder="https://api.provider.com/..."
               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
        @error('api_endpoint') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Required only for some custom or specific gateway integrations.</p>
    </div>

    {{-- Configuration (JSON) --}}
    <div class="md:col-span-2">
        <label for="configuration" class="block text-sm font-medium text-gray-700">{{ __('Additional Configuration (JSON, Optional)') }}</label>
        <textarea name="configuration" id="configuration" rows="3" placeholder='{ "param1": "value1", "param2": true }'
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">{{ old('configuration', isset($gateway->configuration) ? json_encode($gateway->configuration, JSON_PRETTY_PRINT) : '') }}</textarea>
        @error('configuration') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Provider-specific settings in JSON format, if needed.</p>
    </div>

     {{-- Notes --}}
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes (Optional)') }}</label>
        <textarea name="notes" id="notes" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('notes', $gateway->notes ?? '') }}</textarea>
        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Status Checkboxes --}}
    <div class="md:col-span-2 space-y-2">
         <label class="flex items-center">
            <input type="hidden" name="is_active" value="0"> {{-- unchecked value --}}
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   @checked(old('is_active', $gateway->is_active ?? true))> {{-- Default to active on create --}}
            <span class="ml-2 text-sm text-gray-600">{{ __('Active Gateway') }}</span>
        </label>
         <label class="flex items-center">
             <input type="hidden" name="is_default" value="0"> {{-- unchecked value --}}
            <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   @checked(old('is_default', $gateway->is_default ?? false))>
            <span class="ml-2 text-sm text-gray-600">{{ __('Set as Default Gateway') }}</span>
             <p class="ml-2 text-xs text-gray-500">(Only one gateway can be default. Setting this will unset others.)</p>
        </label>
    </div>

</div>