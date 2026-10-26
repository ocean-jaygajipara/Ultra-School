@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Bus Route Wise Students';
    $route = $modules['route'] ?? 'bus-route-report';
    $studentCount = $groups->flatten(1)->count();
    $controller = app(\App\Http\Controllers\admin\BusRouteStudentReportController::class);
    $selectedColumns = $selectedColumns ?? $controller->selectedColumns($filters);
    $blankHeaders = $blankHeaders ?? $controller->blankColumnHeaders($filters);
    $tableColCount = count($selectedColumns) + count($blankHeaders);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .crx { --crx-ink:#1f2580; --crx-ink-2:#2e3a9e; --crx-line:#e1e4f2; --crx-soft:#f7f8fd; --crx-muted:#5f6488; --crx-sky:#bfe3f7; --crx-sky-soft:#eef7fd; color:var(--crx-ink); }
        .crx-panel { background:#fff; border:1px solid var(--crx-line); border-radius:18px; box-shadow:0 10px 30px -12px rgba(15,23,42,.12); margin-bottom:16px; }
        .crx-banner { background:linear-gradient(135deg,#171c6b 0%,#2e3a9e 100%); color:#fff; padding:22px 26px; border-radius:17px 17px 0 0; }
        .crx-banner h3,.crx-banner h4 { color:#fff; margin:0; font-weight:700; }
        .crx-banner p { color:#d6e6f7; margin:4px 0 0; }
        .crx-body { padding:22px 26px; }
        .crx-label { display:block; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--crx-muted); margin-bottom:8px; }
        .crx .select2-container { width:100% !important; }
        .crx-btn { height:46px; padding:0 18px; border-radius:12px; font-weight:600; display:inline-flex; align-items:center; gap:6px; text-decoration:none; border:1.5px solid transparent; cursor:pointer; }
        .crx-btn-go { background:var(--crx-ink); color:#fff; }
        .crx-btn-go:hover { background:var(--crx-ink-2); color:#fff; }
        .crx-btn-print { background:#fff; color:var(--crx-ink); border-color:var(--crx-line); }
        .crx-btn-print:hover { background:var(--crx-soft); color:var(--crx-ink); }
        .crx-stats { display:flex; gap:10px; flex-wrap:wrap; }
        .crx-stat { background:rgba(255,255,255,.1); border:1px solid rgba(191,227,247,.35); border-radius:12px; padding:8px 14px; min-width:110px; }
        .crx-stat small { display:block; color:#bfe3f7; font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; }
        .crx-stat strong { color:#fff; }
        .crx-stat.is-accent { background:#e1141c; border-color:#e1141c; }
        .crx-table-wrap { border:1px solid var(--crx-line); border-radius:14px; overflow:auto; max-height:68vh; }
        .crx-table { width:100%; border-collapse:separate; border-spacing:0; margin:0; }
        .crx-table thead th { position:sticky; top:0; z-index:2; background:#1f2580 !important; color:#fff !important; font-size:.72rem; letter-spacing:.04em; text-transform:uppercase; padding:14px 16px; border:0; white-space:nowrap; }
        .crx-table thead th:first-child { border-top-left-radius:13px; }
        .crx-table thead th:last-child { border-top-right-radius:13px; }
        .crx-table thead th.extra-col { background:#e1141c !important; color:#fff !important; text-align:center; }
        .crx-table td { padding:12px 16px; border-bottom:1px solid #eef0f8; vertical-align:middle; font-size:.9rem; }
        .crx-table td.extra-col { border-left:1px dashed #f7c3c5; background:#fffafa; }
        .crx-route td { background:#d7e8ff; color:#1f2580; font-weight:800; text-align:center; letter-spacing:.03em; }
        .crx-total td { background:#1f2580; color:#fff !important; font-weight:800; }
        .crx-name { font-weight:600; color:var(--crx-ink); }
        .text-center { text-align:center; }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => 'Report', 'url' => ''], ['title' => $page_title, 'url' => '']],
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
        <form method="POST" action="{{ route($route . '.index') }}" class="crx-panel" id="busRouteFilterForm">
            @csrf
            <div class="crx-banner">
                <h3>Bus Route Wise Students</h3>
                <p>Student list grouped by bus route, with a total for each route.</p>
            </div>
            <div class="crx-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="crx-label">Bus Route</label>
                        <select name="route" id="bus_route_filter" class="form-control select2">
                            <option value="">All Routes</option>
                            @foreach ($routes as $routeName)
                                <option value="{{ $routeName }}" @selected($selected === $routeName)>{{ $routeName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="crx-btn crx-btn-go">
                            <i class="ti ti-search"></i> Show
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="crx-panel">
            <div class="crx-banner">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">Student list</p>
                        <h4>{{ $selected !== '' ? $selected : 'All Routes' }}</h4>
                    </div>
                    <div class="crx-stats">
                        <div class="crx-stat"><small>Routes</small><strong>{{ $groups->count() }}</strong></div>
                        <div class="crx-stat is-accent"><small>Students</small><strong>{{ $studentCount }}</strong></div>
                    </div>
                </div>
            </div>
            <div class="crx-body">
                <div class="crx-table-wrap">
                    <table class="crx-table">
                        <thead>
                            <tr>
                                @foreach ($selectedColumns as $key => $label)
                                    <th class="{{ in_array($key, ['sr_no', 'roll_no', 'gr_no', 'standard', 'division', 'mobile_no'], true) ? 'text-center' : '' }}">
                                        {{ $label }}
                                    </th>
                                @endforeach
                                @foreach ($blankHeaders as $heading)
                                    <th class="extra-col" style="min-width:110px">{{ $heading ?: 'Column' }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $routeName => $students)
                                <tr class="crx-route">
                                    <td colspan="{{ max(1, $tableColCount) }}">{{ $routeName }} · {{ $students->count() }} student{{ $students->count() === 1 ? '' : 's' }}</td>
                                </tr>
                                @foreach ($students as $student)
                                    <tr>
                                        @foreach ($selectedColumns as $key => $label)
                                            <td class="{{ in_array($key, ['sr_no', 'roll_no', 'gr_no', 'standard', 'division', 'mobile_no'], true) ? 'text-center' : '' }}">
                                                @if ($key === 'student_name')
                                                    <span class="crx-name">{{ $controller->columnValue($student, $key, $loop->iteration) }}</span>
                                                @else
                                                    {{ $controller->columnValue($student, $key, $loop->iteration) }}
                                                @endif
                                            </td>
                                        @endforeach
                                        @foreach ($blankHeaders as $heading)
                                            <td class="extra-col"></td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="{{ max(1, $tableColCount) }}" class="text-center" style="padding:40px;color:#5f6488;">No student on this route.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($studentCount)
                            <tfoot>
                                <tr class="crx-total">
                                    <td colspan="{{ max(1, $tableColCount) }}">Total students: {{ $studentCount }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $(function() {

            function triggerExportExcel(e) {
                if (e) e.preventDefault();
                var currentRoute = $('#bus_route_filter').val() || '';
                var url = "{{ route($route . '.export-excel') }}" + (currentRoute ? '?route=' + encodeURIComponent(currentRoute) : '');
                window.location.href = url;
            }

            function triggerPrint(e) {
                if (e) e.preventDefault();
                var currentRoute = $('#bus_route_filter').val() || '';
                var url = "{{ route($route . '.print') }}" + (currentRoute ? '?route=' + encodeURIComponent(currentRoute) : '');
                window.open(url, '_blank');
            }

            // Bind breadcrumb export and print buttons
            $('#exportExcelBtn').on('click', triggerExportExcel);
            $('#print_btn').on('click', triggerPrint);
        });
    </script>
@endsection
