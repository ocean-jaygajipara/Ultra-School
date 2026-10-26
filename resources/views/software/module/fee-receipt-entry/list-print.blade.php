<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Fee Receipt List</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 16px; color: #222; }
        h2 { text-align: center; margin: 4px 0; color: #5b2c8a; }
        h4 { text-align: center; margin: 4px 0 12px; color: #c0392b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .right { text-align: right; }
        tfoot td { font-weight: 700; }
        @media print { body { margin: 8mm; } }
    </style>
</head>
<body onload="window.print()">
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>Fee Receipt List</h4>
    <table>
        <thead>
            <tr>
                <th>Roll No</th>
                <th>Date</th>
                <th>GR No</th>
                <th>Student Name</th>
                <th>Std</th>
                <th>Term</th>
                <th class="right">Net Amt</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->roll_no ?: '—' }}</td>
                    <td>{{ $row->date?->format('d-m-Y') }}</td>
                    <td>{{ $row->gr_no ?: '—' }}</td>
                    <td>{{ $row->student_name }}</td>
                    <td>{{ trim(($row->standard->name ?? '') . ($row->division ? ' - ' . $row->division : '')) ?: '—' }}</td>
                    <td>{{ $row->term }}</td>
                    <td class="right">{{ number_format((float) $row->net_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">No fee receipt.</td></tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6">Total ({{ $rows->count() }})</td>
                    <td class="right">{{ number_format((float) $rows->sum('net_amount'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
