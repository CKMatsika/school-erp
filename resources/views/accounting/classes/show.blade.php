<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Class Details') }}: {{ $class->name }}
             </h2>
             {{-- Add Edit/Back buttons --}}
         </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Class Information</h3>
                     <dl>
                         {{-- Display Name, Level, Capacity etc. from $class --}}
                          <div><dt>Name</dt><dd>{{ $class->name }}</dd></div>
                          <div><dt>Level</dt><dd>{{ $class->level }}</dd></div>
                          {{-- etc --}}
                     </dl>
                     {{-- Optionally list students enrolled in this class later --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>