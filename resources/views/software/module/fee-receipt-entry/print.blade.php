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

    $stdText = trim(($entry->standard->name ?? '') . ($entry->division ? ' - ' . $entry->division : ''));
    $isFirst = strtoupper((string) $entry->term) === 'FIRST';
    $isSecond = strtoupper((string) $entry->term) === 'SECOND';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Receipt {{ $entry->receipt_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 8px;
            font-family: "Times New Roman", Times, serif;
            color: #1a3d8f;
            background: #fff;
        }
        .receipt {
            width: 104mm;
            border: 2px solid #1a3d8f;
            margin: 0 auto;
        }
        .receipt table { width: 100%; border-collapse: collapse; }
        .receipt td { border: 1px solid #1a3d8f; vertical-align: top; }
        .head { text-align: center; padding: 6px 8px 7px; }
        .school { font-size: 20px; font-weight: 700; letter-spacing: .3px; }
        .place { font-size: 13px; font-weight: 700; margin-top: 1px; }
        .tagline { font-size: 11px; font-weight: 700; margin-top: 2px; }
        .line { font-size: 13px; padding: 6px 8px; }
        .label { font-weight: 700; }
        .fill { font-weight: 700; border-bottom: 1px solid #1a3d8f; padding: 0 4px; }
        .term { font-size: 12px; line-height: 1.35; font-weight: 700; }
        .term .on { text-decoration: underline; }
        .desc { width: 68%; font-size: 12px; font-weight: 700; line-height: 1.35; padding: 8px; }
        .amt-wrap { padding: 0; width: 32%; }
        .amt-head { font-size: 13px; font-weight: 700; text-align: center; padding: 4px 6px; border-bottom: 1px solid #1a3d8f; }
        .amt-val { font-size: 15px; font-weight: 700; text-align: right; padding: 16px 8px 10px; }
        .total-label { font-weight: 700; text-align: right; padding: 6px 8px; }
        .words { font-size: 13px; font-weight: 700; padding: 8px; }
        .foot-note { font-size: 11px; font-weight: 700; padding: 8px; width: 58%; }
        .sign { font-size: 13px; font-weight: 700; text-align: center; padding: 8px 6px 10px; }
        @media print {
            body { padding: 0; }
            @page { size: 110mm 190mm; margin: 4mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <table>
            <tr>
                <td class="head" colspan="2">
                    <div class="school">{{ $schoolName }}</div>
                    <div class="place">{{ $place }}</div>
                    @if ($tagline !== '')
                        <div class="tagline">{{ $tagline }}</div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="line" colspan="2">
                    <span class="label">No.</span>
                    <span class="fill">{{ $entry->receipt_no }}</span>
                    <span class="label" style="margin-left:18px;">Date :</span>
                    <span class="fill">{{ $entry->date?->format('d-m-Y') }}</span>
                </td>
            </tr>
            <tr>
                <td class="line" style="width:62%;">
                    <span class="label">Std.</span>
                    <span class="fill">{{ $stdText !== '' ? $stdText : '—' }}</span>
                    <span class="label" style="margin-left:10px;">Year :</span>
                    <span class="fill">{{ $entry->academicYear->name ?? '—' }}</span>
                </td>
                <td class="line term">
                    <div class="{{ $isFirst ? 'on' : '' }}">First term /</div>
                    <div class="{{ $isSecond ? 'on' : '' }}">Second term</div>
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
                <td class="sign">For, {{ $signName }}<br>Keshod</td>
            </tr>
        </table>
    </div>
</body>
</html>
