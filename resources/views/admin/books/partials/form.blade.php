<div>
    <form method="POST" action="{{ isset($book) ? route('books.update', $book) : route('books.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($book))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Basic Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Title -->
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', isset($book) ? $book->title : '')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <!-- Book Code -->
                    <div>
                        <x-input-label for="book_code" :value="__('Book Code')" />
                        <x-text-input id="book_code" class="block mt-1 w-full" type="text" name="book_code" :value="old('book_code', isset($book) ? $book->book_code : $bookCode)" required />
                        <x-input-error :messages="$errors->get('book_code')" class="mt-2" />
                    </div>

                    <!-- Author -->
                    <div>
                        <x-input-label for="author" :value="__('Author')" />
                        <x-text-input id="author" class="block mt-1 w-full" type="text" name="author" :value="old('author', isset($book) ? $book->author : '')" required />
                        <x-input-error :messages="$errors->get('author')" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div>
                        <x-input-label for="category_id" :value="__('Category')" />
                        <select id="category_id" name="category_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (old('category_id', isset($book) ? $book->category_id : '')) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Publication Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Publication Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Publisher -->
                    <div>
                        <x-input-label for="publisher" :value="__('Publisher')" />
                        <x-text-input id="publisher" class="block mt-1 w-full" type="text" name="publisher" :value="old('publisher', isset($book) ? $book->publisher : '')" />
                        <x-input-error :messages="$errors->get('publisher')" class="mt-2" />
                    </div>

                    <!-- ISBN -->
                    <div>
                        <x-input-label for="isbn" :value="__('ISBN')" />
                        <x-text-input id="isbn" class="block mt-1 w-full" type="text" name="isbn" :value="old('isbn', isset($book) ? $book->isbn : '')" />
                        <x-input-error :messages="$errors->get('isbn')" class="mt-2" />
                    </div>

                    <!-- Publication Year -->
                    <div>
                        <x-input-label for="publication_year" :value="__('Publication Year')" />
                        <x-text-input id="publication_year" class="block mt-1 w-full" type="number" min="1800" max="{{ date('Y') }}" name="publication_year" :value="old('publication_year', isset($book) ? $book->publication_year : '')" />
                        <x-input-error :messages="$errors->get('publication_year')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Inventory Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Inventory Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Total Copies -->
                    <div>
                        <x-input-label for="total_copies" :value="__('Total Copies')" />
                        <x-text-input id="total_copies" class="block mt-1 w-full" type="number" min="1" name="total_copies" :value="old('total_copies', isset($book) ? $book->total_copies : '1')" required />
                        <x-input-error :messages="$errors->get('total_copies')" class="mt-2" />
                    </div>

                    <!-- Available Copies -->
                    <div>
                        <x-input-label for="available_copies" :value="__('Available Copies')" />
                        <x-text-input id="available_copies" class="block mt-1 w-full" type="number" min="0" name="available_copies" :value="old('available_copies', isset($book) ? $book->available_copies : '1')" required />
                        <x-input-error :messages="$errors->get('available_copies')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ (old('status', isset($book) ? $book->status : 'Available')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Additional Information') }}</h3>
                
                <!-- Description -->
                <div class="mb-4">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', isset($book) ? $book->description : '') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Book Cover Image -->
                    <div>
                        <x-input-label for="cover_image" :value="__('Cover Image')" />
                        <input id="cover_image" name="cover_image" type="file" accept="image/*" class="block mt-1 w-full text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100"
                        />
                        <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                        
                        @if(isset($book) && $book->cover_image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-32 h-auto object-cover rounded">
                            </div>
                        @endif
                    </div>

                    <!-- Location in Library -->
                    <div>
                        <x-input-label for="location" :value="__('Location in Library')" />
                        <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', isset($book) ? $book->location : '')" />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">{{ __('E.g., "Shelf A-12, Row 3"') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            
            <x-primary-button>
                {{ isset($book) ? __('Update Book') : __('Create Book') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate book code based on title and author
        const titleInput = document.getElementById('title');
        const authorInput = document.getElementById('author');
        const bookCodeInput = document.getElementById('book_code');
        
        if (titleInput && authorInput && bookCodeInput) {
            // Only generate code if it's not already set (for new books)
            if (!bookCodeInput.value || bookCodeInput.value === '{{ $bookCode ?? '' }}') {
                const generateBookCode = function() {
                    if (titleInput.value && authorInput.value) {
                        const title = titleInput.value.trim();
                        const author = authorInput.value.trim();
                        
                        // Take first 3 letters of title and first 2 letters of author's last name
                        let code = title.substring(0, 3).toUpperCase();
                        
                        // Get last name
                        const authorNames = author.split(' ');
                        if (authorNames.length > 1) {
                            const lastName = authorNames[authorNames.length - 1];
                            code += lastName.substring(0, 2).toUpperCase();
                        } else {
                            code += author.substring(0, 2).toUpperCase();
                        }
                        
                        // Add current year and random number
                        const year = new Date().getFullYear().toString().substr(2);
                        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                        
                        code += '-' + year + '-' + random;
                        
                        bookCodeInput.value = code;
                    }
                };
                
                titleInput.addEventListener('blur', generateBookCode);
                authorInput.addEventListener('blur', generateBookCode);
            }
        }
        
        // Ensure available copies don't exceed total copies
        const totalCopiesInput = document.getElementById('total_copies');
        const availableCopiesInput = document.getElementById('available_copies');
        
        if (totalCopiesInput && availableCopiesInput) {
            totalCopiesInput.addEventListener('change', function() {
                const total = parseInt(this.value);
                const available = parseInt(availableCopiesInput.value);
                
                if (available > total) {
                    availableCopiesInput.value = total;
                }
                
                availableCopiesInput.setAttribute('max', total);
            });
            
            // Set initial max value
            if (totalCopiesInput.value) {
                availableCopiesInput.setAttribute('max', totalCopiesInput.value);
            }
        }
    });
</script>
@endpush