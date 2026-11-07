<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Student List</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}">
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 13px; }
        .no-print { text-align: center; padding: 10px; background: #f3f4f6; border-bottom: 1px solid #ddd; }
        .no-print button { padding: 8px 16px; margin: 0 4px; border: 0; border-radius: 4px; cursor: pointer; }
        .btn-print { background: #0f766e; color: #fff; }
        .btn-close { background: #6b7280; color: #fff; }
        .sheet { padding: 8px 10px; }
        h1 { margin: 0; text-align: center; font-size: 22px; font-weight: 800; color: #1f2580; letter-spacing: .02em; }
        h2 { margin: 4px 0 4px; text-align: center; font-size: 18px; color: #c62828; font-family: "Times New Roman", Times, serif; }
        .period { text-align: center; font-weight: 700; margin-bottom: 12px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        th { font-weight: 800; text-align: center; background: #fff; }
        .c { text-align: center; }
        .name { text-transform: uppercase; }
        .group-row td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-weight: 700;
            background: #fff;
        }
        .group-row .lbl { color: #1f2580; }
        .group-row .std-val { color: #6a1b9a; font-weight: 800; }
        .group-row .cls-val { color: #b71c1c; font-weight: 800; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <div class="sheet">
        <h1>{{ $schoolName }}</h1>
        <h2>New Student List</h2>
        <div class="period">Period : {{ $rangeLabel }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width:8%">Sr.No.</th>
                    <th style="width:12%">GR No.</th>
                    <th>Name</th>
                    <th style="width:14%">Adm. Date</th>
                    <th style="width:18%">Note</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = 0; @endphp
                @forelse ($grouped as $groupKey => $rows)
                    @php
                        $parts = explode('||', (string) $groupKey);
                        $stdName = $parts[0] ?? '-';
                        $clsName = $parts[1] ?? '-';
                    @endphp
                    <tr class="group-row">
                        <td colspan="5">
                            <span class="lbl">Standard :</span>
                            <span class="std-val">{{ $stdName }}</span>
                            &nbsp;&nbsp;
                            <span class="lbl">Class :</span>
                            <span class="cls-val">{{ $clsName }}</span>
                        </td>
                    </tr>
                    @foreach ($rows as $row)
                        @php
                            $sr++;
                            $name = strtoupper(trim(preg_replace('/\s+/', ' ', ($row->first_name ?? '') . ' ' . ($row->last_name ?? '') . ' ' . ($row->father_name ?? ''))));
                            $admDate = $row->admission_date ? \Carbon\Carbon::parse($row->admission_date)->format('d-m-Y') : '-';
                            $note = '';
                        @endphp
                        <tr>
                            <td class="c">{{ $sr }}</td>
                            <td class="c">{{ $row->gr_no ?: '-' }}</td>
                            <td class="name">{{ $name ?: '-' }}</td>
                            <td class="c">{{ $admDate }}</td>
                            <td>{{ $note }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="c">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
