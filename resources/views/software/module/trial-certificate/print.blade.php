<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />
    <meta charset="UTF-8">
    <title>Trial Certificate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Merriweather:ital,wght@0,400;0,700;0,900;1,400&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #eef2f6;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            -webkit-font-smoothing: antialiased;
        }

        .certificate-container {
            width: 100%;
            max-width: 820px;
            margin: 8px auto 12px;
            background: #fff;
            padding: 8px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border-radius: 4px;
            font-family: 'Lora', 'Merriweather', Georgia, 'Times New Roman', serif;
        }

        .top-section {
            flex-shrink: 0;
        }

        .certificate-inner {
            border: 2.5px solid #000;
            padding: 14px 28px 16px;
            background: #fff;
            position: relative;
            overflow: hidden;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .certificate-inner > *:not(.cert-watermark) {
            position: relative;
            z-index: 1;
        }

        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 62%;
            max-width: 480px;
            transform: translate(-50%, -45%);
            opacity: 0.08;
            pointer-events: none;
            user-select: none;
            z-index: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .brand-school,
        .title-box {
            font-family: 'Cinzel', 'Playfair Display', 'Merriweather', Georgia, serif;
        }

        .letterhead {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 8px;
        }

        .letterhead-logo {
            width: 70px;
            height: 70px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .letterhead-text {
            flex: 1;
            text-align: center;
        }

        .brand-school {
            color: #1f2580;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1.2px;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .letterhead-meta {
            width: 70px;
            flex-shrink: 0;
        }

        .cert-divider {
            height: 5px;
            margin: 0 0 14px;
            border-top: 3px solid #1f2580;
            border-bottom: 1px solid #1f2580;
        }

        .title-box-wrap {
            text-align: center;
            margin-bottom: 14px;
        }

        .title-box {
            display: inline-block;
            border: 2px solid #555;
            box-shadow: 4px 4px 0px #888;
            padding: 5px 36px 6px;
            color: #0033cc;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: 2.5px;
            background: #fff;
            text-transform: uppercase;
        }

        .ids-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin: 2px 0 10px;
            font-size: 16px;
            font-weight: 700;
            color: #0033cc;
            line-height: 1.7;
        }

        .ids-row .right {
            text-align: right;
        }

        .cert-body {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            padding: 12px 8px 8px;
            color: #111;
        }

        .cert-text {
            width: 100%;
            font-size: 18px;
            line-height: 2.2;
            text-align: justify;
            text-justify: inter-word;
            word-spacing: 0.5px;
        }

        .certify-intro-text {
            font-style: italic;
            font-weight: 600;
            color: #222;
        }

        .student-name-val {
            font-weight: 800;
            font-size: 21.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 1px;
            letter-spacing: 1px;
            color: #000;
            display: inline;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        .val-underline {
            display: inline;
            border-bottom: 2px solid #000;
            padding: 0 6px 1px;
            font-weight: 700;
            color: #000;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        .ssc-tag {
            display: inline;
            border-bottom: 2px solid #000;
            padding: 0 6px 1px;
            color: #0033cc;
            font-weight: 800;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        .text-blue {
            color: #0033cc;
            font-weight: 800;
        }

        .footer-section {
            margin-top: 28px;
            padding-top: 0;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: bottom;
            padding: 0;
        }

        .footer-left {
            width: 50%;
            font-size: 18px;
            line-height: 2.1;
            font-weight: 700;
        }

        .footer-right {
            width: 50%;
            text-align: right;
            font-size: 19.5px;
            font-weight: 800;
            padding-bottom: 6px;
        }

        .print-bar {
            max-width: 820px;
            margin: 8px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border: 0;
            border-radius: 10px;
            background: #1f2580;
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(31, 37, 128, .25);
        }

        .print-btn:hover {
            background: #e1141c;
        }

        @media print {
            html,
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .certificate-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
                height: calc(100vh - 2px);
                page-break-after: always;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .certificate-container:last-child {
                page-break-after: auto;
            }

            .certificate-inner {
                border: 2.5px solid #000;
                padding: 28px 34px 32px;
                height: 100%;
                min-height: 0;
            }

            .letterhead-logo {
                width: 88px;
                height: 88px;
            }

            .letterhead-meta {
                width: 88px;
            }

            .brand-school {
                font-size: 24px;
            }

            .cert-divider {
                margin-bottom: 28px;
            }

            .title-box-wrap {
                margin-bottom: 28px;
            }

            .cert-body {
                flex: 1;
            }

            .cert-text {
                font-size: 20px;
                line-height: 2.55;
            }

            .student-name-val {
                font-size: 22px;
            }

            .footer-section {
                margin-top: auto;
                padding-top: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="print-bar no-print">
        <button type="button" class="print-btn" onclick="window.print()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print{{ $certificates->count() > 1 ? ' All (' . $certificates->count() . ')' : '' }}
        </button>
    </div>

    @foreach ($certificates as $c)
        <div class="certificate-container">
            <div class="certificate-inner">
                <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="" class="cert-watermark">

                <div class="top-section">
                    <div class="letterhead">
                        <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="School Logo" class="letterhead-logo">
                        <div class="letterhead-text">
                            <div class="brand-school">{{ $schoolTitle }} - KESHOD</div>
                        </div>
                        <div class="letterhead-meta"></div>
                    </div>
                    <div class="cert-divider"></div>

                    <div class="title-box-wrap">
                        <div class="title-box">Trial Certificate</div>
                    </div>

                    <div class="ids-row">
                        <div class="left">
                            <div>Gr. No. : {{ $c['gr_no'] !== '' ? $c['gr_no'] : '______' }}</div>
                            <div>UID No. : {{ $c['uid_no'] !== '' ? $c['uid_no'] : '______' }}</div>
                        </div>
                        <div class="right">
                            <div>S.S.C. Index No. : {{ $c['ssc_index'] !== '' ? $c['ssc_index'] : '______' }}</div>
                        </div>
                    </div>
                </div>

                <div class="cert-body">
                    <div class="cert-text">
                        <span class="certify-intro-text">This is to certify that,</span>
                        &nbsp;<span class="student-name-val">{{ $c['student_name'] !== '' ? $c['student_name'] : '____________________' }}</span>
                        &nbsp;was a bonafide student of this School.
                        <span class="ssc-tag">S.S.C.</span>
                        Examination
                        <span class="val-underline">{{ $c['exam_label'] }}</span>
                        at <span class="val-underline">{{ $c['trial'] }}</span> attempt.
                    </div>
                </div>

                <div class="footer-section">
                    <table class="footer-table">
                        <tr>
                            <td class="footer-left">
                                <div>Date &nbsp;: <span class="text-blue">{{ $c['print_date'] }}</span></div>
                                <div>Place : <span class="text-blue">{{ $c['place'] }}</span></div>
                            </td>
                            <td class="footer-right">
                                Principal
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</body>

</html>
