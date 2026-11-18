<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />
    <meta charset="UTF-8">
    <title>Character Certificate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    @php
        $logoUrl = file_exists(public_path('admin/assets/images/logo-light.png'))
            ? asset('admin/assets/images/logo-light.png')
            : (file_exists(public_path('uploads/logo/logo.png'))
                ? asset('uploads/logo/logo.png')
                : '');
    @endphp
    <style>
        @page { margin: 10mm 12mm; }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0; background: #eef2f6; color: #000;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            font-family: 'Lora', Georgia, 'Times New Roman', serif;
        }
        .print-bar {
            max-width: 820px; margin: 10px auto 0; display: flex; justify-content: flex-end;
        }
        .print-btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border: 0;
            border-radius: 10px; background: #1f2580; color: #fff; font-family: Arial, sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
        }
        .certificate-container {
            width: 100%; max-width: 820px; margin: 8px auto 16px; background: #fff;
            padding: 8px; box-shadow: 0 8px 30px rgba(0,0,0,.12);
        }
        .certificate-inner {
            position: relative;
            border: 2.5px solid #000; padding: 22px 34px 24px; background: #fff;
            min-height: 0; overflow: hidden;
        }
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 280px;
            height: 280px;
            object-fit: contain;
            opacity: 0.07;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 0;
        }
        .certificate-inner > *:not(.cert-watermark) {
            position: relative;
            z-index: 1;
        }
        .letterhead {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 8px;
        }
        .letterhead-logo {
            width: 78px;
            height: 78px;
            flex-shrink: 0;
            object-fit: contain;
        }
        .letterhead-text {
            flex: 1;
            text-align: center;
        }
        .brand-school {
            color: #0033cc;
            font-family: 'Cinzel', Georgia, serif;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.6px;
            line-height: 1.2;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .letterhead-meta {
            width: 78px;
            flex-shrink: 0;
        }
        .cert-divider {
            height: 5px;
            margin: 0 0 16px;
            border-top: 3px solid #1f2580;
            border-bottom: 1px solid #1f2580;
        }
        .title-wrap { text-align: center; margin: 4px 0 22px; }
        .title-box {
            display: inline-block; border: 2px solid #555; box-shadow: 4px 4px 0 #888;
            padding: 6px 36px; color: #0033cc; font-family: 'Cinzel', Georgia, serif;
            font-weight: 800; font-size: 22px; letter-spacing: 2px; text-transform: uppercase; background: #fff;
        }
        .ids {
            text-align: right; color: #0033cc; font-family: Arial, Helvetica, sans-serif;
            font-size: 15px; font-weight: 700; line-height: 1.8; margin: 0 4px 28px;
        }
        .body {
            text-align: center; font-size: 18px; line-height: 2.1; color: #111; padding: 4px 6px 10px;
        }
        .lead { font-weight: 600; margin-bottom: 10px; }
        .stu-name {
            display: block; font-weight: 800; font-size: 21px; text-transform: uppercase;
            letter-spacing: .8px; margin: 6px 0 14px; color: #000;
        }
        .para { text-align: center; }
        .val { font-weight: 700; }
        .footer {
            display: flex; justify-content: space-between; align-items: flex-end;
            margin-top: 48px; padding: 0 4px; font-family: Arial, Helvetica, sans-serif;
            font-size: 16px; font-weight: 700;
        }
        .footer .left { line-height: 1.9; }
        .footer .blue { color: #0033cc; }
        .footer .right { min-width: 120px; text-align: center; }

        @media print {
            html, body { background: #fff; }
            .no-print { display: none !important; }
            .certificate-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .certificate-inner { padding: 28px 36px 32px; }
            .letterhead-logo { width: 68px; height: 68px; }
            .brand-school { font-size: 18px; letter-spacing: 0.4px; white-space: nowrap; }
            .footer { margin-top: 70px; }
        }
    </style>
</head>
<body>
    <div class="print-bar no-print">
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
    </div>

    <div class="certificate-container">
        <div class="certificate-inner">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="cert-watermark">
            @endif

            <div class="letterhead">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="School Logo" class="letterhead-logo">
                @else
                    <div class="letterhead-meta"></div>
                @endif
                <div class="letterhead-text">
                    <div class="brand-school">{{ $schoolTitle }}</div>
                </div>
                <div class="letterhead-meta"></div>
            </div>
            <div class="cert-divider"></div>

            <div class="title-wrap">
                <div class="title-box">Character Certificate</div>
            </div>

            <div class="ids">
                <div>G.R. No. : {{ $entry->gr_no ?: '______' }}</div>
                <div>UID NO. : {{ $entry->uid_no ?: '______' }}</div>
            </div>

            <div class="body">
                <div class="lead">This is to certify that,</div>
                <div class="stu-name">{{ $entry->student_name ?: '____________________' }}</div>
                <div class="para">
                    is bonafide student of this school and is studying in standard
                    <span class="val">{{ $stdDisplay }}</span>
                    during Year <span class="val">{{ $entry->academic_year ?: '________' }}</span>.
                    According to our knowledge and belief {{ $sheHe }} bears a good conduct and moral character.
                </div>
            </div>

            <div class="footer">
                <div class="left">
                    <div>Date : <span class="blue">{{ $printDate }}</span></div>
                </div>
                <div class="right">Principal</div>
            </div>
        </div>
    </div>
</body>
</html>
