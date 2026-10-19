@php
    $hideExtraColumns = $hideExtraColumns ?? false;
@endphp

@forelse(($histories ?? collect()) as $index => $history)
    <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td class="icx-who">{{ $history['student_name'] ?? '-' }}</td>
        <td>{{ $history['issue_date'] ?? '-' }}</td>
        @unless($hideExtraColumns)
            <td>
                @if (!empty($history['academic_year']) && $history['academic_year'] !== '-')
                    <span class="icx-pill"><i class="ti ti-calendar"></i> {{ $history['academic_year'] }}</span>
                @else
                    -
                @endif
            </td>
            <td class="icx-muted">{{ $history['generated_at'] ?? '-' }}</td>
        @endunless
        <td class="text-center">
            <a href="{{ $history['certificate_url'] ?? '#' }}" target="_blank" class="icx-view" title="View Certificate">
                <i class="ti ti-eye"></i> View
            </a>
        </td>
    </tr>
@empty
    <tr id="certificate-history-empty-row" class="icx-empty">
        <td colspan="{{ $hideExtraColumns ? 4 : 6 }}">
            <i class="ti ti-file-off"></i>
            No certificate generated yet.
        </td>
    </tr>
@endforelse
