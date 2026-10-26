<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Bus Route Wise Students</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #222; }
        h2 { text-align: center; margin: 4px 0; color: #5b2c8a; }
        h4 { text-align: center; margin: 4px 0; color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #555; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .text-center { text-align: center; }
        .route td, tfoot td { font-weight: 700; background: #f5f5f5; }
        .meta { text-align: center; color: #1a56db; font-weight: 600; margin: 8px 0; }
        @media print { body { margin: 8mm; } }
    </style>
</head>
<body onload="window.print()">
    @php
        $controller = app(\App\Http\Controllers\admin\BusRouteStudentReportController::class);
        $dataColumns = $dataColumns ?? $controller->selectedColumns($filters ?? []);
        $blankHeaders = $blankHeaders ?? ($controller->blankColumnHeaders($filters ?? []));
        $totalCols = count($dataColumns) + count($blankHeaders);
    @endphp
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>Bus Route Wise Students</h4>
    <div class="meta">Route: {{ $routeLabel }}</div>
    <table>
        <thead>
            <tr>
                @foreach ($dataColumns as $key => $label)
                    <th class="{{ in_array($key, ['sr_no', 'roll_no', 'gr_no', 'standard', 'division', 'mobile_no'], true) ? 'text-center' : '' }}">
                        {{ $label }}
                    </th>
                @endforeach
                @foreach ($blankHeaders as $heading)
                    <th class="text-center" style="min-width:48px">{{ $heading ?: 'Column' }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse ($groups as $routeName => $students)
                @php $total += $students->count(); @endphp
                <tr class="route">
                    <td colspan="{{ max(1, $totalCols) }}">{{ $routeName }} · {{ $students->count() }} students</td>
                </tr>
                @foreach ($students as $student)
                    <tr>
                        @foreach ($dataColumns as $key => $label)
                            <td class="{{ in_array($key, ['sr_no', 'roll_no', 'gr_no', 'standard', 'division', 'mobile_no'], true) ? 'text-center' : '' }}">
                                {{ $controller->columnValue($student, $key, $loop->iteration) }}
                            </td>
                        @endforeach
                        @foreach ($blankHeaders as $heading)
                            <td></td>
                        @endforeach
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="{{ max(1, $totalCols) }}" style="text-align:center;">No student on this route.</td></tr>
            @endforelse
        </tbody>
        @if ($total)
            <tfoot>
                <tr><td colspan="{{ max(1, $totalCols) }}">Total students: {{ $total }}</td></tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
