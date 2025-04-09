{{-- resources/views/layouts/print-header.blade.php --}}
{{-- This view expects $currentSchool to be passed via a View Composer --}}
@isset($currentSchool)
<table class="header-section" style="width: 100%; border-bottom: 1px solid #eee; margin-bottom: 20px;">
    <tr>
        {{-- Logo Column --}}
        <td style="width: 30%; vertical-align: top;">
            @if($currentSchool->logo_url)
                <img src="{{ $currentSchool->logo_url }}" alt="{{ $currentSchool->name }} Logo" style="max-height: 70px; max-width: 180px;">
            @endif
        </td>

        {{-- School Details Column --}}
        <td style="width: 70%; text-align: right; vertical-align: top; font-size: 9pt; line-height: 1.4;">
            @if($currentSchool->name)
                <strong style="font-size: 13pt; display: block; margin-bottom: 5px;">{{ $currentSchool->name }}</strong>
            @endif
            @if($currentSchool->address)
                {!! nl2br(e($currentSchool->address)) !!}<br>
            @endif
            @if($currentSchool->phone)
                <strong>Phone:</strong> {{ $currentSchool->phone }}<br>
            @endif
            @if($currentSchool->email)
                <strong>Email:</strong> {{ $currentSchool->email }}<br>
            @endif
            @if($currentSchool->website)
                <strong>Web:</strong> {{ $currentSchool->website }}<br>
            @endif
             {{-- Add Tax ID if available and needed --}}
             {{-- @if($currentSchool->tax_id)
                <strong>Tax ID:</strong> {{ $currentSchool->tax_id }}
             @endif --}}
        </td>
    </tr>
</table>
@else
    {{-- Fallback or placeholder if $currentSchool is not available --}}
    <div style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; text-align: center; color: #999;">
        School details not configured.
    </div>
@endisset