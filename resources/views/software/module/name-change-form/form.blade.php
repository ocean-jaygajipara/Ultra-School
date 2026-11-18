@extends('software.layout.app')

@php
    $route = $modules['route'] ?? 'name-change-form';
    $page_title = $modules['title'] ?? 'Name Change Form';
    $isEdit = $isEdit ?? !empty($entry?->id);
    $formTitle = ($isEdit ? 'Edit ' : 'Add ') . $page_title;
    $formAction = $isEdit
        ? route($route . '.update', $entry->id)
        : route($route . '.store');
    $marks = $subjectMarks ?? [];
@endphp
@section('title', $formTitle)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}">
    <style>
        .auto-lock,
        .select2-container--default.select2-container--disabled .select2-selection--single {
            background-color: #f3f3f5 !important;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => ($isEdit ? 'Edit' : 'Add'), 'url' => ''],
            ],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>
@endsection

@section('content')
    <div class="mst my-2">
        <div class="mst-hero">
            <div class="mst-hero-l">
                <div class="mst-hero-icon"><i class="ti ti-forms"></i></div>
                <div>
                    <h4>{{ $formTitle }}</h4>
                    <p>Select student, fill form details, then Save.</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
                <form id="ncfForm" action="{{ $formAction }}" method="POST" autocomplete="off">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="id" id="entry_id" value="{{ old('id', $entry->id ?? '') }}">
                    <input type="hidden" name="admission_id" id="admission_id" value="{{ old('admission_id', $entry->admission_id ?? '') }}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Student (GR) <span class="text-danger">*</span></label>
                            <select id="student_select" class="form-select select2">
                                <option value="">Select Student</option>
                                @foreach ($students as $row)
                                    @php
                                        $name = strtoupper(trim(preg_replace('/\s+/', ' ', ($row->last_name ?? '') . ' ' . ($row->first_name ?? '') . ' ' . ($row->father_name ?? ''))));
                                        $label = ($row->gr_no ?: '-') . ' | ' . ($name ?: '-');
                                    @endphp
                                    <option value="{{ $row->id }}"
                                        data-gr="{{ $row->gr_no }}"
                                        @selected((int) old('admission_id', $entry->admission_id ?? 0) === (int) $row->id)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">GR No. <span class="text-danger">*</span></label>
                            <input type="text" name="gr_no" id="gr_no" class="form-control auto-lock" required readonly
                                   value="{{ old('gr_no', $entry->gr_no ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <select id="academic_year" class="form-select select2 auto-lock" disabled>
                                <option value="">Select</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year }}" @selected(old('academic_year', $entry->academic_year ?? '') === $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="academic_year" id="academic_year_hidden" value="{{ old('academic_year', $entry->academic_year ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Form Date</label>
                            <input type="text" name="form_date" id="form_date" class="form-control flatpickr-date"
                                   value="{{ old('form_date', !empty($entry?->form_date) ? $entry->formatDate('form_date') : now()->format('d-m-Y')) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">SR No.</label>
                            <input type="text" name="sr_no" id="sr_no" class="form-control auto-lock" readonly
                                   value="{{ old('sr_no', $entry->sr_no ?? ($nextSrNo ?? '')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Student ID</label>
                            <input type="text" name="form_student_id" id="form_student_id" class="form-control auto-lock" readonly
                                   value="{{ old('form_student_id', $entry->form_student_id ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">STD</label>
                            <input type="text" name="std" id="std" class="form-control auto-lock" readonly
                                   value="{{ old('std', $entry->std ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Class</label>
                            <input type="text" name="class" id="class" class="form-control auto-lock" readonly
                                   value="{{ old('class', $entry->class ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Roll No.</label>
                            <input type="text" name="roll_no" id="roll_no" class="form-control auto-lock" readonly
                                   value="{{ old('roll_no', $entry->roll_no ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type of Student</label>
                            <input type="text" name="type_of_student" id="type_of_student" class="form-control auto-lock" readonly
                                   value="{{ old('type_of_student', $entry->type_of_student ?? 'REGULAR') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">App No.</label>
                            <input type="text" name="app_no" id="app_no" class="form-control" value="{{ old('app_no', $entry->app_no ?? '') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" id="student_name" class="form-control text-uppercase auto-lock" required readonly
                                   value="{{ old('student_name', $entry->student_name ?? '') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="text" name="date_of_birth" id="date_of_birth" class="form-control flatpickr-date auto-lock" readonly
                                   value="{{ old('date_of_birth', !empty($entry?->date_of_birth) ? $entry->formatDate('date_of_birth') : '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">School Joining Date</label>
                            <input type="text" name="school_joining_date" id="school_joining_date" class="form-control flatpickr-date auto-lock" readonly
                                   value="{{ old('school_joining_date', !empty($entry?->school_joining_date) ? $entry->formatDate('school_joining_date') : '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Medium</label>
                            <select id="medium" class="form-select select2 auto-lock" disabled>
                                <option value="ENGLISH" selected>ENGLISH</option>
                            </select>
                            <input type="hidden" name="medium" id="medium_hidden" value="{{ old('medium', $entry->medium ?? 'ENGLISH') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Primary School Type</label>
                            <select name="primary_school_type" id="primary_school_type" class="form-select select2">
                                <option value="">Select</option>
                                @foreach (['Government', 'Private'] as $t)
                                    <option value="{{ $t }}" @selected(old('primary_school_type', $entry->primary_school_type ?? '') === $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Father Profession</label>
                            <input type="text" name="father_profession" id="father_profession" class="form-control text-uppercase auto-lock" readonly
                                   value="{{ old('father_profession', $entry->father_profession ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Father Income</label>
                            <input type="text" name="father_income" id="father_income" class="form-control"
                                   value="{{ old('father_income', $entry->father_income ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cast</label>
                            <select id="cast" class="form-select select2 auto-lock" disabled>
                                <option value="">Select</option>
                                @foreach (['SC', 'ST', 'OBC', 'GEN'] as $c)
                                    <option value="{{ $c }}" @selected(old('cast', $entry->cast ?? '') === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="cast" id="cast_hidden" value="{{ old('cast', $entry->cast ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Gender</label>
                            <select id="gender" class="form-select select2 auto-lock" disabled>
                                <option value="">Select</option>
                                @foreach (['MALE', 'FEMALE', 'OTHER'] as $g)
                                    <option value="{{ $g }}" @selected(old('gender', $entry->gender ?? '') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="gender" id="gender_hidden" value="{{ old('gender', $entry->gender ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mother Name</label>
                            <input type="text" name="mother_name" id="mother_name" class="form-control text-uppercase auto-lock" readonly
                                   value="{{ old('mother_name', $entry->mother_name ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Aadhar Card No.</label>
                            <input type="text" name="aadhar" id="aadhar" class="form-control auto-lock" maxlength="12" readonly
                                   value="{{ old('aadhar', $entry->aadhar ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">BPL Card No.</label>
                            <input type="text" name="bpl_card_no" id="bpl_card_no" class="form-control"
                                   value="{{ old('bpl_card_no', $entry->bpl_card_no ?? '') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email ID</label>
                            <input type="text" name="email" id="email" class="form-control"
                                   value="{{ old('email', $entry->email ?? 'ultra_school@yahoo.in') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Widow Child</label>
                            <select name="is_widow_child" id="is_widow_child" class="form-select select2">
                                <option value="">Select</option>
                                @foreach (['YES', 'NO'] as $v)
                                    <option value="{{ $v }}" @selected(old('is_widow_child', $entry->is_widow_child ?? '') === $v)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Minority</label>
                            <select name="is_minority" id="is_minority" class="form-select select2">
                                <option value="">Select</option>
                                @foreach (['YES', 'NO'] as $v)
                                    <option value="{{ $v }}" @selected(old('is_minority', $entry->is_minority ?? '') === $v)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Minority Type</label>
                            <input type="text" name="minority_type" id="minority_type" class="form-control"
                                   value="{{ old('minority_type', $entry->minority_type ?? '') }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control text-uppercase auto-lock" rows="2" readonly>{{ old('address', $entry->address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pin Code</label>
                            <input type="text" name="pincode" id="pincode" class="form-control" maxlength="6"
                                   value="{{ old('pincode', $entry->pincode ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile No.</label>
                            <input type="text" name="mobile" id="mobile" class="form-control auto-lock" maxlength="15" readonly
                                   value="{{ old('mobile', $entry->mobile ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">UID No.</label>
                            <input type="text" name="uid" id="uid" class="form-control auto-lock" maxlength="18" readonly
                                   value="{{ old('uid', $entry->uid ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marital Status</label>
                            <select name="marital_status" id="marital_status" class="form-select select2">
                                @foreach (['UNMARRIED', 'MARRIED'] as $ms)
                                    <option value="{{ $ms }}" @selected(old('marital_status', $entry->marital_status ?? 'UNMARRIED') === $ms)>{{ $ms }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <hr>
                            <h6 class="mb-2">Subject Marks</h6>
                        </div>
                        @php
                            $subjectOptions = [
                                'gujarati_fl' => 'Gujarati F.L.',
                                'english_sl' => 'English S.L.',
                                'sanskrit_sl' => 'Sanskrit S.L.',
                                'hindi_sl' => 'Hindi S.L.',
                                'social_science' => 'Social Science',
                                'science_technology' => 'Science Technology',
                                'maths_standard' => 'Maths Standard',
                                'maths_basic' => 'Maths Basic',
                                'computer_theory' => 'Computer Theory',
                                'computer_practical' => 'Computer Practical',
                                'yoga_theory' => 'Yoga Theory',
                                'yoga_practical' => 'Yoga Practical',
                            ];
                        @endphp
                        @foreach ($subjectOptions as $key => $label)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subject_marks[{{ $key }}]" value="1"
                                           id="mark_{{ $key }}" @checked(!empty(old('subject_marks.'.$key, $marks[$key] ?? 0)))>
                                    <label class="form-check-label" for="mark_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-12 d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> {{ $isEdit ? 'Update' : 'Save' }}
                            </button>
                            <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        $(function () {
            if ($.fn.select2) {
                $('.select2').select2({ width: '100%', allowClear: true, placeholder: 'Select' });
            }
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.flatpickr-date:not(.auto-lock)', { dateFormat: 'd-m-Y', allowInput: true });
            }

            function setLockedSelect(id, value) {
                $('#' + id).val(value || '').trigger('change');
                $('#' + id + '_hidden').val(value || '');
            }

            function fillForm(data) {
                $('#admission_id').val(data.admission_id || '');
                $('#gr_no').val(data.gr_no || '');
                setLockedSelect('academic_year', data.academic_year || '');
                $('#form_student_id').val(data.form_student_id || '');
                $('#std').val(data.std || '');
                $('#class').val(data.class || '');
                $('#roll_no').val(data.roll_no || '');
                $('#form_date').val(data.form_date || '');
                $('#type_of_student').val(data.type_of_student || 'REGULAR');
                $('#app_no').val(data.app_no || '');
                $('#student_name').val(data.student_name || '');
                $('#date_of_birth').val(data.date_of_birth || '');
                $('#school_joining_date').val(data.school_joining_date || '');
                setLockedSelect('medium', 'ENGLISH');
                $('#primary_school_type').val(data.primary_school_type || '').trigger('change');
                $('#father_profession').val(data.father_profession || '');
                $('#father_income').val(data.father_income || '');
                setLockedSelect('cast', data.cast || '');
                setLockedSelect('gender', data.gender || '');
                $('#mother_name').val(data.mother_name || '');
                $('#aadhar').val(data.aadhar || '');
                $('#bpl_card_no').val(data.bpl_card_no || '');
                $('#email').val(data.email || 'ultra_school@yahoo.in');
                $('#is_widow_child').val(data.is_widow_child || '').trigger('change');
                $('#is_minority').val(data.is_minority || '').trigger('change');
                $('#minority_type').val(data.minority_type || '');
                $('#address').val(data.address || '');
                $('#pincode').val(data.pincode || '');
                $('#mobile').val(data.mobile || '');
                $('#uid').val(data.uid || '');
                $('#marital_status').val(data.marital_status || 'UNMARRIED').trigger('change');

                var marks = data.subject_marks || {};
                Object.keys(marks).forEach(function (key) {
                    $('#mark_' + key).prop('checked', !!parseInt(marks[key], 10));
                });
            }

            $('#student_select').on('change', function () {
                var id = $(this).val();
                if (!id) return;
                $.get(@json(route($route . '.find-student')), {
                    admission_id: id,
                    exclude_id: $('#entry_id').val() || 0
                }).done(function (res) {
                    if (res.success) {
                        fillForm(res.data);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(res.message || 'Student not found');
                        } else {
                            alert(res.message || 'Student not found');
                        }
                        $('#student_select').val('').trigger('change');
                        $('#admission_id').val('');
                    }
                });
            });

            @if (session('error'))
                if (typeof toastr !== 'undefined') {
                    toastr.error(@json(session('error')));
                }
            @endif
            @if ($errors->any())
                if (typeof toastr !== 'undefined') {
                    toastr.error(@json($errors->first()));
                }
            @endif
        });
    </script>
@endsection
