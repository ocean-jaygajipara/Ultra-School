<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ($autoPrint ?? true) ? 'Print' : 'View' }} Bus Transport Fee Receipt - {{ $feesData->receipt_no }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 12px;
            background: #fff;
        }
        .no-print {
            text-align: right;
            margin-bottom: 10px;
        }
        .print-btn {
            background: #1f2580;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .receipt {
            width: 100%;
            border: 2px solid #1f2580;
            padding: 14px 18px;
            box-sizing: border-box;
            position: relative;
            margin-bottom: 18px;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -45%);
            opacity: 0.07;
            z-index: 0;
            pointer-events: none;
        }
        .watermark img { width: 280px; height: auto; }
        .receipt > *:not(.watermark) { position: relative; z-index: 1; }
        .header {
            border-bottom: 2px solid #1f2580;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .school-name {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            color: #1f2580;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .school-place {
            text-align: center;
            color: #e1141c;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .12em;
        }
        .receipt-title {
            text-align: center;
            margin: 12px 0 8px;
            font-size: 15px;
            font-weight: 800;
            text-decoration: underline;
            color: #1f2580;
        }
        .info-table, .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .info-table td {
            padding: 5px 4px;
            vertical-align: top;
        }
        .fees-table th, .fees-table td {
            border: 1px solid #000;
            padding: 7px 10px;
        }
        .signature {
            margin-top: 50px;
            margin-bottom: 8px;
        }
        .footer {
            margin-top: 10px;
            font-size: 11px;
            font-style: italic;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt { page-break-inside: avoid; }
        }
    </style>
</head>
<body @if ($autoPrint ?? true) onload="window.print()" @endif>
    <div class="no-print">
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
        <a href="{{ route('bus-fee-collection.create') }}" class="print-btn" style="text-decoration:none;display:inline-block;background:#5f6488;">New Receipt</a>
        <a href="{{ route('bus-fee-collection.index') }}" class="print-btn" style="text-decoration:none;display:inline-block;background:#2e3a9e;">List</a>
    </div>

    <div class="receipt">
            <div class="watermark">
                <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="">
            </div>

            <div class="header">
                <table class="header-table">
                    <tr>
                        <td style="width: 90px;">
                            <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="Logo" style="width: 80px; height: auto;">
                        </td>
                        <td>
                            <div class="school-name">{{ $schoolName }}</div>
                            <div class="school-place">KESHOD</div>
                        </td>
                        <td style="width: 90px;"></td>
                    </tr>
                </table>
            </div>

            <div class="receipt-title">
                Bus Transport Fee Receipt
            </div>

            <table class="info-table">
                <tr>
                    <td style="width:50%;">Receipt No.: <strong>{{ $feesData->receipt_no }}</strong></td>
                    <td style="width:50%; text-align:right;">Date: <strong>{{ $feesData->date?->format('d-m-Y') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2">Student Name: <strong>{{ $feesData->student_name }}</strong></td>
                </tr>
                <tr>
                    <td>GR No: <strong>{{ $feesData->gr_no ?? '-' }}</strong></td>
                    <td>Std / Div: <strong>{{ trim(($feesData->standard ?? '-') . ' / ' . ($feesData->division ?? '-')) }}</strong></td>
                </tr>
                <tr>
                    <td>Bus Route: <strong>{{ $feesData->bus_route_village ?? '-' }}</strong></td>
                    <td>Academic Year: <strong>{{ $feesData->academic_year ?? '-' }}</strong></td>
                </tr>
                @if ($feesData->period_label)
                    <tr>
                        <td colspan="2">Fee Type: <strong>{{ $feesData->period_label }}</strong></td>
                    </tr>
                @endif
            </table>

            <table class="fees-table" style="margin-top: 12px;">
                <thead>
                    <tr>
                        <th style="text-align:center;">Particulars</th>
                        <th style="text-align:center; width: 140px;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Bus Transport Fee{{ $feesData->bus_route_village ? ' — ' . $feesData->bus_route_village : '' }}</td>
                        <td style="text-align:right;">{{ number_format((float) $feesData->amount, 2) }}/-</td>
                    </tr>
                    <tr>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:right;">{{ number_format((float) $feesData->amount, 2) }}/-</th>
                    </tr>
                </tbody>
            </table>

            <table class="info-table">
                <tr>
                    <td style="width: 30%;">Rs. In Words</td>
                    <td><strong>{{ $amountInWords }}</strong></td>
                </tr>
                <tr>
                    <td>Payment Mode</td>
                    <td>
                        <strong>
                            @php $mode = strtolower($feesData->mode ?? ''); @endphp
                            @if (($mode === 'online' || $mode === 'upi') && $feesData->upi_id)
                                {{ $feesData->mode }} ({{ $feesData->upi_id }})
                            @elseif ($mode === 'cheque' && $feesData->cheque_no)
                                Cheque ({{ $feesData->cheque_no }})
                            @else
                                {{ $feesData->mode ?? 'Cash' }}
                            @endif
                        </strong>
                    </td>
                </tr>
                @if ($feesData->remark)
                    <tr>
                        <td>Remark</td>
                        <td>{{ $feesData->remark }}</td>
                    </tr>
                @endif
            </table>

            <div class="signature">
                <div style="float:left;">Received By: <strong>{{ $feesData->createdByUser->name ?? '__________' }}</strong></div>
                <div style="float:right;"><strong>Authorised Signatory</strong></div>
                <div style="clear:both;"></div>
            </div>

            <div class="footer">
                Note: This is a Bus Transport Fee receipt only. School / tuition fees are collected on a separate receipt.
            </div>
        </div>
</body>
</html>
