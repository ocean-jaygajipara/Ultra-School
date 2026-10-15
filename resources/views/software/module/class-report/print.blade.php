@php
    $controller = app(\App\Http\Controllers\admin\ClassReportController::class);
    $title = $filters['standard'] ? 'Class Report - ' . $controller->classLabel($filters) : 'Class Wise Summary';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #222; }
        h2, h4 { text-align: center; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #555; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .text-center { text-align: center; }
        .meta { text-align: center; color: #555; margin-bottom: 6px; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        @media print { body { margin: 8mm; } }
    </style>
</head>

<body onload="window.print()">
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>{{ $title }}</h4>
    <div class="meta">Printed on {{ now()->format('d-m-Y h:i A') }}</div>

    @if ($filters['standard'])
        @php $blankHeaders = $controller->blankColumnHeaders($filters); @endphp
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width:60px">Roll No</th>
                    <th>Student Name</th>
                    <th style="width:80px">GR No</th>
                    @foreach ($blankHeaders as $heading)
                        <th class="text-center" style="min-width:60px">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $s)
                    <tr>
                        <td class="text-center">{{ $s->roll_no }}</td>
                        <td>{{ $controller->fullName($s) }}</td>
                        <td>{{ $s->gr_no }}</td>
                        @foreach ($blankHeaders as $heading)
                            <td></td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($blankHeaders) }}" class="text-center">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <p><strong>Total Students: {{ $students->count() }}</strong></p>
    @else
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
