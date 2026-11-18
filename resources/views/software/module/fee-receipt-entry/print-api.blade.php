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
            return $ones[$n] ?? '';
        }
        if ($n < 100) {
            return trim(($tens[intdiv($n, 10)] ?? '') . ' ' . ($ones[$n % 10] ?? ''));
        }
        if ($n < 1000) {
            return trim(($ones[intdiv($n, 100)] ?? '') . ' Hundred ' . $spell($n % 100));
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
    $logo = public_path('admin/assets/images/logo-light.png');
    if (!is_file($logo)) {
        $logo = public_path('uploads/logo/logo.png');
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fee Receipt</title>
    <style>
        body { margin: 0; padding: 16px; font-family: dejavusans, sans-serif; color: #000; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        td { border: 0.6px solid #000; vertical-align: middle; padding: 10px 12px; }
        .noborder td { border: 0; padding: 2px 6px; }
        .head-cell { padding: 12px 10px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .school { font-size: 18px; font-weight: bold; padding: 2px 0; }
        .place, .tagline { font-size: 11px; font-weight: bold; padding: 1px 0; }
        .amt-wrap { width: 32%; font-size: 14px; font-weight: bold; text-align: right; white-space: nowrap; padding: 12px 14px; }
        .desc { width: 68%; line-height: 1.45; padding: 12px 14px; }
        .meta { padding: 10px 12px; }
        .words { padding: 12px 14px; }
        .sign { text-align: center; padding: 48px 10px 14px; font-weight: bold; line-height: 1.4; }
        .note { padding: 12px 14px; font-size: 11px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="2" class="center head-cell">
                <table class="noborder" width="100%">
                    <tr>
                        <td style="width:70px; border:0; vertical-align:middle;">
                            @if (is_file($logo))
                                <img src="{{ $logo }}" width="54" alt="">
                            @endif
                        </td>
                        <td class="center" style="border:0; vertical-align:middle;">
                            <div class="school">{{ $schoolName }}</div>
                            <div class="place">{{ $place }}</div>
                            @if ($tagline !== '')
                                <div class="tagline">{{ $tagline }}</div>
                            @endif
                        </td>
                        <td style="width:70px; border:0;"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="bold meta" style="width:33%;">Student Copy</td>
            <td class="bold meta">No. {{ $entry->receipt_no }} &nbsp;&nbsp;&nbsp; Date : {{ $entry->date?->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="meta">
                <span class="bold">Std.</span> {{ $stdText !== '' ? $stdText : '—' }}
                &nbsp;&nbsp;&nbsp;
                <span class="bold">Year :</span> {{ $entry->academicYear->name ?? '—' }}
            </td>
            <td class="center bold meta">
                <span style="{{ $isFirst ? 'text-decoration:underline;' : '' }}">First term</span>
                /
                <span style="{{ $isSecond ? 'text-decoration:underline;' : '' }}">Second term</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="meta">
                <span class="bold">Received from :</span> {{ $entry->student_name }}
            </td>
        </tr>
        <tr>
            <td class="desc">
                Received against Admission fees (if applicable), Term fees, Education fees - Tuition fees, computer fees, Miscellaneous (if any)
            </td>
            <td class="amt-wrap">Amount Rs. {{ $amountText }}</td>
        </tr>
        <tr>
            <td class="right bold meta">Total</td>
            <td class="amt-wrap">{{ $amountText }}</td>
        </tr>
        <tr>
            <td colspan="2" class="bold words">Rs. (in words) {{ $amountInWords }}</td>
        </tr>
        <tr>
            <td colspan="2" class="meta">
                <span class="bold">Cheque No.</span> ______________
                &nbsp;&nbsp;&nbsp;
                <span class="bold">Date :</span> ______________
            </td>
        </tr>
        <tr>
            <td colspan="2" class="meta">
                <span class="bold">A/c No.</span> ______________
                &nbsp;&nbsp;&nbsp;
                <span class="bold">Bank :</span> ______________
            </td>
        </tr>
        <tr>
            <td class="note">(Subject to the realisation of cheque)</td>
            <td class="sign">For, {{ $signName }}<br>Keshod</td>
        </tr>
    </table>
</body>
</html>
