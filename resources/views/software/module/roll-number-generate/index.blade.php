@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Generate Roll Number';
    $route = $modules['route'] ?? 'roll-number-generate';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .rng {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --gold: #f5b400;
            --line: #e1e4f2;
            --muted: #5f6488;
            --sky: #eef7fd;
            --red: #e1141c;
            --green: #28a745;
        }
        .rng-hero {
            background: linear-gradient(120deg, var(--navy), var(--navy-2));
            color: #fff;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .rng-hero h4 { margin: 0; font-weight: 800; color: #ffffff !important; }
        .rng-hero p { margin: 4px 0 0; color: rgba(255, 255, 255, 0.9) !important; font-size: .9rem; }
        .rng-year {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            padding: 8px 14px;
            text-align: right;
        }
        .rng-year small { display:block; color: var(--gold); font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .rng-year strong { color: #fff; }
        .rng-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 28px -18px rgba(31,37,128,.45);
        }
        .rng-label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .rng-table thead th {
            background: var(--navy) !important;
            color: #fff !important;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }
        .rng-table tbody td { vertical-align: middle; }
        .rng-roll-input {
            width: 85px;
            font-weight: 800;
            text-align: center;
            font-size: 1rem;
            color: var(--navy);
            border: 2px solid #bfe3f7;
            background: #f4faff;
            border-radius: 10px;
            padding: 6px 8px;
            transition: all .2s;
        }
        .rng-roll-input:focus {
            border-color: var(--navy);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(31,37,128,.15);
        }
        .rng-roll-input.is-dup {
            border-color: var(--red) !important;
            background: #fde8ea !important;
            color: var(--red) !important;
        }
        .rng-badge-curr {
            display: inline-flex;
            min-width: 32px;
            justify-content: center;
            background: #e9ecef;
            color: #495057;
            border-radius: 8px;
            padding: 3px 8px;
            font-weight: 700;
            font-size: .85rem;
        }
        .rng-badge-pending {
            background: #fff3cd;
            color: #856404;
            border-radius: 8px;
            padding: 3px 8px;
            font-weight: 600;
            font-size: .78rem;
        }
        .rng-gender-boy {
            background: #e7f1ff;
            color: #0d6efd;
            font-weight: 700;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: .78rem;
            white-space: nowrap;
            display: inline-block;
        }
        .rng-gender-girl {
            background: #fce8f3;
            color: #d63384;
            font-weight: 700;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: .78rem;
            white-space: nowrap;
            display: inline-block;
        }
        .rng-empty {
            text-align: center;
            color: var(--muted);
            padding: 35px 15px;
        }
        .rng-bar {
            background: #f8faff;
            border: 1px dashed #bfe3f7;
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        /* Dual Transfer Listbox styles (Matching user screenshot) */
        .rng-dual-wrap {
            width: 100%;
        }
        .rng-dual-container {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }
        @media (max-width: 768px) {
            .rng-dual-container {
                flex-direction: column;
            }
            .rng-dual-controls {
                flex-direction: row !important;
                margin: 6px 0;
            }
        }
        .rng-dual-col {
            flex: 1;
            min-width: 0;
        }
        .rng-dual-card {
            border: 1px solid #dcdfe6;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: border-color .2s;
        }
        .rng-dual-card:focus-within {
            border-color: #1976d2;
        }
        .rng-dual-header {
            padding: 8px 14px;
            background: #f8fafc;
            border-bottom: 1px solid #eef0f3;
            font-size: 0.8rem;
            font-weight: 700;
            color: #555b6d;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rng-dual-box {
            padding: 10px 12px;
            min-height: 95px;
            max-height: 140px;
            overflow-y: auto;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 6px;
            background: #ffffff;
        }
        .rng-dual-controls {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .rng-dual-btn {
            width: 44px;
            height: 36px;
            border-radius: 6px;
            border: none;
            font-size: 1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .rng-dual-btn-move-right {
            background: #1976d2;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(25, 118, 210, 0.35);
        }
        .rng-dual-btn-move-right:hover {
            background: #1565c0;
            transform: scale(1.05);
        }
        .rng-dual-btn-move-left {
            background: #e53935;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(229, 57, 53, 0.35);
        }
        .rng-dual-btn-move-left:hover {
            background: #d32f2f;
            transform: scale(1.05);
        }
        .rng-dual-btn:active {
            transform: scale(0.95);
        }
        .rng-dual-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: #ffffff;
            color: #1976d2;
            border: 1px solid #1e88e5;
            border-radius: 5px;
            font-size: 0.83rem;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
        }
        .rng-dual-item:hover {
            background: #e3f2fd;
            border-color: #1565c0;
            color: #1565c0;
        }
        .rng-dual-item.is-selected {
            background: #1976d2 !important;
            color: #ffffff !important;
            border-color: #1976d2 !important;
            box-shadow: 0 2px 5px rgba(25, 118, 210, 0.3);
        }
        .rng-dual-item.is-selected .item-text {
            color: #ffffff !important;
        }
        .rng-dual-item .priority-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #1976d2;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 800;
            margin-right: 2px;
        }
        .rng-dual-item.is-selected .priority-num {
            background: #ffffff;
            color: #1976d2;
        }
        .rng-dual-item .item-tools {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            margin-left: 6px;
            padding-left: 6px;
            border-left: 1px solid rgba(25, 118, 210, 0.25);
        }
        .rng-dual-item.is-selected .item-tools {
            border-left-color: rgba(255, 255, 255, 0.4);
        }
        .rng-dual-item .tool-btn {
            background: none;
            border: none;
            padding: 0 2px;
            color: inherit;
            font-size: 0.8rem;
            line-height: 1;
            cursor: pointer;
            opacity: 0.8;
        }
        .rng-dual-item .tool-btn:hover {
            opacity: 1;
            transform: scale(1.2);
        }
        .rng-preset-btn {
            font-size: 0.72rem;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #bfe3f7;
            background: #f4faff;
            color: #1976d2;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .rng-preset-btn:hover {
            background: #1976d2;
            color: #fff;
            border-color: #1976d2;
        }
        /* Modern Save Roll Numbers Action Bar & Button */
        .rng-bottom-bar {
            background: #ffffff;
            border-top: 1px solid #eef0f6;
            padding-top: 18px;
            margin-top: 20px;
        }
        .rng-save-hint {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #555b6d;
            font-size: 0.85rem;
        }
        .rng-btn-save-modern {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            border: 1px solid #059669;
            border-radius: 10px;
            padding: 11px 26px;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.38);
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            text-decoration: none;
            user-select: none;
        }
        .rng-btn-save-modern:hover:not(:disabled) {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-color: #047857;
            box-shadow: 0 8px 22px 0 rgba(16, 185, 129, 0.48);
            transform: translateY(-2px);
            color: #ffffff !important;
        }
        .rng-btn-save-modern:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px 0 rgba(16, 185, 129, 0.3);
        }
        .rng-btn-save-modern:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .rng-btn-save-modern .rng-icon-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            background: rgba(255, 255, 255, 0.22);
            border-radius: 6px;
            font-size: 1.05rem;
            transition: transform 0.2s;
        }
        .rng-btn-save-modern:hover:not(:disabled) .rng-icon-pill {
            transform: scale(1.1);
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
            'show_back_btn' => false,
        ])
    </div>

    <div class="rng my-3">
        {{-- Hero Header --}}
        <div class="rng-hero">
            <div>
                <h4>Generate Roll Number</h4>
                <p>Bulk sort students and auto-generate roll numbers by Surname, Name, Father Name, or GR No.</p>
            </div>
            @if ($currentYear)
                <div class="rng-year">
                    <small>Academic Year</small>
                    <strong>{{ $currentYear }}</strong>
                </div>
            @endif
        </div>

        {{-- Filter / Generation Options Form --}}
        <div class="rng-panel mb-3">
            <form method="POST" action="{{ route($route . '.filter') }}" id="filterForm" class="row g-3 align-items-end">
                @csrf
                
                {{-- Standard --}}
                <div class="col-md-4">
                    <label class="rng-label">Standard <span class="text-danger">*</span></label>
                    <select name="standard_id" id="standard_id" class="form-select select2" required>
                        <option value="">Select Standard</option>
                        @foreach ($standards as $std)
                            <option value="{{ $std->id }}" {{ (string) old('standard_id', $standardId) === (string) $std->id ? 'selected' : '' }}>
                                {{ $std->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Division --}}
                <div class="col-md-4">
                    <label class="rng-label">Division</label>
                    <select name="division" id="division" class="form-select select2">
                        <option value="">Select Division</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ old('division', $division) === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender --}}
                <div class="col-md-4">
                    <label class="rng-label">Gender</label>
                    <select name="gender_order" id="gender_order" class="form-select select2">
                        <option value="">Select Gender</option>
                        <option value="both" {{ old('gender_order', $genderOrder ?? 'both') === 'both' ? 'selected' : '' }}>Both</option>
                        <option value="male" {{ old('gender_order', $genderOrder) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender_order', $genderOrder) === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                {{-- Dual Transfer Panel for Order List By --}}
                <div class="col-12 mt-2">
                    @php
                        $allOrderOptions = [
                            'surname'     => 'Surname',
                            'name'        => 'Name',
                            'father_name' => 'Father Name',
                            'gr_no'       => 'GR No',
                        ];
                        $selectedKeys = array_values(array_filter(array_map('trim', explode(',', (string) $orderBy))));
                        $selectedKeys = array_values(array_intersect($selectedKeys, array_keys($allOrderOptions)));
                        $availableKeys = array_values(array_diff(array_keys($allOrderOptions), $selectedKeys));
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div>
                            <label class="rng-label mb-0">Order List By <span class="text-danger">*</span></label>
                            <small class="text-muted ms-2">Click items and use &gt;&gt; / &lt;&lt; or double-click to transfer</small>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="text-muted small me-1">Preset:</span>
                            <button type="button" class="rng-preset-btn" data-preset="surname,name,father_name">Surname &gt; Name &gt; Father Name</button>
                        </div>
                    </div>

                    <input type="hidden" name="order_by" id="order_by" value="{{ implode(',', $selectedKeys) }}">

                    <div class="rng-dual-wrap">
                        <div class="rng-dual-container">
                            {{-- Left Box (Available) --}}
                            <div class="rng-dual-col">
                                <div class="rng-dual-card">
                                    <div class="rng-dual-header">
                                        <span>Available Fields</span>
                                        <small class="text-muted count-available">({{ count($availableKeys) }})</small>
                                    </div>
                                    <div class="rng-dual-box rng-dual-box-left" id="dualBoxLeft">
                                        @foreach ($availableKeys as $key)
                                            <div class="rng-dual-item" data-key="{{ $key }}">
                                                <span class="item-text">{{ $allOrderOptions[$key] }}</span>
                                            </div>
                                        @endforeach
                                        <div class="rng-dual-empty text-muted small fst-italic py-2 {{ count($availableKeys) > 0 ? 'd-none' : '' }}">
                                            All fields added to sort order
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Center Controls --}}
                            <div class="rng-dual-controls">
                                <button type="button" class="rng-dual-btn rng-dual-btn-move-right" id="btnMoveRight" title="Move to Right Box (&gt;&gt;)">
                                    &gt;&gt;
                                </button>
                                <button type="button" class="rng-dual-btn rng-dual-btn-move-left" id="btnMoveLeft" title="Move to Left Box (&lt;&lt;)">
                                    &lt;&lt;
                                </button>
                            </div>

                            {{-- Right Box (Selected Order Priority) --}}
                            <div class="rng-dual-col">
                                <div class="rng-dual-card">
                                    <div class="rng-dual-header">
                                        <span>Selected Order Priority</span>
                                        <small class="text-muted count-selected">({{ count($selectedKeys) }})</small>
                                    </div>
                                    <div class="rng-dual-box rng-dual-box-right" id="dualBoxRight">
                                        @foreach ($selectedKeys as $idx => $key)
                                            <div class="rng-dual-item in-right" data-key="{{ $key }}">
                                                <span class="priority-num">{{ $idx + 1 }}</span>
                                                <span class="item-text">{{ $allOrderOptions[$key] }}</span>
                                                <div class="item-tools">
                                                    <button type="button" class="tool-btn btn-tool-up" title="Move Up"><i class="ti ti-chevron-up"></i></button>
                                                    <button type="button" class="tool-btn btn-tool-down" title="Move Down"><i class="ti ti-chevron-down"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="rng-dual-empty text-muted small fst-italic py-2 {{ count($selectedKeys) > 0 ? 'd-none' : '' }}">
                                            No fields selected. Click or use &gt;&gt; to add sort order.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary" id="btnGenerate">
                        <i class="ti ti-wand me-1"></i> Generate
                    </button>
                    <button type="submit" name="clear" value="1" class="btn btn-outline-secondary" formnovalidate>
                        Clear
                    </button>
                    @if ($standardId)
                        <a href="{{ route('roll-number-list.index') }}" class="btn btn-outline-info ms-auto">
                            <i class="ti ti-list-numbers me-1"></i> View Saved Roll List
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Students List / Generation Preview Table --}}
        <div class="rng-panel" id="rollListSection">
            @if (!$currentYear)
                <div class="rng-empty">
                    <i class="ti ti-alert-triangle text-warning" style="font-size:2.5rem;"></i>
                    <p class="mt-2 mb-0">Please set Current Academic Year in Academic Year Master first.</p>
                </div>
            @elseif (!$standardId)
                <div class="rng-empty">
                    <h5 class="mt-2 text-muted">Select Standard and click "Generate"</h5>
                    <p class="small text-muted mb-0">Set your sort criteria order and click Generate to see the generated roll numbers list below.</p>
                </div>
            @elseif (!empty($allAssigned))
                <div class="rng-empty py-5">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: #ecfdf5; color: #10b981;">
                            <i class="ti ti-circle-check fs-1"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">All Roll Numbers Already Generated</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 560px;">
                        All <strong>{{ $assignedCount }} student(s)</strong> in <strong>{{ $selectedStandard->name ?? '' }}{{ $division ? ' (Div ' . $division . ')' : '' }}</strong> already have their roll numbers assigned for Academic Year <strong>{{ $currentYear }}</strong> (Roll 1 to {{ $existingMaxRoll }}).<br>
                        When new students are admitted without roll numbers, they will appear here to be assigned the next sequential roll numbers (starting from Roll No <strong>{{ $existingMaxRoll + 1 }}</strong>).
                    </p>
                    @if (($missingGenderCount ?? 0) > 0)
                        <div class="alert alert-warning py-3 px-3 mx-auto mb-4 text-start border-warning" style="max-width: 650px;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="ti ti-alert-triangle text-warning fs-4"></i>
                                <strong class="text-dark">Notice: {{ $missingGenderCount }} student(s) in this class do not have Gender set</strong>
                            </div>
                            <p class="small text-muted mb-2">
                                Please update their Gender in Admission module so that future roll number generation and reports have complete student details.
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach ($missingGenderStudents->take(10) as $mStudent)
                                    <span class="badge bg-warning text-dark py-1 px-2" style="font-size: 0.78rem;">
                                        {{ $mStudent->first_name }} {{ $mStudent->last_name }}{{ $mStudent->gr_no ? ' (GR: ' . $mStudent->gr_no . ')' : '' }}
                                    </span>
                                @endforeach
                                @if ($missingGenderCount > 10)
                                    <span class="badge bg-light text-muted">+{{ $missingGenderCount - 10 }} more</span>
                                @endif
                            </div>
                            <a href="{{ route('admission.index') }}" class="btn btn-sm btn-warning text-dark fw-bold">
                                <i class="ti ti-edit me-1"></i> Update Gender in Admission Module
                            </a>
                        </div>
                    @endif
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('roll-number-list.index') }}" class="btn btn-primary px-4">
                            <i class="ti ti-list-numbers me-1"></i> View Saved Roll List
                        </a>
                    </div>
                </div>
            @elseif ($students->isEmpty())
                <div class="rng-empty">
                    <i class="ti ti-users-minus" style="font-size:2.5rem; color:#a5a9c7;"></i>
                    <h5 class="mt-2 text-muted">No students found</h5>
                    <p class="text-muted">
                        No students found for <strong>{{ $currentYear }}</strong> in
                        <strong>{{ $selectedStandard->name ?? '' }}{{ $division ? ' — ' . $division : '' }}</strong>.
                    </p>
                </div>
            @else
                {{-- Quick action bar & save form --}}
                <form method="POST" action="{{ route($route . '.store') }}" id="saveRollForm">
                    @csrf

                    @if (($assignedCount ?? 0) > 0)
                        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2">
                            <i class="ti ti-info-circle fs-5"></i>
                            <span>
                                <strong>{{ $assignedCount }} existing student(s)</strong> already have roll numbers (Roll 1 to {{ $existingMaxRoll }}).
                                Generating roll numbers for <strong>{{ $students->count() }} new student(s)</strong> starting from Roll No <strong>{{ $startFrom }}</strong>.
                            </span>
                        </div>
                    @endif

                    @if (($missingGenderCount ?? 0) > 0)
                        <div class="alert alert-danger py-3 px-3 mb-3 d-flex align-items-start gap-2 shadow-sm border-danger">
                            <i class="ti ti-alert-triangle fs-2 text-danger flex-shrink-0 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-danger mb-1">
                                    Cannot Generate Roll Numbers: Gender Missing for {{ $missingGenderCount }} Student(s)
                                </h6>
                                <p class="mb-2 text-dark" style="font-size: 0.9rem;">
                                    All students must have a <strong>Gender</strong> specified before roll numbers can be generated and saved.
                                    The following student(s) currently do not have gender set:
                                </p>
                                <div class="p-2 rounded bg-white border border-danger mb-2 d-flex flex-wrap gap-1">
                                    @foreach ($missingGenderStudents as $mStudent)
                                        <span class="badge bg-danger py-1 px-2" style="font-size: 0.8rem;">
                                            <i class="ti ti-user-x me-1"></i>
                                            {{ $mStudent->first_name }} {{ $mStudent->last_name }}{{ $mStudent->gr_no ? ' (GR: ' . $mStudent->gr_no . ')' : '' }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('admission.index') }}" class="btn btn-sm btn-danger fw-bold">
                                        <i class="ti ti-edit me-1"></i> Update Gender in Admission Module
                                    </a>
                                    <span class="text-muted small">Once updated, reload this page to generate roll numbers.</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="rng-bar">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="selectAll" checked>
                                <label class="form-check-label fw-bold" for="selectAll">Select All</label>
                            </div>
                            <span class="badge bg-label-primary px-3 py-2">
                                <strong>{{ $selectedStandard->name ?? '' }}</strong>
                                {{ $division ? ' | Div ' . $division : ' | All Divisions' }}
                                — <span id="studentCountBadge">{{ $students->count() }}</span> Students
                            </span>
                            @if (($startFrom ?? 1) > 1)
                                <span class="badge bg-label-info px-3 py-2" title="Sequencing continues from previous batch">
                                    <i class="ti ti-arrow-forward me-1"></i> Starts From: <strong>{{ $startFrom }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" id="btnResequence" class="btn btn-sm btn-outline-primary" data-start="{{ $startFrom ?? 1 }}">
                                <i class="ti ti-refresh me-1"></i> Re-sequence ({{ $startFrom ?? 1 }}, {{ ($startFrom ?? 1) + 1 }}, {{ ($startFrom ?? 1) + 2 }}...)
                            </button>
                        </div>
                    </div>

                    <div id="duplicateAlert" class="alert alert-danger d-none py-2 px-3 mb-3">
                        <i class="ti ti-alert-circle me-1"></i> Warning: Duplicate roll numbers detected! Each student must have a unique roll number.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover rng-table mb-0" id="rollTable">
                            <thead>
                                <tr>
                                    <th style="width:40px;" class="text-center">#</th>
                                    <th style="width:50px;" class="text-center">Sr</th>
                                    <th style="width:120px;" class="text-center">New Roll No</th>
                                    <th style="width:100px;" class="text-center">Current Roll</th>
                                    <th style="width:110px;">GR No</th>
                                    <th>Student Name</th>
                                    <th style="width:90px;" class="text-center">Gender</th>
                                    <th style="width:100px;">Division</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $proposed = $startFrom ?? 1;
                                @endphp
                                @foreach ($students as $index => $student)
                                    @php
                                        $currRoll = $student->roll_no;
                                        $isMale = strtolower(trim((string)$student->gender)) === 'male';
                                        $isFemale = strtolower(trim((string)$student->gender)) === 'female';
                                    @endphp
                                    <tr class="student-row" data-id="{{ $student->id }}" data-curr-roll="{{ $currRoll ?: '' }}">
                                        {{-- Checkbox --}}
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input student-check" 
                                                name="students[{{ $student->id }}][selected]" 
                                                value="1" checked>
                                        </td>
                                        {{-- Sr --}}
                                        <td class="text-center fw-bold text-muted sr-no">{{ $index + 1 }}</td>
                                        {{-- Proposed New Roll Number --}}
                                        <td class="text-center">
                                            <input type="number" min="1" 
                                                name="students[{{ $student->id }}][roll_no]" 
                                                class="rng-roll-input roll-input" 
                                                value="{{ $proposed }}" required>
                                        </td>
                                        {{-- Current Roll No --}}
                                        <td class="text-center">
                                            @if (!empty($currRoll))
                                                <span class="rng-badge-curr">{{ $currRoll }}</span>
                                            @else
                                                <span class="rng-badge-pending">Pending</span>
                                            @endif
                                        </td>
                                        {{-- GR No --}}
                                        <td><strong>{{ $student->gr_no ?: '—' }}</strong></td>
                                        {{-- Student Name (Always: Surname - Student Name - Father Name) --}}
                                        <td>
                                            <span class="fw-bold text-dark">{{ $student->first_name }}</span>
                                            <span class="text-primary fw-semibold">{{ $student->last_name }}</span>
                                            <span class="text-muted">{{ $student->father_name }}</span>
                                        </td>
                                        {{-- Gender --}}
                                        <td class="text-center">
                                            @if ($isMale)
                                                <span class="rng-gender-boy">Male</span>
                                            @elseif ($isFemale)
                                                <span class="rng-gender-girl">Female</span>
                                            @else
                                                <span class="badge bg-danger py-1 px-2 fw-bold" title="Gender is required before generating roll numbers">
                                                    <i class="ti ti-alert-circle me-1"></i>Missing Gender
                                                </span>
                                            @endif
                                        </td>
                                        {{-- Division --}}
                                        <td>{{ $student->division ?: '—' }}</td>
                                    </tr>
                                    @php
                                        $proposed++;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="rng-btn-save-modern" id="btnSubmitRollsBottom"
                            {{ ($missingGenderCount ?? 0) > 0 ? 'disabled' : '' }}
                            @if (($missingGenderCount ?? 0) > 0) title="All students must have Gender specified before roll numbers can be saved" @endif>
                            <span class="rng-icon-pill"><i class="ti ti-device-floppy"></i></span>
                            <span>Save Roll Numbers</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@section('page_leavel_script')
<script>
    $(function() {
        if ($.fn.select2) {
            $('#standard_id, #division, #gender_order').select2({ width: '100%' });
        }

        // --- Dual Transfer Listbox for Order List By ---
        var allOrderOptions = {
            'surname': 'Surname',
            'name': 'Name',
            'father_name': 'Father Name',
            'gr_no': 'GR No'
        };

        function renderItem(key, inRight, priority) {
            var label = allOrderOptions[key] || key;
            if (inRight) {
                return '<div class="rng-dual-item in-right" data-key="' + key + '">' +
                    '<span class="priority-num">' + (priority || 1) + '</span>' +
                    '<span class="item-text">' + label + '</span>' +
                    '<div class="item-tools">' +
                        '<button type="button" class="tool-btn btn-tool-up" title="Move Up"><i class="ti ti-chevron-up"></i></button>' +
                        '<button type="button" class="tool-btn btn-tool-down" title="Move Down"><i class="ti ti-chevron-down"></i></button>' +
                    '</div>' +
                '</div>';
            } else {
                return '<div class="rng-dual-item" data-key="' + key + '">' +
                    '<span class="item-text">' + label + '</span>' +
                '</div>';
            }
        }

        function syncOrderBy() {
            var selectedKeys = [];
            var $rightItems = $('#dualBoxRight .rng-dual-item');
            $rightItems.each(function(index) {
                var key = $(this).data('key');
                selectedKeys.push(key);
                $(this).addClass('in-right');

                var $num = $(this).find('.priority-num');
                if ($num.length) {
                    $num.text(index + 1);
                } else {
                    $(this).prepend('<span class="priority-num">' + (index + 1) + '</span>');
                }

                if (!$(this).find('.item-tools').length) {
                    $(this).append('<div class="item-tools">' +
                        '<button type="button" class="tool-btn btn-tool-up" title="Move Up"><i class="ti ti-chevron-up"></i></button>' +
                        '<button type="button" class="tool-btn btn-tool-down" title="Move Down"><i class="ti ti-chevron-down"></i></button>' +
                    '</div>');
                }
            });

            var leftCount = $('#dualBoxLeft .rng-dual-item').length;
            var rightCount = $rightItems.length;

            $('#dualBoxLeft .rng-dual-empty').toggleClass('d-none', leftCount > 0);
            $('#dualBoxRight .rng-dual-empty').toggleClass('d-none', rightCount > 0);
            $('.count-available').text('(' + leftCount + ')');
            $('.count-selected').text('(' + rightCount + ')');

            $('#order_by').val(selectedKeys.join(','));
        }

        // Toggle selection highlight on click
        $(document).on('click', '.rng-dual-item', function(e) {
            if ($(e.target).closest('.tool-btn').length) {
                return;
            }
            $(this).toggleClass('is-selected');
        });

        // Double click to instantly transfer
        $(document).on('dblclick', '#dualBoxLeft .rng-dual-item', function() {
            var key = $(this).data('key');
            $(this).remove();
            $('#dualBoxRight').append(renderItem(key, true, $('#dualBoxRight .rng-dual-item').length + 1));
            syncOrderBy();
        });

        $(document).on('dblclick', '#dualBoxRight .rng-dual-item', function(e) {
            if ($(e.target).closest('.tool-btn').length) return;
            var key = $(this).data('key');
            $(this).remove();
            $('#dualBoxLeft').append(renderItem(key, false));
            syncOrderBy();
        });

        // Move Up in priority sequence
        $(document).on('click', '.btn-tool-up', function(e) {
            e.stopPropagation();
            var $item = $(this).closest('.rng-dual-item');
            var $prev = $item.prev('.rng-dual-item');
            if ($prev.length) {
                $item.insertBefore($prev);
                syncOrderBy();
            }
        });

        // Move Down in priority sequence
        $(document).on('click', '.btn-tool-down', function(e) {
            e.stopPropagation();
            var $item = $(this).closest('.rng-dual-item');
            var $next = $item.next('.rng-dual-item');
            if ($next.length) {
                $item.insertAfter($next);
                syncOrderBy();
            }
        });

        // >> Button: Move Selected to Right Box
        $('#btnMoveRight').on('click', function() {
            var $selected = $('#dualBoxLeft .rng-dual-item.is-selected');
            if (!$selected.length) {
                $selected = $('#dualBoxLeft .rng-dual-item');
            }
            $selected.each(function() {
                var key = $(this).data('key');
                $(this).remove();
                $('#dualBoxRight').append(renderItem(key, true, $('#dualBoxRight .rng-dual-item').length + 1));
            });
            syncOrderBy();
        });

        // << Button: Move Selected to Left Box
        $('#btnMoveLeft').on('click', function() {
            var $selected = $('#dualBoxRight .rng-dual-item.is-selected');
            if (!$selected.length) {
                $selected = $('#dualBoxRight .rng-dual-item');
            }
            $selected.each(function() {
                var key = $(this).data('key');
                $(this).remove();
                $('#dualBoxLeft').append(renderItem(key, false));
            });
            syncOrderBy();
        });

        // Presets button click
        $('.rng-preset-btn').on('click', function() {
            var preset = $(this).data('preset');
            var keys = preset.split(',');
            $('#dualBoxLeft .rng-dual-item').remove();
            $('#dualBoxRight .rng-dual-item').remove();

            var allKeys = ['surname', 'name', 'father_name', 'gr_no'];
            keys.forEach(function(k, idx) {
                if (allOrderOptions[k]) {
                    $('#dualBoxRight').append(renderItem(k, true, idx + 1));
                }
            });
            allKeys.forEach(function(k) {
                if (keys.indexOf(k) === -1) {
                    $('#dualBoxLeft').append(renderItem(k, false));
                }
            });
            syncOrderBy();
        });

        var isClearBtn = false;
        $('button[name="clear"]').on('click', function() {
            isClearBtn = true;
        });

        // Prevent filter submit if no order criteria
        $('#filterForm').on('submit', function(e) {
            if (isClearBtn) {
                return true;
            }
            var val = ($('#order_by').val() || '').trim();
            if (!val) {
                e.preventDefault();
                alert('Please select at least one field in "Order List By" before generating.');
                return false;
            }
            $('#btnGenerate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generating...');
        });

        @if ($standardId && $students->isNotEmpty())
            $('html, body').animate({
                scrollTop: $('#rollListSection').offset().top - 80
            }, 300);
        @endif

        // Select All / Deselect All
        $('#selectAll').on('change', function() {
            var checked = $(this).is(':checked');
            $('.student-check').prop('checked', checked).trigger('change');
        });

        // Update row styling and disabled state when unchecked
        $(document).on('change', '.student-check', function() {
            var $row = $(this).closest('tr');
            var $input = $row.find('.roll-input');
            if ($(this).is(':checked')) {
                $row.removeClass('table-light opacity-50');
                $input.prop('disabled', false);
            } else {
                $row.addClass('table-light opacity-50');
                $input.prop('disabled', true);
            }
            checkDuplicates();
        });

        // Quick Re-sequence button
        $('#btnResequence').on('click', function() {
            var start = parseInt($(this).data('start') || '1', 10);
            if (isNaN(start) || start < 1) start = 1;
            $('.student-row').each(function() {
                var $chk = $(this).find('.student-check');
                if ($chk.is(':checked')) {
                    $(this).find('.roll-input').val(start);
                    start++;
                }
            });
            checkDuplicates();
        });

        // Duplicate detection
        function checkDuplicates() {
            var seen = {};
            var hasDup = false;
            $('.roll-input').removeClass('is-dup');

            $('.student-row').each(function() {
                var $chk = $(this).find('.student-check');
                if ($chk.is(':checked')) {
                    var $input = $(this).find('.roll-input');
                    var val = parseInt($input.val(), 10);
                    if (val) {
                        if (seen[val]) {
                            hasDup = true;
                            $input.addClass('is-dup');
                            seen[val].addClass('is-dup');
                        } else {
                            seen[val] = $input;
                        }
                    }
                }
            });

            $('#duplicateAlert').toggleClass('d-none', !hasDup);
            var missingGender = {{ ($missingGenderCount ?? 0) }};
            $('#btnSubmitRolls, #btnSubmitRollsBottom').prop('disabled', hasDup || missingGender > 0);
            return hasDup;
        }

        $(document).on('input', '.roll-input', function() {
            checkDuplicates();
        });

        $('#saveRollForm').on('submit', function(e) {
            var missingGender = {{ ($missingGenderCount ?? 0) }};
            if (missingGender > 0) {
                e.preventDefault();
                alert('All students must have Gender specified before roll numbers can be generated. Please update gender in Admission first.');
                return false;
            }
            if (checkDuplicates()) {
                e.preventDefault();
                alert('Please resolve duplicate roll numbers before saving.');
                return false;
            }
            var selectedCount = $('.student-check:checked').length;
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one student.');
                return false;
            }
            $('#btnSubmitRolls, #btnSubmitRollsBottom').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        });
    });
</script>
@endsection
