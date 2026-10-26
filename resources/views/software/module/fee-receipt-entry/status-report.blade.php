@extends('software.layout.app')

@php
    $page_title = $meta['title'] ?? 'Fee Report';
    $route = $modules['route'] ?? 'fee-receipt-entry';
    $kind = $meta['kind'] ?? 'paid';
    $yearName = '—';
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
    $termName = match ($filters['term'] ?? '') {
        'FIRST' => 'First Term',
        'SECOND' => 'Second Term',
        default => 'Both Terms',
    };
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <style>
        .crx {
            --crx-ink: #1f2580;
            --crx-ink-2: #2e3a9e;
            --crx-muted: #5f6488;
            --crx-line: #e1e4f2;
            --crx-soft: #f7f8fd;
            --crx-accent: #e1141c;
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
        .crx-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .crx-btn {
            height: 46px; padding: 0 18px; border-radius: 12px;
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; border: 1.5px solid transparent;
        }
        .crx-btn-go { background: var(--crx-ink); color: #fff; border-color: var(--crx-ink); }
        .crx-btn-go:hover { background: #171c6b; color: #fff; }
        .crx-btn-print { background: #fff; color: var(--crx-ink); border-color: var(--crx-line); }
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
            background: #1f2580 !important; color: #fff !important;
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
        .crx-amt { font-weight: 700; }
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
        .crx .dataTables_wrapper .dataTables_length,
        .crx .dataTables_wrapper .dataTables_filter,
        .crx .dataTables_wrapper .dataTables_info,
        .crx .dataTables_wrapper .dataTables_paginate { margin-top: 12px; }
        .crx .dataTables_wrapper .dataTables_filter input,
        .crx .dataTables_wrapper .dataTables_length select {
            border: 1.5px solid var(--crx-line);
            border-radius: 10px;
            padding: 6px 10px;
            color: var(--crx-ink);
        }
        .crx #yajra-datatables thead th,
        .crx #yajra-datatables.dataTable thead > tr > th,
        .crx table.dataTable.mst-table thead th {
            background-color: #1f2580 !important;
            color: #fff !important;
            font-weight: 700;
            border-color: #1f2580 !important;
        }
        .crx table.dataTable tfoot th {
            background: var(--crx-sky-soft);
            color: var(--crx-ink);
            font-weight: 800;
            border-top: 1px solid var(--crx-sky);
        }
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
        <form method="POST" action="{{ route($route . '.' . $kind . '-report') }}" class="mb-4">
            @csrf
            <div class="crx-panel">
                <div class="crx-banner d-flex align-items-center gap-3">
                    <span class="crx-badge-icon"><i class="ti {{ $meta['icon'] }}"></i></span>
                    <div>
                        <h3>{{ $page_title }}</h3>
                        <p>{{ $meta['subtitle'] }}</p>
                    </div>
                </div>
                <div class="crx-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="crx-label" for="academic_year_id">Academic Year</label>
                            <select id="academic_year_id" name="academic_year_id" class="form-control select2">
                                @foreach ($academicYears as $yearItem)
                                    <option value="{{ $yearItem->id }}" {{ (string) ($filters['academic_year_id'] ?? '') === (string) $yearItem->id ? 'selected' : '' }}>
                                        {{ $yearItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
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
                            <label class="crx-label" for="term">Term</label>
                            <select id="term" name="term" class="form-control select2">
                                <option value="" {{ ($filters['term'] ?? '') === '' ? 'selected' : '' }}>Both Terms</option>
                                <option value="FIRST" {{ ($filters['term'] ?? '') === 'FIRST' ? 'selected' : '' }}>First Term</option>
                                <option value="SECOND" {{ ($filters['term'] ?? '') === 'SECOND' ? 'selected' : '' }}>Second Term</option>
                            </select>
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
                            <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">{{ $page_title }}</p>
                            <h4>{{ $yearName }} · {{ $stdName }} · {{ $termName }}</h4>
                        </div>
                    </div>
                    {{-- Stats hidden as requested --}}
                </div>
            </div>
            <div class="crx-body">
                <div class="table-responsive">
                    <table id="yajra-datatables" class="table table-hover w-100 mst-table mb-0">
                        <thead>
                            <tr>
                                @foreach ($columns as $item)
                                    <th class="{{ $item->className ?? '' }}">{{ $item->td_label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                @foreach ($columns as $item)
                                    <th class="{{ $item->className ?? '' }}">{{ $loop->first ? 'Total' : '' }}</th>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            $('#exportExcelBtn').on('click', function (e) {
                e.preventDefault();
                window.location.href = "{{ route($route . '.' . $kind . '-report-export') }}";
            });
            $('#print_btn').on('click', function (e) {
                e.preventDefault();
                window.open("{{ route($route . '.' . $kind . '-report-print') }}", '_blank');
            });

            var table = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [],
                ajax: {
                    url: "{{ route($route . '.' . $kind . '-report') }}",
                    type: 'GET',
                    beforeSend: function (request) {
                        request.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: { searchPlaceholder: 'Search...' },
                drawCallback: function () {
                    var json = this.api().ajax.json();
                    if (json && json.total_amount_text) {
                        $('#yajra-datatables tfoot th:last').text(json.total_amount_text);
                    }

                    @if ($kind === 'unpaid')
                    // Merge Sr, GR No, Roll, Student, Std, Division for the same student
                    var tableBody = $('#yajra-datatables tbody');
                    var mergeCols = [0, 1, 2, 3, 4, 5]; // Sr, GR No, Roll, Student, Std, Division

                    mergeCols.forEach(function (colIdx) {
                        var prevCell = null;
                        var prevKey = null;
                        var rowspan = 1;

                        tableBody.find('tr').each(function () {
                            var row = $(this);
                            var cell = row.find('td').eq(colIdx);
                            // Student identification key from GR No (col 1) and Student Name (col 3)
                            var studentKey = row.find('td').eq(1).text().trim() + '||' + row.find('td').eq(3).text().trim();

                            if (prevCell && prevKey === studentKey && studentKey !== '' && studentKey !== '—||—') {
                                rowspan++;
                                prevCell.attr('rowspan', rowspan);
                                prevCell.css('vertical-align', 'middle');
                                cell.hide();
                            } else {
                                prevCell = cell;
                                prevKey = studentKey;
                                rowspan = 1;
                                cell.show();
                                cell.removeAttr('rowspan');
                            }
                        });
                    });
                    @endif
                }
            });
        });
    </script>
@endsection
