@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Name Change Form';
    $route = $modules['route'] ?? 'name-change-form';
@endphp
@section('title', $page_title)

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => true,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>
@endsection

@section('content')
    <div class="mst my-2">
        <div class="mst-hero">
            <div class="mst-hero-l">
                <div class="mst-hero-icon"><i class="ti ti-forms"></i></div>
                <div>
                    <h4>Name Change Form</h4>
                    <p>US School — create, edit and print S.S.C. Subject Selection form.</p>
                </div>
            </div>
        </div>

        <div class="card mst-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route($route . '.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search (GR / Name / Mobile)</label>
                        <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="GR No. / Name">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Show</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mst-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover w-100 mst-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">Sr.</th>
                                <th class="text-center">GR No.</th>
                                <th>Student Name</th>
                                <th class="text-center">Std</th>
                                <th class="text-center">Class</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $i => $row)
                                <tr>
                                    <td class="text-center">{{ $entries->firstItem() + $i }}</td>
                                    <td class="text-center fw-semibold">{{ $row->gr_no ?: '-' }}</td>
                                    <td class="text-uppercase">{{ $row->student_name ?: '-' }}</td>
                                    <td class="text-center">{{ $row->std ?: '-' }}</td>
                                    <td class="text-center">{{ $row->class ?: '-' }}</td>
                                    <td class="text-center">{{ $row->formatDate('form_date') ?: '-' }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 justify-content-center">
                                            <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-info btn-icon btn-sm" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <a href="{{ route($route . '.print', $row->id) }}" target="_blank" class="btn btn-primary btn-icon btn-sm" title="Print">
                                                <i class="ti ti-printer"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-icon btn-sm btn-delete" data-id="{{ $row->id }}" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No records found. Click Add to create.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($entries->hasPages())
                    <div class="mt-3">{{ $entries->withQueryString()->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script>
        $(function () {
            $(document).on('click', '.btn-delete', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this Name Change Form?')) return;
                $.ajax({
                    url: @json(url('name-change-form')) + '/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: @json(csrf_token()) },
                    success: function (res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            alert(res.message || 'Delete failed');
                        }
                    },
                    error: function (xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed');
                    }
                });
            });
        });
    </script>
@endsection
