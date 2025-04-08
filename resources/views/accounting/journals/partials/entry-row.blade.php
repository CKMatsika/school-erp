{{--
    Receives:
    $index - The index for the form array name (e.g., 0, 1, __INDEX__)
    $entry - Old input data for this row (array or null)
    $accounts - Collection of available accounts
    $contacts - Collection of available contacts
--}}
<tr class="entry-row border-b border-gray-200 align-top"> {{-- Use align-top --}}
    <td class="py-2 px-1">
        <select name="entries[{{ $index }}][account_id]" class="entry-account block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            <option value="">-- Select Account --</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" data-name="{{ $account->name }} ({{ $account->account_code }})" {{-- Add data-name --}}
                        {{-- Check old input first, then existing entry data if editing --}}
                        {{ (old('entries.'.$index.'.account_id', $entry['account_id'] ?? '') == $account->id) ? 'selected' : '' }}>
                    {{ $account->account_code }} - {{ Str::limit($account->name, 35) }} {{-- Limit name length --}}
                </option>
            @endforeach
        </select>
         <x-input-error :messages="$errors->get('entries.'.$index.'.account_id')" class="mt-1" />
    </td>
    <td class="py-2 px-1">
        <select name="entries[{{ $index }}][contact_id]" class="entry-contact block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option value="">-- Optional --</option>
             @foreach($contacts as $contact)
                <option value="{{ $contact->id }}" {{ (old('entries.'.$index.'.contact_id', $entry['contact_id'] ?? '') == $contact->id) ? 'selected' : '' }}>
                    {{ Str::limit($contact->name, 35) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('entries.'.$index.'.contact_id')" class="mt-1" />
    </td>
     {{-- Optional Description per line - uncomment if needed and add header
     <td class="py-2 px-1">
         <textarea name="entries[{{ $index }}][description]" placeholder="Line desc." rows="1" class="mt-1 block w-full text-xs rounded-md border-gray-300 shadow-sm">{{ old("entries.$index.description", $entry['description'] ?? '') }}</textarea>
          <x-input-error :messages="$errors->get('entries.'.$index.'.description')" class="mt-1" />
     </td>
      --}}
    <td class="py-2 px-1">
        <x-text-input type="number" name="entries[{{ $index }}][debit]" class="entry-debit block w-full text-right sm:text-sm" :value="old('entries.'.$index.'.debit', number_format($entry['debit'] ?? 0, 2, '.', ''))" min="0" step="0.01" placeholder="0.00" />
        <x-input-error :messages="$errors->get('entries.'.$index.'.debit')" class="mt-1" />
    </td>
     <td class="py-2 px-1">
        <x-text-input type="number" name="entries[{{ $index }}][credit]" class="entry-credit block w-full text-right sm:text-sm" :value="old('entries.'.$index.'.credit', number_format($entry['credit'] ?? 0, 2, '.', ''))" min="0" step="0.01" placeholder="0.00" />
        <x-input-error :messages="$errors->get('entries.'.$index.'.credit')" class="mt-1" />
    </td>
    <td class="py-2 px-1 text-center">
        {{-- Use $index which is passed to the partial --}}
        <button type="button" class="text-red-500 hover:text-red-700 remove-entry font-bold text-lg leading-none p-1" title="Remove Row" {{ $index < 2 ? 'disabled' : '' }}>×</button>
    </td>
</tr>