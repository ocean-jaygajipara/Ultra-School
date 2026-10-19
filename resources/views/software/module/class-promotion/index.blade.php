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
        }
        .cpx-empty { text-align: center; color: var(--muted); padding: 28px 12px; }
        .cpx-arrow {
            display: flex; align-items: center; justify-content: center;
            color: var(--navy); font-size: 1.5rem; padding-top: 28px;
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
        <div class="cpx-hero">
            <div>
                <h4>Class Promotion</h4>
                <p>Move students from one Standard / Division to the next for the current academic year.</p>
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
                Current year: <strong>{{ $currentYear->name }}</strong>.
                Select From Standard → To Standard, promote students, then set new roll numbers from Roll Number List.
                (Year always comes from Academic Year Master — change Current there anytime.)
            </div>
        @endif

        <div class="cpx-panel">
            <form method="POST" action="{{ route($route . '.filter') }}" class="row g-3 align-items-end">
                @csrf
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
                <div class="col-md-2">
                    <label class="cpx-label">To Division</label>
                    <select name="to_division" id="to_division" class="form-select select2">
                        <option value="">Keep Same</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $toDivision === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i> Load Students
                    </button>
                    <button type="submit" name="clear" value="1" class="btn btn-outline-secondary" formnovalidate>Clear</button>
                </div>
            </form>
        </div>

        <div class="cpx-panel">
            @if (!$fromStandardId)
                <div class="cpx-empty">Select From Standard and click Load Students.</div>
            @elseif ($students->isEmpty())
                <div class="cpx-empty">No students found in {{ $fromStandard->name ?? '' }}{{ $fromDivision ? ' / ' . $fromDivision : '' }}.</div>
            @else
                <form method="POST" action="{{ route($route . '.promote') }}" id="promoteForm"
                      onsubmit="return confirmPromote();">
                    @csrf
                    <input type="hidden" name="from_standard_id" value="{{ $fromStandardId }}">
                    <input type="hidden" name="from_division" value="{{ $fromDivision }}">
                    <input type="hidden" name="to_standard_id" id="promote_to_standard_id" value="{{ $toStandardId }}">
                    <input type="hidden" name="to_division" id="promote_to_division" value="{{ $toDivision }}">

                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <strong style="color:#1f2580;">
                                {{ $fromStandard->name ?? '' }}{{ $fromDivision ? ' — ' . $fromDivision : '' }}
                                →
                                {{ $toStandard->name ?? 'Select To Standard' }}{{ $toDivision ? ' — ' . $toDivision : '' }}
                            </strong>
                            <span class="text-muted ms-2">{{ $students->count() }} students</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" id="autoRollBtn" title="Fill 1, 2, 3...">
                                Auto Roll 1..n
                            </button>
                            <button type="submit" class="btn btn-success" {{ !$currentYear || !$toStandardId || empty($modules['permission_create']) ? 'disabled' : '' }} @if(empty($modules['permission_create'])) title="You do not have permission to promote students." @endif>
                                <i class="ti ti-arrows-up-right me-1"></i> Promote Selected
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover cpx-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:50px;">
                                        <input type="checkbox" id="checkAll" checked>
                                    </th>
                                    <th style="width:110px;">New Roll No</th>
                                    <th style="width:90px;">Old Roll</th>
                                    <th style="width:110px;">GR No</th>
                                    <th>Student Name</th>
                                    <th style="width:100px;">Standard</th>
                                    <th style="width:90px;">Division</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="student-check" name="student_ids[]"
                                                   value="{{ $student->id }}" checked>
                                        </td>
                                        <td>
                                            <input type="number" min="1" step="1"
                                                   name="roll_nos[{{ $student->id }}]"
                                                   class="form-control form-control-sm roll-input"
                                                   value="{{ $loop->iteration }}"
                                                   required>
                                        </td>
                                        <td>{{ $student->roll_no ?: '—' }}</td>
                                        <td>{{ $student->gr_no ?: '—' }}</td>
                                        <td><strong>{{ $student->full_name }}</strong></td>
                                        <td>{{ $student->current_std ?: '—' }}</td>
                                        <td>{{ $student->division ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@section('page_leavel_script')
<script>
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

        var rolls = [];
        var missing = false;
        checked.each(function() {
            var input = $(this).closest('tr').find('.roll-input');
            var val = $.trim(input.val());
            if (!val || parseInt(val, 10) < 1) {
                missing = true;
                input.focus();
                return false;
            }
            rolls.push(val);
        });
        if (missing) {
            toastr.error('Please enter New Roll No for all selected students.');
            return false;
        }
        if (rolls.length !== new Set(rolls).size) {
            toastr.error('Duplicate New Roll No found. Each roll must be unique.');
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
            $('#from_standard_id, #from_division, #to_standard_id, #to_division').select2({ width: '100%' });
        }

        $('#to_standard_id').on('change', function() {
            $('#promote_to_standard_id').val($(this).val());
        });
        $('#to_division').on('change', function() {
            $('#promote_to_division').val($(this).val());
        });

        $('#checkAll').on('change', function() {
            $('.student-check').prop('checked', $(this).is(':checked'));
        });
        $(document).on('change', '.student-check', function() {
            $('#checkAll').prop('checked', $('.student-check').length === $('.student-check:checked').length);
        });

        $('#autoRollBtn').on('click', function() {
            var n = 1;
            $('.student-check:checked').each(function() {
                $(this).closest('tr').find('.roll-input').val(n++);
            });
        });
    });
</script>
@endsection
