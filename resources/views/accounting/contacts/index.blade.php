<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Contacts') }} {{ isset($type) ? '- ' . ucfirst($type) . 's' : '' }}
            </h2>
            <div class="flex space-x-2">
                {{-- Existing Filter Buttons --}}
                <a href="{{ route('accounting.contacts.type', 'customer') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Customers
                </a>
                <a href="{{ route('accounting.contacts.type', 'vendor') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Vendors
                </a>
                <a href="{{ route('accounting.contacts.type', 'student') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Students
                </a>

                {{-- ===== ADDED IMPORT BUTTON ===== --}}
                {{-- @can('import', App\Models\Accounting\Contact::class) --}} {{-- Add authorization check if needed --}}
                <a href="{{ route('accounting.contacts.import.form') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 active:bg-teal-900 focus:outline-none focus:border-teal-900 focus:ring ring-teal-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{-- Upload Icon --}}
                    <svg class="w-4 h-4 mr-1 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import Contacts
                </a>
                {{-- @endcan --}}
                {{-- ===== END OF ADDED BUTTON ===== --}}

                {{-- Existing Create Button --}}
                <a href="{{ route('accounting.contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create Contact
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Flash Messages --}}
                    @include('components.flash-messages') {{-- Assuming this component exists --}}

                    {{-- Filters could go here if needed --}}
                    {{-- <div class="mb-4"> ... Filter Form ... </div> --}}

                    <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th> {{-- Changed text-align --}}
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th> {{-- Changed text-align --}}
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> {{-- Changed text-align --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($contacts as $contact)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{-- Removed extra div/ml-4 --}}
                                            <div class="text-sm font-medium text-gray-900">{{ $contact->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $contact->student ? 'Student ID: ' . ($contact->student->student_number ?? $contact->student->id) : '' }}</div> {{-- Added student_number preference --}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($contact->contact_type == 'customer') bg-blue-100 text-blue-800
                                                @elseif($contact->contact_type == 'vendor') bg-purple-100 text-purple-800
                                                @elseif($contact->contact_type == 'student') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($contact->contact_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->phone }}</td>
                                        {{-- Changed text-align --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ ($balance = $contact->getBalance()) < 0 ? 'text-red-600' : 'text-gray-500' }}">
                                            {{ number_format($balance, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center"> {{-- Changed text-align --}}
                                            @if($contact->is_active ?? true) {{-- Default to true if column might be missing --}}
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center"> {{-- Changed text-align --}}
                                            <a href="{{ route('accounting.contacts.show', $contact) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                            <a href="{{ route('accounting.contacts.edit', $contact) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Edit</a>
                                            {{-- Simplified delete check: can delete if no related invoices or payments exist --}}
                                            @if(!($contact->invoices()->exists() || $contact->payments()->exists()))
                                                {{-- @can('delete', $contact) --}}
                                                <form action="{{ route('accounting.contacts.destroy', $contact) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this contact? This cannot be undone if they have no transactions.')">Delete</button>
                                                </form>
                                                {{-- @endcan --}}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No contacts found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                         {{ $contacts->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>