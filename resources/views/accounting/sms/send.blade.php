<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send SMS Message') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>{{ session('success') }}</p></div>
                    @endif
                     @if(session('warning'))
                        <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert"><p>{{ session('warning') }}</p></div>
                     @endif
                     @if(session('error'))
                         <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>{{ session('error') }}</p></div>
                     @endif
                     @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">...</div>
                    @endif

                    <form method="POST" action="{{ route('accounting.sms.process-send') }}">
                        @csrf

                        {{-- Recipients (Example: Multi-select dropdown) --}}
                        <div class="mb-4">
                            <x-input-label for="recipients" :value="__('Recipients *')" />
                            {{-- Use a multi-select library like Select2 or TomSelect for better UX --}}
                            <select name="recipients[]" id="recipients" multiple required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm h-40">
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}" {{ in_array($contact->id, old('recipients', [])) ? 'selected' : '' }}>
                                        {{ $contact->name }} ({{ $contact->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('recipients')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Select one or more contacts (Hold Ctrl/Cmd to select multiple).</p>
                        </div>

                         {{-- Message --}}
                        <div class="mb-4">
                            <x-input-label for="message" :value="__('Message *')" />
                            <textarea name="message" id="message" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                            {{-- Optional: Add character counter --}}
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button type="submit">
                                Send Message
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>