@php
    $controller = app(\App\Http\Controllers\admin\ClassReportController::class);
    $title = $filters['standard'] ? 'Class Wise Student List' : 'Class Wise Strength Summary';
    $dataColumns = $dataColumns ?? $controller->selectedColumns($filters);
    $blankHeaders = $filters['standard'] ? $controller->blankColumnHeaders($filters) : [];
    $colCount = count($dataColumns) + count($blankHeaders);

    // Dynamic School Information
    $schoolName = strtoupper($school['name'] ?? config('app.name', 'Ultra School'));
    $schoolShort = $school['short_name'] ?? 'UES';
    $schoolCampus = 'Keshod - 362220, Dist. Junagadh (Gujarat)';

    // School Logo
    $logoUrl = file_exists(public_path('admin/assets/images/logo-light.png'))
        ? asset('admin/assets/images/logo-light.png')
        : (file_exists(public_path('uploads/logo/report_logo.png'))
            ? asset('uploads/logo/report_logo.png')
            : asset('uploads/logo/logo.png'));
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />
    <title>{{ $title }} - {{ $schoolName }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 12mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            margin: 0;
            padding: 16px 20px;
            background: #fff;
            line-height: 1.35;
        }

        /* Top Action Bar (hidden on print) */
        .no-print-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1f2580;
            color: #fff;
            padding: 10px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px rgba(31, 37, 128, 0.2);
        }

        .no-print-bar .bar-title {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .no-print-bar .bar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action {
            border: 0;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-print {
            background: #f5b400;
            color: #1f2580;
        }

        .btn-print:hover {
            background: #e0a300;
        }

        .btn-close-tab {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .btn-close-tab:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Official School Letterhead Header */
        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 2px solid #1f2580;
            margin-bottom: 12px;
            gap: 16px;
        }

        .header-logo-box {
            flex: 0 0 85px;
            text-align: center;
        }

        .header-logo-box img {
            max-width: 82px;
            max-height: 82px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .header-center-info {
            flex: 1;
            text-align: center;
        }

        .school-main-title {
            font-size: 20px;
            font-weight: 800;
            color: #1f2580;
            margin: 0 0 3px;
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .school-location-text {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin: 0 0 6px;
            letter-spacing: 0.3px;
        }

        .report-title-badge-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f4fb;
            border: 1px solid #d4defc;
            padding: 3px 14px;
            border-radius: 20px;
        }

        .school-code-tag {
            background: #1f2580;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            letter-spacing: 0.5px;
        }

        .report-name-title {
            font-size: 13px;
            font-weight: 700;
            color: #1f2580;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .header-right-meta {
            flex: 0 0 95px;
            text-align: right;
            border-left: 1px dashed #cbd5e1;
            padding-left: 12px;
        }

        .print-date-badge {
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.4;
        }

        .print-date-badge strong {
            display: block;
            color: #1f2580;
            font-size: 11px;
            font-weight: 700;
        }

        /* Meta Filter Summary Ribbon */
        .meta-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 7px 14px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 11.5px;
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .meta-item .lbl {
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.4px;
        }

        .meta-item .val {
            color: #1f2580;
            font-weight: 700;
        }

        .count-pill {
            background: #1f2580;
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }

        /* Report Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 11px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #334155;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .report-table thead th {
            background: #1f2580 !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 10.5px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-align: left;
            border-color: #0f172a !important;
        }

        .report-table thead th.text-center {
            text-align: center;
        }

        .report-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .report-table tbody td.text-center {
            text-align: center;
        }

        .report-table td.extra-blank-cell {
            min-width: 70px;
            background: #ffffff;
        }

        .roll-badge {
            font-weight: 700;
            color: #1f2580;
        }

        .gr-code {
            font-family: 'JetBrains Mono', Consolas, monospace;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .student-name-text {
            font-weight: 600;
            color: #0f172a;
        }

        .report-table tfoot td {
            background: #eef2ff !important;
            font-weight: 700;
            border-color: #1f2580 !important;
            color: #1f2580;
            font-size: 11.5px;
        }

        /* System Print Footer */
        .report-system-footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #94a3b8;
            page-break-inside: avoid;
        }

        /* Print Media Overrides */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .report-table {
                page-break-inside: auto;
            }

            .report-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    {{-- On-Screen Floating Controls (Hidden on Print) --}}
    <div class="no-print-bar">
        <div class="bar-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            <span>Class Report Print Preview &mdash; {{ $schoolName }}</span>
        </div>
        <div class="bar-actions">
            <button type="button" class="btn-action btn-print" onclick="window.print()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Document
            </button>
            <button type="button" class="btn-action btn-close-tab" onclick="window.close()">
                Close
            </button>
        </div>
    </div>

    {{-- Official School Header with Logo --}}
    <div class="report-header">
        <div class="header-logo-box">
            <img src="{{ $logoUrl }}" alt="{{ $schoolName }} Logo">
        </div>
        <div class="header-center-info">
            <h1 class="school-main-title">{{ $schoolName }}</h1>
            <div class="school-location-text">{{ $schoolCampus }}</div>
            <div class="report-title-badge-wrap">
                <span class="school-code-tag">{{ $schoolShort }}</span>
                <span class="report-name-title">{{ $title }}</span>
            </div>
        </div>
        <div class="header-right-meta">
            <div class="print-date-badge">
                PRINT DATE
                <strong>{{ now()->format('d/m/Y') }}</strong>
                <span>{{ now()->format('h:i A') }}</span>
            </div>
        </div>
    </div>

    @if ($filters['standard'])
        {{-- Metadata Strip for Specific Class --}}
        <div class="meta-ribbon">
            <div class="meta-item">
                <span class="lbl">Standard:</span>
                <span class="val">{{ $filters['standard'] }}</span>
            </div>
            <div class="meta-item">
                <span class="lbl">Division:</span>
                <span class="val">{{ $filters['division'] ?: 'All Divisions' }}</span>
            </div>
            <div class="meta-item">
                <span class="lbl">Total Students:</span>
                <span class="val count-pill">{{ $students->count() }}</span>
            </div>
            <div class="meta-item">
                <span class="lbl">Campus:</span>
                <span class="val">{{ $school['name'] ?? 'Ultra School' }}</span>
            </div>
        </div>

        {{-- Students Table --}}
        <table class="report-table">
            <thead>
                <tr>
                    @foreach ($dataColumns as $key => $label)
                        <th class="{{ in_array($key, ['roll_no', 'gr_no', 'phone', 'birth_date'], true) ? 'text-center' : '' }}">
                            {{ $label }}
                        </th>
                    @endforeach
                    @foreach ($blankHeaders as $heading)
                        <th class="text-center" style="min-width: 65px;">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $s)
                    <tr>
                        @foreach ($dataColumns as $key => $label)
                            <td class="{{ in_array($key, ['roll_no', 'gr_no', 'phone', 'birth_date'], true) ? 'text-center' : '' }}">
                                @if ($key === 'roll_no')
                                    <span class="roll-badge">{{ $controller->columnValue($s, $key) }}</span>
                                @elseif ($key === 'gr_no')
                                    <span class="gr-code">{{ $controller->columnValue($s, $key) }}</span>
                                @elseif ($key === 'student_name')
                                    <span class="student-name-text">{{ $controller->columnValue($s, $key) }}</span>
                                @else
                                    {{ $controller->columnValue($s, $key) }}
                                @endif
                            </td>
                        @endforeach
                        @foreach ($blankHeaders as $heading)
                            <td class="extra-blank-cell"></td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(1, $colCount) }}" class="text-center" style="padding: 24px; color: #64748b;">
                            No students found for Standard {{ $filters['standard'] }} {{ $filters['division'] ? ' - Division ' . $filters['division'] : '' }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
    @else
        {{-- Metadata Strip for Summary --}}
        <div class="meta-ribbon">
            <div class="meta-item">
                <span class="lbl">Report Mode:</span>
                <span class="val">All Standards Strength Summary</span>
            </div>
            <div class="meta-item">
                <span class="lbl">Total Classes:</span>
                <span class="val">{{ $summary->count() }}</span>
            </div>
          
        </div>

        {{-- Summary Table --}}
        <table class="report-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">Sr No.</th>
                    <th>Standard</th>
                    <th>Division</th>
                    <th class="text-center" style="width: 85px;">Boys</th>
                    <th class="text-center" style="width: 85px;">Girls</th>
                    <th class="text-center" style="width: 100px;">Other</th>
                    <th class="text-center" style="width: 90px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td><strong>{{ $row['standard'] }}</strong></td>
                        <td>{{ $row['division'] }}</td>
                        <td class="text-center" style="font-weight: 600;">{{ $row['boys'] }}</td>
                        <td class="text-center" style="font-weight: 600;">{{ $row['girls'] }}</td>
                        <td class="text-center">{{ $row['other'] }}</td>
                        <td class="text-center" style="font-weight: 700; color: #1f2580;">{{ $row['total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 24px; color: #64748b;">
                            No student summary data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($summary->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding-right: 14px;">Total Students:</td>
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
