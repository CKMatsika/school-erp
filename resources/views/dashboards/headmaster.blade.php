```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Headmaster Dashboard') }}
        </h2>
    </x-slot>

     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             @include('components.flash-messages')

              {{-- KPI Cards --}}
              <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">School Overview</h3>
                    {{-- Ensure controller passes $kpis array --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                         <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800">Total Students</h4>
                            <p class="text-2xl font-bold text-blue-600">{{ $kpis['totalStudents'] ?? 'N/A' }}</p>
                        </div>
                         <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <h4 class="text-sm font-medium text-indigo-800">Teaching Staff</h4>
                            <p class="text-2xl font-bold text-indigo-600">{{ $kpis['teachingStaff'] ?? 'N/A' }}</p>
                        </div>
                         <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <h4 class="text-sm font-medium text-purple-800">Non-Teaching Staff</h4>
                            <p class="text-2xl font-bold text-purple-600">{{ $kpis['nonTeachingStaff'] ?? 'N/A' }}</p>
                        </div>
                         <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h4 class="text-sm font-medium text-green-800">Income (Month)</h4>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($kpis['monthlyIncome'] ?? 0, 2) }}</p>
                        </div>
                         <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <h4 class="text-sm font-medium text-red-800">Expense (Month)</h4>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($kpis['monthlyExpense'] ?? 0, 2) }}</p>
                        </div>
                         <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <h4 class="text-sm font-medium text-yellow-800">Pending Approvals</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ $kpis['pendingApprovals'] ?? 'N/A' }}</p>
                             <p class="text-xs text-yellow-600">(PRs, Leave etc.)</p>
                        </div>
                        {{-- Add more KPIs as needed --}}
                    </div>
                </div>
            </div>

             {{-- Placeholder for Charts --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">Visualizations</h3>
                     <p class="text-gray-500 italic">Charts for enrollment trends, financials, etc. will be displayed here.</p>
                     {{-- Example Canvas for Chart.js --}}
                     {{-- <canvas id="enrollmentChart"></canvas> --}}
                </div>
            </div>

            {{-- Placeholder for Recent Activities/Notifications --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                     <p class="text-gray-500 italic">A feed of recent important activities (approvals, new registrations, issues) will appear here.</p>
                </div>
            </div>

        </div>
    </div>
     {{-- Include Charting library JS if needed --}}
     {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <script>
         // Example Chart.js setup
         // const ctx = document.getElementById('enrollmentChart');
         // new Chart(ctx, { ... config ... });
     </script> --}}
</x-app-layout>