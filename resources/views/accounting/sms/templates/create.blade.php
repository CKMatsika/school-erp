<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create SMS Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('components.flash-messages')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('accounting.sms.templates.store') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Template Type</label>
                            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Template Type</option>
                                <option value="payment_reminder" {{ old('type') == 'payment_reminder' ? 'selected' : '' }}>Payment Reminder</option>
                                <option value="payment_receipt" {{ old('type') == 'payment_receipt' ? 'selected' : '' }}>Payment Receipt</option>
                                <option value="attendance_alert" {{ old('type') == 'attendance_alert' ? 'selected' : '' }}>Attendance Alert</option>
                                <option value="event_notification" {{ old('type') == 'event_notification' ? 'selected' : '' }}>Event Notification</option>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General Message</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Message Template</label>
                            <textarea name="content" id="content" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('content') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500"><span id="char_count">0</span>/160 characters. You can use placeholders like {STUDENT_NAME}, {PARENT_NAME}, {AMOUNT}, etc.</p>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <div class="flex items-center mt-4">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <a href="{{ route('accounting.sms.templates') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Save Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Available Template Variables</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variable</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Example</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{STUDENT_NAME}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Full name of the student</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">John Smith</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{PARENT_NAME}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Name of parent/guardian</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jane Smith</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{AMOUNT}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Amount due/paid</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$500.00</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{INVOICE_NO}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Invoice number</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">INV-2023-001</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{DUE_DATE}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Payment due date</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15/04/2023</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{SCHOOL_NAME}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Name of the school</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sunshine Academy</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{DATE}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Current date</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09/04/2023</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter for SMS
        document.getElementById('content').addEventListener('input', function() {
            const charCount = this.value.length;
            document.getElementById('char_count').textContent = charCount;
        });
        
        // Trigger input event on page load to update character count
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('content').dispatchEvent(new Event('input'));
        });
    </script>
</x-app-layout>