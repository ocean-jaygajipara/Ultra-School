@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Fee Receipt Entry';
    $route = $modules['route'] ?? 'fee-receipt-entry';
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
            'show_add_btn' => isset($modules['permission_add']) ? $modules['permission_add'] : false,
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
                <div class="mst-hero-icon"><i class="ti ti-receipt"></i></div>
                <div>
                    <h4>{{ $page_title }}</h4>
                    <p>School fee receipts by standard, term and student.</p>
                </div>
            </div>
        </div>

        <div class="card mst-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="yajra-datatables" class="table table-hover w-100 mst-table mb-0">
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
                window.location.href = "{{ route($route . '.export-excel') }}";
            });
            $('#print_btn').on('click', function (e) {
                e.preventDefault();
                window.open("{{ route($route . '.print-list') }}", '_blank');
            });
            $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: 'GET',
                    beforeSend: function (request) {
                        request.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: { searchPlaceholder: 'Search...' }
            });
        });
    </script>
    @include('software.includes.script-delete-record')
@endsection
