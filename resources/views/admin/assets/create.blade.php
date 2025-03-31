<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Asset') }}
            </h2>
            
            <x-secondary-button onclick="window.location.href='{{ route('assets.index') }}'">
                <i class="fa fa-arrow-left mr-1"></i> {{ __('Back to Assets') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('administration.assets.partials.form')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Any JavaScript needed for the create asset page
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Auto-generate asset code based on category
            const categorySelect = document.getElementById('category_id');
            const assetCodeInput = document.getElementById('asset_code');
            
            if (categorySelect && assetCodeInput) {
                categorySelect.addEventListener('change', function() {
                    if (!assetCodeInput.value || assetCodeInput.value === '{{ $assetCode }}') {
                        // Only update if the user hasn't manually entered a code
                        fetch(`/api/generate-asset-code?category_id=${this.value}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.asset_code) {
                                    assetCodeInput.value = data.asset_code;
                                }
                            });
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>