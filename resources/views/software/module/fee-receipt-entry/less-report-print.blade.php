@php
    $from = !empty($filters['from_date']) ? \Carbon\Carbon::parse($filters['from_date'])->format('d-m-Y') : '';
    $to = !empty($filters['to_date']) ? \Carbon\Carbon::parse($filters['to_date'])->format('d-m-Y') : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Less Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #222; }
        h2 { text-align: center; margin: 4px 0; color: #5b2c8a; }
        h4 { text-align: center; margin: 4px 0; color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #555; padding: 5px 6px; text-align: left; }
        th { background: #eee; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .meta { display: flex; justify-content: space-between; color: #1a56db; font-weight: 600; margin: 8px 0 4px; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        @media print { body { margin: 8mm; } }
    </style>
</head>
<body onload="window.print()">
    <h2>{{ $school['name'] ?? config('app.name') }}</h2>
    <h4>Less Report</h4>
    <div class="meta">
        <span>Year: {{ $yearLabel }}</span>
        <span>Standard: {{ $stdLabel }}</span>
        <span>
            @if ($from || $to)
                Date: {{ $from ?: '—' }} to {{ $to ?: '—' }}
            @else
                Printed on {{ now()->format('d-m-Y h:i A') }}
            @endif
        </span>
    </div>
    <table>
        <thead>
            <tr>
                <th class="text-center">Sr</th>
                <th>Receipt No</th>
                <th>Date</th>
                <th>GR No</th>
                <th>Student Name</th>
                <th>Std</th>
                <th>Term</th>
                <th class="text-end">Total</th>
                <th class="text-end">Less</th>
                <th class="text-end">Net Amt</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row->receipt_no ?: '—' }}</td>
                    <td>{{ $row->date ? $row->date->format('d-m-Y') : '—' }}</td>
                    <td>{{ $row->gr_no ?: '—' }}</td>
                    <td>{{ $row->student_name }}</td>
                    <td>{{ $row->standard->name ?? '—' }}{{ $row->division ? ' - ' . $row->division : '' }}</td>
                    <td>{{ $row->term }}</td>
                    <td class="text-end">{{ number_format((float) $row->total, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $row->less_amount, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $row->net_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No receipt with Less amount.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="7">Total ({{ $rows->count() }} receipts)</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('total'), 2) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('less_amount'), 2) }}</td>
                    <td class="text-end">{{ number_format((float) $rows->sum('net_amount'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
