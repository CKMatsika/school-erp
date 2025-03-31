<div>
    <form method="POST" action="{{ isset($circulation) ? route('book-circulations.update', $circulation) : route('book-circulations.store') }}">
        @csrf
        @if(isset($circulation))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Book Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Book Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Book Selection -->
                    <div>
                        <x-input-label for="book_id" :value="__('Book')" />
                        <select id="book_id" name="book_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ isset($circulation) && $circulation->status !== 'Pending' ? 'disabled' : '' }}>
                            <option value="">{{ __('Select Book') }}</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" 
                                    {{ (old('book_id', isset($circulation) ? $circulation->book_id : request('book_id'))) == $book->id ? 'selected' : '' }}
                                    {{ $book->available_copies < 1 && !isset($circulation) ? 'disabled' : '' }}>
                                    {{ $book->title }} ({{ $book->book_code }}) - {{ $book->available_copies }} {{ __('available') }}
                                </option>
                            @endforeach
                        </select>
                        @if(isset($circulation) && $circulation->status !== 'Pending')
                            <input type="hidden" name="book_id" value="{{ $circulation->book_id }}">
                        @endif
                        <x-input-error :messages="$errors->get('book_id')" class="mt-2" />
                    </div>
                    
                    <!-- Book Details -->
                    <div>
                        <x-input-label for="book_details" :value="__('Book Details')" />
                        <div id="book_details" class="mt-1 text-sm text-gray-500">
                            {{ __('Select a book to view details') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Member Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Member Selection -->
                    <div>
                        <x-input-label for="member_id" :value="__('Member')" />
                        <select id="member_id" name="member_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required {{ isset($circulation) && $circulation->status !== 'Pending' ? 'disabled' : '' }}>
                            <option value="">{{ __('Select Member') }}</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ (old('member_id', isset($circulation) ? $circulation->member_id : '')) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} 
                                    @if($member->member_id)
                                        ({{ $member->member_id }})
                                    @endif
                                    - {{ $member->member_type }}
                                </option>
                            @endforeach
                        </select>
                        @if(isset($circulation) && $circulation->status !== 'Pending')
                            <input type="hidden" name="member_id" value="{{ $circulation->member_id }}">
                        @endif
                        <x-input-error :messages="$errors->get('member_id')" class="mt-2" />
                    </div>
                    
                    <!-- Member Details -->
                    <div>
                        <x-input-label for="member_details" :value="__('Member Details')" />
                        <div id="member_details" class="mt-1 text-sm text-gray-500">
                            {{ __('Select a member to view details') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Circulation Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Circulation Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Issue Date -->
                    <div>
                        <x-input-label for="issue_date" :value="__('Issue Date')" />
                        <x-text-input id="issue_date" class="block mt-1 w-full" type="date" name="issue_date" :value="old('issue_date', isset($circulation) ? $circulation->issue_date->format('Y-m-d') : now()->format('Y-m-d'))" required {{ isset($circulation) && $circulation->status !== 'Pending' ? 'readonly' : '' }} />
                        <x-input-error :messages="$errors->get('issue_date')" class="mt-2" />
                    </div>

                    <!-- Due Date -->
                    <div>
                        <x-input-label for="due_date" :value="__('Due Date')" />
                        <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date', isset($circulation) ? $circulation->due_date->format('Y-m-d') : now()->addDays($defaultDueDays)->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($circulation) ? $circulation->status : 'Issued')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <!-- Return Date - Only shown if status is Returned -->
                    <div id="return_date_container" class="{{ (old('status', isset($circulation) ? $circulation->status : '')) == 'Returned' ? '' : 'hidden' }}">
                        <x-input-label for="return_date" :value="__('Return Date')" />
                        <x-text-input id="return_date" class="block mt-1 w-full" type="date" name="return_date" :value="old('return_date', isset($circulation) && $circulation->return_date ? $circulation->return_date->format('Y-m-d') : now()->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('return_date')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="col-span-2">
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', isset($circulation) ? $circulation->notes : '') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($circulation) ? __('Update Record') : __('Issue Book') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle status change to show/hide return date
        const statusSelect = document.getElementById('status');
        const returnDateContainer = document.getElementById('return_date_container');
        
        if (statusSelect && returnDateContainer) {
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Returned') {
                    returnDateContainer.classList.remove('hidden');
                } else {
                    returnDateContainer.classList.add('hidden');
                }
            });
        }
        
        // Fetch book details when a book is selected
        const bookSelect = document.getElementById('book_id');
        const bookDetailsElement = document.getElementById('book_details');
        
        if (bookSelect && bookDetailsElement) {
            bookSelect.addEventListener('change', function() {
                if (this.value) {
                    fetch(`/api/books/${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            let details = `<strong>${data.title}</strong><br>`;
                            details += `${__('Author')}: ${data.author}<br>`;
                            details += `${__('Category')}: ${data.category?.name || '-'}<br>`;
                            details += `${__('Available Copies')}: ${data.available_copies} / ${data.total_copies}`;
                            
                            bookDetailsElement.innerHTML = details;
                        })
                        .catch(error => {
                            bookDetailsElement.textContent = '{{ __("Error fetching book details") }}';
                            console.error('Error fetching book:', error);
                        });
                } else {
                    bookDetailsElement.textContent = '{{ __("Select a book to view details") }}';
                }
            });
            
            // Trigger change event if a value is already selected (edit mode)
            if (bookSelect.value) {
                bookSelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Fetch member details when a member is selected
        const memberSelect = document.getElementById('member_id');
        const memberDetailsElement = document.getElementById('member_details');
        
        if (memberSelect && memberDetailsElement) {
            memberSelect.addEventListener('change', function() {
                if (this.value) {
                    fetch(`/api/library-members/${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            let details = `<strong>${data.name}</strong><br>`;
                            details += `${__('ID')}: ${data.member_id || '-'}<br>`;
                            details += `${__('Type')}: ${data.member_type}<br>`;
                            details += `${__('Books Out')}: ${data.current_books_count}`;
                            
                            if (data.is_blocked) {
                                details += `<br><span class="text-red-600 font-medium">${__('Member is blocked from borrowing')}</span>`;
                            }
                            
                            memberDetailsElement.innerHTML = details;
                        })
                        .catch(error => {
                            memberDetailsElement.textContent = '{{ __("Error fetching member details") }}';
                            console.error('Error fetching member:', error);
                        });
                } else {
                    memberDetailsElement.textContent = '{{ __("Select a member to view details") }}';
                }
            });
            
            // Trigger change event if a value is already selected (edit mode)
            if (memberSelect.value) {
                memberSelect.dispatchEvent(new Event('change'));
            }
        }
        
        // Set due date based on member type
        if (memberSelect && document.getElementById('due_date')) {
            memberSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    const memberType = selectedOption.textContent.includes('Student') ? 'student' : 
                                      selectedOption.textContent.includes('Staff') ? 'staff' : 'external';
                    
                    // Set due date based on member type
                    let daysToAdd = 14; // Default
                    
                    if (memberType === 'student') {
                        daysToAdd = 14;
                    } else if (memberType === 'staff') {
                        daysToAdd = 30;
                    } else {
                        daysToAdd = 7;
                    }
                    
                    const issueDate = new Date(document.getElementById('issue_date').value);
                    const dueDate = new Date(issueDate);
                    dueDate.setDate(dueDate.getDate() + daysToAdd);
                    
                    document.getElementById('due_date').value = dueDate.toISOString().slice(0, 10);
                }
            });
        }
    });
</script>
@endpush