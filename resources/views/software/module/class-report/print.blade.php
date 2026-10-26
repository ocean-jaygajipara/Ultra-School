@php
    $controller = app(\App\Http\Controllers\admin\ClassReportController::class);
    $title = $filters['standard'] ? 'Class wise Student List' : 'Class Wise Summary';
    $dataColumns = $dataColumns ?? $controller->selectedColumns($filters);
    $blankHeaders = $filters['standard'] ? $controller->blankColumnHeaders($filters) : [];
    $colCount = count($dataColumns) + count($blankHeaders);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #222; }
        h2 { text-align: center; margin: 4px 0; color: #5b2c8a; }
        h4 { text-align: center; margin: 4px 0; color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #555; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .text-center { text-align: center; }
        .meta { display: flex; justify-content: space-between; color: #1a56db; font-weight: 600; margin: 8px 0 4px; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        @media print { body { margin: 8mm; } }
    </style>
</head>

<body onload="window.print()">
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>{{ $title }}</h4>

    @if ($filters['standard'])
        <div class="meta">
            <span>Standard: {{ $filters['standard'] }}</span>
            <span>Class: {{ $filters['division'] ?: 'All' }}</span>
        </div>
        <table>
            <thead>
                <tr>
                    @foreach ($dataColumns as $key => $label)
                        <th class="{{ in_array($key, ['roll_no', 'gr_no', 'phone', 'birth_date'], true) ? 'text-center' : '' }}">{{ $label }}</th>
                    @endforeach
                    @foreach ($blankHeaders as $heading)
                        <th class="text-center" style="min-width:48px">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $s)
                    <tr>
                        @foreach ($dataColumns as $key => $label)
                            <td class="{{ in_array($key, ['roll_no', 'gr_no', 'phone', 'birth_date'], true) ? 'text-center' : '' }}">
                                {{ $controller->columnValue($s, $key) }}
                            </td>
                        @endforeach
                        @foreach ($blankHeaders as $heading)
                            <td></td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(1, $colCount) }}" class="text-center">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <p><strong>Total Students: {{ $students->count() }}</strong></p>
    @else
        <div class="meta" style="justify-content:center;color:#555;font-weight:400">Printed on {{ now()->format('d-m-Y h:i A') }}</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center">Sr No.</th>
                    <th>Standard</th>
                    <th>Division</th>
                    <th class="text-center">Boys</th>
                    <th class="text-center">Girls</th>
                    <th class="text-center">Gender Not Set</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row['standard'] }}</td>
                        <td>{{ $row['division'] }}</td>
                        <td class="text-center">{{ $row['boys'] }}</td>
                        <td class="text-center">{{ $row['girls'] }}</td>
                        <td class="text-center">{{ $row['other'] }}</td>
                        <td class="text-center">{{ $row['total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($summary->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right">Total</td>
                        <td class="text-center">{{ $summary->sum('boys') }}</td>
                        <td class="text-center">{{ $summary->sum('girls') }}</td>
                        <td class="text-center">{{ $summary->sum('other') }}</td>
                        <td class="text-center">{{ $summary->sum('total') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</body>

</html>
