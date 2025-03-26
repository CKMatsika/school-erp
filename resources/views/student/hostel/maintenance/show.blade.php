{{-- resources/views/student/hostel/maintenance/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Maintenance Issue Details') }}
            </h2>
            <div>
                @if($issue->status !== 'resolved')
                    <a href="{{ route('student.hostel.maintenance.edit', $issue->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        Update Issue
                    </a>
                @endif
                <a href="{{ route('student.hostel.maintenance.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Back to Issues
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Banner -->
            <div class="mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 
                        @if($issue->status === 'open') bg-red-50 
                        @elseif($issue->status === 'in_progress') bg-yellow-50 
                        @else bg-green-50 @endif">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="@if($issue->status === 'open') text-red-800 
                                    @elseif($issue->status === 'in_progress') text-yellow-800 
                                    @else text-green-800 @endif font-semibold">
                                    Status: {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                </div>
                                
                                <span class="text-gray-400">|</span>
                                
                                <div class="@if($issue->priority === 'emergency') text-red-800 
                                    @elseif($issue->priority === 'high') text-orange-800
                                    @elseif($issue->priority === 'medium') text-yellow-800
                                    @else text-blue-800 @endif font-semibold">
                                    Priority: {{ ucfirst($issue->priority) }}
                                </div>
                                
                                <span class="text-gray-400">|</span>
                                
                                <div class="text-gray-600">
                                    Reported: {{ $issue->created_at->format('M d, Y H:i') }}
                                </div>
                            </div>
                            
                            @if($issue->status !== 'resolved')
                                <form method="POST" action="{{ route('student.hostel.maintenance.resolve', $issue->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Mark as Resolved
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Issue Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Issue Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-700">Title</h4>
                                <p class="mt-1">{{ $issue->title }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-700">Location</h4>
                                <p class="mt-1">
                                    {{ $issue->house->name }} 
                                    @if($issue->room)
                                        , Room {{ $issue->room->room_number }}
                                    @endif
                                    , {{ $issue->location }}
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-700">Category</h4>
                                <p class="mt-1">{{ ucfirst($issue->category) }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-700">Reported By</h4>
                                <p class="mt-1">{{ $issue->user->name }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-700">Contact Information</h4>
                                <p class="mt-1">{{ $issue->user->email }}, {{ $issue->user->phone ?? 'No phone provided' }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-700">Access Information</h4>
                                <p class="mt-1">{{ $issue->access_info ?: 'No access information provided' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h4 class="text-base font-medium text-gray-700">Detailed Description</h4>
                        <div class="mt-1 p-4 bg-gray-50 rounded-md">
                            <p class="whitespace-pre-line">{{ $issue->description }}</p>
                        </div>
                    </div>
                    
                    @if($issue->images && count($issue->images) > 0)
                        <div class="mt-6">
                            <h4 class="text-base font-medium text-gray-700">Images</h4>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($issue->images as $image)
                                    <div class="relative group">
                                        <img src="{{ Storage::url($image) }}" alt="Issue Image" class="w-full h-40 object-cover rounded-md border border-gray-200">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <a href="{{ Storage::url($image) }}" target="_blank" class="px-3 py-1 bg-white text-gray-800 rounded-md text-sm font-medium">View Full Size</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Maintenance Updates -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Maintenance Updates</h3>
                        @if($issue->status !== 'resolved')
                            <button type="button" id="add-update-btn" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Add Update
                            </button>
                        @endif
                    </div>
                    
                    <!-- Add Update Form (Hidden by default) -->
                    <div id="update-form" class="mb-6 bg-gray-50 p-4 rounded-md hidden">
                        <form method="POST" action="{{ route('student.hostel.maintenance.update-status', $issue->id) }}">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="update_status" class="block text-sm font-medium text-gray-700">Update Status</label>
                                    <select id="update_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="open" {{ $issue->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="in_progress" {{ $issue->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                                    <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required></textarea>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="button" id="cancel-update-btn" class="mr-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </button>
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Submit Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    @if($updates->isEmpty())
                        <div class="text-gray-500 text-center py-4">No updates have been posted for this issue yet.</div>
                    @else
                        <div class="space-y-4">
                            @foreach($updates as $update)
                                <div class="border-l-4 
                                    @if($update->status === 'open') border-red-400 
                                    @elseif($update->status === 'in_progress') border-yellow-400 
                                    @else border-green-400 @endif 
                                    pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-600">Status changed to <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $update->status)) }}</span></p>
                                            <p class="text-sm font-medium mt-1">{{ $update->comment }}</p>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <div>{{ $update->created_at->format('M d, Y H:i') }}</div>
                                            <div>By: {{ $update->user->name }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addUpdateBtn = document.getElementById('add-update-btn');
            const updateForm = document.getElementById('update-form');
            const cancelUpdateBtn = document.getElementById('cancel-update-btn');
            
            if (addUpdateBtn) {
                addUpdateBtn.addEventListener('click', function() {
                    updateForm.classList.remove('hidden');
                    addUpdateBtn.classList.add('hidden');
                });
            }
            
            if (cancelUpdateBtn) {
                cancelUpdateBtn.addEventListener('click', function() {
                    updateForm.classList.add('hidden');
                    addUpdateBtn.classList.remove('hidden');
                });
            }
        });
    </script>
</x-app-layout>