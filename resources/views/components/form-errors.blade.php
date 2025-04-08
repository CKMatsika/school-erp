@if ($errors->any())
    <div class="mb-4">
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p class="font-bold">{{ __('Validation Errors') }}</p>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif