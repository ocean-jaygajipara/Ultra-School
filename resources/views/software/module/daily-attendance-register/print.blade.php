@php
    $schoolName = strtoupper($school['name'] ?? 'Ultra School');
    if ((\App\Services\SchoolDatabaseManager::getActiveSchoolCode() ?? '') === 'ues') {
        $schoolName .= '-KESHOD';
    }
    $dayCols = 31;
    $infoCols = 5;
    $summaryCols = 3;
    $totalCols = $infoCols + $dayCols + $summaryCols;
    $classTeacher = $filters['class_teacher'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />
    <title>Daily Attendance Register - {{ $schoolName }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 5mm 8mm 5mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #000;
            margin: 0;
            padding: 10px 12px 16px;
            background: #fff;
        }

        .no-print-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1f2580;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .btn-action {
            border: 0;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print { background: #f5b400; color: #1f2580; }
        .btn-close { background: #fff; color: #1f2580; }

        .school-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #1f2580;
            letter-spacing: 0.4px;
            margin: 0 0 4px;
            line-height: 1.2;
        }

        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 6px;
        }

        .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #e1141c;
            margin: 0;
        }

        .report-date {
            font-size: 12px;
            font-weight: 700;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            align-items: baseline;
        }

        .meta-row span em {
            font-style: normal;
            font-weight: 400;
            border-bottom: 1px solid #000;
            min-width: 70px;
            display: inline-block;
            padding: 0 4px;
            margin-left: 4px;
        }

        .meta-row .teacher em {
            min-width: 160px;
        }

        table.register {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.register th,
        table.register td {
            border: 1px solid #000;
            padding: 1px 2px;
            vertical-align: middle;
            text-align: center;
            font-size: 8px;
            line-height: 1.15;
            height: 16px;
        }

        table.register thead th {
            font-weight: 700;
            background: #fff;
        }

        table.register .col-roll { width: 28px; }
        table.register .col-gr { width: 36px; }
        table.register .col-name { width: 150px; text-align: left; padding-left: 4px; }
        table.register .col-caste { width: 78px; text-align: left; padding-left: 3px; }
        table.register .col-dob { width: 58px; }
        table.register .col-day { width: 14px; padding: 0; }
        table.register .col-sum { width: 44px; font-size: 7px; }

        .dow {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1;
            height: 22px;
            display: inline-block;
        }

        .day-num { font-size: 8px; font-weight: 700; }

        .sun-col { background: #f3f3f3; }
        .name-cell { text-align: left !important; white-space: nowrap; overflow: hidden; font-weight: 600; font-size: 8.5px; }
        .caste-cell { text-align: left !important; font-size: 7.5px; white-space: nowrap; overflow: hidden; }
        .mark { font-weight: 700; font-size: 10px; }
        .sum-val { font-weight: 700; font-size: 9px; }

        .footer-label {
            text-align: left !important;
            font-weight: 700;
            font-size: 8px;
            padding-left: 4px !important;
        }

        .bottom-wrap {
            display: flex;
            gap: 16px;
            margin-top: 10px;
            align-items: flex-start;
        }

        .avg-box {
            border: 1px solid #000;
            width: 280px;
            flex-shrink: 0;
        }

        .avg-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .avg-box th,
        .avg-box td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 9px;
            text-align: center;
        }

        .avg-box th {
            background: #f5f5f5;
            font-weight: 700;
        }

        .avg-box .lbl {
            text-align: left;
            font-weight: 700;
            width: 55%;
        }

        .count-box {
            border: 1px solid #000;
            margin-left: auto;
            min-width: 130px;
        }

        .count-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .count-box td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .sign-row {
            margin-left: 20px;
            display: flex;
            gap: 50px;
            align-items: flex-end;
            padding-top: 40px;
            font-size: 11px;
            font-weight: 600;
        }

        .sign-item {
            text-align: center;
            min-width: 140px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div>Daily Attendance Register · {{ $report['standard_label'] }}{{ $filters['division'] ? '-' . $filters['division'] : '' }} · {{ $report['month_name'] }} {{ $filters['year'] }}</div>
        <div>
            <button type="button" class="btn-action btn-print" onclick="window.print()">Print</button>
            <button type="button" class="btn-action btn-close" onclick="window.close()">Close</button>
        </div>
    </div>

    <h1 class="school-title">{{ $schoolName }}</h1>
    <div class="title-row">
        <h2 class="report-title">Daily Attendance Register</h2>
        <div class="report-date">{{ $report['report_date'] }}</div>
    </div>
    <div class="meta-row">
        <span>Month: <em>{{ $report['month_name'] }}</em></span>
        <span>Year: <em>{{ $filters['year'] }}</em></span>
        <span>Standard: <em>{{ $report['standard_label'] }}</em></span>
        <span>Class: <em>{{ $filters['division'] ?: '' }}</em></span>
        <span class="teacher">Class Teacher: <em>{{ $classTeacher }}</em></span>
    </div>

    <table class="register">
        <thead>
            <tr>
                <th class="col-roll" rowspan="2">Roll<br>No.</th>
                <th class="col-gr" rowspan="2">GR<br>No.</th>
                <th class="col-name" rowspan="2">Name of Student</th>
                <th class="col-caste" rowspan="2">Caste</th>
                <th class="col-dob" rowspan="2">Birthdate</th>
                @for ($day = 1; $day <= $dayCols; $day++)
                    @php
                        $meta = $report['days'][$day - 1] ?? null;
                        $sun = $meta && $meta['is_sunday'];
                    @endphp
                    <th class="col-day {{ $sun ? 'sun-col' : '' }}">
                        @if ($meta)
                            <span class="dow">{{ $meta['dow'] }}</span>
                        @endif
                    </th>
                @endfor
                <th class="col-sum" rowspan="2">Atten. From<br>This Month</th>
                <th class="col-sum" rowspan="2">Atten. From<br>Pre. Month</th>
                <th class="col-sum" rowspan="2">Atten. From<br>End of this Month</th>
            </tr>
            <tr>
                @for ($day = 1; $day <= $dayCols; $day++)
                    @php
                        $meta = $report['days'][$day - 1] ?? null;
                        $sun = $meta && $meta['is_sunday'];
                    @endphp
                    <th class="col-day day-num {{ $sun ? 'sun-col' : '' }}">{{ $meta ? $meta['day'] : '' }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($report['students'] as $student)
                <tr>
                    <td>{{ $student['roll_no'] }}</td>
                    <td>{{ $student['gr_no'] }}</td>
                    <td class="name-cell">{{ $student['name'] }}</td>
                    <td class="caste-cell">{{ $student['caste'] }}</td>
                    <td>{{ $student['birthdate'] }}</td>
                    @for ($day = 1; $day <= $dayCols; $day++)
                        @php
                            $meta = $report['days'][$day - 1] ?? null;
                            $sun = $meta && $meta['is_sunday'];
                            $mark = ($meta && isset($student['marks'][$day])) ? $student['marks'][$day] : '';
                        @endphp
                        <td class="col-day {{ $sun ? 'sun-col' : '' }}"><span class="mark">{{ $mark }}</span></td>
                    @endfor
                    <td class="sum-val">{{ $student['this_month'] !== '' ? $student['this_month'] : '' }}</td>
                    <td class="sum-val">{{ $student['pre_month'] !== '' ? $student['pre_month'] : '' }}</td>
                    <td class="sum-val">{{ $student['end_month'] !== '' ? $student['end_month'] : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $totalCols }}" style="padding:16px;text-align:center;">No students found for this class.</td>
                </tr>
            @endforelse

            @php
                $footerRows = [
                    'Total Present Boys'  => 'present_boys',
                    'Total Absent Boys'   => 'absent_boys',
                    'Total Present Girls' => 'present_girls',
                    'Total Absent Girls'  => 'absent_girls',
                    'Total Present'       => 'present_total',
                    'Total Absent'        => 'absent_total',
                    'Total'               => 'strength',
                ];
            @endphp
            @foreach ($footerRows as $label => $key)
                <tr>
                    <td class="footer-label" colspan="5">{{ $label }}</td>
                    @for ($day = 1; $day <= $dayCols; $day++)
                        @php
                            $meta = $report['days'][$day - 1] ?? null;
                            $sun = $meta && ($meta['is_sunday'] ?? false);
                            $val = $meta ? ($report['daily_totals'][$day][$key] ?? '') : '';
                            if ($val === 0 && $key !== 'strength') {
                                $val = '';
                            }
                        @endphp
                        <td class="col-day {{ $sun ? 'sun-col' : '' }}">{{ $val }}</td>
                    @endfor
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="bottom-wrap">
        <div class="avg-box">
            <table>
                <tr>
                    <th class="lbl">Avg. Attendance</th>
                    <th>Boys</th>
                    <th>Girls</th>
                    <th>Total</th>
                </tr>
                <tr>
                    <td class="lbl">1. Boys / 2. Girls / 3. Total</td>
                    <td>{{ $report['avg']['boys'] ?? '' }}</td>
                    <td>{{ $report['avg']['girls'] ?? '' }}</td>
                    <td>{{ $report['avg']['total'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="lbl">% of Avg. Attendance</th>
                    <th>Boys</th>
                    <th>Girls</th>
                    <th>Total</th>
                </tr>
                <tr>
                    <td class="lbl">1. Boys / 2. Girls / 3. Total</td>
                    <td>{{ $report['avg_pct']['boys'] ?? '' }}</td>
                    <td>{{ $report['avg_pct']['girls'] ?? '' }}</td>
                    <td>{{ $report['avg_pct']['total'] ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="count-box">
            <table>
                <tr><td>Boys</td><td>{{ $report['counts']['boys'] ?? '' }}</td></tr>
                <tr><td>Girls</td><td>{{ $report['counts']['girls'] ?? '' }}</td></tr>
                <tr><td>Total</td><td>{{ $report['counts']['total'] ?? '' }}</td></tr>
            </table>
        </div>

        <div class="sign-row">
            <div class="sign-item">Sign of Teacher</div>
            <div class="sign-item">Sign of Principal</div>
        </div>
    </div>
</body>
</html>
