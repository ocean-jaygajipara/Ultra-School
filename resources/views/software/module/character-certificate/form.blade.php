@extends('software.layout.app')

@php
    $route = $modules['route'] ?? 'character-certificate';
    $page_title = $modules['title'] ?? 'Character Certificate';
    $isEdit = $isEdit ?? !empty($entry?->id);
    $formTitle = ($isEdit ? 'Edit ' : 'Generate ') . $page_title;
@endphp
@section('title', $formTitle)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}">
    <style>
        .icx {
            --navy: #1f2580; --navy-2: #2e3a9e; --red: #e1141c; --sky: #bfe3f7;
            --sky-soft: #eef7fd; --gold: #f5b400; --line: #e1e4f2; --muted: #5f6488;
        }
        .icx-hero {
            position: relative; overflow: hidden; display: flex; align-items: center; gap: 16px;
            padding: 22px 26px; border-radius: 16px; color: #fff; margin-bottom: 18px;
            background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #4a56c4 100%);
            box-shadow: 0 10px 24px rgba(31, 37, 128, .18);
        }
        .icx-hero-icon {
            width: 54px; height: 54px; border-radius: 14px; display: grid; place-items: center;
            font-size: 27px; color: var(--gold); background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25); flex-shrink: 0;
        }
        .icx-hero h4 { margin: 0; color: #fff; font-weight: 700; font-size: 1.3rem; }
        .icx-hero p { margin: 3px 0 0; color: rgba(255,255,255,.78); font-size: .86rem; }
        .icx-card {
            border: 1px solid var(--line); border-radius: 16px; background: #fff;
            box-shadow: 0 4px 16px rgba(31, 37, 128, .06); margin-bottom: 18px; overflow: hidden;
        }
        .icx-card-head {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
            padding: 14px 20px; border-bottom: 1px solid var(--line); background: var(--sky-soft);
        }
        .icx-card-head h5 {
            margin: 0; font-size: 1rem; font-weight: 700; color: var(--navy);
            display: flex; align-items: center; gap: 8px;
        }
        .icx-card-head h5 i {
            width: 30px; height: 30px; border-radius: 9px; display: inline-grid; place-items: center;
            background: var(--navy); color: #fff; font-size: 1rem;
        }
        .icx-card-body { padding: 20px; }
        .icx .form-label {
            font-size: .74rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
            color: var(--muted); margin-bottom: 6px;
        }
        .icx .form-control,
        .icx .select2-container--default .select2-selection--single {
            min-height: 42px; border-color: var(--line); border-radius: 10px;
        }
        .icx .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
        .icx .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .icx-generate {
            height: 44px; padding: 0 26px; border: 0; border-radius: 11px; font-weight: 700; color: #fff;
            background: linear-gradient(120deg, var(--red), #b80f16);
            box-shadow: 0 6px 16px rgba(225, 20, 28, .25);
            display: inline-flex; align-items: center; gap: 8px;
        }
        .icx-preview {
            height: 44px; padding: 0 20px; border: 1px solid var(--navy); border-radius: 11px;
            font-weight: 700; color: var(--navy); background: #fff;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .icx-table { margin: 0; }
        .icx .icx-table thead th {
            background: var(--navy) !important; color: #fff !important; font-size: .72rem; font-weight: 700;
            letter-spacing: .05em; text-transform: uppercase; border: 0; padding: 12px 14px; white-space: nowrap;
        }
        .icx-table tbody td { padding: 11px 14px; vertical-align: middle; border-color: var(--line); color: #2b2f55; }
        .icx-who { font-weight: 600; color: #20245e !important; }
        .icx-pill {
            display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px;
            font-size: .74rem; font-weight: 600; background: #e7e9f8; color: var(--navy);
        }
        .icx-empty td { padding: 34px 14px !important; text-align: center; color: var(--muted); }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => $isEdit ? 'Edit' : 'Create', 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="icx my-2">
        <div class="icx-hero">
            <span class="icx-hero-icon"><i class="ti ti-award"></i></span>
            <div>
                <h4>{{ $formTitle }}</h4>
                <p>
                    @if ($isEdit)
                        Update student certificate details.
                    @else
                        Select standard → Show Students → Generate All.
                    @endif
                </p>
            </div>
        </div>

        @if ($isEdit)
            <div class="icx-card">
                <div class="icx-card-head">
                    <h5><i class="ti ti-file-plus"></i> Update Certificate</h5>
                </div>
                <div class="icx-card-body">
                    <form method="POST" action="{{ route($route . '.update', $entry->id) }}" autocomplete="off">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $entry->id }}">
                        <input type="hidden" name="admission_id" id="admission_id" value="{{ old('admission_id', $entry->admission_id ?? '') }}">
                        <input type="hidden" name="standard_id" id="standard_id" value="{{ old('standard_id', $entry->standard_id ?? '') }}">
                        <input type="hidden" name="gender" id="gender" value="{{ old('gender', $entry->gender ?? '') }}">
                        <input type="hidden" name="std_display" id="std_display" value="{{ old('std_display', $entry->std_display ?? '') }}">

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select id="student_pick" class="form-control select2">
                                    <option value="">Select Student (GR / Name)</option>
                                    @foreach ($students as $stu)
                                        @php
                                            $label = trim(($stu->gr_no ? $stu->gr_no . ' - ' : '') . strtoupper(trim(($stu->first_name ?? '') . ' ' . ($stu->father_name ?? '') . ' ' . ($stu->last_name ?? ''))));
                                        @endphp
                                        <option value="{{ $stu->id }}"
                                            @selected((int) old('admission_id', $entry->admission_id ?? 0) === (int) $stu->id)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">GR No. <span class="text-danger">*</span></label>
                                <input type="text" name="gr_no" id="gr_no" class="form-control" required
                                       value="{{ old('gr_no', $entry->gr_no ?? '') }}">
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">UID No.</label>
                                <input type="text" name="uid_no" id="uid_no" class="form-control"
                                       value="{{ old('uid_no', $entry->uid_no ?? '') }}">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Student Name <span class="text-danger">*</span></label>
                                <input type="text" name="student_name" id="student_name" class="form-control text-uppercase" required
                                       value="{{ old('student_name', $entry->student_name ?? '') }}">
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Standard</label>
                                <input type="text" name="std" id="std" class="form-control"
                                       value="{{ old('std', $entry->std ?? '') }}">
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Class</label>
                                <input type="text" name="class" id="class" class="form-control"
                                       value="{{ old('class', $entry->class ?? '') }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <select name="academic_year" id="academic_year" class="form-control select2" required>
                                    <option value="">Select Year</option>
                                    @foreach ($academicYears as $y)
                                        <option value="{{ $y }}" @selected(old('academic_year', $entry->academic_year ?? '') === $y)>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Date</label>
                                <input type="text" name="print_date" id="print_date" class="form-control flatpickr-date"
                                       value="{{ old('print_date', $entry->print_date ?? now()->format('d-m-Y')) }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="icx-generate">
                                    <i class="ti ti-file-check"></i> Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="icx-card">
                <div class="icx-card-head">
                    <h5><i class="ti ti-file-plus"></i> Generate Certificate</h5>
                </div>
                <div class="icx-card-body">
                    <form method="GET" action="{{ route($route . '.create') }}" class="row g-3 align-items-start" autocomplete="off">
                        <div class="col-lg-4 col-md-6">
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
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year" class="form-control select2">
                                <option value="">Select Year</option>
                                @foreach ($academicYears as $y)
                                    <option value="{{ $y }}" @selected(($filters['academic_year'] ?? '') === $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Date</label>
                            <input type="text" name="print_date" class="form-control flatpickr-date"
                                   value="{{ $filters['print_date'] ?? now()->format('d-m-Y') }}">
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
                <form method="POST" action="{{ route($route . '.store') }}" id="ccGenerateForm">
                    @csrf
                    <input type="hidden" name="standard_id" value="{{ $filters['standard_id'] }}">
                    <input type="hidden" name="print_date" value="{{ $filters['print_date'] }}">
                    <input type="hidden" name="academic_year" value="{{ $filters['academic_year'] }}">

                    <div class="icx-card">
                        <div class="icx-card-head">
                            <h5><i class="ti ti-users"></i> Class Students</h5>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="icx-pill"><i class="ti ti-user"></i> <span id="ccSelectedCount">0</span> / {{ $students->count() }} selected</span>
                                @if ($students->count() > 0)
                                    <button type="submit" class="icx-generate" id="ccGenerateBtn" disabled>
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
                                            <input type="checkbox" id="ccSelectAll" title="Select All" @disabled($students->count() === 0)>
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
                                                <input type="checkbox" class="cc-student-check" name="admission_ids[]" value="{{ $row->id }}">
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
                                            <td colspan="7">No students found in this standard.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            @endif
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

            function syncCcSelection() {
                var total = $('.cc-student-check').length;
                var checked = $('.cc-student-check:checked').length;
                $('#ccSelectedCount').text(checked);
                $('#ccGenerateBtn').prop('disabled', checked === 0);
                $('#ccSelectAll').prop('checked', total > 0 && checked === total);
                $('#ccSelectAll').prop('indeterminate', checked > 0 && checked < total);
            }

            $('#ccSelectAll').on('change', function () {
                $('.cc-student-check').prop('checked', $(this).is(':checked'));
                syncCcSelection();
            });

            $(document).on('change', '.cc-student-check', syncCcSelection);
            syncCcSelection();

            $('#ccGenerateForm').on('submit', function (e) {
                if ($('.cc-student-check:checked').length === 0) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please select at least one student.');
                    } else {
                        alert('Please select at least one student.');
                    }
                }
            });

            @if ($isEdit)
            function fillStudent(data) {
                $('#admission_id').val(data.admission_id || '');
                $('#gr_no').val(data.gr_no || '');
                $('#uid_no').val(data.uid_no || '');
                $('#student_name').val(data.student_name || '');
                $('#gender').val(data.gender || '');
                $('#standard_id').val(data.standard_id || '');
                $('#std').val(data.std || '');
                $('#std_display').val(data.std_display || '');
                $('#class').val(data.class || '');
                if (data.academic_year) {
                    $('#academic_year').val(data.academic_year).trigger('change');
                }
            }

            $('#student_pick').on('change', function () {
                var id = $(this).val();
                if (!id) return;
                $.get(@json(route($route . '.find-student')), { admission_id: id }, function (res) {
                    if (res.success) fillStudent(res.data);
                    else if (typeof toastr !== 'undefined') toastr.error(res.message || 'Student not found');
                });
            });

            $('#gr_no').on('change blur', function () {
                var gr = $(this).val().trim();
                if (!gr) return;
                $.get(@json(route($route . '.find-student')), { gr_no: gr }, function (res) {
                    if (res.success) {
                        fillStudent(res.data);
                        $('#student_pick').val(String(res.data.admission_id)).trigger('change.select2');
                    }
                });
            });
            @endif
        });
    </script>
@endsection
