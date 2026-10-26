<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Day to Day Fees</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #222; }
        h2 { text-align: center; margin: 4px 0; color: #5b2c8a; }
        h4 { text-align: center; margin: 4px 0; color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #555; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .right { text-align: right; }
        .day td { font-weight: 700; background: #d7e8ff; text-align: center; }
        .sub td, tfoot td { font-weight: 700; background: #f5f5f5; }
        @media print { body { margin: 8mm; } }
    </style>
</head>
<body onload="window.print()">
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>Day to Day Fees</h4>
    <table>
        <thead>
            <tr>
                <th>Receipt No</th>
                <th>GR No</th>
                <th>Student</th>
                <th>Std</th>
                <th>Term</th>
                <th class="right">Total</th>
                <th class="right">Less</th>
                <th class="right">Net Amt</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($days as $dayKey => $dayRows)
                <tr class="day">
                    <td colspan="8">{{ \Carbon\Carbon::parse($dayKey)->format('d-m-Y') }}</td>
                </tr>
                @foreach ($dayRows as $row)
                    <tr>
                        <td>{{ $row->receipt_no ?: '—' }}</td>
                        <td>{{ $row->gr_no ?: '—' }}</td>
                        <td>{{ $row->student_name }}</td>
                        <td>{{ $row->standard->name ?? '—' }}{{ $row->division ? ' - ' . $row->division : '' }}</td>
                        <td>{{ $row->term }}</td>
                        <td class="right">{{ number_format((float) $row->total, 2) }}</td>
                        <td class="right">{{ number_format((float) $row->less_amount, 2) }}</td>
                        <td class="right">{{ number_format((float) $row->net_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="sub">
                    <td colspan="5">Day total</td>
                    <td class="right">{{ number_format((float) $dayRows->sum('total'), 2) }}</td>
                    <td class="right">{{ number_format((float) $dayRows->sum('less_amount'), 2) }}</td>
                    <td class="right">{{ number_format((float) $dayRows->sum('net_amount'), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;">No fee receipt.</td></tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5">Grand total ({{ $rows->count() }} receipts)</td>
                    <td class="right">{{ number_format((float) $rows->sum('total'), 2) }}</td>
                    <td class="right">{{ number_format((float) $rows->sum('less_amount'), 2) }}</td>
                    <td class="right">{{ number_format((float) $rows->sum('net_amount'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
