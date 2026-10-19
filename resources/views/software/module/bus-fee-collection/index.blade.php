@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Bus Transport Fee';
    $route = $modules['route'] ?? 'bus-fee-collection';
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
            'page_title' => $page_title,
            'route' => $route,
            'show_add_btn' => $modules['permission_add'] ?? false,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="yajra-datatables" class="table table-hover w-100">
                        @if (isset($columns))
                            <thead>
                                <tr>
                                    @foreach ($columns as $item)
                                        <th class="{{ $item->className ?? '' }}">{{ $item->td_label ?? $item->name }}</th>
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
        $(function() {
            $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route($route . '.index') }}",
                columns: [
                    @foreach ($columns as $item)
                        { data: '{{ $item->data }}', name: '{{ $item->name }}', orderable: {{ isset($item->orderable) && !$item->orderable ? 'false' : 'true' }}, searchable: {{ isset($item->searchable) && !$item->searchable ? 'false' : 'true' }} },
                    @endforeach
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
