@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Timetable';
    $route = $modules['route'] ?? 'timetable';
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => true,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-2">
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <form id="timetableFilterForm">
                        <div class="row align-items-end">
                            <div class="col-md-4 col-sm-12 mb-2">
                                <label class="form-label fw-semibold mb-1">Standard</label>
                                <select id="filter_standard_id" name="standard_id" class="form-control select2 select_filter">
                                    <option value="">All Standards</option>
                                    @foreach ($standards ?? [] as $std)
                                        <option value="{{ $std->id }}">{{ $std->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-2">
                                <button type="button" id="reset_filter_btn" class="btn btn-secondary w-100">
                                    <i class="fa-solid fa-rotate-right me-1"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalLabel"><i class="fa-solid fa-users me-2"></i>Standard Students</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="student-modal-loader" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="student-modal-content" class="table-responsive" style="display: none;">
                        <table class="table table-bordered table-striped" id="students-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>GR No</th>
                                    <th>Student Name</th>
                                    <th>Mobile No</th>
                                </tr>
                            </thead>
                            <tbody id="student-table-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dtable = null;
        $(document).ready(function () {
            if ($.fn.select2) {
                $('.select2').select2({ width: '100%', allowClear: true, placeholder: 'Select' });
            }

            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function (data) {
                        data.standard_id = $('#filter_standard_id').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: { searchPlaceholder: 'Search standard...' },
            });

            $(document).on('change', '.select_filter', function () {
                dtable.draw();
            });

            $('#reset_filter_btn').on('click', function () {
                $('#filter_standard_id').val('').trigger('change');
                dtable.draw();
            });

            $(document).on('click', '.view-students-btn', function () {
                var timetableId = $(this).data('id');
                $('#studentModal').modal('show');
                $('#student-modal-loader').show();
                $('#student-modal-content').hide();

                $.ajax({
                    url: "{{ route('timetable.students', ':id') }}".replace(':id', timetableId),
                    type: "GET",
                    success: function (response) {
                        $('#student-modal-loader').hide();
                        $('#student-modal-content').show();
                        var tbody = $('#student-table-body');
                        tbody.empty();

                        if (response.students && response.students.length > 0) {
                            $.each(response.students, function (index, student) {
                                tbody.append(
                                    '<tr>' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td>' + (student.gr_no || '-') + '</td>' +
                                    '<td>' + (student.name || '-') + '</td>' +
                                    '<td>' + (student.mobile || '-') + '</td>' +
                                    '</tr>'
                                );
                            });
                        } else {
                            tbody.append('<tr><td colspan="4" class="text-center">No students found for this standard.</td></tr>');
                        }
                    },
                    error: function () {
                        $('#student-modal-loader').hide();
                        $('#student-modal-content').show();
                        $('#student-table-body').html('<tr><td colspan="4" class="text-center text-danger">Failed to fetch students.</td></tr>');
                    }
                });
            });
        });
    </script>

    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
@endsection
