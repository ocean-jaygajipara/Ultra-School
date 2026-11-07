<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaving Register</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 12px; }
        .no-print { text-align: center; padding: 10px; background: #f3f4f6; border-bottom: 1px solid #ddd; }
        .no-print button { padding: 8px 16px; margin: 0 4px; border: 0; border-radius: 4px; cursor: pointer; }
        .btn-print { background: #1f2580; color: #fff; }
        .btn-close { background: #6b7280; color: #fff; }
        .sheet { padding: 8px 10px; }
        h1 { margin: 0; text-align: center; font-size: 20px; letter-spacing: .03em; }
        h2 { margin: 4px 0 12px; text-align: center; font-size: 16px; }
        .meta { text-align: right; margin-bottom: 6px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #c00; padding: 5px 6px; vertical-align: middle; }
        th { background: #fff; font-size: 12px; }
        td { font-size: 11px; }
        .c { text-align: center; }
        .name { text-transform: uppercase; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>
    <div class="sheet">
        <h1>{{ $schoolName }}</h1>
        <h2>Leaving Register</h2>
        @if (!empty($academicYear))
            <div class="meta">Academic Year : {{ $academicYear }}</div>
        @endif
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="c">GR No.</th>
                    <th rowspan="2">Student Name</th>
                    <th colspan="2" class="c">Class</th>
                    <th rowspan="2" class="c">Leaving Date</th>
                    <th rowspan="2">Leaving Reason</th>
                    <th rowspan="2" class="c">LC No.</th>
                    <th rowspan="2" class="c">Late Fee</th>
                    <th rowspan="2">Note</th>
                    <th rowspan="2" class="c">Principal Sign</th>
                </tr>
                <tr>
                    <th class="c">Std</th>
                    <th class="c">Div</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $row)
                    <tr>
                        <td class="c">{{ $row->gr_no }}</td>
                        <td class="name">{{ $row->student_name }}</td>
                        <td class="c">{{ $row->last_std }}</td>
                        <td class="c">{{ $row->last_division }}</td>
                        <td class="c">{{ $row->formatDate('leave_date') }}</td>
                        <td>{{ $row->leave_reason }}</td>
                        <td class="c">{{ $row->leaving_certi_no }}</td>
                        <td class="c">{{ $row->fee !== null ? number_format((float) $row->fee, 0) : '0' }}</td>
                        <td>{{ $row->note }}</td>
                        <td class="c"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="c">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
