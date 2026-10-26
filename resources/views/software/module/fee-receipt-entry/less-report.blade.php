@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Less Report';
    $route = $modules['route'] ?? 'fee-receipt-entry';
    $yearName = 'All Years';
    foreach ($academicYears as $yearItem) {
        if ((string) ($filters['academic_year_id'] ?? '') === (string) $yearItem->id) {
            $yearName = $yearItem->name;
            break;
        }
    }
    $stdName = 'All Standards';
    foreach ($standards as $std) {
        if ((string) ($filters['standard_id'] ?? '') === (string) $std->id) {
            $stdName = $std->name;
            break;
        }
    }
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        .crx {
            --crx-ink: #1f2580;
            --crx-ink-2: #2e3a9e;
            --crx-muted: #5f6488;
            --crx-line: #e1e4f2;
            --crx-soft: #f7f8fd;
            --crx-accent: #e1141c;
            --crx-accent-soft: #fff1f1;
            --crx-sky: #bfe3f7;
            --crx-sky-soft: #eef7fd;
            --crx-gold: #f5b400;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--crx-ink);
        }
        .crx-panel {
            background: #fff;
            border: 1px solid var(--crx-line);
            border-radius: 18px;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, .12);
        }
        .crx-panel > :first-child { border-top-left-radius: 17px; border-top-right-radius: 17px; }
        .crx-panel > :last-child { border-bottom-left-radius: 17px; border-bottom-right-radius: 17px; }
        .crx .select2-container { width: 100% !important; }
        .crx .select2-dropdown {
            border: 1.5px solid var(--crx-ink) !important;
            border-radius: 12px !important;
            box-shadow: 0 16px 32px -12px rgba(15, 23, 42, .25);
            overflow: hidden;
            z-index: 1060;
        }
        .crx .select2-results__option--highlighted[aria-selected],
        .crx .select2-results__option--highlighted {
            background: var(--crx-ink) !important;
            color: #fff !important;
        }
        .crx .select2-container--default .select2-selection--single {
            height: 46px !important;
            border: 1.5px solid var(--crx-line) !important;
            border-radius: 12px !important;
            background: var(--crx-soft) !important;
        }
        .crx .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 43px !important;
            padding-left: 14px !important;
            color: var(--crx-ink) !important;
            font-weight: 500;
        }
        .crx .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px !important; }
        .crx .select2-container--focus .select2-selection--single,
        .crx .select2-container--open .select2-selection--single {
            border-color: var(--crx-ink) !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(191, 227, 247, .7) !important;
        }
        .crx-banner {
            background:
                radial-gradient(circle at 88% 15%, rgba(245, 180, 0, .35), transparent 42%),
                radial-gradient(circle at 5% 130%, rgba(191, 227, 247, .35), transparent 45%),
                linear-gradient(135deg, #171c6b 0%, var(--crx-ink-2) 100%);
            color: #fff;
            padding: 22px 26px;
        }
        .crx-banner h3, .crx-banner h4 { color: #fff; margin: 0; font-weight: 700; letter-spacing: -.3px; }
        .crx-banner p { color: #d6e6f7; margin: 4px 0 0; font-size: .875rem; }
        .crx-badge-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(245, 180, 0, .18);
            border: 1px solid rgba(245, 180, 0, .6);
            color: var(--crx-gold);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
        }
        .crx-body { padding: 22px 26px; }
        .crx-label {
            display: block; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--crx-muted); margin-bottom: 8px;
        }
        .crx-input {
            height: 46px; width: 100%;
            border: 1.5px solid var(--crx-line);
            border-radius: 12px;
            background: var(--crx-soft);
            padding: 0 14px;
            color: var(--crx-ink);
            font-weight: 500;
        }
        .crx select.select2 {
            position: absolute !important;
            opacity: 0 !important;
            pointer-events: none;
            height: 0 !important;
            width: 0 !important;
        }
        .crx .flatpickr-input { width: 100% !important; }
        .crx-input:focus {
            border-color: var(--crx-ink);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(191, 227, 247, .7);
            outline: none;
        }
        .crx-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .crx-btn {
            height: 46px; padding: 0 18px; border-radius: 12px;
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; border: 1.5px solid transparent;
        }
        .crx-btn-go { background: var(--crx-ink); color: #fff; border-color: var(--crx-ink); }
        .crx-btn-go:hover { background: #171c6b; color: #fff; }
        .crx-btn-print { background: #fff; color: var(--crx-ink); border-color: var(--crx-line); }
        .crx-btn-print:hover { border-color: var(--crx-ink); color: var(--crx-ink); }
        .crx-stats { display: flex; flex-wrap: wrap; gap: 10px; }
        .crx-stat {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(191, 227, 247, .35);
            border-radius: 12px; padding: 8px 14px; min-width: 110px;
        }
        .crx-stat small { display: block; color: var(--crx-sky); font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; }
        .crx-stat strong { color: #fff; font-size: 1.05rem; }
        .crx-stat.is-accent { background: var(--crx-accent); border-color: var(--crx-accent); }
        .crx-stat.is-accent small { color: #ffd9db; }
        .crx-table-wrap {
            border: 1px solid var(--crx-line); border-radius: 14px;
            max-height: 65vh; overflow: auto;
        }
        .crx-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .crx-table thead th {
            position: sticky; top: 0; z-index: 2;
            background: var(--crx-ink); color: #fff;
            font-size: .72rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            padding: 14px 16px; white-space: nowrap;
        }
        .crx-table tbody td {
            padding: 12px 16px; border-bottom: 1px solid #eef0f8;
            font-size: .9rem; vertical-align: middle;
        }
        .crx-table tbody tr:nth-child(even) { background: #fafbff; }
        .crx-table tbody tr:hover { background: var(--crx-sky-soft); }
        .crx-table tfoot td {
            position: sticky; bottom: 0;
            background: var(--crx-sky-soft); color: var(--crx-ink);
            font-weight: 800; padding: 14px 16px; border-top: 1px solid var(--crx-sky);
        }
        .crx-name { font-weight: 600; }
        .crx-gr {
            font-family: Consolas, monospace;
            background: var(--crx-sky-soft); color: var(--crx-ink); border: 1px solid var(--crx-sky);
            padding: 4px 10px; border-radius: 8px; font-size: .82rem; font-weight: 600;
        }
        .crx-less { color: var(--crx-accent); font-weight: 700; }
        .crx-empty { text-align: center; padding: 56px 20px; }
        .crx-empty-icon {
            width: 88px; height: 88px; border-radius: 24px; margin: 0 auto 18px;
            background: linear-gradient(135deg, var(--crx-sky-soft), #fff4d1);
            color: var(--crx-ink); font-size: 2.4rem;
            border: 2px solid var(--crx-sky);
            display: flex; align-items: center; justify-content: center;
        }
        .crx-empty h5 { font-weight: 700; margin-bottom: 6px; }
        .crx-empty p { color: var(--crx-muted); margin: 0; }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => 'Fee Entry', 'url' => route('fee-receipt-entry.index')], ['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="crx my-3">
        <form method="POST" action="{{ route($route . '.less-report') }}" id="lessReportFilter" class="mb-4">
            @csrf
            <div class="crx-panel">
                <div class="crx-banner d-flex align-items-center gap-3">
                    <span class="crx-badge-icon"><i class="ti ti-report-money"></i></span>
                    <div>
                        <h3>Less Report</h3>
                        <p>Receipts where a Less amount was given.</p>
                    </div>
                </div>
                <div class="crx-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="crx-label" for="academic_year_id">Academic Year</label>
                            <select id="academic_year_id" name="academic_year_id" class="form-control select2">
                                <option value="">All Years</option>
                                @foreach ($academicYears as $yearItem)
                                    <option value="{{ $yearItem->id }}" {{ (string) ($filters['academic_year_id'] ?? '') === (string) $yearItem->id ? 'selected' : '' }}>
                                        {{ $yearItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="crx-label" for="standard_id">Standard</label>
                            <select id="standard_id" name="standard_id" class="form-control select2">
                                <option value="">All Standards</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std->id }}" {{ (string) ($filters['standard_id'] ?? '') === (string) $std->id ? 'selected' : '' }}>
                                        {{ $std->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="crx-label" for="from_date">From Date</label>
                            <input type="hidden" name="from_date" id="from_date_value" value="{{ $filters['from_date'] ?? '' }}">
                            <input id="from_date" type="text" class="crx-input crx-date" placeholder="Select date" value="{{ !empty($filters['from_date']) ? \Carbon\Carbon::parse($filters['from_date'])->format('d-m-Y') : '' }}" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <label class="crx-label" for="to_date">To Date</label>
                            <input type="hidden" name="to_date" id="to_date_value" value="{{ $filters['to_date'] ?? '' }}">
                            <input id="to_date" type="text" class="crx-input crx-date" placeholder="Select date" value="{{ !empty($filters['to_date']) ? \Carbon\Carbon::parse($filters['to_date'])->format('d-m-Y') : '' }}" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <div class="crx-actions">
                                <button type="submit" class="crx-btn crx-btn-go"><i class="ti ti-search"></i> Show</button>
                                <button type="submit" name="reset_filter" value="1" class="crx-btn crx-btn-print"><i class="ti ti-refresh"></i> Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="crx-panel">
            <div class="crx-banner">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="crx-badge-icon"><i class="ti ti-receipt"></i></span>
                        <div>
                            <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">Less Amount</p>
                            <h4>{{ $yearName }} · {{ $stdName }}</h4>
                        </div>
                    </div>
                    <div class="crx-stats">
                        <div class="crx-stat"><small>Receipts</small><strong>{{ $rows->count() }}</strong></div>
                        <div class="crx-stat"><small>Total</small><strong>{{ number_format((float) $rows->sum('total'), 2) }}</strong></div>
                        <div class="crx-stat is-accent"><small>Less</small><strong>{{ number_format((float) $rows->sum('less_amount'), 2) }}</strong></div>
                        <div class="crx-stat"><small>Net</small><strong>{{ number_format((float) $rows->sum('net_amount'), 2) }}</strong></div>
                    </div>
                </div>
            </div>
            <div class="crx-body">
                <div class="crx-table-wrap">
                    <table class="crx-table">
                        <thead>
                            <tr>
                                <th style="width:70px">Sr</th>
                                <th>Receipt No</th>
                                <th>Date</th>
                                <th>GR No</th>
                                <th>Student Name</th>
                                <th>Std</th>
                                <th>Term</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Less</th>
                                <th class="text-end">Net Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->receipt_no ?: '—' }}</td>
                                    <td>{{ $row->date ? $row->date->format('d-m-Y') : '—' }}</td>
                                    <td><span class="crx-gr">{{ $row->gr_no ?: '—' }}</span></td>
                                    <td><span class="crx-name">{{ $row->student_name }}</span></td>
                                    <td>{{ $row->standard->name ?? '—' }}{{ $row->division ? ' - ' . $row->division : '' }}</td>
                                    <td>{{ $row->term }}</td>
                                    <td class="text-end">{{ number_format((float) $row->total, 2) }}</td>
                                    <td class="text-end crx-less">{{ number_format((float) $row->less_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $row->net_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="crx-empty">
                                            <div class="crx-empty-icon"><i class="ti ti-receipt-off"></i></div>
                                            <h5>No receipt with Less amount</h5>
                                            <p>Try another year, standard or date.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="7">Total ({{ $rows->count() }} receipts)</td>
                                    <td class="text-end">{{ number_format((float) $rows->sum('total'), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $rows->sum('less_amount'), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $rows->sum('net_amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            $('#exportExcelBtn').on('click', function (e) {
                e.preventDefault();
                window.location.href = "{{ route($route . '.less-report-export') }}";
            });
            $('#print_btn').on('click', function (e) {
                e.preventDefault();
                window.open("{{ route($route . '.less-report-print') }}", '_blank');
            });
            if (typeof flatpickr !== 'undefined') {
                ['from_date', 'to_date'].forEach(function (id) {
                    flatpickr('#' + id, {
                        dateFormat: 'd-m-Y',
                        allowInput: true,
                        onChange: function (dates, str, inst) {
                            $('#' + id + '_value').val(dates[0] ? inst.formatDate(dates[0], 'Y-m-d') : '');
                        }
                    });
                });
            }
        });
    </script>
@endsection
