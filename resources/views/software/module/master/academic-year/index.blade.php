@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? null;
    $route = $modules['route'] ?? null;
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
            'show_add_btn' => $modules['permission_add'] ?? false,
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
                <div class="mst-hero-icon"><i class="ti ti-calendar"></i></div>
                <div>
                    <h4>{{ $page_title }}</h4>
                    <p>Manage academic years. Only one can be Current.</p>
                </div>
            </div>
        </div>

        <div class="mst-note">
            <i class="ti ti-info-circle me-1"></i>
            The <b>Current</b> academic year is used for admissions, updates, Class History and Roll Number List.
            Only one year can be current at a time.
        </div>

        <div class="card mst-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="yajra-datatables" class="table table-hover w-100 mst-table mb-0">
                        <thead>
                            <tr>
                                @foreach ($columns as $item)
                                    <th class="{{ $item?->className ?? '' }}">{{ $item->td_label }}</th>
                                @endforeach
                            </tr>
                        </thead>
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
    <script type="text/javascript">
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                },
                columns: {!! json_encode($columns) !!},
                language: { searchPlaceholder: 'Search...' },
            });

            $(document).on('click', '.set-current', function() {
                var url = $(this).data('url');
                var name = $(this).data('name');
                Swal.fire({
                    title: 'Set ' + name + ' as current?',
                    text: 'New admissions and student updates will be saved in this academic year.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, set current',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.post(url, { _token: '{{ csrf_token() }}' }).done(function(res) {
                        toastr.success(res.message || 'Updated');
                        dtable.ajax.reload(null, false);
                    }).fail(function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                    });
                });
            });
        });
    </script>

    @include('software.includes.script-delete-record')
@endsection
