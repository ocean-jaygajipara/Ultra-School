@extends('software.layout.app')

@php
    $page_title = 'Registered Student';

    // $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $course_register_add = isset($modules['course-register_add']) && $modules['course-register_add'] ? 'true' : 'false';
    $fees_collection_create =
        isset($modules['fees-collection-create']) && $modules['fees-collection-create'] ? 'true' : 'false';
    $fees_collection_edit =
        isset($modules['fees-collection-edit']) && $modules['fees-collection-edit'] ? 'true' : 'false';
    $exportColumns = $availableExportColumns ?? [];
    $defaultColumns = $defaultExportColumns ?? array_keys($exportColumns);
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">                                                                                                                                        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"> -->

    <style>
        /* Colours from the school logo: navy, red, sky, gold */
        .adm-ui {
            --adm-ink: #1f2580;
            --adm-ink-2: #2e3a9e;
            --adm-muted: #5f6488;
            --adm-line: #e1e4f2;
            --adm-soft: #f7f8fd;
            --adm-red: #e1141c;
            --adm-red-soft: #fff1f1;
            --adm-sky: #bfe3f7;
            --adm-sky-soft: #eef7fd;
            --adm-gold: #f5b400;
        }

        .adm-panel {
            background: #fff;
            border: 1px solid var(--adm-line);
            border-radius: 18px;
            box-shadow: 0 10px 30px -12px rgba(31, 37, 128, .15);
        }

        .adm-hero {
            background:
                radial-gradient(circle at 88% 15%, rgba(245, 180, 0, .35), transparent 42%),
                radial-gradient(circle at 5% 130%, rgba(191, 227, 247, .35), transparent 45%),
                linear-gradient(135deg, #171c6b 0%, var(--adm-ink-2) 100%);
            color: #fff;
            border-radius: 18px;
            padding: 22px 26px;
            margin-bottom: 20px;
        }
        .adm-hero h3 { color: #fff; margin: 0; font-weight: 700; letter-spacing: -.3px; }
        .adm-hero p { color: #d6e6f7; margin: 4px 0 0; font-size: .875rem; }
        .adm-hero-icon {
            width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
            background: rgba(245, 180, 0, .18); border: 1px solid rgba(245, 180, 0, .6);
            color: var(--adm-gold); font-size: 1.5rem;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .adm-stat {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(191, 227, 247, .35);
            border-radius: 12px; padding: 8px 16px; min-width: 104px;
        }
        .adm-stat small { display: block; color: var(--adm-sky); font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; }
        .adm-stat strong { color: #fff; font-size: 1.15rem; }
        .adm-ui .adm-label {
            display: block; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--adm-muted); margin-bottom: 8px;
        }
        .adm-ui #filter_section .form-control,
        .adm-ui #filter_section .select2-container--default .select2-selection--single {
            height: 46px !important;
            border: 1.5px solid var(--adm-line) !important;
            border-radius: 12px !important;
            background: var(--adm-soft) !important;
        }
        .adm-ui #filter_section .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 43px !important; padding-left: 14px !important; color: var(--adm-ink) !important; font-weight: 500;
        }
        .adm-ui #filter_section .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px !important; }
        .adm-ui #filter_section .form-control:focus,
        .adm-ui #filter_section .select2-container--open .select2-selection--single {
            border-color: var(--adm-ink) !important; background: #fff !important;
            box-shadow: 0 0 0 4px rgba(191, 227, 247, .7) !important;
        }
        .adm-btn-clear {
            height: 46px; padding: 0 18px; border-radius: 12px;
            border: 1.5px solid #f7c3c5; background: var(--adm-red-soft); color: var(--adm-red);
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: all .2s;
        }
        .adm-btn-clear:hover { background: var(--adm-red); color: #fff; border-color: var(--adm-red); }

        .adm-ui .adm-table-card { padding: 18px 20px; }
        .adm-ui #yajra-datatables { border-collapse: separate !important; border-spacing: 0; }
        .adm-ui #yajra-datatables thead th {
            background: var(--adm-ink) !important; color: #fff !important;
            font-size: .72rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
            padding: 13px 14px; border: 0 !important; white-space: nowrap;
        }
        .adm-ui #yajra-datatables thead th:first-child { border-top-left-radius: 12px; }
        .adm-ui #yajra-datatables thead th:last-child { border-top-right-radius: 12px; }
        .adm-ui #yajra-datatables tbody td {
            padding: 11px 14px; border-bottom: 1px solid #eef0f8; vertical-align: middle; color: #2b2f55;
        }
        .adm-ui #yajra-datatables.table-striped > tbody > tr:nth-of-type(odd) > * { --bs-table-accent-bg: #fafbff; }
        .adm-ui #yajra-datatables.table-hover > tbody > tr:hover > * { --bs-table-accent-bg: var(--adm-sky-soft); }
        .adm-ui #yajra-datatables thead .sorting:before,
        .adm-ui #yajra-datatables thead .sorting:after,
        .adm-ui #yajra-datatables thead .sorting_asc:before,
        .adm-ui #yajra-datatables thead .sorting_asc:after,
        .adm-ui #yajra-datatables thead .sorting_desc:before,
        .adm-ui #yajra-datatables thead .sorting_desc:after { color: var(--adm-sky); }

        .adm-ui .dataTables_wrapper .dataTables_length select {
            border: 1.5px solid var(--adm-line); border-radius: 10px; background-color: var(--adm-soft);
        }
        .adm-ui .dataTables_wrapper .dataTables_info { color: var(--adm-muted); font-size: .85rem; }
        .adm-ui .dataTables_wrapper .page-item .page-link {
            border-radius: 10px !important; margin: 0 3px; border: 1px solid var(--adm-line);
            color: var(--adm-ink); font-weight: 600;
        }
        .adm-ui .dataTables_wrapper .page-item.active .page-link {
            background: var(--adm-ink) !important; border-color: var(--adm-ink) !important; color: #fff !important;
            box-shadow: 0 4px 10px -4px rgba(31, 37, 128, .6);
        }
        .adm-ui .dataTables_wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
            background: var(--adm-sky-soft); border-color: var(--adm-sky);
        }
        .adm-act {
            display: inline-flex; align-items: center; justify-content: center; gap: 5px;
            height: 32px; padding: 0 14px; border-radius: 10px;
            font-size: .78rem; font-weight: 600; line-height: 1; white-space: nowrap;
            border: 1px solid transparent; cursor: pointer; text-decoration: none;
            transition: transform .15s, box-shadow .15s, background .15s, color .15s;
        }
        .adm-act i { font-size: 1rem; }
        .adm-act:hover { transform: translateY(-1px); box-shadow: 0 6px 14px -6px rgba(31, 37, 128, .45); }
        .adm-act.disabled { opacity: .6; pointer-events: none; }
        .adm-act-icon { width: 34px; padding: 0; }

        .adm-act-view { background: #e8eafb; color: #1f2580; border-color: #cfd3f5; }
        .adm-act-view:hover { background: #1f2580; color: #fff; }

        .adm-act-att { background: #fff6dc; color: #a86b00; border-color: #ffe39a; }
        .adm-act-att:hover { background: #f5b400; color: #fff; }

        .adm-act-test { background: #eef7fd; color: #1565a6; border-color: #bfe3f7; }
        .adm-act-test:hover { background: #1565a6; color: #fff; }

        .adm-act-drive { background: #fff; color: #1f2580; border-color: #e1e4f2; }
        .adm-act-drive i { background: linear-gradient(135deg, #1fa463 0%, #ffd04b 50%, #4688f4 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .adm-act-drive:hover { border-color: #bfe3f7; background: #eef7fd; }

        .adm-act-folder { background: #f7f8fd; color: #5f6488; border: 1.5px dashed #c5c9e6; }
        .adm-act-folder:hover { background: #1f2580; color: #fff; border-style: solid; border-color: #1f2580; }

        .adm-act-book { background: #eef7fd; color: #1f2580; border-color: #bfe3f7; }
        .adm-act-book:hover { background: #2e3a9e; color: #fff; }

        .adm-act-update { background: linear-gradient(135deg, #1f2580, #2e3a9e); color: #fff; }
        .adm-act-update:hover { color: #fff; box-shadow: 0 6px 14px -4px rgba(31, 37, 128, .6); }

        .adm-act-delete { background: #fff1f1; color: #e1141c; border-color: #f7c3c5; }
        .adm-act-delete:hover { background: #e1141c; color: #fff; }

        .adm-act-restore { background: #f7f8fd; color: #1f2580; border-color: #e1e4f2; }
        .adm-act-restore:hover { background: #1f2580; color: #fff; }

        /* Student Info modal */
        .si-modal { border: 0; border-radius: 20px; overflow: hidden; }
        .si-modal .modal-body { position: relative; }
        .si-close {
            position: absolute; top: 16px; right: 16px; z-index: 5;
            background-color: #fff; border-radius: 50%; padding: 10px; opacity: .9;
            box-shadow: 0 4px 12px rgba(31, 37, 128, .2);
        }
        .si-side {
            background:
                radial-gradient(circle at 85% 8%, rgba(245, 180, 0, .35), transparent 40%),
                radial-gradient(circle at 0% 100%, rgba(191, 227, 247, .3), transparent 45%),
                linear-gradient(160deg, #171c6b 0%, #2e3a9e 100%);
            color: #fff; text-align: center; padding: 36px 26px 28px;
        }
        .si-photo-wrap {
            width: 140px; height: 140px; margin: 0 auto 16px; border-radius: 50%;
            padding: 5px; background: linear-gradient(135deg, #f5b400, #ffe08a);
            box-shadow: 0 12px 30px -10px rgba(0, 0, 0, .5);
        }
        .si-photo {
            width: 100%; height: 100%; border-radius: 50%; object-fit: cover;
            border: 4px solid #fff; background: #fff;
        }
        .si-initials {
            display: flex; align-items: center; justify-content: center;
            font-size: 2.6rem; font-weight: 800; color: #1f2580; background: #eef7fd;
        }
        .si-name { color: #fff; font-weight: 800; font-size: 1.25rem; margin: 0 0 4px; line-height: 1.3; }
        .si-father { color: #bfe3f7; font-size: .85rem; min-height: 1.2em; }
        .si-tags { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin: 14px 0 18px; }
        .si-tag {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255, 255, 255, .14); border: 1px solid rgba(191, 227, 247, .4);
            padding: 5px 12px; border-radius: 50rem; font-size: .8rem; font-weight: 600;
        }
        .si-tag-gold { background: rgba(245, 180, 0, .2); border-color: rgba(245, 180, 0, .55); color: #ffe08a; }
        .si-keys { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 20px; }
        .si-key { background: rgba(255, 255, 255, .1); border-radius: 12px; padding: 10px 6px; }
        .si-key small { display: block; color: #bfe3f7; font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; }
        .si-key strong { color: #fff; font-size: 1rem; word-break: break-all; }
        .si-actions { display: grid; grid-template-columns: 1fr; gap: 8px; }
        .si-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            height: 40px; border-radius: 12px; font-weight: 600; font-size: .85rem;
            text-decoration: none; transition: transform .15s, box-shadow .15s;
        }
        .si-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px -8px rgba(0, 0, 0, .5); }
        .si-btn-light { background: rgba(255, 255, 255, .15); color: #fff; border: 1px solid rgba(255, 255, 255, .3); }
        .si-btn-light:hover { background: #fff; color: #1f2580; }
        .si-btn-primary { grid-column: 1 / -1; background: #e1141c; color: #fff; }
        .si-btn-primary:hover { color: #fff; background: #c40f16; }

        .si-main { padding: 28px 28px 24px; background: #fff; }
        .si-main-head { margin-bottom: 16px; padding-right: 40px; }
        .si-main-head h5 { color: #1f2580; font-weight: 800; }
        .si-tabs {
            gap: 6px; padding: 6px; background: #f7f8fd; border-radius: 14px;
            border: 1px solid #e1e4f2; margin-bottom: 20px; flex-wrap: wrap;
        }
        .si-tabs .nav-link {
            border: 0; border-radius: 10px; color: #5f6488; font-weight: 600; font-size: .85rem;
            padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; background: transparent;
        }
        .si-tabs .nav-link:hover { color: #1f2580; background: #eef7fd; }
        .si-tabs .nav-link.active { background: #1f2580; color: #fff; box-shadow: 0 6px 14px -6px rgba(31, 37, 128, .6); }
        .si-tab-content { min-height: 300px; }
        .si-field {
            background: #f7f8fd; border: 1px solid #e1e4f2; border-left: 4px solid #bfe3f7;
            border-radius: 12px; padding: 10px 14px; height: 100%; transition: border-color .15s, background .15s;
        }
        .si-field:hover { background: #eef7fd; border-left-color: #1f2580; }
        .si-field small {
            display: block; color: #5f6488; font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em; margin-bottom: 3px;
        }
        .si-field span { color: #1f2580; font-weight: 600; font-size: .95rem; word-break: break-word; }
        .si-field span.is-empty { color: #b0b4cf; font-weight: 500; font-style: italic; font-size: .85rem; }

        .si-history-wrap {
            border: 1px solid #e1e4f2; border-radius: 14px; overflow: hidden; background: #fff;
        }
        .si-history-table { width: 100%; margin: 0; border-collapse: separate; border-spacing: 0; }
        .si-history-table thead th {
            background: #1f2580; color: #fff !important;
            font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            padding: 12px 14px; border: 0; white-space: nowrap; vertical-align: middle;
        }
        .si-history-table tbody td {
            padding: 12px 14px; vertical-align: middle; border-top: 1px solid #eef0f8;
            color: #1f2580; font-size: .92rem; font-weight: 600; background: #fff;
        }
        .si-history-table tbody tr:nth-child(even) td { background: #f7f8fd; }
        .si-history-table tbody tr:hover td { background: #eef7fd; }
        .si-history-table .col-sr { width: 56px; text-align: center; color: #5f6488; font-weight: 700; }
        .si-history-table .col-div,
        .si-history-table .col-roll { text-align: center; width: 90px; }
        .si-history-table .col-std { font-weight: 800; }
        .si-history-table .si-empty {
            text-align: center; color: #b0b4cf; font-weight: 500; font-style: italic; padding: 28px 14px;
        }
        .si-history-badge {
            display: inline-flex; align-items: center; justify-content: center; min-width: 36px;
            padding: 4px 10px; border-radius: 8px; background: #eef7fd; color: #1f2580;
            font-weight: 700; border: 1px solid #d5eaf6;
        }

        @media (max-width: 991.98px) {
            .si-main { padding: 22px 18px; }
        }

        .adm-ui .dataTables_processing {
            background: #fff; border: 1px solid var(--adm-sky); border-radius: 12px;
            color: var(--adm-ink); font-weight: 600; box-shadow: 0 10px 25px -10px rgba(31, 37, 128, .35);
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => true,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>


    <div class="row my-3 adm-ui">
        <div class="col-12">
            <div class="adm-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="adm-hero-icon"><i class="ti ti-school"></i></span>
                    <div>
                        <h3>{{ $page_title }}</h3>
                        <p>Search, filter and manage all admitted students.</p>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <div class="adm-stat"><small>Total Students</small><strong id="admStatTotal">—</strong></div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4" id="filter_section" style="display: none;">
            <div class="adm-panel">
                <div class="card-body p-4">
                    <div class="row align-items-end g-3">
                        <!-- Filter Input -->
                        <div class="col-md-4">
                            <label for="search" class="adm-label"><i class="ti ti-search me-1"></i> Filter by Student</label>
                            <input list="search" id="search_id" name="search"
                                class="form-control search @error('search') is-invalid @enderror"
                                value="{{ old('search') }}" placeholder="Search">
                            <datalist id="search" class="search_by_admission" data-append="search_by_admission"
                                data-selectedAdmissionId="{{ old('search') }}">
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label for="filter_standard" class="adm-label"><i class="ti ti-school me-1"></i> Standard</label>
                            <select id="filter_standard" class="form-select select2">
                                <option value="">All Standards</option>
                                @foreach ($standards ?? [] as $std)
                                    <option value="{{ $std }}">{{ $std }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="filter_division" class="adm-label"><i class="ti ti-layout-grid me-1"></i> Division</label>
                            <select id="filter_division" class="form-select select2">
                                <option value="">All Divisions</option>
                                @foreach ($divisions ?? [] as $div)
                                    <option value="{{ $div }}">{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Clear Filter Button -->
                        <div class="col-md-2">
                            <button type="button" title="Clear Filter" id="cilory_filter" class="adm-btn-clear">
                                <i class="ti ti-rotate"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="adm-panel">
                <div class="adm-table-card">
                    <div class="table-responsive">
                        <!-- <table id="yajra-datatables" class="dt-responsive table table-hover"> -->
                        <!-- <table id="yajra-datatables" class="dt-responsive table table-hover" style="width:100%"> -->
                        <table id="yajra-datatables" class="table table-striped table-hover dt-responsive">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {!! $item?->td_label ?: $item?->name !!}
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
 
    {{-- Export / Print Options Modal --}}
    <div class="modal fade" id="exportModal" aria-labelledby="exportModalLabel" aria-hidden="true">
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
                                <label class="form-check-label" for="scopeFiltered">
                                    Use current filters / search
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeAll"
                                       value="all">
                                <label class="form-check-label" for="scopeAll">
                                    Export complete Admission list
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="export_course_filter" class="form-label fw-bold">Filter By Course</label>
                            <select id="export_course_filter" class="form-select select2">
                                <option value="">All Courses</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="export_batch_filter" class="form-label fw-bold">Filter By Batch</label>
                            <select id="export_batch_filter" class="form-select select2">
                                <option value="">Filter By Batch</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="export_class_filter" class="form-label fw-bold">Filter By Class</label>
                            <select id="export_class_filter" class="form-select select2">
                                <option value="">Filter By Class</option>
                            </select>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Columns</label>
                                <div class="ms-auto d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-light border" id="selectAllColumns">
                                        Select all
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border" id="clearAllColumns">
                                        Clear all
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="resetDefaultColumns">
                                        Reset to default
                                    </button>
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
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No exportable columns configured.</p>
                                    </div>
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
    <!-- Modal -->
    <!-- Student Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content si-modal">
                @php
                    $infoSections = [
                        'school' => ['School', 'ti-school', [
                            'gr_no' => 'GR No.',
                            'roll_no' => 'Roll No.',
                            'admission_date' => 'Admission Date',
                            'admission_std' => 'Admission Std',
                            'current_std' => 'Current Std',
                            'division' => 'Division',
                            'stream' => 'Stream',
                            'house' => 'House',
                            'bus_route_village' => 'Bus Route Village',
                        ]],
                        'personal' => ['Personal', 'ti-user', [
                            'first_name' => 'Surname',
                            'last_name' => 'Name',
                            'father_name' => 'Father Name',
                            'mother_name' => 'Mother Name',
                            'date_of_birth' => 'Date of Birth',
                            'birth_place' => 'Birth Place',
                            'gender' => 'Gender',
                            'religion' => 'Religion',
                            'cast' => 'Cast',
                            'category' => 'Category',
                            'occupation' => 'Father Occupation',
                            'mother_occupation' => 'Mother Occupation',
                        ]],
                        'contact' => ['Contact', 'ti-phone', [
                            'mobile_no' => 'Mobile No.',
                            'parent_mobile_no' => 'Parent Mobile No.',
                            'other_mobile_no' => 'Other Mobile No.',
                            'whatsapp_no' => 'WhatsApp No.',
                            'temporary_address' => 'Temporary Address',
                            'permanent_address' => 'Permanent Address',
                        ]],
                        'ids' => ['IDs & Bank', 'ti-id', [
                            'aadhar_card_no' => 'Aadhaar Card No.',
                            'pen_no' => 'PEN No.',
                            'apaar_id_abc_id' => 'APAAR ID',
                            'udise' => 'UDISE',
                            'spid' => 'SPID',
                            'bank_name' => 'Bank Name',
                            'bank_account_no' => 'Bank Account No.',
                        ]],
                        'previous' => ['Previous School', 'ti-building-community', [
                            'last_school_name' => 'Last School Name',
                            'old_gr_no' => 'Old GR No.',
                            'passed_standard' => 'Passed Standard',
                            'lc_no' => 'LC No.',
                            'lc_date' => 'LC Date',
                            'attendance' => 'Attendance',
                        ]],
                        'class_history' => ['Class History', 'ti-list-details', []],
                    ];
                    $dateFields = ['admission_date', 'date_of_birth', 'lc_date'];
                    $wideFields = ['temporary_address', 'permanent_address', 'last_school_name', 'bus_route_village'];
                @endphp

                <div class="modal-body p-0">
                    <button type="button" class="btn-close si-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="row g-0">
                        {{-- Profile side --}}
                        <div class="col-lg-4 si-side">
                            <div class="si-photo-wrap">
                                <img id="profilePic" src="" alt="Student Photo" class="si-photo d-none">
                                <div id="profileInitials" class="si-photo si-initials">--</div>
                            </div>
                            <h4 class="si-name" id="siName">-</h4>
                            <div class="si-father" id="siFather"></div>

                            <div class="si-tags">
                                <span class="si-tag"><i class="ti ti-school"></i> <span id="siClass">-</span></span>
                                <span class="si-tag si-tag-gold" id="siGenderTag"><i class="ti ti-gender-bigender"></i> <span id="siGender">-</span></span>
                            </div>

                            <div class="si-keys">
                                <div class="si-key"><small>GR No.</small><strong id="siGr">-</strong></div>
                                <div class="si-key"><small>Roll No.</small><strong id="siRoll">-</strong></div>
                                <div class="si-key"><small>Standard</small><strong id="siStd">-</strong></div>
                                <div class="si-key"><small>Division</small><strong id="siDiv">-</strong></div>
                            </div>

                            <div class="si-actions">
                                <a href="#" id="siDrive" target="_blank" class="si-btn si-btn-light d-none"><i class="fab fa-google-drive"></i> Documents</a>
                                @if ($modules['permission_edit'] ?? false)
                                    <a href="#" id="siEdit" class="si-btn si-btn-primary"><i class="ti ti-edit"></i> Edit Student</a>
                                @endif
                            </div>
                        </div>

                        {{-- Details side --}}
                        <div class="col-lg-8 si-main">
                            <div class="si-main-head">
                                <h5 class="mb-0" id="infoModalLabel">Student Information</h5>
                                <small class="text-muted">All details of the student</small>
                            </div>

                            <ul class="nav si-tabs" role="tablist">
                                @foreach ($infoSections as $key => [$title, $icon, $fields])
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                                            data-bs-target="#si-tab-{{ $key }}" type="button" role="tab">
                                            <i class="ti {{ $icon }}"></i> {{ $title }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content si-tab-content">
                                @foreach ($infoSections as $key => [$title, $icon, $fields])
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="si-tab-{{ $key }}" role="tabpanel">
                                        @if ($key === 'class_history')
                                            <div class="si-history-wrap table-responsive">
                                                <table class="si-history-table" id="siClassHistoryTable">
                                                    <thead>
                                                        <tr>
                                                            <th class="col-sr">Sr</th>
                                                            <th>Academic Year</th>
                                                            <th>Standard</th>
                                                            <th class="col-div">Division</th>
                                                            <th class="col-roll">Roll No</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="siClassHistoryBody">
                                                        <tr>
                                                            <td colspan="5" class="si-empty">No class history.</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="row g-3">
                                                @foreach ($fields as $field => $label)
                                                    <div class="{{ in_array($field, $wideFields) ? 'col-12' : 'col-md-6' }}">
                                                        <div class="si-field">
                                                            <small>{{ $label }}</small>
                                                            <span class="student-info-field" data-field="{{ $field }}"
                                                                @if (in_array($field, $dateFields)) data-type="date" @endif>-</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Course Modal -->
    <div class="modal fade" id="course" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalLabel">Course Details</h5>
                    <div class="d-flex align-items-center">
                        <!-- Edit Button -->
                        {{-- <a href="#" id="courseEditBtn" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-edit"></i> Update Course
                        </a> --}}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div id="courseDetailsHeader" class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <div class="container">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>GR No:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalGRNo">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Name:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalFullName">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Course:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalCourse">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Class:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalClass">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>University:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalUniversity">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Enrollment No:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalEnrollNo">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>APAAR ID:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalUdiseABC">-</p>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>DOJ:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalDOJ">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>&nbsp;</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>&nbsp;</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Department:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalDepartment2">-</p>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Shift:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalShift">-</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>&nbsp;</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>SPID:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalSPID2">-</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>UDISE No:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modaludiseno">-</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>DOC:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalDOC">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr></tr>
                            </thead>
                            <tbody id="courseData">
                                <!-- Rows will be appended dynamically here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>






@endsection

@section('page_script_file')
    <!-- jQuery (required by DataTables) - removed duplicate to prevent select2 override -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- DataTables JS -->
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>

    <!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                                                                                                                                                                                                                                                    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script> -->

@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        const canUpdateStatus = @json(auth()->check() && (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('developer')));

        function fillStudentInfo(data) {
            const clean = v => (v === null || v === undefined || String(v).trim() === '') ? '' : String(v).trim();
            const fullName = [data.first_name, data.last_name, data.father_name].map(clean).filter(Boolean).join(' ');
            const initials = [data.first_name, data.last_name].map(clean).filter(Boolean)
                .map(w => w.charAt(0).toUpperCase()).join('') || 'S';

            $('#profileInitials').text(initials).removeClass('d-none');
            $('#profilePic').addClass('d-none').attr('src', '');
            if (clean(data.profile_pic)) {
                $('#profilePic')
                    .off('load error')
                    .on('load', function() {
                        $(this).removeClass('d-none');
                        $('#profileInitials').addClass('d-none');
                    })
                    .on('error', function() {
                        $(this).addClass('d-none');
                        $('#profileInitials').removeClass('d-none');
                    })
                    .attr('src', "{{ asset('') }}" + data.profile_pic);
            }

            $('#siName').text(fullName || '-');
            $('#siFather').text(clean(data.mother_name) ? 'Mother: ' + clean(data.mother_name) : '');
            const std = clean(data.current_std), div = clean(data.division);
            $('#siClass').text(std ? (std + (div ? ' - ' + div : '')) : 'No class');
            $('#siGender').text(clean(data.gender) || 'Gender not set');
            $('#siGr').text(clean(data.gr_no) || '-');
            $('#siRoll').text(clean(data.roll_no) || '-');
            $('#siStd').text(std || '-');
            $('#siDiv').text(div || '-');
            $('#siDrive').toggleClass('d-none', !clean(data.gdrivefolderurl)).attr('href', clean(data.gdrivefolderurl) || '#');
            $('#siEdit').attr('href', "{{ route($route . '.edit', '__ID__') }}".replace('__ID__', data.id));

            $('#infoModal .student-info-field').each(function() {
                const value = clean(data[$(this).data('field')]);
                const $el = $(this).toggleClass('is-empty', !value);
                if (!value) {
                    $el.text('Not added');
                } else if ($el.data('type') === 'date') {
                    $el.text(moment(value).format('DD-MM-YYYY'));
                } else {
                    $el.text(value);
                }
            });

            $('#infoModal .si-tabs .nav-link').first().tab('show');
        }

        function fillClassHistory(rows) {
            const $body = $('#siClassHistoryBody').empty();
            if (!rows || !rows.length) {
                $body.append('<tr><td colspan="5" class="si-empty">No class history.</td></tr>');
                return;
            }
            rows.forEach(function(row, i) {
                $body.append(
                    '<tr>' +
                    '<td class="col-sr">' + (i + 1) + '</td>' +
                    '<td>' + (row.academic_year || '-') + '</td>' +
                    '<td class="col-std">' + (row.standard || '-') + '</td>' +
                    '<td class="col-div"><span class="si-history-badge">' + (row.division || '-') + '</span></td>' +
                    '<td class="col-roll"><span class="si-history-badge">' + (row.roll_no || '-') + '</span></td>' +
                    '</tr>'
                );
            });
        }

        // Show student details modal
        $(document).on('click', '.view-btn', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route($route . '.show') }}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        let data = response.student;
                        if (data) {
                            fillStudentInfo(data);
                            fillClassHistory(response.class_history || []);
                            $('#infoModal').modal('show');
                        } else {
                            toastr.error('Student data not found');
                        }
                    } else {
                        toastr.error(response.message || 'Student not found');
                    }
                },
                error: function(err) {
                    console.error(err);
                    toastr.error('Error fetching student info');
                }
            });
        });



        // Update the course modal display logic
        $(document).on('click', '.course-btn', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route($route . '.show') }}",
                type: "GET",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        let courses = response.courses;
                        let student = response.student;

                        if (courses.length > 0) {
                            // ✅ Set Edit Button URL
                            let editUrl = 'cource-registration/' + student.id + '/edit';
                            $('#courseEditBtn').attr('href', editUrl);

                            // ===== Populate Header =====
                            $('#modalGRNo').text(student.id ?? '-');
                            $('#modalFullName').text(
                                `${student.first_name} ${student.last_name} ${student.father_name}`
                                .toUpperCase()
                            );

                            let courseNames = courses.map(c => {
                                let batchName = c?.batch?.batch_name ?
                                    ` (${c.batch.batch_name})` : '';
                                return `${c.course.course_name}${batchName}`;
                            }).join(', ');

                            $('#modalCourse').text(courseNames);

                            $('#modalClass').text(courses[0]?.class?.class ?? '-');

                            let shiftTime = courses[0]?.shift ?
                                `${courses[0].shift.from_time} - ${courses[0].shift.to_time}` : '-';
                            $('#modalShift').text(shiftTime);

                            $('#modalUniversity').text(courses[0]?.university ?? '-');
                            $('#modalDepartment2').text(courses[0]?.department ?? '-');
                            $('#modalEnrollNo').text(student.enrolment_no ?? '-');
                            $('#modalSPID2').text(student.spid ?? '-');
                            $('#modalUdiseABC').text(student.apaar_id_abc_id ?? '-');
                            $('#modaludiseno').text(student.udise ?? '-');


                            let firstCourse = courses[0];

                            let doj = '-';
                            if (firstCourse.created_at) {
                                doj = moment(firstCourse.created_at).format('DD-MM-YYYY');
                            }

                            let doc = '-';
                            if (firstCourse.status === 'Completed' && firstCourse.updated_at) {
                                doc = moment(firstCourse.updated_at).format('DD-MM-YYYY');
                            }

                            $('#modalDOJ').text(doj);
                            $('#modalDOC').text(doc);

                            $('#modalName').text(
                                `${student.first_name} ${student.father_name} ${student.last_name}`
                            );

                            $('#courseData').empty();

                            courses.forEach(function(course) {
                                let row = `<div class="row mb-3">`;

                                if ("{{ $fees_collection_create ?? 'false' }}" == "true") {
                                    row += `<div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-success w-100 collectFees" data-id="${course.id}">
                                    Fees
                                </button>
                            </div>`;
                                }

                                row += `<div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-primary w-100 formBtn"
                                data-register_id="${course.register_id}"
                                data-course_id="${course.course_id}">
                                Form
                            </button>
                        </div>`;

                                row += `<div class="col-md-3 mb-2">
                                    <button type="button" class="btn btn-warning w-100 updateBtn"
                                 data-id="${course.id}"

                                        data-register_id="${course.register_id}"
                                        data-course_id="${course.course_id}">
                                        Update
                                    </button>
                                </div>`;



                                let statusOptions = ['Running', 'Completed', 'Cancel'];
                                let currentStatus = course.status ?? '';
                                let statusClass =
                                    currentStatus === 'Running' ? 'btn-primary' :
                                    currentStatus === 'Completed' ? 'btn-success' :
                                    currentStatus === 'Cancel' ? 'btn-danger' : 'btn-secondary';

                                let statusDropdown = '';
                                if (canUpdateStatus) {
                                    statusDropdown = `<div class="btn-group w-100">
                                <button type="button" class="btn ${statusClass} btn-sm dropdown-toggle w-100"
                                    data-bs-toggle="dropdown" aria-expanded="false" style="height: 37px">
                                    ${currentStatus || 'Select Status'}
                                </button>
                                <ul class="dropdown-menu w-100">`;

                                    statusOptions.forEach(function(option) {
                                        if (option !== currentStatus) {
                                            statusDropdown += `<li>
                                        <a href="javascript:void(0)" class="dropdown-item update-status"
                                            data-register_id="${course.register_id}"
                                            data-course_id="${course.course_id}"
                                            data-update_status="${option}">
                                            ${option}
                                        </a>
                                    </li>`;
                                        }
                                    });

                                    statusDropdown += `</ul></div>`;
                                } else {
                                    statusDropdown = `<button type="button" class="btn ${statusClass} btn-sm w-100" disabled style="height: 37px">
                                    ${currentStatus || 'Select Status'}
                                </button>`;
                                }
                                row +=
                                    `<div class="col-md-3 mb-2">${statusDropdown}</div>`;
                                row +=
                                    `</div>`;

                                $('#courseData').append(row);
                            });

                            $('#course').modal('show');
                        } else {
                            if ("{{ $course_register_add ?? 'false' }}" == 'true') {
                                toastr.warning("User does not have the right permissions.");
                                return;
                            }
                            window.location.href =
                                "{{ url('software/cource-registration/create') }}/" + student.id;
                        }
                    } else {
                        toastr.error(response.message || 'Student not found');
                    }
                },
                error: function(err) {
                    console.error(err);
                    toastr.error('Error fetching course info');
                }
            });
        });




        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on("click", ".create_google_drive_folder", function(e) {
            e.preventDefault();
            let btn = $(this);
            let studentId = btn.data("id");
            btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admission.create-folder') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    id: studentId
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || "Folder created successfully!");
                        } else {
                            alert("Folder created successfully!");
                        }
                        $('#yajra-datatables').DataTable().ajax.reload(null, false);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error("Failed: " + response.message);
                        } else {
                            alert("Failed: " + response.message);
                        }
                        btn.removeClass('disabled').html('<i class="fa fa-folder-plus"></i>');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Something went wrong';
                    if (typeof toastr !== 'undefined') {
                        toastr.error("Error: " + msg);
                    } else {
                        alert("Error: " + msg);
                    }
                    btn.removeClass('disabled').html('<i class="fa fa-folder-plus"></i>');
                }
            });
        });
        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    beforeSend: function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = {
                            value: $('input[name="search"]').val(),
                            regex: false
                        };
                        data.filter_standard = $('#filter_standard').val();
                        data.filter_division = $('#filter_division').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...'
                },
                // dom:'lBfrtip',
                // buttons: ["csv"],
            });

            $('#yajra-datatables').on('xhr.dt', function(e, settings, json) {
                if (!json) return;
                $('#admStatTotal').text(json.recordsTotal ?? '—');
            });
        });

        // Redraw DataTable on search input keyup / input / change with debounce
        let searchDebounceTimer;
        $('input[name="search"]').on('input change keyup', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                dtable.draw();
            }, 500);
        });

        // Toggle filter section on filter button click
        $(document).on('click', '#show_filter', function() {
            $('#filter_section').slideToggle();
        });

        $("#cilory_filter").click(function() {
            $('.search').val('');
            $('#filter_standard, #filter_division').val('').trigger('change.select2');
            dtable.draw();
        });

        // Redraw DataTable on select change
        $(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });

        {{--
        // Handle Google Drive folder open/create button click
        $(document).on('click', '.open_google_drive_folder', function() {
            var id = $(this).data('id');
            var folderName = $(this).data('foldername');
            var folderUrl = $(this).data('folderurl');
            var button = $(this);

            if (folderUrl) {
                window.open(folderUrl, '_blank');
            } else {
                $('#yajra-datatables_processing').css('font-weight', 'bold').show();

                $.ajax({
                    url: "{{ route($route . '.create-google-drive-folder') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        foldername: folderName,
                    },
                    success: function(response) {
                        console.log("response 459 | success", response);
                        if (response.folder_url) {
                            button.attr('data-folderurl', response.folder_url);
                            toastr.success('Google Drive folder created successfully.');
                            window.open(response.folder_url, '_blank');
                        } else {
                            toastr.success('Google Drive folder created successfully.');
                        }
                        $('#yajra-datatables').DataTable().ajax.reload(null, false);
                    },
                    error: function(error) {
                        console.error("response 470 | Error", error);
                        toastr.error('Error creating Google Drive folder.');
                    },
                    complete: function() {
                        $('#yajra-datatables_processing').hide();
                    }
                });
            }
        });
        --}}
        // Create folder button click




        $(document).on('click', '.collectFees', function() {
            let admId = $(this).data('id');
            let url = '/software/fees-collection/get-details/' + admId;
            window.open(url, '_blank'); // 🔹 Opens in new tab
        });
        $(document).on('click', '.updateBtn', function() {

            let id = $(this).data('id');
            window.location.href = "/software/cource-registration/" + id + "/edit";


            window.location.href = url;
        });


        // Submit Update Form
        $(document).ready(function() {
            $('#updateForm').on('submit', function(e) {
                e.preventDefault();

                var registerId = $('#updateRegisterId').val();
                var courseId = $('#updateCourseId').val();
                var fees = $('#updateFees').val();

                $.ajax({
                    url: "{{ route($route . '.course-fees-update') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        register_id: registerId,
                        course_id: courseId,
                        fee: fees
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#updateForm')[0].reset();
                            $('#updateModal').modal('hide');
                            $('.modal-backdrop').remove();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'Update failed');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error updating fee.');
                    }
                });
            });
        });
        // Status button dropdown click event
        $(document).on('click', '.update-status', function() {
            let register_id = $(this).data('register_id');
            let course_id = $(this).data('course_id');
            let newStatus = $(this).data('update_status');

            $.ajax({
                url: "{{ route('admission.update-course-status') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    register_id: register_id,
                    course_id: course_id,
                    status: newStatus
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message || 'Status updated successfully');

                        // Re-fetch courses for this student and refresh modal
                        $.ajax({
                            url: "{{ route($route . '.show') }}",
                            type: "GET",
                            data: {
                                id: register_id
                            },
                            dataType: "json",
                            success: function(resp) {
                                if (resp.success && resp.courses.length > 0) {
                                    let courses = resp.courses;
                                    let student = resp.student;

                                    // ✅ HEADER UPDATE - DOJ/DOC (AA ADD KARO)
                                    let firstCourse = courses[0];

                                    // Date of Joining
                                    let doj = firstCourse.created_at ?
                                        moment(firstCourse.created_at).format(
                                            'DD-MM-YYYY') : '-';

                                    // Date of Completion - Status Completed hoy tyare j show thase
                                    let doc = (firstCourse.status === 'Completed' &&
                                            firstCourse.updated_at) ?
                                        moment(firstCourse.updated_at).format(
                                            'DD-MM-YYYY') : '-';

                                    // Header ma DOJ ane DOC update karo
                                    $('#modalDOJ').text(doj);
                                    $('#modalDOC').text(doc);

                                    // Clear previous data
                                    $('#courseData').empty();

                                    courses.forEach(function(course) {
                                        // Start row
                                        let row =
                                            `<div class="row mb-3 align-items-center">`;

                                        // ===== Fees Button =====
                                        if ("{{ $fees_collection_create ?? 'false' }}" ==
                                            "true") {
                                            row += `
                                    <div class="col-md-3 mb-2">
                                        <button type="button" class="btn btn-success w-100 collectFees" data-id="${course.id}">
                                            Fees
                                        </button>
                                    </div>`;
                                        }

                                        // ===== Form Button =====
                                        row += `
                                <div class="col-md-3 mb-2">
                                    <button type="button" class="btn btn-primary w-100 formBtn"
                                        data-register_id="${course.register_id}"
                                        data-course_id="${course.course_id}">
                                        Form
                                    </button>
                                </div>`;

                                        // ===== Update Button =====
                                        if ("{{ $fees_collection_edit ?? 'false' }}" ==
                                            "true") {
                                            row += `
                                    <div class="col-md-3 mb-2">
                                        <button type="button" class="btn btn-warning w-100 updateBtn"
                                            data-register_id="${course.register_id}"
                                            data-course_id="${course.course_id}">
                                            Update
                                        </button>
                                    </div>`;
                                        }

                                        // ===== Status Dropdown =====
                                        let statusOptions = ['Running', 'Completed',
                                            'Cancel'
                                        ];
                                        let currentStatus = course.status ?? '';
                                        let statusClass =
                                            currentStatus === 'Running' ?
                                            'btn-primary' :
                                            currentStatus === 'Completed' ?
                                            'btn-success' :
                                            currentStatus === 'Cancel' ?
                                            'btn-danger' :
                                            'btn-secondary';

                                         let statusDropdown = '';
                                         if (canUpdateStatus) {
                                             statusDropdown = `<div class="btn-group w-100">
                                    <button type="button" class="btn ${statusClass} btn-sm dropdown-toggle w-100" style="height: 37px"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        ${currentStatus || 'Select Status'}
                                    </button>
                                    <ul class="dropdown-menu w-100">`;

                                             statusOptions.forEach(function(option) {
                                                 if (option !== currentStatus) {
                                                     statusDropdown += `
                                             <li>
                                                 <a href="javascript:void(0)" class="dropdown-item update-status"
                                                     data-register_id="${course.register_id}"
                                                     data-course_id="${course.course_id}"
                                                     data-update_status="${option}">
                                                     ${option}
                                                 </a>
                                             </li>`;
                                                 }
                                             });

                                             statusDropdown += `</ul></div>`;
                                         } else {
                                             statusDropdown = `<button type="button" class="btn ${statusClass} btn-sm w-100" disabled style="height: 37px">
                                             ${currentStatus || 'Select Status'}
                                         </button>`;
                                         }

                                        row +=
                                            `<div class="col-md-3 mb-2">${statusDropdown}</div>`;
                                        row += `</div>`; // close row

                                        // Append the constructed row to container
                                        $('#courseData').append(row);
                                    });
                                } else {
                                    toastr.warning('No courses found for this student.');
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                toastr.error('Error refreshing course modal');
                            }
                        });
                    } else {
                        toastr.error(response.message || 'Failed to update status');
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    toastr.error('Error updating status');
                }
            });
        });

        // Form button click → open new view
        $(document).on('click', '.formBtn', function() {
            let register_id = $(this).data('register_id');
            let course_id = $(this).data('course_id');

            // redirect to view page
            window.open("/software/admission/view/" + register_id + "/" + course_id, "_blank");
        });
        $(document).on('click', '.open-attendance', function(e) {
            e.preventDefault();
            var admissionId = $(this).data('admission-id');
            localStorage.setItem('admission_id', admissionId);
            window.open("{{ url('software/attedance') }}", "_blank");
        });
        $(document).on('click', '.open-test', function(e) {
            e.preventDefault();
            var admissionId = $(this).data('admission-id');
            localStorage.setItem('admission_id', admissionId);
            window.open($(this).attr('href'), '_blank');
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

            const showExportModal = () => {
                if (exportModalInstance) {
                    exportModalInstance.show();
                } else {
                    $('#exportModal').modal('show');
                }
            };

            const hideExportModal = () => {
                if (exportModalInstance) {
                    exportModalInstance.hide();
                } else {
                    $('#exportModal').modal('hide');
                }
            };

            function openExportModal(actionType) {
                $('#export_action').val(actionType);
                $('#exportModalLabel').text(actionType === 'print' ? 'Print Preview' : 'Export to Excel');
                showExportModal();
                
                // Initialize select2 inside the modal with the proper dropdown parent
                $('#export_course_filter').select2({ dropdownParent: $('#exportModal') });
                $('#export_batch_filter').select2({ dropdownParent: $('#exportModal') });
                $('#export_class_filter').select2({ dropdownParent: $('#exportModal') });
            }

            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                openExportModal('excel');
            });

            $('#print_btn').on('click', function(e) {
                e.preventDefault();
                openExportModal('print');
            });

            $('#selectAllColumns').on('click', function() {
                $('.export-column').prop('checked', true);
            });

            $('#clearAllColumns').on('click', function() {
                $('.export-column').prop('checked', false);
            });

            $('#resetDefaultColumns').on('click', function() {
                $('.export-column').each(function() {
                    $(this).prop('checked', defaultColumns.includes($(this).val()));
                });
            });

            $('#exportOptionsForm').on('submit', function(e) {
                e.preventDefault();

                const selectedColumns = $('.export-column:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!selectedColumns.length) {
                    alert('Please select at least one column to continue.');
                    return;
                }

                const scope = $('input[name="export_scope"]:checked').val() || 'filtered';
                const params = new URLSearchParams();
                params.append('scope', scope);

                const courseId = $('#export_course_filter').val();
                if (courseId) {
                    params.append('course_id', courseId);
                }

                const batchId = $('#export_batch_filter').val();
                if (batchId) {
                    params.append('batch_id', batchId);
                }

                const classId = $('#export_class_filter').val();
                if (classId) {
                    params.append('class_id', classId);
                }

                if (scope === 'filtered') {
                    const searchValue = $('#yajra-datatables_filter input[type="search"]').val();
                    if (searchValue) {
                        params.append('search', searchValue);
                    }
                    if ($('#filter_standard').val()) {
                        params.append('filter_standard', $('#filter_standard').val());
                    }
                    if ($('#filter_division').val()) {
                        params.append('filter_division', $('#filter_division').val());
                    }
                }

                selectedColumns.forEach(column => params.append('columns[]', column));

                const actionType = $('#export_action').val();
                const baseUrl = actionType === 'print'
                    ? "{{ route($route . '.print') }}"
                    : "{{ route($route . '.export-excel') }}";

                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

                if (actionType === 'print') {
                    window.open(finalUrl, '_blank');
                } else {
                    window.location.href = finalUrl;
                }

                hideExportModal();
            });

            // Store all batches for dynamic filtering
            const allBatches = @json($batches);

            $('#export_course_filter').on('change', function() {
                const courseId = $(this).val();
                let optionsHtml = '';
                
                if (courseId) {
                    optionsHtml += '<option value="">All Batches</option>';
                    allBatches.forEach(function(batch) {
                        if (batch.course_id == courseId) {
                            optionsHtml += `<option value="${batch.id}">${batch.batch_name}</option>`;
                        }
                    });
                } else {
                    optionsHtml += '<option value="">Filter By Batch</option>';
                }
                
                $('#export_batch_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
            });

            $('#export_batch_filter').on('change', function() {
                const batchId = $(this).val();
                let optionsHtml = '';
                
                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if (response.status) {
                                optionsHtml += '<option value="">All Classes</option>';
                                $.each(response.data, function(i, item) {
                                    optionsHtml += `<option value="${item.id}">${item.name}</option>`;
                                });
                            } else {
                                optionsHtml += '<option value="">Filter By Class</option>';
                            }
                            $('#export_class_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
                        },
                        error: function() {
                            optionsHtml += '<option value="">Filter By Class</option>';
                            $('#export_class_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
                        }
                    });
                } else {
                    optionsHtml += '<option value="">Filter By Class</option>';
                    $('#export_class_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
                }
            });
        });
    </script>


    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    {{-- @include('software.includes.script-update-status') --}}
    <script>
        $(document).ready(function() {
            let instance = $('.search_by_admission');
            let admission_id = instance.attr("data-selectedAdmissionId");

            $.ajax({
                type: 'POST',
                url: "{{ route('get-all-admission') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        let options = "<option value=''>Select Admission</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (admission_id == item.id) {
                                    options += "<option value='" + item.id + "' selected>" + item.name + "</option>";
                                } else {
                                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                                }
                            });
                        }
                        if (instance.attr('data-append')) {
                            $("." + instance.attr('data-append')).empty().append(options);
                        }
                    }
                }
            });
        });
    </script>
@endsection
