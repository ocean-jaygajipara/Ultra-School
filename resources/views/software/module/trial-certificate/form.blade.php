@extends('software.layout.app')

@php
    $route = $modules['route'] ?? 'trial-certificate';
    $page_title = $modules['title'] ?? 'Trial Certificate';
    $formTitle = 'Generate ' . $page_title;
@endphp
@section('title', $formTitle)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}">
    <style>
        .icx {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --red: #e1141c;
            --sky: #bfe3f7;
            --sky-soft: #eef7fd;
            --gold: #f5b400;
            --line: #e1e4f2;
            --muted: #5f6488;
        }

        .icx-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding: 22px 26px;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #4a56c4 100%);
            box-shadow: 0 10px 24px rgba(31, 37, 128, .18);
            margin-bottom: 18px;
        }

        .icx-hero::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(191, 227, 247, .12);
            pointer-events: none;
        }

        .icx-hero-main {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
        }

        .icx-hero-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 27px;
            color: var(--gold);
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            flex-shrink: 0;
        }

        .icx-hero h4 {
            margin: 0;
            color: #fff;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .icx-hero p {
            margin: 3px 0 0;
            color: rgba(255, 255, 255, .78);
            font-size: .86rem;
        }

        .icx-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 16px rgba(31, 37, 128, .06);
            margin-bottom: 18px;
            overflow: hidden;
        }

        .icx-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--line);
            background: var(--sky-soft);
        }

        .icx-card-head h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icx-card-head h5 i {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: inline-grid;
            place-items: center;
            background: var(--navy);
            color: #fff;
            font-size: 1rem;
        }

        .icx-card-body { padding: 20px; }

        .icx .form-label {
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .icx .form-control,
        .icx .select2-container--default .select2-selection--single {
            min-height: 42px;
            border-color: var(--line);
            border-radius: 10px;
        }

        .icx .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
        .icx .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }

        .icx-hint {
            font-size: .76rem;
            color: var(--muted);
            margin-top: 5px;
        }

        .icx-generate {
            height: 44px;
            padding: 0 26px;
            border: 0;
            border-radius: 11px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(120deg, var(--red), #b80f16);
            box-shadow: 0 6px 16px rgba(225, 20, 28, .25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .icx-preview {
            height: 44px;
            padding: 0 20px;
            border: 1px solid var(--navy);
            border-radius: 11px;
            font-weight: 700;
            color: var(--navy);
            background: #fff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .icx-table { margin: 0; }

        .icx .icx-table thead th {
            background: var(--navy) !important;
            color: #fff !important;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            border: 0;
            padding: 12px 14px;
            white-space: nowrap;
        }

        .icx-table tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            border-color: var(--line);
            color: #2b2f55;
        }

        .icx-who { font-weight: 600; color: #20245e !important; }

        .icx-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 600;
            background: #e7e9f8;
            color: var(--navy);
        }

        .icx-empty td {
            padding: 34px 14px !important;
            text-align: center;
            color: var(--muted);
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => 'Create', 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="icx my-2">
        <div class="icx-hero">
            <div class="icx-hero-main">
                <span class="icx-hero-icon"><i class="ti ti-certificate"></i></span>
                <div>
                    <h4>{{ $formTitle }}</h4>
                    <p>Select class → Show Students → Select students → Generate Selected.</p>
                </div>
            </div>
        </div>

        <div class="icx-card">
            <div class="icx-card-head">
                <h5><i class="ti ti-file-plus"></i> Generate Certificate</h5>
            </div>
            <div class="icx-card-body">
                <form method="GET" action="{{ route($route . '.create') }}" class="row g-3 align-items-start" autocomplete="off">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Standard <span class="text-danger">*</span></label>
                        <select name="standard_id" class="form-control select2" required>
                            <option value="">Select Standard</option>
                            @foreach ($standards as $std)
                                <option value="{{ $std->id }}" @selected((int) ($filters['standard_id'] ?? 0) === (int) $std->id)>
                                    {{ $std->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Date</label>
                        <input type="text" name="print_date" class="form-control flatpickr-date"
                               value="{{ $filters['print_date'] ?? now()->format('d-m-Y') }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Trial</label>
                        <select name="trial" class="form-control select2">
                            @foreach ($trials as $t)
                                <option value="{{ $t }}" @selected(($filters['trial'] ?? 'First') === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Exam Month</label>
                        <select name="exam_month" class="form-control select2">
                            @foreach ($examMonths as $m)
                                <option value="{{ $m }}" @selected(($filters['exam_month'] ?? 'June') === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Exam Year</label>
                        <select name="exam_year" class="form-control select2">
                            @foreach ($examYears as $y)
                                <option value="{{ $y }}" @selected((string) ($filters['exam_year'] ?? date('Y')) === (string) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                        <button type="submit" class="icx-preview">
                            <i class="ti ti-search"></i> Show Students
                        </button>
                    </div>
                </form>

            </div>
        </div>

        @if (!empty($filters['standard_id']))
            <form method="POST" action="{{ route($route . '.store') }}" id="tcGenerateForm">
                @csrf
                <input type="hidden" name="standard_id" value="{{ $filters['standard_id'] }}">
                <input type="hidden" name="print_date" value="{{ $filters['print_date'] }}">
                <input type="hidden" name="trial" value="{{ $filters['trial'] }}">
                <input type="hidden" name="exam_month" value="{{ $filters['exam_month'] }}">
                <input type="hidden" name="exam_year" value="{{ $filters['exam_year'] }}">

                <div class="icx-card">
                    <div class="icx-card-head">
                        <h5><i class="ti ti-users"></i> Class Students</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="icx-pill"><i class="ti ti-user"></i> <span id="tcSelectedCount">0</span> / {{ $students->count() }} selected</span>
                            @if ($students->count() > 0)
                                <button type="submit" class="icx-generate" id="tcGenerateBtn" disabled>
                                    <i class="ti ti-file-check"></i> Generate Selected
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table icx-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 52px;">
                                        <input type="checkbox" id="tcSelectAll" title="Select All" @disabled($students->count() === 0)>
                                    </th>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th class="text-center">GR No.</th>
                                    <th>Student Name</th>
                                    <th class="text-center">Std</th>
                                    <th class="text-center">Class</th>
                                    <th class="text-center">Roll No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $i => $row)
                                    @php
                                        $name = strtoupper(trim(preg_replace(
                                            '/\s+/',
                                            ' ',
                                            ($row->first_name ?? '') . ' ' . ($row->father_name ?? '') . ' ' . ($row->last_name ?? '')
                                        )));
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="tc-student-check" name="admission_ids[]" value="{{ $row->id }}">
                                        </td>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td class="text-center">{{ $row->gr_no ?: '-' }}</td>
                                        <td class="icx-who text-uppercase">{{ $name ?: '-' }}</td>
                                        <td class="text-center">{{ $row->current_std ?: '-' }}</td>
                                        <td class="text-center">{{ $row->division ?: '-' }}</td>
                                        <td class="text-center">{{ $row->roll_no ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr class="icx-empty">
                                        <td colspan="7">No students found in this class.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            if ($.fn.select2) {
                $('.select2').select2({ width: '100%', allowClear: true, placeholder: 'Select' });
            }
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.flatpickr-date', { dateFormat: 'd-m-Y', allowInput: true });
            }

            function syncTcSelection() {
                var total = $('.tc-student-check').length;
                var checked = $('.tc-student-check:checked').length;
                $('#tcSelectedCount').text(checked);
                $('#tcGenerateBtn').prop('disabled', checked === 0);
                $('#tcSelectAll').prop('checked', total > 0 && checked === total);
                $('#tcSelectAll').prop('indeterminate', checked > 0 && checked < total);
            }

            $('#tcSelectAll').on('change', function () {
                $('.tc-student-check').prop('checked', $(this).is(':checked'));
                syncTcSelection();
            });

            $(document).on('change', '.tc-student-check', syncTcSelection);
            syncTcSelection();

            $('#tcGenerateForm').on('submit', function (e) {
                if ($('.tc-student-check:checked').length === 0) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please select at least one student.');
                    } else {
                        alert('Please select at least one student.');
                    }
                }
            });
        });
    </script>
@endsection
