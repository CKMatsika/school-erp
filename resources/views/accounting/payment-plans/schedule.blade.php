<x-app-layout>
    <x-slot name="header">
         <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payment Schedule for Plan: {{ $paymentPlan->name }}
             </h2>
             <a href="{{ route('accounting.payment-plans.show', $paymentPlan) }}" class="text-sm ...">Back to Plan Details</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800 mb-4">Full Schedule</h3>
                      {{-- Copy/paste the schedule table from show.blade.php here --}}
                      <div class="overflow-x-auto shadow border ...">
                           <table class="min-w-full divide-y ...">
                               {{-- thead --}}
                               <tbody>
                                   @forelse ($paymentPlan->paymentSchedules as $schedule)
                                        {{-- table row as in show.blade.php --}}
                                   @empty
                                        {{-- No schedule message --}}
                                   @endforelse
                               </tbody>
                           </table>
                       </div>
                 </div>
             </div>
         </div>
     </div>
</x-app-layout>