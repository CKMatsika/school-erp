<div>
    <form method="POST" action="{{ isset($ticket) ? route('support-ticket.update', $ticket) : route('support-ticket.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($ticket))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Ticket Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Ticket Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Subject -->
                    <div class="md:col-span-2">
                        <x-input-label for="subject" :value="__('Subject')" />
                        <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject', isset($ticket) ? $ticket->subject : '')" required autofocus />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>
                    
                    <!-- Requester -->
                    <div>
                        <x-input-label for="requester_id" :value="__('Requester')" />
                        <select id="requester_id" name="requester_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Requester') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('requester_id', isset($ticket) ? $ticket->requester_id : auth()->id())) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('requester_id')" class="mt-2" />
                    </div>
                    
                    <!-- Assignee -->
                    <div>
                        <x-input-label for="assignee_id" :value="__('Assign To')" />
                        <select id="assignee_id" name="assignee_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ (old('assignee_id', isset($ticket) ? $ticket->assignee_id : '')) == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ $staff->department }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('assignee_id')" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div>
                        <x-input-label for="category" :value="__('Category')" />
                        <select id="category" name="category" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ (old('category', isset($ticket) ? $ticket->category : '')) == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category')" class="mt-2" />
                    </div>

                    <!-- Priority -->
                    <div>
                        <x-input-label for="priority" :value="__('Priority')" />
                        <select id="priority" name="priority" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach(['Low', 'Medium', 'High', 'Critical'] as $priority)
                                <option value="{{ $priority }}" {{ (old('priority', isset($ticket) ? $ticket->priority : 'Medium')) == $priority ? 'selected' : '' }}>
                                    {{ $priority }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach(['New', 'Open', 'In Progress', 'Resolved', 'Closed'] as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($ticket) ? $ticket->status : 'New')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Due Date -->
                    <div>
                        <x-input-label for="due_date" :value="__('Due Date')" />
                        <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date', isset($ticket) && $ticket->due_date ? $ticket->due_date->format('Y-m-d') : '')" />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Ticket Details -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Ticket Details') }}</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Description -->
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="5" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('description', isset($ticket) ? $ticket->description : '') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <!-- Attachments -->
                    <div>
                        <x-input-label for="attachments" :value="__('Attachments')" />
                        <input id="attachments" name="attachments[]" type="file" multiple class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                        
                        @if(isset($ticket) && $ticket->attachments->count() > 0)
                            <div class="mt-2">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">{{ __('Existing Attachments:') }}</h4>
                                <ul class="list-disc list-inside text-sm">
                                    @foreach($ticket->attachments as $attachment)
                                        <li class="flex items-center">
                                            <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" class="text-blue-600 hover:underline mr-2">
                                                {{ $attachment->filename }}
                                            </a>
                                            <button type="button" onclick="document.getElementById('delete-attach-{{ $attachment->id }}').submit();" class="text-red-600 hover:text-red-900">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <form id="delete-attach-{{ $attachment->id }}" action="{{ route('ticket-attachments.destroy', $attachment) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Internal Notes (Admin Only) -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Internal Notes') }}</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-input-label for="internal_notes" :value="__('Internal Notes (Not visible to requester)')" />
                        <textarea id="internal_notes" name="internal_notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('internal_notes', isset($ticket) ? $ticket->internal_notes : '') }}</textarea>
                        <x-input-error :messages="$errors->get('internal_notes')" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($ticket) ? __('Update Ticket') : __('Create Ticket') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Priority affects the due date calculation
        const prioritySelect = document.getElementById('priority');
        const dueDateInput = document.getElementById('due_date');
        
        if (prioritySelect && dueDateInput && !dueDateInput.value) {
            prioritySelect.addEventListener('change', function() {
                // Set due date based on priority if not already set
                const today = new Date();
                let daysToAdd = 0;
                
                switch(this.value) {
                    case 'Critical':
                        daysToAdd = 1; // 24 hours
                        break;
                    case 'High':
                        daysToAdd = 3; // 3 days
                        break;
                    case 'Medium':
                        daysToAdd = 5; // 5 days
                        break;
                    case 'Low':
                        daysToAdd = 7; // 7 days
                        break;
                }
                
                if (daysToAdd > 0) {
                    today.setDate(today.getDate() + daysToAdd);
                    dueDateInput.value = today.toISOString().slice(0, 10);
                }
            });
            
            // Set initial due date based on selected priority
            if (!dueDateInput.value) {
                prioritySelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Category might affect available assignees in a real application
        const categorySelect = document.getElementById('category');
        const assigneeSelect = document.getElementById('assignee_id');
        
        if (categorySelect && assigneeSelect) {
            categorySelect.addEventListener('change', function() {
                // In a real app, you might fetch staff specialized in the selected category
                // This is a placeholder for that functionality
                console.log('Category changed to:', this.value);
            });
        }
    });
</script>
@endpush