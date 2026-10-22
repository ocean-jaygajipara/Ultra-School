@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Class Promotion';
    $route = $modules['route'] ?? 'class-promotion';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .cpx {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --gold: #f5b400;
            --line: #e1e4f2;
            --muted: #5f6488;
            --sky: #eef7fd;
            --red: #e1141c;
            --green: #28a745;
        }
        .cpx-hero {
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
        .cpx-hero h4 { margin: 0; font-weight: 800; color: #ffffff !important; }
        .cpx-hero p { margin: 4px 0 0; color: rgba(255, 255, 255, 0.9) !important; font-size: .9rem; }
        .cpx-year {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            padding: 8px 14px;
            text-align: right;
        }
        .cpx-year small {
            display: block; color: var(--gold); font-size: .68rem;
            font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        }
        .cpx-year strong { color: #fff; }
        .cpx-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 28px -18px rgba(31,37,128,.45);
            margin-bottom: 16px;
        }
        .cpx-label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .cpx-note {
            background: #fff8e6;
            border: 1px solid #f5b400;
            color: #5a4200;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: .88rem;
            margin-bottom: 16px;
        }
        .cpx-table thead th {
            background: var(--navy) !important;
            color: #fff !important;
            font-weight: 700;
            white-space: nowrap;
            vertical-align: middle;
        }
        .cpx-table tbody td {
            vertical-align: middle;
        }
        .cpx-empty { text-align: center; color: var(--muted); padding: 28px 12px; }
        .cpx-arrow {
            display: flex; align-items: center; justify-content: center;
            color: var(--navy); font-size: 1.5rem; padding-top: 28px;
        }

        /* Roll input styling like roll-number-generate */
        .rng-roll-input {
            width: 85px;
            font-weight: 800;
            text-align: center;
            font-size: 0.95rem;
            color: var(--navy);
            border: 2px solid #bfe3f7;
            background: #f4faff;
            border-radius: 8px;
            padding: 5px 8px;
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

        /* Dual Transfer Listbox styles (Matching roll-number-generate) */
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

        /* Roll Generator Toolbar */
        .cpx-gen-toolbar {
            background: #f8faff;
            border: 1px dashed #bfe3f7;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 16px;
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

    <div class="cpx my-3">
        {{-- Hero Header --}}
        <div class="cpx-hero">
            <div>
                <h4>Class Promotion</h4>
                <p>Move students from one Standard / Division to the next with order-wise roll number generation.</p>
            </div>
            <div class="cpx-year">
                <small>Academic Year</small>
                <strong>{{ $currentYear?->name ?? 'Not set' }}</strong>
            </div>
        </div>

        @if (!$currentYear)
            <div class="alert alert-danger">Please set Current Academic Year in Academic Year Master before promotion.</div>
        @else
            <div class="cpx-note">
                <i class="ti ti-info-circle me-1"></i>
                Current Academic Year: <strong>{{ $currentYear->name }}</strong>.
                Class promotion promotes students into the <strong>Current Academic Year</strong>. If your students are already in <strong>{{ $currentYear->name }}</strong> and you wish to promote them to the next standard (e.g., from 3rd to 4th), please first set the new Academic Year as <strong>Current</strong> in Academic Year Master.
            </div>
        @endif

        {{-- Filter / Load Students Form --}}
        <div class="cpx-panel">
            <form method="POST" action="{{ route($route . '.filter') }}" id="filterForm" class="row g-3 align-items-end">
                @csrf
                
                {{-- From Standard --}}
                <div class="col-md-3">
                    <label class="cpx-label">From Standard <span class="text-danger">*</span></label>
                    <select name="from_standard_id" id="from_standard_id" class="form-select select2" required>
                        <option value="">Select Standard</option>
                        @foreach ($standards as $std)
                            <option value="{{ $std->id }}" {{ (string) $fromStandardId === (string) $std->id ? 'selected' : '' }}>
                                {{ $std->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- From Division --}}
                <div class="col-md-2">
                    <label class="cpx-label">From Division</label>
                    <select name="from_division" id="from_division" class="form-select select2">
                        <option value="">All</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $fromDivision === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 cpx-arrow d-none d-md-flex"><i class="ti ti-arrow-right"></i></div>

                {{-- To Standard --}}
                <div class="col-md-3">
                    <label class="cpx-label">To Standard <span class="text-danger">*</span></label>
                    <select name="to_standard_id" id="to_standard_id" class="form-select select2">
                        <option value="">Select Standard</option>
                        @foreach ($standards as $std)
                            <option value="{{ $std->id }}" {{ (string) $toStandardId === (string) $std->id ? 'selected' : '' }}>
                                {{ $std->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- To Division --}}
                <div class="col-md-3">
                    <label class="cpx-label">To Division</label>
                    <select name="to_division" id="to_division" class="form-select select2">
                        <option value="">Keep Same</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $toDivision === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Gender Order --}}
                <div class="col-md-3">
                    <label class="cpx-label">Gender Order</label>
                    <select name="gender_order" id="gender_order" class="form-select select2">
                        <option value="both" {{ ($genderOrder ?? 'both') === 'both' ? 'selected' : '' }}>Both</option>
                        <option value="male" {{ ($genderOrder ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ ($genderOrder ?? '') === 'female' ? 'selected' : '' }}>Female</option>
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
                            'old_roll'    => 'Old Roll No',
                        ];
                        $selectedKeys = array_values(array_filter(array_map('trim', explode(',', (string) $orderBy))));
                        $selectedKeys = array_values(array_intersect($selectedKeys, array_keys($allOrderOptions)));
                        if (empty($selectedKeys)) {
                            $selectedKeys = ['surname', 'name', 'father_name'];
                        }
                        $availableKeys = array_values(array_diff(array_keys($allOrderOptions), $selectedKeys));
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div>
                            <label class="cpx-label mb-0">Order List By <span class="text-danger">*</span></label>
                            <small class="text-muted ms-2">Click items and use &gt;&gt; / &lt;&lt; or double-click to transfer order</small>
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

                {{-- Action Buttons --}}
                <div class="col-md-12 d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary" id="btnLoadStudents">
                        <i class="ti ti-search me-1"></i> Load Students
                    </button>
                    <button type="submit" name="clear" value="1" class="btn btn-outline-secondary" formnovalidate>
                        Clear
                    </button>
                </div>
            </form>
        </div>

        {{-- Promotion Panel --}}
        <div class="cpx-panel" id="promotionSection">
            @if (!$fromStandardId)
                <div class="cpx-empty">Select From Standard and click Load Students.</div>
            @elseif ($students->isEmpty())
                @if (($alreadyInCurrentYearCount ?? 0) > 0)
                    <div class="cpx-empty py-4 text-center">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 68px; height: 68px; background: #fff8e6; border: 2px solid #f5b400; color: #b45309;">
                                <i class="ti ti-calendar-exclamation fs-1"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Academic Year Update Required Before Promotion</h4>
                        <p class="text-muted mb-3 mx-auto" style="max-width: 640px; font-size: 0.96rem; line-height: 1.6;">
                            All <strong>{{ $alreadyInCurrentYearCount }} student(s)</strong> in <strong>{{ $fromStandard->name ?? '' }}{{ $fromDivision ? ' (Div ' . $fromDivision . ')' : '' }}</strong> are currently enrolled in Academic Year <strong>{{ $currentYear->name }}</strong>.<br>
                            To promote them to <strong>{{ $toStandard->name ?? 'the next standard' }}</strong>, please first set your upcoming Academic Year (e.g. the next school year) as <strong>"Current"</strong> in Academic Year Master.
                        </p>
                        <a href="{{ route('academic-year.index') }}" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="ti ti-calendar-event me-1"></i> Go to Academic Year Master
                        </a>
                    </div>
                @else
                    <div class="cpx-empty">No students found in {{ $fromStandard->name ?? '' }}{{ $fromDivision ? ' / ' . $fromDivision : '' }}.</div>
                @endif
            @else
                <form method="POST" action="{{ route($route . '.promote') }}" id="promoteForm"
                      onsubmit="return confirmPromote();">
                    @csrf
                    <input type="hidden" name="from_standard_id" value="{{ $fromStandardId }}">
                    <input type="hidden" name="from_division" value="{{ $fromDivision }}">
                    <input type="hidden" name="to_standard_id" id="promote_to_standard_id" value="{{ $toStandardId }}">
                    <input type="hidden" name="to_division" id="promote_to_division" value="{{ $toDivision }}">

                    {{-- Top Status Bar --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <strong style="color:#1f2580; font-size: 1.05rem;">
                                {{ $fromStandard->name ?? '' }}{{ $fromDivision ? ' — ' . $fromDivision : '' }}
                                <i class="ti ti-arrow-right mx-1 text-muted"></i>
                                {{ $toStandard->name ?? 'Select To Standard' }}{{ $toDivision ? ' — ' . $toDivision : '' }}
                            </strong>
                            <span class="badge bg-label-primary ms-2">{{ $students->count() }} students</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success fw-bold px-3" id="btnPromoteTop"
                                {{ !$currentYear || !$toStandardId || empty($modules['permission_create']) ? 'disabled' : '' }} 
                                @if(empty($modules['permission_create'])) title="You do not have permission to promote students." @endif>
                                <i class="ti ti-arrows-up-right me-1"></i> Promote Selected
                            </button>
                        </div>
                    </div>

                    {{-- Order-Wise Roll Generator Toolbar (Matching roll-number-generate experience) --}}
                    <div class="cpx-gen-toolbar">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-auto">
                                <span class="fw-bold text-primary d-inline-flex align-items-center gap-1">
                                    <i class="ti ti-wand fs-5"></i> Roll Generator:
                                </span>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="small text-muted mb-1 d-block fw-semibold">Order By</label>
                                <select id="quick_order_by" class="form-select form-select-sm">
                                    <option value="surname,name,father_name" {{ $orderBy === 'surname,name,father_name' ? 'selected' : '' }}>Surname &gt; Name &gt; Father Name</option>
                                    <option value="name,surname,father_name" {{ $orderBy === 'name,surname,father_name' ? 'selected' : '' }}>Name &gt; Surname &gt; Father Name</option>
                                    <option value="old_roll" {{ $orderBy === 'old_roll' ? 'selected' : '' }}>Old Roll Number</option>
                                    <option value="gr_no" {{ $orderBy === 'gr_no' ? 'selected' : '' }}>GR Number</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label class="small text-muted mb-1 d-block fw-semibold">Gender Order</label>
                                <select id="quick_gender_order" class="form-select form-select-sm">
                                    <option value="both" {{ ($genderOrder ?? 'both') === 'both' ? 'selected' : '' }}>Both</option>
                                    <option value="male" {{ ($genderOrder ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ ($genderOrder ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label class="small text-muted mb-1 d-block fw-semibold">Start From Roll No</label>
                                <input type="number" id="quick_start_from" class="form-control form-control-sm text-center fw-bold" value="1" min="1" step="1">
                            </div>
                            <div class="col-md-auto col-sm-6 d-flex align-items-end gap-1 pt-3 pt-md-0">
                                <button type="button" id="btnQuickGenerate" class="btn btn-sm btn-primary fw-bold px-3">
                                    <i class="ti ti-bolt me-1"></i> Generate Rolls
                                </button>
                                <button type="button" id="btnQuickAutoSeq" class="btn btn-sm btn-outline-secondary" title="Re-sequence 1, 2, 3... without reordering rows">
                                    <i class="ti ti-refresh me-1"></i> 1..n
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Duplicate Alert --}}
                    <div id="duplicateAlert" class="alert alert-danger d-none py-2 px-3 mb-3">
                        <i class="ti ti-alert-circle me-1"></i> Warning: Duplicate New Roll No detected! Each student must have a unique roll number.
                    </div>

                    {{-- Students Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover cpx-table mb-0" id="studentsTable">
                            <thead>
                                <tr>
                                    <th style="width:45px;" class="text-center">
                                        <input type="checkbox" id="checkAll" checked>
                                    </th>
                                    <th style="width:50px;" class="text-center">Sr</th>
                                    <th style="width:115px;" class="text-center">New Roll No</th>
                                    <th style="width:90px;" class="text-center">Old Roll</th>
                                    <th style="width:110px;">GR No</th>
                                    <th>Student Name</th>
                                    <th style="width:90px;" class="text-center">Gender</th>
                                    <th style="width:100px;">Standard</th>
                                    <th style="width:90px;">Division</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    @php
                                        $currRoll = $student->roll_no;
                                        $isMale = strtolower(trim((string)$student->gender)) === 'male';
                                        $isFemale = strtolower(trim((string)$student->gender)) === 'female';
                                    @endphp
                                    <tr class="student-row"
                                        data-id="{{ $student->id }}"
                                        data-first-name="{{ strtolower(trim($student->first_name ?? '')) }}"
                                        data-last-name="{{ strtolower(trim($student->last_name ?? '')) }}"
                                        data-father-name="{{ strtolower(trim($student->father_name ?? '')) }}"
                                        data-gr-no="{{ (int) preg_replace('/\D/', '', (string)($student->gr_no ?? '0')) }}"
                                        data-old-roll="{{ (int) ($student->roll_no ?: 999999) }}"
                                        data-gender="{{ strtolower(trim($student->gender ?? 'male')) }}">
                                        
                                        {{-- Checkbox --}}
                                        <td class="text-center">
                                            <input type="checkbox" class="student-check" name="student_ids[]"
                                                   value="{{ $student->id }}" checked>
                                        </td>

                                        {{-- Sr --}}
                                        <td class="text-center fw-bold text-muted sr-col">{{ $index + 1 }}</td>

                                        {{-- New Roll No --}}
                                        <td class="text-center">
                                            <input type="number" min="1" step="1"
                                                   name="roll_nos[{{ $student->id }}]"
                                                   class="form-control form-control-sm rng-roll-input roll-input mx-auto"
                                                   value="{{ $loop->iteration }}"
                                                   required>
                                        </td>

                                        {{-- Old Roll --}}
                                        <td class="text-center">
                                            @if (!empty($currRoll))
                                                <span class="rng-badge-curr">{{ $currRoll }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- GR No --}}
                                        <td><strong>{{ $student->gr_no ?: '—' }}</strong></td>

                                        {{-- Student Name (Surname - Name - Father Name) --}}
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
                                                <span class="text-muted">{{ $student->gender ?: '—' }}</span>
                                            @endif
                                        </td>

                                        {{-- Standard --}}
                                        <td>{{ $student->current_std ?: '—' }}</td>

                                        {{-- Division --}}
                                        <td>{{ $student->division ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Bottom Promote Button --}}
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success btn-lg fw-bold px-4" id="btnPromoteBottom"
                            {{ !$currentYear || !$toStandardId || empty($modules['permission_create']) ? 'disabled' : '' }}>
                            <i class="ti ti-arrows-up-right me-1"></i> Promote Selected (<span class="selected-count-badge">{{ $students->count() }}</span>)
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@section('page_leavel_script')
<script>
    function checkDuplicates() {
        var seen = {};
        var hasDup = false;
        $('.roll-input').removeClass('is-dup');

        $('.student-row').each(function() {
            var $chk = $(this).find('.student-check');
            if ($chk.is(':checked')) {
                var $input = $(this).find('.roll-input');
                var val = parseInt($input.val(), 10);
                if (val && !isNaN(val)) {
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
        $('#btnPromoteTop, #btnPromoteBottom').prop('disabled', hasDup);
        return hasDup;
    }

    function updateSelectedCount() {
        var total = $('.student-check').length;
        var checked = $('.student-check:checked').length;
        $('.selected-count-badge').text(checked);
        $('#checkAll').prop('checked', total > 0 && total === checked);
    }

    function confirmPromote() {
        $('#promote_to_standard_id').val($('#to_standard_id').val());
        $('#promote_to_division').val($('#to_division').val());

        var toStd = $('#promote_to_standard_id').val();
        if (!toStd) {
            toastr.error('Please select To Standard.');
            return false;
        }
        var checked = $('.student-check:checked');
        if (!checked.length) {
            toastr.error('Please select at least one student.');
            return false;
        }

        if (checkDuplicates()) {
            toastr.error('Duplicate New Roll No found. Each roll must be unique.');
            return false;
        }

        var missing = false;
        checked.each(function() {
            var input = $(this).closest('tr').find('.roll-input');
            var val = $.trim(input.val());
            if (!val || parseInt(val, 10) < 1) {
                missing = true;
                input.focus();
                return false;
            }
        });
        if (missing) {
            toastr.error('Please enter New Roll No for all selected students.');
            return false;
        }

        // Disable unchecked roll inputs so they are not posted
        $('.student-check:not(:checked)').closest('tr').find('.roll-input').prop('disabled', true);

        var fromName = $('#from_standard_id option:selected').text();
        var toName = $('#to_standard_id option:selected').text();
        return confirm('Promote ' + checked.length + ' student(s) from ' + fromName.trim() + ' to ' + toName.trim() + ' with the entered roll numbers?');
    }

    $(function() {
        if ($.fn.select2) {
            $('#from_standard_id, #from_division, #to_standard_id, #to_division, #gender_order').select2({ width: '100%' });
        }

        $('#to_standard_id').on('change', function() {
            $('#promote_to_standard_id').val($(this).val());
        });
        $('#to_division').on('change', function() {
            $('#promote_to_division').val($(this).val());
        });

        // --- Dual Transfer Listbox for Order List By ---
        var allOrderOptions = {
            'surname': 'Surname',
            'name': 'Name',
            'father_name': 'Father Name',
            'gr_no': 'GR No',
            'old_roll': 'Old Roll No'
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

            var allKeys = ['surname', 'name', 'father_name', 'gr_no', 'old_roll'];
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

        // Checkbox events
        $('#checkAll').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.student-check').prop('checked', isChecked).trigger('change');
        });

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
            updateSelectedCount();
            checkDuplicates();
        });

        $(document).on('input', '.roll-input', function() {
            checkDuplicates();
        });

        // Dynamic Order-Wise Roll Generator
        $('#btnQuickGenerate').on('click', function() {
            var orderBy = $('#quick_order_by').val() || 'surname,name,father_name';
            var genderOrder = $('#quick_gender_order').val() || 'both';
            var startFrom = parseInt($('#quick_start_from').val(), 10) || 1;
            if (startFrom < 1) startFrom = 1;

            // Filter rows by gender (Both / Male / Female)
            $('.student-row').each(function() {
                var g = $(this).data('gender') || '';
                if (genderOrder === 'male' && g !== 'male') {
                    $(this).hide().find('.student-check').prop('checked', false).trigger('change');
                } else if (genderOrder === 'female' && g !== 'female') {
                    $(this).hide().find('.student-check').prop('checked', false).trigger('change');
                } else {
                    $(this).show().find('.student-check').prop('checked', true).trigger('change');
                }
            });

            var $tbody = $('#studentsTable tbody');
            var rows = $tbody.find('.student-row').get();

            // Sort rows in DOM based on chosen criteria
            rows.sort(function(a, b) {
                var $a = $(a);
                var $b = $(b);

                // Criteria sort
                var criteria = orderBy.split(',');
                for (var i = 0; i < criteria.length; i++) {
                    var crit = $.trim(criteria[i]);
                    if (crit === 'surname') {
                        var cmp = ($a.data('first-name') || '').localeCompare($b.data('first-name') || '');
                        if (cmp !== 0) return cmp;
                    } else if (crit === 'name') {
                        var cmp = ($a.data('last-name') || '').localeCompare($b.data('last-name') || '');
                        if (cmp !== 0) return cmp;
                    } else if (crit === 'father_name') {
                        var cmp = ($a.data('father-name') || '').localeCompare($b.data('father-name') || '');
                        if (cmp !== 0) return cmp;
                    } else if (crit === 'gr_no') {
                        var numA = parseInt($a.data('gr-no') || '0', 10);
                        var numB = parseInt($b.data('gr-no') || '0', 10);
                        if (numA !== numB) return numA - numB;
                    } else if (crit === 'old_roll') {
                        var rA = parseInt($a.data('old-roll') || '999999', 10);
                        var rB = parseInt($b.data('old-roll') || '999999', 10);
                        if (rA !== rB) return rA - rB;
                    }
                }
                return 0;
            });

            // Re-append sorted rows to tbody
            $.each(rows, function(idx, row) {
                $tbody.append(row);
            });

            // Sequentially update roll numbers and Sr for visible rows
            var roll = startFrom;
            var sr = 1;
            $('.student-row:visible').each(function() {
                $(this).find('.sr-col').text(sr++);
                var $chk = $(this).find('.student-check');
                if ($chk.is(':checked')) {
                    $(this).find('.roll-input').val(roll++);
                }
            });

            checkDuplicates();
            toastr.success('Roll numbers generated successfully in selected order starting from ' + startFrom + '!');
        });

        $('#quick_gender_order').on('change', function() {
            $('#btnQuickGenerate').trigger('click');
        });

        // Quick auto 1..n re-sequence without reordering rows
        $('#btnQuickAutoSeq').on('click', function() {
            var startFrom = parseInt($('#quick_start_from').val(), 10) || 1;
            if (startFrom < 1) startFrom = 1;
            var roll = startFrom;
            $('.student-row').each(function() {
                var $chk = $(this).find('.student-check');
                if ($chk.is(':checked')) {
                    $(this).find('.roll-input').val(roll++);
                }
            });
            checkDuplicates();
            toastr.info('Roll numbers re-sequenced starting from ' + startFrom);
        });

        checkDuplicates();
    });
</script>
@endsection
