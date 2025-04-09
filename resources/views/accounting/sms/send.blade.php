<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send SMS Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('components.flash-messages')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Send SMS Message</h3>
                    
                    <form method="POST" action="{{ route('accounting.sms.process-send') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="recipient_type" class="block text-sm font-medium text-gray-700">Recipient Type</label>
                            <select id="recipient_type" name="recipient_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="individual">Individual Numbers</option>
                                <option value="parents">All Parents</option>
                                <option value="class">Specific Class</option>
                                <option value="staff">All Staff</option>
                                <option value="debtors">Fee Debtors</option>
                            </select>
                        </div>
                        
                        <div id="individual_section">
                            <label for="recipients" class="block text-sm font-medium text-gray-700">Recipients (comma separated phone numbers)</label>
                            <textarea id="recipients" name="recipients" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            <p class="mt-1 text-sm text-gray-500">Enter phone numbers in international format (e.g., +254712345678)</p>
                        </div>
                        
                        <div id="class_section" class="hidden">
                            <label for="class_id" class="block text-sm font-medium text-gray-700">Select Class</label>
                            <select id="class_id" name="class_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select a class</option>
                                @if(isset($classes))
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <div>
                            <label for="gateway_id" class="block text-sm font-medium text-gray-700">SMS Gateway</label>
                            <select id="gateway_id" name="gateway_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Gateway</option>
                                @if(isset($gateways))
                                    @foreach($gateways as $gateway)
                                        <option value="{{ $gateway->id }}">{{ $gateway->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <div>
                            <label for="template_id" class="block text-sm font-medium text-gray-700">Use Template (
                                <label for="template_id" class="block text-sm font-medium text-gray-700">Use Template (Optional)</label>
                            <select id="template_id" name="template_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Template or Write Custom Message</option>
                                @if(isset($templates))
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea id="message" name="message" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            <p class="mt-1 text-sm text-gray-500"><span id="char_count">0</span>/160 characters. Messages longer than 160 characters may be split into multiple SMS.</p>
                        </div>
                        
                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule for later (Optional)</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Send SMS
                            </button>
                            
                            <span class="text-sm text-gray-500">
                                Estimated cost: <span id="cost_estimate">$0.00</span>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SMS Best Practices</h3>
                    
                    <div class="prose max-w-none">
                        <ul>
                            <li>Keep messages concise and to the point.</li>
                            <li>Include the school name to identify the sender.</li>
                            <li>Avoid sending SMS during late hours.</li>
                            <li>For urgent notifications, consider sending SMS in addition to email.</li>
                            <li>Remember that some recipients may incur charges for receiving SMS.</li>
                            <li>Include opt-out instructions for bulk messages.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter for SMS
        document.getElementById('message').addEventListener('input', function() {
            const charCount = this.value.length;
            document.getElementById('char_count').textContent = charCount;
            
            // Simple cost estimator
            const numMessages = Math.ceil(charCount / 160);
            const costPerMessage = 0.01; // Example cost in dollars
            const totalCost = (numMessages * costPerMessage * document.getElementById('recipients').value.split(',').filter(x => x.trim()).length).toFixed(2);
            document.getElementById('cost_estimate').textContent = '$' + totalCost;
        });
        
        // Show/hide sections based on recipient type
        document.getElementById('recipient_type').addEventListener('change', function() {
            const individualSection = document.getElementById('individual_section');
            const classSection = document.getElementById('class_section');
            
            if (this.value === 'individual') {
                individualSection.classList.remove('hidden');
                classSection.classList.add('hidden');
            } else if (this.value === 'class') {
                individualSection.classList.add('hidden');
                classSection.classList.remove('hidden');
            } else {
                individualSection.classList.add('hidden');
                classSection.classList.add('hidden');
            }
        });
        
        // Populate message when template is selected
        document.getElementById('template_id').addEventListener('change', function() {
            if (this.value) {
                // This would typically be an AJAX call to get template content
                // For demo purposes, we'll just simulate it
                fetch(`/accounting/sms/templates/${this.value}/content`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.content) {
                            document.getElementById('message').value = data.content;
                            // Trigger the input event to update character count
                            document.getElementById('message').dispatchEvent(new Event('input'));
                        }
                    })
                    .catch(error => console.error('Error fetching template:', error));
            }
        });
    </script>
</x-app-layout>