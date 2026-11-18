@php
    $amount = (float) collect($lines)->sum('amount');
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
    $words = (int) round($amount) === 0 ? 'Zero' : $spell((int) round($amount));
    $amountInWords = trim($words) . ' Only.';
    $stdName = $entry->standard->name ?? '—';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bus Fee Receipt</title>
    <style>
        body { margin: 0; padding: 14px; font-family: dejavusans, sans-serif; color: #000; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 8px 10px; vertical-align: middle; }
        .head { text-align: center; font-weight: bold; line-height: 1.35; padding: 12px 10px; white-space: nowrap; font-size: 14px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .date-cell { width: 160px; text-align: right; font-weight: bold; white-space: nowrap; }
        .amt-col { width: 160px; text-align: right; white-space: nowrap; }
        .total { color: #e1141c; font-weight: bold; text-align: right; white-space: nowrap; }
        .sign { text-align: right; font-weight: bold; padding: 48px 14px 14px; height: 78px; vertical-align: bottom; border: 1px solid #000; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="head" colspan="3">
                SHREEMAD VALLABHADHISH EDU. &amp; CHA. TRUST KESHOD
            </td>
        </tr>
        <tr>
            <td colspan="2" class="bold">Receipt No : {{ $entry->receipt_no }}</td>
            <td class="date-cell">Date : {{ $entry->date?->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td colspan="3" class="bold">Name : {{ strtoupper($entry->student_name) }}</td>
        </tr>
        <tr>
            <td colspan="3" class="bold">Standard : {{ $stdName }}</td>
        </tr>
        <tr>
            <th style="width:50px" class="center">No</th>
            <th>Description</th>
            <th class="amt-col">Amount</th>
        </tr>
        @foreach ($lines as $line)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $line['description'] }}</td>
                <td class="amt-col">{{ number_format((float) $line['amount'], 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" style="height:60px"></td>
        </tr>
        <tr>
            <td colspan="2" class="total">Total :</td>
            <td class="total amt-col">{{ number_format($amount, 2) }}</td>
        </tr>
        <tr>
            <td class="bold" colspan="3">In Words Rs. : {{ $amountInWords }}</td>
        </tr>
        <tr>
            <td class="sign" colspan="3">Received By</td>
        </tr>
    </table>
</body>
</html>
