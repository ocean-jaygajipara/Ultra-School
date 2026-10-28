@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Class Wise Report';
    $route = $modules['route'] ?? null;
    $controller = app(\App\Http\Controllers\admin\ClassReportController::class);
    $dataColumns = $controller->selectedColumns($filters);
    $blankHeaders = $controller->blankColumnHeaders($filters);
    $tableColCount = count($dataColumns) + count($blankHeaders);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .crx {
            --crx-ink: #1f2580;
            --crx-ink-2: #2e3a9e;
            --crx-muted: #5f6488;
            --crx-line: #e1e4f2;
            --crx-soft: #f7f8fd;
            --crx-accent: #e1141c;
            --crx-accent-dark: #b80f16;
            --crx-accent-soft: #fff1f1;
            --crx-sky: #bfe3f7;
            --crx-sky-soft: #eef7fd;
            --crx-gold: #f5b400;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--crx-ink);
        }

        .crx-panel {
            background: #fff;
            border: 1px solid var(--crx-line);
            border-radius: 18px;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, .12);
        }

        .crx-panel > :first-child { border-top-left-radius: 17px; border-top-right-radius: 17px; }
        .crx-panel > :last-child { border-bottom-left-radius: 17px; border-bottom-right-radius: 17px; }

        .crx .select2-dropdown {
            border: 1.5px solid var(--crx-ink) !important;
            border-radius: 12px !important;
            box-shadow: 0 16px 32px -12px rgba(15, 23, 42, .25);
            overflow: hidden;
            z-index: 1060;
        }
        .crx .select2-results__option--highlighted[aria-selected],
        .crx .select2-results__option--highlighted {
            background: var(--crx-ink) !important;
            color: #fff !important;
        }

        .crx-banner {
            background:
                radial-gradient(circle at 88% 15%, rgba(245, 180, 0, .35), transparent 42%),
                radial-gradient(circle at 5% 130%, rgba(191, 227, 247, .35), transparent 45%),
                linear-gradient(135deg, #171c6b 0%, var(--crx-ink-2) 100%);
            color: #fff;
            padding: 26px 28px;
        }

        .crx-banner h3, .crx-banner h4 { color: #fff; margin: 0; font-weight: 700; letter-spacing: -.3px; }
        .crx-banner p { color: #d6e6f7; margin: 4px 0 0; font-size: .875rem; }

        .crx-badge-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(245, 180, 0, .18);
            border: 1px solid rgba(245, 180, 0, .6);
            color: var(--crx-gold);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
        }

        .crx-body { padding: 24px 28px; }

        .crx-label {
            display: block; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--crx-muted); margin-bottom: 8px;
        }

        .crx .select2-container--default .select2-selection--single,
        .crx-input {
            height: 46px !important;
            border: 1.5px solid var(--crx-line) !important;
            border-radius: 12px !important;
            background: var(--crx-soft) !important;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .crx .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 43px !important; padding-left: 14px !important;
            color: var(--crx-ink) !important; font-weight: 500;
        }

        .crx .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px !important; }

        .crx .select2-container--focus .select2-selection--single,
        .crx .select2-container--open .select2-selection--single,
        .crx-input:focus {
            border-color: var(--crx-ink) !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(191, 227, 247, .7) !important;
            outline: none;
        }

        .crx-input { width: 100%; padding: 0 14px 0 42px; font-size: .9rem; color: var(--crx-ink); }
        .crx-field { position: relative; }
        .crx-field > i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--crx-ink-2); font-size: 1.1rem; pointer-events: none;
        }

        .crx-btn-clear {
            height: 46px; padding: 0 20px; border-radius: 12px;
            border: 1.5px solid #f7c3c5; background: var(--crx-accent-soft); color: var(--crx-accent);
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
            transition: all .2s; text-decoration: none;
        }
        .crx-btn-clear:hover { background: var(--crx-accent); color: #fff; border-color: var(--crx-accent); }

        .crx-stats { display: flex; flex-wrap: wrap; gap: 10px; }
        .crx-stat {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(191, 227, 247, .35);
            border-radius: 12px; padding: 8px 14px; min-width: 96px;
        }
        .crx-stat small { display: block; color: var(--crx-sky); font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; }
        .crx-stat strong { color: #fff; font-size: 1.05rem; }
        .crx-stat.is-accent { background: var(--crx-accent); border-color: var(--crx-accent); }
        .crx-stat.is-accent small { color: #ffd9db; }

        .crx-table-wrap {
            border: 1px solid var(--crx-line); border-radius: 14px;
            max-height: 65vh; overflow: auto;
        }
        .crx-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .crx-table thead th {
            position: sticky; top: 0; z-index: 2;
            background: var(--crx-ink); color: #fff;
            border-bottom: 0 !important;
            font-size: .72rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            padding: 14px 16px; white-space: nowrap; border-top: 0; border-left: 0; border-right: 0;
        }
        .crx-table tbody td {
            padding: 12px 16px; border-bottom: 1px solid #eef0f8;
            font-size: .9rem; vertical-align: middle;
        }
        .crx-table tbody tr { transition: background .15s; }
        .crx-table tbody tr:nth-child(even) { background: #fafbff; }
        .crx-table tbody tr:hover { background: var(--crx-sky-soft); }
        .crx-table tbody tr:last-child td { border-bottom: 0; }

        .crx-table thead th.extra-col { background: var(--crx-accent); color: #fff; text-align: center; }
        .crx-table td.extra-col { border-left: 1px dashed #f7c3c5; background: #fffafa; }

        .crx-roll {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 30px; padding: 0 8px; border-radius: 9px;
            background: var(--crx-ink); color: #fff; font-weight: 700; font-size: .8rem;
        }
        .crx-roll.is-empty { background: #eef0f8; color: #9aa0c3; }

        .crx-name { font-weight: 600; color: var(--crx-ink); }

        .crx-gr {
            font-family: 'JetBrains Mono', Consolas, monospace;
            background: var(--crx-sky-soft); color: var(--crx-ink); border: 1px solid var(--crx-sky);
            padding: 4px 10px; border-radius: 8px; font-size: .82rem; font-weight: 600;
        }

        .crx-empty { text-align: center; padding: 56px 20px; }
        .crx-empty-icon {
            width: 88px; height: 88px; border-radius: 24px; margin: 0 auto 18px;
            background: linear-gradient(135deg, var(--crx-sky-soft), #fff4d1);
            color: var(--crx-ink); font-size: 2.4rem;
            border: 2px solid var(--crx-sky);
            display: flex; align-items: center; justify-content: center;
        }
        .crx-empty h5 { font-weight: 700; color: var(--crx-ink); margin-bottom: 6px; }
        .crx-empty p { color: var(--crx-muted); margin: 0; }

        /* --- Report Settings Modal Redesign --- */
        #classReportSettingsModal .modal-dialog {
            max-width: 620px;
            margin: 1.75rem auto;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #classReportSettingsModal .modal-content {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 60px -12px rgba(15, 23, 42, 0.28), 0 0 0 1px rgba(31, 37, 128, 0.08);
            background: #fff;
        }

        /* Modal Header */
        #classReportSettingsModal .crx-modal-header {
            background:
                radial-gradient(circle at 90% 10%, rgba(245, 180, 0, 0.25), transparent 45%),
                linear-gradient(135deg, #151a62 0%, #29369e 100%);
            color: #fff !important;
            border-bottom: 0;
            padding: 20px 24px;
            position: relative;
        }
        .crx-modal-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(245, 180, 0, 0.16);
            border: 1.5px solid rgba(245, 180, 0, 0.45);
            color: #f5b400;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .crx-modal-title {
            color: #ffffff !important;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.2px;
            line-height: 1.3;
        }
        .crx-modal-sub {
            color: #d2daf5;
            font-size: 0.8rem;
            margin-top: 3px;
            font-weight: 400;
        }
        .crx-modal-close {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
            outline: none;
        }
        .crx-modal-close:hover {
            background: rgba(239, 68, 68, 0.9);
            border-color: rgba(239, 68, 68, 0.9);
            color: #ffffff;
            transform: rotate(90deg);
        }

        /* Modal Body */
        #classReportSettingsModal .crx-modal-body {
            padding: 22px 24px 16px;
            background: #f8faff;
        }

        /* Cards inside modal */
        .crx-modal-card {
            background: #ffffff;
            border: 1px solid #e1e6f4;
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }
        .crx-card-title {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--crx-ink);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .crx-card-title i {
            font-size: 1.05rem;
            color: var(--crx-ink-2);
        }
        .crx-card-desc {
            font-size: 0.8rem;
            color: var(--crx-muted);
            margin: 4px 0 14px;
            line-height: 1.4;
        }

        /* Quick links */
        .crx-quick-links {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .crx-link-btn {
            background: none;
            border: none;
            padding: 0;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--crx-ink-2);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: color 0.15s;
        }
        .crx-link-btn:hover {
            color: #171c6b;
            text-decoration: underline;
        }
        .crx-sep {
            color: #cbd5e1;
            font-size: 0.75rem;
        }

        /* Column grid & tiles */
        .crx-col-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        @media (max-width: 576px) {
            .crx-col-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .crx-checkbox-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
        }
        .crx-col-check {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1.5px solid #e1e6f4;
            background: #ffffff;
            cursor: pointer;
            margin: 0;
            user-select: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .crx-col-check:hover {
            border-color: #a5b4fc;
            background: #fcfdff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(31, 37, 128, 0.06);
        }
        .crx-col-check.is-on {
            border-color: var(--crx-ink);
            background: #f0f4ff;
            box-shadow: 0 2px 8px rgba(31, 37, 128, 0.1);
        }
        .crx-col-info {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .crx-col-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f1f4fb;
            color: var(--crx-ink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .crx-col-check.is-on .crx-col-icon {
            background: var(--crx-ink);
            color: #ffffff;
        }
        .crx-col-title {
            font-size: 0.84rem;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }
        .crx-col-check.is-on .crx-col-title {
            color: var(--crx-ink);
        }
        .crx-col-check-box {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            color: transparent;
            transition: all 0.15s ease-in-out;
            flex-shrink: 0;
        }
        .crx-col-check.is-on .crx-col-check-box {
            border-color: var(--crx-ink);
            background: var(--crx-ink);
            color: #ffffff;
        }

        /* Extra Columns Inputs */
        .crx-tag-badge {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: #eef3ff;
            color: var(--crx-ink);
            border: 1px solid #d4defc;
        }
        .crx-input-label {
            display: block;
            font-size: 0.74rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }
        .crx-label-hint {
            font-weight: 400;
            color: #94a3b8;
            font-size: 0.7rem;
        }
        .crx-field-wrap {
            position: relative;
        }
        .crx-field-wrap > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b84a9;
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.2s;
        }
        .crx-field-wrap:focus-within > i {
            color: var(--crx-ink);
        }
        .crx-modal-input {
            width: 100%;
            height: 44px;
            padding: 0 14px 0 42px;
            border-radius: 11px;
            border: 1.5px solid #d5daea;
            background: #ffffff;
            font-size: 0.88rem;
            color: var(--crx-ink);
            font-weight: 500;
            transition: all 0.2s;
        }
        .crx-modal-input:focus {
            outline: none;
            border-color: var(--crx-ink);
            box-shadow: 0 0 0 4px rgba(31, 37, 128, 0.1);
            background: #ffffff;
        }
        .crx-modal-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Presets Bar */
        .crx-presets-bar {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .crx-presets-label {
            font-size: 0.74rem;
            font-weight: 700;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .crx-presets-label i {
            color: #eab308;
            font-size: 0.9rem;
        }
        .crx-preset-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .crx-chip {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            line-height: 1.2;
        }
        .crx-chip:hover {
            background: #e2e8f0;
            color: var(--crx-ink);
            border-color: #cbd5e1;
        }
        .crx-chip.is-active {
            background: var(--crx-ink);
            color: #ffffff;
            border-color: var(--crx-ink);
        }
        .crx-chip-clear {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #e11d48;
        }
        .crx-chip-clear:hover {
            background: #e11d48;
            border-color: #e11d48;
            color: #ffffff;
        }

        /* Modal Footer */
        #classReportSettingsModal .crx-modal-footer {
            border-top: 1px solid #edf0f7;
            background: #ffffff;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .crx-btn-reset {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 9px 15px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .crx-btn-reset:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .crx-btn-cancel {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 9px 18px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .crx-btn-cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .crx-btn-save {
            background: linear-gradient(135deg, #1f2580 0%, #2e3a9e 100%);
            border: 1.5px solid #1f2580;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 9px 22px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(31, 37, 128, 0.25);
            transition: all 0.2s;
        }
        .crx-btn-save:hover {
            background: linear-gradient(135deg, #181d68 0%, #242f88 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(31, 37, 128, 0.35);
            color: #ffffff !important;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => 'Report', 'url' => ''], ['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_report_settings_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="crx my-3">
        <form id="filterForm" action="{{ route('class-report.filter') }}" method="POST" class="mb-4">
            @csrf
            <div class="crx-panel">
                <div class="crx-banner d-flex align-items-center gap-3">
                    <span class="crx-badge-icon"><i class="ti ti-report-analytics"></i></span>
                    <div>
                        <h3>Class Wise Report</h3>
                        <p>Pick a class to view, print or export its student list.</p>
                    </div>
                </div>
                <div class="crx-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 col-sm-12">
                            <label class="crx-label" for="standard">Standard</label>
                            <select id="standard" name="standard" class="form-control select2 report_filter">
                                <option value="">Select Standard</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std }}" {{ $filters['standard'] === $std ? 'selected' : '' }}>{{ $std }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <label class="crx-label" for="division">Division</label>
                            <select id="division" name="division" class="form-control select2 report_filter">
                                <option value="">All Divisions</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div }}" {{ $filters['division'] === $div ? 'selected' : '' }}>{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <button type="submit" name="clear" value="1" class="crx-btn-clear">
                                <i class="ti ti-rotate"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if ($filters['standard'])
            <div class="crx-panel">
                <div class="crx-banner">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="crx-badge-icon"><i class="ti ti-users-group"></i></span>
                            <div>
                                <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">Student List</p>
                                <h4>{{ $controller->classLabel($filters) }}</h4>
                            </div>
                        </div>
                        <div class="crx-stats">
                            <div class="crx-stat"><small>Standard</small><strong>{{ $filters['standard'] }}</strong></div>
                            <div class="crx-stat"><small>Division</small><strong>{{ $filters['division'] ?: 'All' }}</strong></div>
                            <div class="crx-stat is-accent"><small>Students</small><strong>{{ $students->count() }}</strong></div>
                        </div>
                    </div>
                </div>

                <div class="crx-body">
                    <div class="crx-table-wrap">
                        <table class="crx-table" id="classStudentTable">
                            <thead>
                                <tr>
                                    @foreach ($dataColumns as $key => $label)
                                        <th class="{{ $key === 'roll_no' ? 'text-center' : '' }}"
                                            @if ($key === 'roll_no') style="width:100px" @endif
                                            @if ($key === 'gr_no') style="width:140px" @endif>
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                    @foreach ($blankHeaders as $heading)
                                        <th class="extra-col" style="min-width:110px">{{ $heading ?: 'Column' }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $i => $s)
                                    <tr>
                                        @foreach ($dataColumns as $key => $label)
                                            <td class="{{ $key === 'roll_no' ? 'text-center' : '' }}">
                                                @if ($key === 'roll_no')
                                                    <span class="crx-roll {{ $s->roll_no ? '' : 'is-empty' }}">{{ $s->roll_no ?: '—' }}</span>
                                                @elseif ($key === 'student_name')
                                                    <span class="crx-name">{{ $controller->columnValue($s, $key) }}</span>
                                                @elseif ($key === 'gr_no')
                                                    <span class="crx-gr">{{ $controller->columnValue($s, $key) }}</span>
                                                @else
                                                    {{ $controller->columnValue($s, $key) }}
                                                @endif
                                            </td>
                                        @endforeach
                                        @foreach ($blankHeaders as $heading)
                                            <td class="extra-col"></td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr class="no-students">
                                        <td colspan="{{ max(1, $tableColCount) }}">
                                            <div class="crx-empty">
                                                <div class="crx-empty-icon"><i class="ti ti-user-off"></i></div>
                                                <h5>No students in this class</h5>
                                                <p>Try another Standard or Division.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="crx-panel">
                <div class="crx-empty">
                    <div class="crx-empty-icon"><i class="ti ti-list-search"></i></div>
                    <h5>Select a class to get started</h5>
                    <p>Choose <strong>Standard</strong> and <strong>Division</strong> above to see the student list.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Report Settings Modal --}}
    <div class="modal fade" id="classReportSettingsModal" tabindex="-1" aria-labelledby="classReportSettingsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('class-report.settings') }}" class="modal-content crx">
                @csrf
                <div class="modal-header crx-modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="crx-modal-icon-badge">
                            <i class="ti ti-adjustments-horizontal"></i>
                        </div>
                        <div>
                            <h5 class="modal-title crx-modal-title mb-0" id="classReportSettingsLabel">Report Settings</h5>
                            <div class="crx-modal-sub">Customize visible columns &amp; printable blank columns</div>
                        </div>
                    </div>
                    <button type="button" class="crx-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <div class="modal-body crx-modal-body">
                    {{-- Section 1: Show Columns --}}
                    <div class="crx-modal-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="crx-card-title">
                                <i class="ti ti-columns"></i>
                                <span>Show Columns</span>
                            </div>
                            <div class="crx-quick-links">
                                <button type="button" class="crx-link-btn" id="selectAllCols">
                                    <i class="ti ti-check"></i> Select All
                                </button>
                                <span class="crx-sep">•</span>
                                <button type="button" class="crx-link-btn" id="deselectAllCols">
                                    <i class="ti ti-x"></i> Deselect All
                                </button>
                            </div>
                        </div>
                        <div class="crx-card-desc">Select which student data fields appear in the table, print and Excel export.</div>

                        @php
                            $columnMeta = [
                                'roll_no'      => ['icon' => 'ti-numbers'],
                                'student_name' => ['icon' => 'ti-user'],
                                'gr_no'        => ['icon' => 'ti-id'],
                                'phone'        => ['icon' => 'ti-phone'],
                                'birth_date'   => ['icon' => 'ti-calendar'],
                            ];
                        @endphp

                        <div class="crx-col-grid">
                            @foreach ($availableColumns as $key => $label)
                                @php
                                    $icon = $columnMeta[$key]['icon'] ?? 'ti-file-text';
                                    $isOn = in_array($key, $filters['columns'], true);
                                @endphp
                                <label class="crx-col-check {{ $isOn ? 'is-on' : '' }}">
                                    <input type="checkbox" name="columns[]" value="{{ $key }}"
                                        {{ $isOn ? 'checked' : '' }} class="crx-checkbox-input">
                                    <div class="crx-col-info">
                                        <span class="crx-col-icon"><i class="ti {{ $icon }}"></i></span>
                                        <span class="crx-col-title">{{ $label }}</span>
                                    </div>
                                    <span class="crx-col-check-box">
                                        <i class="ti ti-check"></i>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Section 2: Extra Blank Columns --}}
                    <div class="crx-modal-card mb-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="crx-card-title">
                                <i class="ti ti-layout-columns"></i>
                                <span>Extra Blank Columns</span>
                            </div>
                            <span class="crx-tag-badge">For Print &amp; Marks</span>
                        </div>
                        <div class="crx-card-desc">Add empty columns on print and export for manual notes, signs, or grades.</div>

                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="crx-input-label" for="extra_columns">
                                    <i class="ti ti-hash"></i> Column Count
                                </label>
                                <div class="crx-field-wrap">
                                    <i class="ti ti-hash"></i>
                                    <input type="number" id="extra_columns" name="extra_columns" class="crx-modal-input" min="0"
                                        max="{{ \App\Http\Controllers\admin\ClassReportController::MAX_EXTRA_COLUMNS }}"
                                        placeholder="e.g. 2"
                                        value="{{ $filters['extra_columns'] ?: '' }}">
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <label class="crx-input-label" for="column_names">
                                    <i class="ti ti-tag"></i> Header Titles <span class="crx-label-hint">(Optional, comma separated)</span>
                                </label>
                                <div class="crx-field-wrap">
                                    <i class="ti ti-letter-case"></i>
                                    <input type="text" id="column_names" name="column_names" class="crx-modal-input"
                                        placeholder="e.g. Sign, Remark, Attendance"
                                        value="{{ $filters['column_names'] }}">
                                </div>
                            </div>
                        </div>

                        {{-- Quick Presets --}}
                        <div class="crx-presets-bar">
                            <span class="crx-presets-label"><i class="ti ti-sparkles"></i> Quick Presets:</span>
                            <div class="crx-preset-chips">
                                <button type="button" class="crx-chip" data-count="2" data-names="Sign, Remark">+ Sign &amp; Remark</button>
                                <button type="button" class="crx-chip" data-count="2" data-names="Attendance, Sign">+ Attendance &amp; Sign</button>
                                <button type="button" class="crx-chip" data-count="1" data-names="Remark">+ Remark</button>
                                <button type="button" class="crx-chip crx-chip-clear" id="clearExtraCols" title="Clear blank columns">
                                    <i class="ti ti-x"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer crx-modal-footer">
                    <button type="button" class="crx-btn-reset" id="resetReportColumns" title="Reset to standard default columns">
                        <i class="ti ti-rotate"></i>
                        <span>Reset Defaults</span>
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="crx-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="crx-btn-save">
                            <i class="ti ti-device-floppy"></i>
                            <span>Save Settings</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $(function() {
            var defaultColumns = @json(\App\Http\Controllers\admin\ClassReportController::DEFAULT_COLUMNS);
            var maxExtra = {{ \App\Http\Controllers\admin\ClassReportController::MAX_EXTRA_COLUMNS }};

            $('.report_filter').on('change', function() {
                $('#filterForm').submit();
            });

            function syncColCheckState() {
                $('#classReportSettingsModal input[name="columns[]"]').each(function() {
                    $(this).closest('.crx-col-check').toggleClass('is-on', this.checked);
                });
            }

            $('#classReportSettingsModal').on('change', 'input[name="columns[]"]', syncColCheckState);

            $('#selectAllCols').on('click', function() {
                $('#classReportSettingsModal input[name="columns[]"]').prop('checked', true);
                syncColCheckState();
            });

            $('#deselectAllCols').on('click', function() {
                $('#classReportSettingsModal input[name="columns[]"]').prop('checked', false);
                syncColCheckState();
            });

            $('.crx-chip[data-count]').on('click', function() {
                var count = $(this).data('count');
                var names = $(this).data('names');
                $('#extra_columns').val(count);
                $('#column_names').val(names);
                $('.crx-chip').removeClass('is-active');
                $(this).addClass('is-active');
            });

            $('#clearExtraCols').on('click', function() {
                $('#extra_columns').val('');
                $('#column_names').val('');
                $('.crx-chip').removeClass('is-active');
            });

            $('#resetReportColumns').on('click', function() {
                $('#classReportSettingsModal input[name="columns[]"]').each(function() {
                    $(this).prop('checked', defaultColumns.indexOf($(this).val()) !== -1);
                });
                $('#extra_columns').val('');
                $('#column_names').val('');
                $('.crx-chip').removeClass('is-active');
                syncColCheckState();
            });

            $('#classReportSettingsModal form').on('submit', function(e) {
                if (!$('#classReportSettingsModal input[name="columns[]"]:checked').length) {
                    e.preventDefault();
                    var msg = 'Please select at least one column.';
                    typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                    return false;
                }
            });

            function selectedColumns() {
                return $('#classReportSettingsModal input[name="columns[]"]:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            function extraColumnCount() {
                var n = parseInt($('#extra_columns').val(), 10);
                if (isNaN(n) || n < 0) n = 0;
                return Math.min(n, maxExtra);
            }

            function currentParams() {
                return {
                    standard: $('#standard').val() || '',
                    division: $('#division').val() || '',
                    columns: selectedColumns().join(','),
                    extra_columns: extraColumnCount(),
                    column_names: $('#column_names').val() || ''
                };
            }

            function postTo(url, target) {
                var $form = $('<form>', { method: 'POST', action: url, target: target }).hide();
                $form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
                $.each(currentParams(), function(name, value) {
                    $form.append($('<input>', { type: 'hidden', name: name, value: value }));
                });
                $form.appendTo('body').trigger('submit');
                setTimeout(function() { $form.remove(); }, 1000);
            }

            function hasStandard() {
                if ($('#standard').val()) return true;
                var msg = 'Please select Standard first.';
                typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                return false;
            }

            function openPrint(e) {
                e.preventDefault();
                if (!hasStandard()) return;
                postTo("{{ route('class-report.print') }}", '_blank');
            }

            function exportExcel(e) {
                e.preventDefault();
                if (!hasStandard()) return;
                postTo("{{ route('class-report.export-excel') }}", '_self');
            }

            $('#print_btn').on('click', openPrint);
            $('#exportExcelBtn').on('click', exportExcel);
        });
    </script>
@endsection
