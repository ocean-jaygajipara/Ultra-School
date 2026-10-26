@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Day to Day Fees';
    $route = $modules['route'] ?? 'fee-receipt-entry';
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .crx { --crx-ink:#1f2580; --crx-line:#e1e4f2; color:var(--crx-ink); }
        .crx-panel { background:#fff; border:1px solid var(--crx-line); border-radius:18px; box-shadow:0 10px 30px -12px rgba(15,23,42,.12); overflow:hidden; }
        .crx-banner { background:linear-gradient(135deg,#171c6b 0%,#2e3a9e 100%); color:#fff; padding:22px 26px; }
        .crx-banner h4 { color:#fff; margin:0; font-weight:700; }
        .crx-banner p { color:#d6e6f7; margin:4px 0 0; }
        .crx-body { padding:22px 26px; }
        .crx-stats { display:flex; flex-wrap:wrap; gap:10px; }
        .crx-stat { background:rgba(255,255,255,.1); border:1px solid rgba(191,227,247,.35); border-radius:12px; padding:8px 14px; min-width:110px; }
        .crx-stat small { display:block; color:#bfe3f7; font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; }
        .crx-stat strong { color:#fff; font-size:1.05rem; }
        .crx-stat.is-accent { background:#e1141c; border-color:#e1141c; }
        .crx-btn { height:42px; padding:0 16px; border-radius:12px; font-weight:600; display:inline-flex; align-items:center; gap:6px; text-decoration:none; background:#fff; color:#1f2580; }
        .crx-table { width:100%; border-collapse:separate; border-spacing:0; margin:0; }
        .crx-table thead th { background:#1f2580 !important; color:#fff !important; font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; padding:14px 16px; border:0; }
        .crx-table thead th:first-child { border-top-left-radius:13px; }
        .crx-table thead th:last-child { border-top-right-radius:13px; }
        .crx-sheet { border:1px solid var(--crx-line); border-radius:14px; overflow:hidden; }
        .crx-table td { padding:12px 16px; border-bottom:1px solid #eef0f8; font-size:.9rem; vertical-align:middle; }
        .crx-day td { background:#d7e8ff; color:#1f2580; font-weight:800; text-align:center; letter-spacing:.03em; font-size:.95rem; padding:14px 16px; }
        .crx-sub td { background:#f7f8fd; font-weight:700; color:#1f2580; }
        .crx-name { font-weight:600; }
        .crx-pages { display:flex; justify-content:flex-end; margin-top:16px; }
        .crx-pages .pagination { margin:0; gap:6px; }
        .crx-pages .page-link { border-radius:10px; color:#1f2580; border-color:#e1e4f2; }
        .crx-pages .page-item.active .page-link { background:#1f2580; border-color:#1f2580; color:#fff; }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => 'Fee Entry', 'url' => route('fee-receipt-entry.index')], ['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="crx my-3">
        <div class="crx-panel">
            <div class="crx-banner">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">Date wise</p>
                        <h4>Day to Day Fees</h4>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="crx-stats">
                            <div class="crx-stat"><small>Receipts</small><strong>{{ $totals->receipts }}</strong></div>
                            <div class="crx-stat"><small>Total</small><strong>{{ number_format((float) $totals->total, 2) }}</strong></div>
                            <div class="crx-stat is-accent"><small>Net</small><strong>{{ number_format((float) $totals->net_amount, 2) }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="crx-body">
                <div class="crx-sheet">
                    <table class="crx-table">
                        <thead>
                            <tr>
                                <th>Receipt No</th>
                                <th>GR No</th>
                                <th>Student</th>
                                <th>Std</th>
                                <th>Term</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Less</th>
                                <th class="text-end">Net Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($days as $dayKey => $dayRows)
                                <tr class="crx-day">
                                    <td colspan="8">{{ \Carbon\Carbon::parse($dayKey)->format('d-m-Y') }}</td>
                                </tr>
                                @foreach ($dayRows as $row)
                                    <tr>
                                        <td>{{ $row->receipt_no ?: '—' }}</td>
                                        <td>{{ $row->gr_no ?: '—' }}</td>
                                        <td><span class="crx-name">{{ $row->student_name }}</span></td>
                                        <td>{{ $row->standard->name ?? '—' }}{{ $row->division ? ' - ' . $row->division : '' }}</td>
                                        <td>{{ $row->term }}</td>
                                        <td class="text-end">{{ number_format((float) $row->total, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $row->less_amount, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $row->net_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="crx-sub">
                                    <td colspan="5">Day total</td>
                                    <td class="text-end">{{ number_format((float) $dayRows->sum('total'), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $dayRows->sum('less_amount'), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $dayRows->sum('net_amount'), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center" style="padding:40px;color:#5f6488;">No fee receipt.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="crx-pages">
                    {{ $datePage->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            $('#exportExcelBtn').on('click', function (e) {
                e.preventDefault();
                window.location.href = "{{ route($route . '.day-report-export') }}";
            });
            $('#print_btn').on('click', function (e) {
                e.preventDefault();
                window.open("{{ route($route . '.day-report-print') }}", '_blank');
            });
        });
    </script>
@endsection
