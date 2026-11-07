@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'New Student List';
    $route = $modules['route'] ?? 'new-student-list';
@endphp
@section('title', $page_title)

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>
@endsection

@section('content')
    <div class="mst my-2">
        <div class="mst-hero">
            <div class="mst-hero-l">
                <div class="mst-hero-icon"><i class="ti ti-user-plus"></i></div>
                <div>
                    <h4>New Student List</h4>
                    <p>Select month range and view new admission students.</p>
                </div>
            </div>
        </div>

        <div class="card mst-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route($route . '.index') }}" id="newStudentFilterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="month" name="from_ym" id="from_ym" class="form-control" value="{{ $filters['from_ym'] ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="month" name="to_ym" id="to_ym" class="form-control" value="{{ $filters['to_ym'] ?? '' }}" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Show</button>
                    </div>
                </form>
            </div>
        </div>

        @if (!empty($submitted))
            <div class="card mst-card">
                <div class="card-body">
                    <div class="text-muted small mb-2">
                        Range: <strong>{{ $rangeLabel }}</strong>
                        &nbsp;|&nbsp; Total: <strong>{{ $students->count() }}</strong>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover w-100 mst-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center">Sr.No.</th>
                                    <th class="text-center">GR No.</th>
                                    <th>Name</th>
                                    <th class="text-center">Adm. Date</th>
                                    <th>Note</th>
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
                                    <tr>
                                        <td colspan="5" class="fw-semibold">
                                            Standard : {{ $stdName }} &nbsp;&nbsp; Class : {{ $clsName }}
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
                                            <td class="text-center">{{ $sr }}</td>
                                            <td class="text-center fw-semibold">{{ $row->gr_no ?: '-' }}</td>
                                            <td class="text-uppercase">{{ $name ?: '-' }}</td>
                                            <td class="text-center">{{ $admDate }}</td>
                                            <td>{{ $note }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data available in table</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            function filterQs() {
                return $('#newStudentFilterForm').serialize();
            }

            $('#print_btn').on('click', function (e) {
                e.preventDefault();
                if (!$('#from_ym').val() || !$('#to_ym').val()) {
                    toastr.error('Please select From and To month.');
                    return;
                }
                window.open("{{ route($route . '.print') }}?" + filterQs(), '_blank');
            });

            $('#exportExcelBtn').on('click', function (e) {
                e.preventDefault();
                if (!$('#from_ym').val() || !$('#to_ym').val()) {
                    toastr.error('Please select From and To month.');
                    return;
                }
                window.location.href = "{{ route($route . '.export-excel') }}?" + filterQs();
            });
        });
    </script>
@endsection
