@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Leaving Certi. Entry';
    $route = $modules['route'] ?? 'leaving-certificate-entry';
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => !empty($modules['permission_add']),
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => false,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>
@endsection

@section('content')
    <div class="mst my-2">
        <div class="mst-hero">
            <div class="mst-hero-l">
                <div class="mst-hero-icon"><i class="ti ti-certificate"></i></div>
                <div>
                    <h4>Leaving Register</h4>
                    <p>Students leaving certificate register list.</p>
                </div>
            </div>
        </div>

        <div class="card mst-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover w-100 mst-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">GR No.</th>
                                <th>Student Name</th>
                                <th class="text-center">Std</th>
                                <th class="text-center">Div</th>
                                <th class="text-center">Leaving Date</th>
                                <th>Leaving Reason</th>
                                <th class="text-center">LC No.</th>
                                <th class="text-center">Late Fee</th>
                                <th>Note</th>
                                <th class="text-center">Principal Sign</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $row)
                                <tr>
                                    <td class="text-center fw-semibold">{{ $row->gr_no ?: '-' }}</td>
                                    <td class="text-uppercase">{{ $row->student_name ?: '-' }}</td>
                                    <td class="text-center">{{ $row->last_std ?: '-' }}</td>
                                    <td class="text-center">{{ $row->last_division ?: '-' }}</td>
                                    <td class="text-center">{{ $row->formatDate('leave_date') ?: '-' }}</td>
                                    <td>{{ $row->leave_reason ?: '-' }}</td>
                                    <td class="text-center">{{ $row->leaving_certi_no ?: '-' }}</td>
                                    <td class="text-center">{{ $row->fee !== null ? number_format((float) $row->fee, 0) : '0' }}</td>
                                    <td>{{ $row->note ?: '' }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 justify-content-center">
                                            @if (!empty($modules['permission_edit']))
                                            <a href="{{ route($route . '.edit', $row->id) }}" class="btn btn-info btn-icon btn-sm" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route($route . '.print', $row->id) }}" target="_blank" class="btn btn-primary btn-icon btn-sm" title="Print">
                                                <i class="ti ti-printer"></i>
                                            </a>
                                            @if (!empty($modules['permission_delete']))
                                            <a href="javascript:void(0)"
                                               data-id="{{ $row->id }}"
                                               data-did="{{ route($route . '.destroy', $row->id) }}"
                                               class="btn btn-danger btn-icon btn-sm deletebutton"
                                               title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No data available in table</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($entries instanceof \Illuminate\Pagination\LengthAwarePaginator && $entries->hasPages())
                    <div class="mt-3">{{ $entries->withQueryString()->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            $('#print_btn').on('click', function (e) {
                e.preventDefault();
                window.open("{{ route($route . '.print-list') }}", '_blank');
            });
        });

        $(document).on('click', '.deletebutton', function () {
            var did = $(this).attr('data-did');
            var _token = '{{ csrf_token() }}';

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this data!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-success'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (!result.isConfirmed) {
                    toastr.info('Your data is safe 🙂');
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: did,
                    data: { _token: _token, _method: 'DELETE' },
                    success: function (res) {
                        if (res.success) {
                            toastr.success(res.message);
                            setTimeout(function () { window.location.reload(); }, 600);
                        } else {
                            toastr.error(res.message || 'Delete failed.');
                        }
                    },
                    error: function (xhr) {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong, deletion failed.');
                    }
                });
            });
        });
    </script>
@endsection
