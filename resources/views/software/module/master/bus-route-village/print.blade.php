<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Route Village Master Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()"
            style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
        <button onclick="window.close()"
            style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Close</button>
    </div>

    <div class="header">
        <h1>Bus Route Village Master Report</h1>
        <p>Generated on: {{ date('d-m-Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">Sr No</th>
                @foreach ($columns as $columnKey)
                    @if (isset($availableExportColumns[$columnKey]))
                        <th>{{ $availableExportColumns[$columnKey] }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($villages as $index => $village)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @foreach ($columns as $columnKey)
                        @if (isset($availableExportColumns[$columnKey]))
                            <td>
                                @if ($columnKey === 'name')
                                    {{ $village->name ?? '-' }}
                                @elseif ($columnKey === 'status')
                                    {{ $village->status ? ucfirst($village->status) : '-' }}
                                @else
                                    {{ data_get($village, $columnKey, '-') }}
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="text-center">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
