<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />
    <meta charset="UTF-8">
    <title>{{ $meta['title'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #222; }
        h2 { text-align: center; margin: 4px 0; color: #000; }
        h4 { text-align: center; margin: 4px 0; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 0.6px solid #000; padding: 5px 6px; text-align: left; }
        th { background: #1f2580; color: #fff !important; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .meta { display: flex; justify-content: space-between; font-weight: 600; margin: 8px 0 4px; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        @media print { body { margin: 8mm; } }
    </style>
</head>
<body onload="window.print()">
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>{{ $meta['title'] }}</h4>
    <div class="meta">
        <span>Year: {{ $yearLabel }}</span>
        <span>Standard: {{ $stdLabel }}</span>
        <span>Term: {{ $termLabel }}</span>
    </div>
    <table>
        <thead>
            <tr>
                @foreach ($meta['columns'] as $column)
                    <th class="{{ $loop->last ? 'text-end' : '' }}">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row['cells'] as $cell)
                        <td class="{{ !empty($cell['end']) ? 'text-end' : '' }}">{{ $cell['text'] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($meta['columns']) }}" class="text-center">{{ $meta['empty'] }}</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="{{ count($meta['columns']) - 1 }}">Total ({{ $rows->count() }})</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
