<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>S.S.C. Subject Selection Form - {{ $data['gr_no'] ?? '' }}</title>
    <style>
        @page { size: A4 portrait; margin: 5mm 7mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
            line-height: 1.25;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page {
            width: 196mm;
            max-width: 100%;
            margin: 0 auto;
        }
        .sheet {
            border: 2.4px double #000;
            padding: 7px 10px 8px;
            min-height: 277mm;
            display: flex;
            flex-direction: column;
        }
        .sheet-body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 1px;
        }
        .sheet-foot {
            margin-top: auto;
            padding-top: 10px;
        }

        /* header meta */
        .meta {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            align-items: flex-end;
            margin-bottom: 2px;
        }
        .meta .item {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            min-width: 0;
        }
        .meta .item .lab {
            flex: 0 0 auto;
            font-weight: 700;
            white-space: nowrap;
        }
        .meta .item .val {
            flex: 1 1 auto;
            border-bottom: 1px solid #000;
            min-width: 36px;
            padding: 0 3px 1px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
        }

        .title-box { text-align: center; margin: 6px 0 8px; }
        .title-box .t1 {
            color: #008080;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .title-box .t2 {
            color: #008080;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* field rows — label never overlaps value */
        .fline {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            margin: 4px 0;
            width: 100%;
        }
        .fline .lab {
            flex: 0 0 auto;
            font-weight: 700;
            white-space: nowrap;
        }
        .fline .val {
            flex: 1 1 auto;
            border-bottom: 1px solid #000;
            min-width: 0;
            min-height: 15px;
            padding: 0 4px 1px;
            font-weight: 700;
            text-transform: uppercase;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .fline .plain {
            flex: 1 1 auto;
            min-width: 0;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fline .grow { flex: 1 1 0; min-width: 0; display: flex; align-items: flex-end; gap: 6px; }
        .fline .grow .lab { flex: 0 0 auto; }
        .fline .grow .val,
        .fline .grow .plain { flex: 1 1 auto; min-width: 0; }
        .fline-col {
            display: flex;
            flex-direction: column;
            flex: 1 1 0;
            min-width: 0;
            gap: 1px;
            margin-bottom: 2px;
        }
        .fline-col .fline-top,
        .fline-col .pen-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            width: 100%;
        }
        .fline-col .lab {
            flex: 0 0 auto;
            font-weight: 700;
            white-space: nowrap;
        }
        .fline-col .lab-ghost {
            visibility: hidden;
            height: 0;
            overflow: hidden;
            flex: 0 0 auto;
            white-space: nowrap;
            font-weight: 700;
        }
        .fline-col .val-print {
            flex: 1 1 auto;
            min-width: 0;
            min-height: 14px;
            padding: 0 4px 0;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: none;
        }
        .fline-col .pen-line {
            flex: 1 1 auto;
            min-width: 0;
            height: 14px;
            border-bottom: 1px solid #000;
        }

        .blue { color: #0000CC; font-weight: 700; }

        .boxes {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 1px;
            vertical-align: middle;
        }
        .box {
            width: 13px;
            height: 15px;
            border: 1px solid #000;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            line-height: 14px;
            flex: 0 0 auto;
        }

        .addr-lines {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .addr-lines .val {
            width: 100%;
            border-bottom: 1px solid #000;
            min-height: 16px;
            padding: 0 4px 1px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .subj {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 10.5px;
            table-layout: fixed;
        }
        .subj th, .subj td {
            border: 1px solid #000;
            padding: 4px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .subj th { font-weight: 700; }
        .subj td.l { text-align: left; }
        .tick { font-size: 13px; font-weight: 700; }

        .guj {
            margin: 7px 0 3px;
            font-size: 12px;
            font-weight: 700;
        }
        .declare {
            margin-top: 10px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.45;
        }
        .exam-month {
            margin: 45px auto 0;
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .06em;
            color: #000;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .signs {
            display: flex;
            justify-content: space-between;
            margin-top: 28px;
            padding: 0 8px 4px;
        }
        .signs div {
            width: 42%;
            text-align: center;
            color: #0000CC;
            font-weight: 700;
            font-size: 12px;
        }

        .print-bar {
            width: 194mm;
            max-width: 100%;
            margin: 8px auto;
            text-align: right;
        }
        .print-bar button {
            padding: 6px 14px;
            font-size: 13px;
            cursor: pointer;
        }

        @media print {
            .print-bar { display: none !important; }
            .page { width: auto; }
            .sheet { min-height: 277mm; }
            html, body { margin: 0; }
        }
    </style>
</head>
<body>
@php
    $boxes = function (?string $value, int $len): array {
        $clean = preg_replace('/\D+/', '', (string) $value);
        $out = [];
        for ($i = 0; $i < $len; $i++) {
            $out[] = $clean[$i] ?? '';
        }
        return $out;
    };

    $aadhar = $boxes($data['aadhar'] ?? '', 12);
    $pin = $boxes($data['pincode'] ?? '', 6);
    $mobile = $boxes($data['mobile'] ?? '', 10);
    $uid = $boxes($data['uid'] ?? '', 18);

    $addr = trim(preg_replace('/\s+/', ' ', (string) ($data['address'] ?? '')));
    $addrParts = [$addr, '', ''];

    $stdShort = (string) ($data['std'] ?? '');
    if (preg_match('/(\d+)/', $stdShort, $m)) {
        $stdShort = $m[1];
    }

    $yearLabel = $formYearLabel ?? '';
    if (preg_match('/^(\d{4})-(\d{2})$/', $yearLabel, $ym)) {
        $yearLabel = $ym[1] . '-20' . $ym[2];
    }

    $marks = $subjectMarks ?? [];
    $mk = fn ($key) => !empty($marks[$key]);

    $mainSubjects = [
        ['01', 'FIRST LANGUAGE', 'GUJARATI F.L.', '01', '01 GUJARATI', $mk('gujarati_fl')],
        ['02', 'SECOND LANGUAGE 01', 'ENGLISH S.L.', '16', '04 ENGLISH', $mk('english_sl')],
        ['03', 'SECOND LANGUAGE 02', 'SANSKRIT S.L.', '17', '01 GUJARATI', $mk('sanskrit_sl')],
        ['03', 'SECOND LANGUAGE 03', 'HINDI S.L.', '14', '02 HINDI', $mk('hindi_sl')],
        ['04', 'SOCIAL SCIENCE', 'SOCIAL SCIENCE', '10', '01 GUJARATI', $mk('social_science')],
        ['05', 'SCIENCE TECHNOLOGY', 'SCIENCE TECHNOLOGY', '11', '01 GUJARATI', $mk('science_technology')],
        ['06', 'MATHS STANDARD', 'MATHS', '12', '01 GUJARATI', $mk('maths_standard')],
        ['06', 'MATHS BASIC', 'MATHS', '18', '01 GUJARATI', $mk('maths_basic')],
    ];

    $optionalSubjects = [
        ['01', 'OPTION 01', 'COMPUTER THEORY', '24', '04 ENGLISH', $mk('computer_theory')],
        ['02', 'OPTION 02', 'COMPUTER PRACTICAL', '51', '04 ENGLISH', $mk('computer_practical')],
        ['01', 'OPTION 01', 'YOGA, SWA. SHA. THEORY', '40', '', $mk('yoga_theory')],
        ['02', 'OPTION 02', 'YOGA, SWA. SHA. PRACTICAL', '64', '', $mk('yoga_practical')],
    ];
@endphp

<div class="print-bar">
    <button type="button" onclick="window.print()">Print</button>
</div>

<div class="page">
<div class="sheet">
<div class="sheet-body">

    <div class="meta">
        <div class="item" style="flex:1.1"><span class="lab">SR. NO. :</span><span class="val">{{ $data['sr_no'] ?? '' }}</span></div>
        <div class="item" style="flex:1.2"><span class="lab">STUDENT ID. :</span><span class="val">{{ $data['student_id'] ?? '' }}</span></div>
        <div class="item" style="flex:0.9"><span class="lab">STD. :</span><span class="val">{{ $stdShort }}</span></div>
        <div class="item" style="flex:0.9"><span class="lab">CLASS :</span><span class="val">{{ $data['class'] ?? '' }}</span></div>
        <div class="item" style="flex:1.1"><span class="lab">ROLL NO. :</span><span class="val">{{ $data['roll_no'] ?? '' }}</span></div>
    </div>

    <div class="title-box">
        <div class="t1">S.S.C. SUBJECT SELECTION FORM - {{ $yearLabel }}</div>
        <div class="t2">ENGLISH MEDIUM</div>
    </div>

    <div class="meta" style="margin-bottom:6px;">
        <div class="item" style="flex:1.1"><span class="lab">DATE :-</span><span class="val">{{ $formDate }}</span></div>
        <div class="item" style="flex:1.4"><span class="lab">TYPE OF STUDENT :-</span><span class="val">{{ $data['type_of_student'] ?? 'REGULAR' }}</span></div>
        <div class="item" style="flex:1.1"><span class="lab">APP NO. :-</span><span class="val">{{ $data['app_no'] ?? '' }}</span></div>
    </div>

    {{-- Student Name + pen blank line --}}
    <div class="fline" style="align-items:flex-start;">
        <div class="fline-col" style="flex:1 1 100%;">
            <div class="fline-top">
                <span class="lab">01. STUDENT NAME :-</span>
                <span class="val-print">{{ $data['student_name'] ?? '' }}</span>
            </div>
            <div class="pen-row">
                <span class="lab-ghost">01. STUDENT NAME :-</span>
                <span class="pen-line"></span>
            </div>
        </div>
    </div>

    {{-- DOB (pen) + Joining Date --}}
    <div class="fline" style="align-items:flex-start;">
        <div class="fline-col">
            <div class="fline-top">
                <span class="lab">02. DATE OF BIRTH :-</span>
                <span class="val-print">{{ $data['date_of_birth'] ?? '' }}</span>
            </div>
            <div class="pen-row">
                <span class="lab-ghost">02. DATE OF BIRTH :-</span>
                <span class="pen-line"></span>
            </div>
        </div>
        <div class="grow" style="align-self:flex-end;">
            <span class="lab">03. SCHOOL JOINING DATE :-</span>
            <span class="val">{{ $data['school_joining_date'] ?? '' }}</span>
        </div>
    </div>

    <div class="fline">
        <div class="grow">
            <span class="lab">04. MEDIUM :-</span>
            <span class="plain"><span class="blue">{{ $data['medium'] ?: 'ENGLISH' }}</span></span>
        </div>
        <div class="grow">
            <span class="lab">05. PRIMARY SCHOOL TYPE :-</span>
            <span class="plain">{{ trim((string) ($data['primary_school_type'] ?? $entry->primary_school_type ?? '')) }}</span>
        </div>
    </div>

    {{-- Profession + Father Income (pen) --}}
    <div class="fline" style="align-items:flex-start;">
        <div class="grow" style="align-self:flex-end;">
            <span class="lab">06. FATHERS PROFESSION :-</span>
            <span class="val">{{ $data['father_profession'] ?? '' }}</span>
        </div>
        <div class="fline-col">
            <div class="fline-top">
                <span class="lab">07. FATHER INCOME :-</span>
                <span class="val-print">{{ $data['father_income'] ?? '' }}</span>
            </div>
            <div class="pen-row">
                <span class="lab-ghost">07. FATHER INCOME :-</span>
                <span class="pen-line"></span>
            </div>
        </div>
    </div>

    {{-- GR + Cast (pen) + Gender (pen) --}}
    <div class="fline" style="align-items:flex-start;">
        <div class="grow" style="flex:0.85; align-self:flex-end;">
            <span class="lab">08. G.R. NO. :-</span>
            <span class="val">{{ $data['gr_no'] ?? '' }}</span>
        </div>
        <div class="fline-col" style="flex:1.3">
            <div class="fline-top">
                <span class="lab">09. CAST :-</span>
                <span class="val-print">{{ strtoupper(trim((string) ($data['cast'] ?? $entry->cast ?? ''))) }}</span>
            </div>
            <div class="pen-row">
                <span class="lab-ghost">09. CAST :-</span>
                <span class="pen-line"></span>
            </div>
        </div>
        <div class="fline-col" style="flex:0.9">
            <div class="fline-top">
                <span class="lab">10. GENDER :-</span>
                <span class="val-print">{{ $data['gender'] ?? '' }}</span>
            </div>
            <div class="pen-row">
                <span class="lab-ghost">10. GENDER :-</span>
                <span class="pen-line"></span>
            </div>
        </div>
    </div>

    {{-- Mother Name + pen --}}
    <div class="fline" style="align-items:flex-start;">
        <div class="fline-col" style="flex:1 1 100%;">
            <div class="fline-top">
                <span class="lab">11. MOTHER NAME :-</span>
                <span class="val-print">{{ $data['mother_name'] ?? '' }}</span>
            </div>
            <div class="pen-row">
                <span class="lab-ghost">11. MOTHER NAME :-</span>
                <span class="pen-line"></span>
            </div>
        </div>
    </div>

    <div class="fline">
        <span class="lab">12. AADHAR CARD NO. :-</span>
        <span class="boxes">
            @foreach ($aadhar as $ch)<span class="box">{{ $ch }}</span>@endforeach
        </span>
    </div>

    <div class="fline">
        <div class="grow">
            <span class="lab">13. BPL CARD NO. :-</span>
            <span class="val">{{ $data['bpl_card_no'] ?? '' }}</span>
        </div>
        <div class="grow">
            <span class="lab">EMAIL ID :-</span>
            <span class="plain"><span class="blue">{{ $data['email'] ?? 'ultra_school@yahoo.in' }}</span></span>
        </div>
    </div>

    <div class="fline">
        <div class="grow">
            <span class="lab">14. IS WIDOW CHILD :-</span>
            <span class="val">{{ $data['is_widow_child'] ?? '' }}</span>
        </div>
        <div class="grow">
            <span class="lab">15. IS MINORITY :-</span>
            <span class="val">{{ $data['is_minority'] ?? '' }}</span>
        </div>
        <div class="grow">
            <span class="lab">16. MINORITY TYPE :-</span>
            <span class="val">{{ $data['minority_type'] ?? '' }}</span>
        </div>
    </div>

    <div class="fline" style="align-items:flex-start;">
        <span class="lab" style="padding-top:2px;">17. ADDRESS :-</span>
        <div class="addr-lines">
            <div class="val" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $addrParts[0] }}</div>
            <div class="val">{{ $addrParts[1] }}</div>
            <div class="val">{{ $addrParts[2] }}</div>
        </div>
    </div>

    <div class="fline" style="padding-left:12px;">
        <div class="grow" style="flex:0.9">
            <span class="lab">PIN CODE :-</span>
            <span class="boxes">@foreach ($pin as $ch)<span class="box">{{ $ch }}</span>@endforeach</span>
        </div>
        <div class="grow" style="flex:1.1">
            <span class="lab">MO. NO. :-</span>
            <span class="boxes">@foreach ($mobile as $ch)<span class="box">{{ $ch }}</span>@endforeach</span>
        </div>
    </div>

    <div class="fline">
        <span class="lab">18. UID NO. :-</span>
        <span class="boxes">@foreach ($uid as $ch)<span class="box">{{ $ch }}</span>@endforeach</span>
    </div>

    <div class="fline">
        <span class="lab">19. MARITAL STATUS :-</span>
        <span class="plain">{{ strtoupper(trim((string) ($data['marital_status'] ?? $entry->marital_status ?? 'UNMARRIED'))) }}</span>
    </div>

    <table class="subj">
        <thead>
            <tr>
                <th style="width:6%">No.</th>
                <th style="width:24%">Subject Structure</th>
                <th style="width:28%">Subject Name</th>
                <th style="width:14%">Subject Code</th>
                <th style="width:18%">Language Code</th>
                <th style="width:10%">Mark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mainSubjects as $row)
                <tr>
                    <td>{{ $row[0] }}</td>
                    <td class="l">{{ $row[1] }}</td>
                    <td class="l">{{ $row[2] }}</td>
                    <td>{{ $row[3] }}</td>
                    <td class="l">{{ $row[4] }}</td>
                    <td class="tick">{{ $row[5] ? '✓' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="guj">21.</div>
    <table class="subj">
        <tbody>
            @foreach ($optionalSubjects as $row)
                <tr>
                    <td style="width:6%">{{ $row[0] }}</td>
                    <td class="l" style="width:24%">{{ $row[1] }}</td>
                    <td class="l" style="width:28%">{{ $row[2] }}</td>
                    <td style="width:14%">{{ $row[3] }}</td>
                    <td class="l" style="width:18%">{{ $row[4] }}</td>
                    <td class="tick" style="width:10%">{{ $row[5] ? '✓' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="exam-month">MARCH - {{ $examYear ?? '2027' }}</div>

</div>{{-- /.sheet-body --}}

<div class="sheet-foot">
    <div class="signs">
        <div>22. STUDENTS SIGNATURE</div>
        <div>23. PARENTS SIGNATURE</div>
    </div>
</div>

</div>
</div>
</body>
</html>
