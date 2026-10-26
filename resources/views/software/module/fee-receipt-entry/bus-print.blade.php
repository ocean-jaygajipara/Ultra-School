@php
    $amount = (float) collect($lines)->sum('amount');
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
    $words = (int) round($amount) === 0 ? 'Zero' : $spell((int) round($amount));
    $words = ucfirst(strtolower($words));
    $words = preg_replace('/ (thousand|lakh|crore) /', ' $1, ', $words);
    $amountInWords = trim($words) . ' Only.';
    $stdName = $entry->standard->name ?? '—';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Bus Fee Receipt</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 8px; background: #fff; }
        .sheet { display: flex; flex-direction: column; gap: 12px; align-items: stretch; }
        .slip {
            width: 100%;
            border: 1.5px solid #000;
            color: #000;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
        }
        .slip table { width: 100%; border-collapse: collapse; }
        .slip td, .slip th { border: 1px solid #000; padding: 4px 6px; }
        .head { text-align: center; font-weight: 700; line-height: 1.25; padding: 8px 6px; }
        .head .org { font-size: 18px; }
        .meta td { font-weight: 700; }
        .right { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .total { color: #e1141c; font-weight: 700; text-align: right; }
        .words { font-weight: 700; padding: 8px 6px; }
        .sign { text-align: right; font-weight: 700; padding: 48px 10px 8px; border: 0; height: 70px; vertical-align: bottom; }
        .plain td { border: 0; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>
    <div class="sheet">
        @for ($copy = 0; $copy < 2; $copy++)
            <div class="slip">
                <table>
                    <tr>
                        <td class="head" colspan="3">
                            <div class="org">SHREEMAD VALLABHADHISH</div>
                            <div class="org">EDU. &amp; CHA. TRUST</div>
                            <div>KESHOD</div>
                        </td>
                    </tr>
                    <tr class="meta">
                        <td colspan="2">Receipt No : {{ $entry->receipt_no }}</td>
                        <td class="right" style="white-space:nowrap; width:130px;">Date : {{ $entry->date?->format('d-m-Y') }}</td>
                    </tr>
                    <tr class="meta">
                        <td colspan="3">Name : {{ strtoupper($entry->student_name) }}</td>
                    </tr>
                    <tr class="meta">
                        <td colspan="3">Standard : {{ $stdName }}</td>
                    </tr>
                    <tr>
                        <th style="width:46px">No</th>
                        <th>Description</th>
                        <th class="right" style="width:110px">Amount</th>
                    </tr>
                    @foreach ($lines as $line)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $line['description'] }}</td>
                            <td class="right">{{ number_format((float) $line['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="height:72px"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="total">Total :</td>
                        <td class="total">{{ number_format($amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="words" colspan="3">In Words Rs. : {{ $amountInWords }}</td>
                    </tr>
                    <tr class="plain">
                        <td class="sign" colspan="3">Received By</td>
                    </tr>
                </table>
            </div>
        @endfor
    </div>
</body>
</html>
