@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
    $exportColumns = $availableExportColumns ?? [];
    $defaultColumns = $defaultExportColumns ?? array_keys($exportColumns);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <style>
        .brv-village { display: inline-flex; align-items: center; gap: 8px; }
        .brv-village i {
            width: 32px; height: 32px; border-radius: 10px;
            background: #eef7fd; color: #1f2580;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .brv-charge {
            display: inline-block; background: #fff8e6; color: #9a6b00;
            border: 1px solid #f5d98a; border-radius: 8px; padding: 4px 10px; font-weight: 700;
        }
        .brv-actions { display: inline-flex; gap: 6px; }
        .brv-actions .btn {
            width: 34px; height: 34px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center; border-radius: 10px;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => isset($modules) && isset($modules['permission_add']) ? $modules['permission_add'] : false,
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
                <div class="mst-hero-icon"><i class="ti ti-bus"></i></div>
                <div>
                    <h4>{{ $page_title }}</h4>
                    <p>Manage bus stop villages, route numbers and term fees.</p>
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

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="exportOptionsForm">
                    <div class="modal-body">
                        <input type="hidden" id="export_action" value="excel">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data Scope</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeFiltered"
                                    value="filtered" checked>
                                <label class="form-check-label" for="scopeFiltered">Use current filters / search</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeAll" value="all">
                                <label class="form-check-label" for="scopeAll">Export complete {{ $page_title }} list</label>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Columns</label>
                                <div class="ms-auto d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-light border" id="selectAllColumns">Select all</button>
                                    <button type="button" class="btn btn-sm btn-light border" id="clearAllColumns">Clear all</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="resetDefaultColumns">Reset to default</button>
                                </div>
                            </div>
                            <p class="text-muted mb-2">Choose the columns that should appear in the export/print output.</p>
                            <div class="row" style="max-height: 260px; overflow-y: auto;">
                                @forelse ($exportColumns as $columnKey => $columnLabel)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input export-column" type="checkbox"
                                                value="{{ $columnKey }}" id="export_column_{{ $columnKey }}"
                                                {{ in_array($columnKey, $defaultColumns) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="export_column_{{ $columnKey }}">
                                                {{ $columnLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12"><p class="text-muted mb-0">No exportable columns configured.</p></div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
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
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                order: [[0, 'asc']],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    beforeSend: function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                columnDefs: [
                    { targets: 0, width: '24%' },
                    { targets: 1, width: '16%' },
                    { targets: 2, width: '14%', className: 'text-end' },
                    { targets: 3, width: '14%', className: 'text-end' },
                    { targets: 4, width: '14%', className: 'text-center' },
                    { targets: 5, width: '18%', className: 'text-center', orderable: false, searchable: false },
                ],
                language: { searchPlaceholder: 'Search village...' },
            });
        });

        $('input[name="search"]').keyup(function() { dtable.draw(); });
        jQuery(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });
    </script>
    <script type="text/javascript">
        $(function() {
            const defaultColumns = @json(array_values($defaultColumns));
            const exportModalElement = document.getElementById('exportModal');
            let exportModalInstance = null;

            if (exportModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                exportModalInstance = new bootstrap.Modal(exportModalElement);
            }

            const showExportModal = () => exportModalInstance ? exportModalInstance.show() : $('#exportModal').modal('show');
            const hideExportModal = () => exportModalInstance ? exportModalInstance.hide() : $('#exportModal').modal('hide');

            function openExportModal(actionType) {
                $('#export_action').val(actionType);
                $('#exportModalLabel').text(actionType === 'print' ? 'Print Preview' : 'Export to Excel');
                showExportModal();
            }

            $('#exportExcelBtn').on('click', function(e) { e.preventDefault(); openExportModal('excel'); });
            $('#print_btn').on('click', function(e) { e.preventDefault(); openExportModal('print'); });
            $('#selectAllColumns').on('click', function() { $('.export-column').prop('checked', true); });
            $('#clearAllColumns').on('click', function() { $('.export-column').prop('checked', false); });
            $('#resetDefaultColumns').on('click', function() {
                $('.export-column').each(function() {
                    $(this).prop('checked', defaultColumns.includes($(this).val()));
                });
            });

            $('#exportOptionsForm').on('submit', function(e) {
                e.preventDefault();
                const selectedColumns = $('.export-column:checked').map(function() { return $(this).val(); }).get();
                if (!selectedColumns.length) {
                    toastr.warning('Please select at least one column to continue.');
                    return;
                }

                const scope = $('input[name="export_scope"]:checked').val() || 'filtered';
                const params = new URLSearchParams();
                params.append('scope', scope);
                if (scope === 'filtered') {
                    const searchValue = $('#yajra-datatables_filter input[type="search"]').val();
                    if (searchValue) params.append('search', searchValue);
                }
                selectedColumns.forEach(column => params.append('columns[]', column));

                const actionType = $('#export_action').val();
                const baseUrl = actionType === 'print'
                    ? "{{ route($route . '.print') }}"
                    : "{{ route($route . '.export-excel') }}";
                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

                if (actionType === 'print') window.open(finalUrl, '_blank');
                else window.location.href = finalUrl;

                hideExportModal();
            });
        });
    </script>

    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
