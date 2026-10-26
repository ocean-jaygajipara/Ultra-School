<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="utf-8">
    <title>Roll Number List - {{ $standard->name }}{{ $division ? ' / ' . $division : '' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #000; margin: 0; padding: 16px; }
        .no-print { text-align: right; margin-bottom: 10px; }
        .print-btn {
            background: #1f2580; color: #fff; border: none; border-radius: 8px;
            padding: 8px 16px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block;
        }
        .header { text-align: center; border-bottom: 2px solid #1f2580; padding-bottom: 10px; margin-bottom: 14px; }
        .school { font-size: 20px; font-weight: 800; color: #1f2580; text-transform: uppercase; white-space: nowrap; letter-spacing: .02em; }
        .title { margin-top: 8px; font-size: 15px; font-weight: 800; text-decoration: underline; }
        .meta { margin: 10px 0 14px; }
        .meta td { padding: 3px 8px 3px 0; }
        table.list { width: 100%; border-collapse: collapse; }
        table.list th, table.list td { border: 1px solid #000; padding: 7px 8px; }
        table.list th { background: #eef0fb; }
        .footer { margin-top: 18px; font-size: 11px; text-align: center; color: #555; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
        <a href="{{ route('roll-number-list.index') }}" class="print-btn" style="background:#5f6488;">Back</a>
    </div>

    @php
        $schoolName = trim($school['name'] ?? 'Ultra English School');
        $fullSchoolName = preg_match('/keshod/i', $schoolName) ? $schoolName : ($schoolName . ' KESHOD');
    @endphp

    <div class="header">
        <div class="school">{{ $fullSchoolName }}</div>
        <div class="title">Roll Number List</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Standard:</strong> {{ $standard->name }}</td>
            <td><strong>Division:</strong> {{ $division ?: 'All' }}</td>
            <td><strong>Academic Year:</strong> {{ $currentYear ?: '—' }}</td>
            <td><strong>Total:</strong> {{ $students->count() }}</td>
        </tr>
    </table>

    <table class="list">
        <thead>
            <tr>
                <th style="width:60px;">Sr</th>
                <th style="width:90px;">Roll No</th>
                <th style="width:100px;">GR No</th>
                <th>Student Name</th>
                <th style="width:90px;">Division</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $i => $student)
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td style="text-align:center;">{{ $student->roll_no ?: '—' }}</td>
                    <td>{{ $student->gr_no ?: '—' }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td style="text-align:center;">{{ $student->division ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Printed on {{ now()->format('d-m-Y h:i A') }}</div>
</body>
</html>
