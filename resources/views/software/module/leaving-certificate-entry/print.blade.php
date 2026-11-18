<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaving Certificate - {{ $entry->leaving_certi_no ?: $entry->gr_no }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}">
    <style>
        @page { size: A4 portrait; margin: 10mm 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            color: #111;
            font-size: 14px;
            line-height: 1.35;
            background: #e8e8e8;
        }
        .no-print {
            text-align: center;
            padding: 10px;
            background: #f3f4f6;
            border-bottom: 1px solid #ddd;
            font-family: Arial, Helvetica, sans-serif;
        }
        .no-print button {
            padding: 8px 16px;
            margin: 0 4px;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-print { background: #0f766e; color: #fff; }
        .btn-close { background: #6b7280; color: #fff; }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 12px auto;
            background: #fff;
            padding: 8mm;
        }
        .frame {
            border: 2.5px solid #1a237e;
            padding: 4px;
            min-height: calc(297mm - 16mm);
        }
        .frame-inner {
            border: 1px solid #3949ab;
            padding: 10mm 12mm 12mm;
            min-height: calc(297mm - 26mm);
            position: relative;
        }

        .header {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .letterhead {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-logo {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
            object-fit: contain;
        }
        .header-text {
            flex: 1;
            text-align: center;
            min-width: 0;
        }
        .header-spacer {
            width: 72px;
            flex-shrink: 0;
        }
        .school-name {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: .03em;
            color: #0d1b5e;
            text-transform: uppercase;
            line-height: 1.15;
            white-space: nowrap;
        }
        .school-place {
            margin: 2px 0 0;
            font-size: 13px;
            color: #333;
            font-family: Arial, Helvetica, sans-serif;
        }
        .doc-title {
            margin: 10px 0 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .12em;
            color: #b71c1c;
            text-transform: uppercase;
            text-decoration: underline;
            text-align: center;
        }

        .meta-row {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 10px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            table-layout: fixed;
        }
        .meta-row td {
            padding: 4px 6px;
            white-space: nowrap;
            vertical-align: middle;
            width: 33.33%;
        }
        .meta-row td.center { text-align: center; }
        .meta-row td.right { text-align: right; }
        .meta-row .lbl {
            font-weight: 700;
            margin-right: 4px;
        }
        .meta-row .val {
            display: inline;
            border-bottom: 1px solid #333;
            padding: 0 2px 1px;
            font-weight: 700;
            text-transform: uppercase;
            vertical-align: bottom;
        }

        .body-wrap {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-top: 6px;
        }
        .fields {
            flex: 1;
            min-width: 0;
        }

        .photo-frame {
            width: 32mm;
            height: 40mm;
            border: 1.5px solid #000;
            background: #fff;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }
        table.info td {
            padding: 5px 3px;
            vertical-align: bottom;
            border-bottom: 1px dotted #9e9e9e;
        }
        table.info td.lbl {
            width: 34%;
            font-weight: 700;
            color: #222;
            white-space: nowrap;
        }
        table.info td.lbl .no {
            display: inline-block;
            width: 22px;
            color: #1a237e;
            font-weight: 800;
        }
        table.info td.val-cell {
            font-weight: 700;
            text-transform: uppercase;
            color: #111;
        }
        table.info tr.spacer td {
            border-bottom: 0;
            padding: 4px 0;
        }

        .certify {
            margin-top: 14px;
            font-size: 13px;
            text-align: justify;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.55;
        }
        .certify strong { text-transform: uppercase; }

        .footer {
            margin-top: 42px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .sign {
            text-align: center;
            min-width: 150px;
            flex: 1;
        }
        .sign .line {
            margin-top: 52px;
            border-top: 1px solid #111;
            padding-top: 6px;
            font-weight: 700;
            font-size: 12px;
        }
        .note-box {
            margin-top: 16px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            border-top: 1px dashed #bbb;
            padding-top: 8px;
        }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
            }
            .frame {
                min-height: auto;
            }
            .frame-inner {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <div class="sheet">
        <div class="frame">
            <div class="frame-inner">
                @php
                    $logoUrl = file_exists(public_path('admin/assets/images/logo-light.png'))
                        ? asset('admin/assets/images/logo-light.png')
                        : (file_exists(public_path('uploads/logo/logo.png'))
                            ? asset('uploads/logo/logo.png')
                            : asset('admin/assets/images/favicon/favicon.png'));
                @endphp
                <div class="header">
                    <div class="letterhead">
                        <img class="header-logo" src="{{ $logoUrl }}" alt="{{ $schoolName }} Logo">
                        <div class="header-text">
                            <h1 class="school-name">{{ $schoolName }}</h1>
                            @if (!empty($school['code']) && $school['code'] === 'ues')
                                <p class="school-place">KESHOD, DIST. JUNAGADH (GUJARAT)</p>
                            @endif
                        </div>
                        <div class="header-spacer" aria-hidden="true"></div>
                    </div>
                    <h2 class="doc-title">Leaving Certificate</h2>
                </div>

                <table class="meta-row">
                    <tr>
                        <td>
                            <span class="lbl">L.C. No. :</span>
                            <span class="val">{{ $entry->leaving_certi_no ?: '-' }}</span>
                        </td>
                        <td class="center">
                            <span class="lbl">G.R. No. :</span>
                            <span class="val">{{ $entry->gr_no ?: '-' }}</span>
                        </td>
                        <td class="right">
                            <span class="lbl">Date :</span>
                            <span class="val">{{ $entry->formatDate('certi_date') ?: '-' }}</span>
                        </td>
                    </tr>
                </table>

                <div class="body-wrap">
                    <div class="fields">
                        <table class="info">
                            <tr>
                                <td class="lbl"><span class="no">1.</span> UID No.</td>
                                <td class="val-cell">{{ $entry->uid_no ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">2.</span> Name of Student</td>
                                <td class="val-cell">{{ $entry->student_name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">3.</span> Caste</td>
                                <td class="val-cell">{{ $entry->caste ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">4.</span> Category</td>
                                <td class="val-cell">{{ $entry->category ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">5.</span> Date of Birth</td>
                                <td class="val-cell">{{ $entry->formatDate('birthdate') ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">6.</span> Place of Birth</td>
                                <td class="val-cell">{{ $entry->birthplace ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">7.</span> Last School Attended</td>
                                <td class="val-cell">{{ $entry->last_school ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">8.</span> Date of Admission</td>
                                <td class="val-cell">{{ $entry->formatDate('admission_date') ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">9.</span> Std. at Admission</td>
                                <td class="val-cell">{{ $entry->admission_std ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">10.</span> Last Std. / Div.</td>
                                <td class="val-cell">{{ trim(($entry->last_std ?: '-') . ' / ' . ($entry->last_division ?: '-')) }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">11.</span> Date of Leaving</td>
                                <td class="val-cell">{{ $entry->formatDate('leave_date') ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">12.</span> Std. Studied</td>
                                <td class="val-cell">{{ $entry->studied_std ?: ($entry->last_std ?: '-') }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">13.</span> Reason for Leaving</td>
                                <td class="val-cell">{{ $entry->leave_reason ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">14.</span> Conduct / Behavior</td>
                                <td class="val-cell">{{ $entry->behavior ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">15.</span> Progress</td>
                                <td class="val-cell">{{ $entry->progress ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">16.</span> Attendance</td>
                                <td class="val-cell">
                                    {{ $entry->attendance ?: '-' }}
                                    @if ($entry->attendance_from)
                                        &nbsp;(From: {{ $entry->attendance_from }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">17.</span> Academic Year</td>
                                <td class="val-cell">{{ $entry->academic_year ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl"><span class="no">18.</span> Fee</td>
                                <td class="val-cell">{{ $entry->fee !== null ? number_format((float) $entry->fee, 2) : '0.00' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="photo-frame">
                        @if ($entry->photo)
                            <img src="{{ asset($entry->photo) }}" alt="Student Photo">
                        @endif
                    </div>
                </div>

                <p class="certify">
                    Certified that the above information is according to the General Register of the school
                    and that <strong>{{ $entry->student_name ?: 'the pupil' }}</strong>
                    has left the school on <strong>{{ $entry->formatDate('leave_date') ?: '________' }}</strong>
                    from Std. <strong>{{ $entry->last_std ?: '____' }}</strong>
                    @if ($entry->last_division)
                        Div. <strong>{{ $entry->last_division }}</strong>
                    @endif
                    for the reason stated above.
                </p>

                @if ($entry->note)
                    <div class="note-box"><strong>Note:</strong> {{ $entry->note }}</div>
                @endif

                <div class="footer">
                    <div class="sign">
                        <div class="line">Class Teacher</div>
                    </div>
                    <div class="sign">
                        <div class="line">Clerk</div>
                    </div>
                    <div class="sign">
                        <div class="line">Principal</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
