@php
    $schoolName = $school['name'] ?? config('app.name');
    $schoolCode = strtolower($school['code'] ?? '');
    $place = 'Keshod-362220';
    $taglines = [
        'uv'  => 'Guj. Med. - Std. 1 to 8 - Govt. Regd.-GSEB',
        'ues' => 'Eng. Med. - Std. 1 to 8',
        'ups' => 'Std. 1 to 8',
        'us'  => 'Std. 9 to 10',
    ];
    $tagline = $taglines[$schoolCode] ?? '';
    if ($schoolCode === 'uv') {
        $schoolName = 'Ultra Vidhyalaya';
    }
    $signName = $schoolCode === 'uv' ? 'Ultra Vidhyalay' : $schoolName;

    $net = (float) $entry->net_amount;
    $amountText = number_format($net, 2);
    $spell = function (int $n) use (&$spell): string {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($n < 20) {
            return $ones[$n];
        }
        if ($n < 100) {
            return trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
        }
        if ($n < 1000) {
            return trim($ones[intdiv($n, 100)] . ' Hundred ' . $spell($n % 100));
        }
        if ($n < 100000) {
            return trim($spell(intdiv($n, 1000)) . ' Thousand ' . $spell($n % 1000));
        }
        if ($n < 10000000) {
            return trim($spell(intdiv($n, 100000)) . ' Lakh ' . $spell($n % 100000));
        }

        return trim($spell(intdiv($n, 10000000)) . ' Crore ' . $spell($n % 10000000));
    };
    $amountInWords = ((int) round($net) === 0 ? 'Zero' : $spell((int) round($net))) . ' Rupees Only';

    $stdText = trim((string) ($entry->standard->name ?? ''));
    $isFirst = strtoupper((string) $entry->term) === 'FIRST';
    $isSecond = strtoupper((string) $entry->term) === 'SECOND';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Fee Receipt</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 8px;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
        }
        .sheet { width: 190mm; max-width: 100%; margin: 0 auto; }
        .receipt {
            width: 100%;
            border: 1px solid #000;
            margin: 0 auto 0;
            position: relative;
            overflow: hidden;
            background: #fff;
        }
        .wm {
            position: absolute;
            left: 50%;
            top: 54%;
            width: 58mm;
            height: auto;
            transform: translate(-50%, -50%);
            opacity: 0.14;
            z-index: 0;
            pointer-events: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .receipt table { width: 100%; border-collapse: collapse; position: relative; z-index: 1; }
        .receipt td { border: 0.6px solid #000; vertical-align: top; background: transparent; }
        .meta { padding: 0; }
        .meta-row { display: flex; width: 100%; }
        .meta-row .col {
            flex: 0 0 33.333%;
            max-width: 33.333%;
            padding: 6px 8px;
            border-right: 0.6px solid #000;
            font-size: 13px;
            font-weight: 700;
        }
        .meta-row .col:last-child { border-right: 0; }
        .head { text-align: center; padding: 4px 8px 5px; }
        .head-row { display: flex; align-items: center; }
        .head-logo { flex: 0 0 18mm; }
        .head-logo img { width: 16mm; height: auto; display: block; }
        .head-text { flex: 1; text-align: center; padding-right: 18mm; }
        .cut { border-top: 1px dashed #000; margin: 5mm 0; height: 0; }
        .school { font-size: 20px; font-weight: 700; letter-spacing: .3px; }
        .place { font-size: 13px; font-weight: 700; margin-top: 1px; }
        .tagline { font-size: 11px; font-weight: 700; margin-top: 2px; }
        .line { font-size: 13px; padding: 6px 8px; }
        .label { font-weight: 700; }
        .fill { font-weight: 700; border-bottom: 0.6px solid #000; padding: 0 4px; }
        .term { font-size: 12px; line-height: 1.2; font-weight: 700; white-space: nowrap; text-align: center; }
        .term .on { text-decoration: underline; }
        .desc { width: 68%; font-size: 12px; font-weight: 700; line-height: 1.35; padding: 8px; }
        .amt-wrap { padding: 0; width: 32%; }
        .amt-head { font-size: 13px; font-weight: 700; text-align: center; padding: 4px 6px; border-bottom: 0.6px solid #000; }
        .amt-val { font-size: 15px; font-weight: 700; text-align: right; padding: 16px 8px 10px; }
        .total-label { font-weight: 700; text-align: right; padding: 6px 8px; }
        .words { font-size: 13px; font-weight: 700; padding: 8px; }
        .foot-note { font-size: 11px; font-weight: 700; padding: 8px; width: 44%; }
        .sign { font-size: 12px; font-weight: 700; text-align: center; padding: 64px 4px 8px; line-height: 1.35; }
        .sign-for { white-space: nowrap; }
        @media print {
            body { padding: 0; }
            .sheet { width: 100%; }
            .receipt { break-inside: avoid; page-break-inside: avoid; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="sheet">
        @foreach (['Student Copy', 'School Copy'] as $index => $copyLabel)
            <div class="receipt">
                <img class="wm" src="{{ asset('admin/assets/images/logo-light.png') }}" alt="">
                <table>
                    <tr>
                        <td class="head" colspan="2">
                            <div class="head-row">
                                <div class="head-logo">
                                    <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="">
                                </div>
                                <div class="head-text">
                                    <div class="school">{{ $schoolName }}</div>
                                    <div class="place">{{ $place }}</div>
                                    @if ($tagline !== '')
                                        <div class="tagline">{{ $tagline }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta" colspan="2">
                            <div class="meta-row">
                                <div class="col">{{ $copyLabel }}</div>
                                <div class="col"><span class="label">No.</span> <span class="fill">{{ $entry->receipt_no }}</span></div>
                                <div class="col"><span class="label">Date :</span> <span class="fill">{{ $entry->date?->format('d-m-Y') }}</span></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="line">
                            <span class="label">Std.</span>
                            <span class="fill">{{ $stdText !== '' ? $stdText : '—' }}</span>
                            <span class="label" style="margin-left:10px;">Year :</span>
                            <span class="fill">{{ $entry->academicYear->name ?? '—' }}</span>
                        </td>
                        <td class="line term">
                            <span class="{{ $isFirst ? 'on' : '' }}">First term</span> / <span class="{{ $isSecond ? 'on' : '' }}">Second term</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="line" colspan="2">
                            <span class="label">Received from :</span>
                            <span class="fill">{{ $entry->student_name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="desc">
                            Received against Admission fees (if applicable), Term fees, Education fees - Tuition fees, computer fees, Miscellaneous (if any)
                        </td>
                        <td class="amt-wrap">
                            <div class="amt-head">Amount Rs.</div>
                            <div class="amt-val">{{ $amountText }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="total-label">Total</td>
                        <td class="amt-val">{{ $amountText }}</td>
                    </tr>
                    <tr>
                        <td class="words" colspan="2">Rs. (in words) {{ $amountInWords }}</td>
                    </tr>
                    <tr>
                        <td class="line" colspan="2">
                            <span class="label">Cheque No.</span> ______________
                            <span class="label" style="margin-left:10px;">Date :</span> ______________
                        </td>
                    </tr>
                    <tr>
                        <td class="line" colspan="2">
                            <span class="label">A/c No.</span> ______________
                            <span class="label" style="margin-left:16px;">Bank :</span> ______________
                        </td>
                    </tr>
                    <tr>
                        <td class="foot-note">(Subject to the realisation of cheque)</td>
                        <td class="sign"><span class="sign-for">For, {{ $signName }}</span><br>Keshod</td>
                    </tr>
                </table>
            </div>
            @if ($index === 0)
                <div class="cut"></div>
            @endif
        @endforeach
    </div>
</body>
</html>
