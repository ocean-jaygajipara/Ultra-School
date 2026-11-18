@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
    $isEdit = isset($edit) && $edit?->id;
    $selectedAdmission = $selectedAdmission ?? null;
    $oldGrNos = old('gr_no', $isEdit ? ($edit->gr_no ?? ($selectedAdmission->gr_no ?? null)) : []);
    if (!is_array($oldGrNos) && $oldGrNos !== null && $oldGrNos !== '') {
        $oldGrNos = [$oldGrNos];
    }
    $oldGrNos = array_values(array_filter(array_map('strval', (array) $oldGrNos)));
@endphp
@section('title', $page_title)

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => ($isEdit ? 'Edit' : 'Add') . ' ' . $page_title, 'url' => ''],
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
                <div class="mst-hero-icon"><i class="ti ti-message-report"></i></div>
                <div>
                    <h4>{{ $isEdit ? 'Edit' : 'Add' }} {{ $page_title }}</h4>
                    <p>{{ $isEdit ? 'Update teacher complaint report details.' : 'Create a new teacher complaint report.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
            <form
                action="{{ $isEdit ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" id="teacherComplaintForm">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endif

                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select name="academic_year_id" id="academic_year_id"
                                class="form-control select2 @error('academic_year_id') is-invalid @enderror" required>
                                <option value="">Select Academic Year</option>
                                @foreach ($academicYears as $yearItem)
                                    <option value="{{ $yearItem->id }}"
                                        @selected((string) old('academic_year_id', $selectedAcademicYearId ?? ($currentAcademicYear->id ?? '')) === (string) $yearItem->id)>
                                        {{ $yearItem->name }}{{ $yearItem->is_current ? ' (Current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Standard <span class="text-danger">*</span></label>
                            <select name="standard_id" id="standard_id"
                                class="form-control select2 @error('standard_id') is-invalid @enderror" required>
                                <option value="">Select Standard</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std->id }}"
                                        @selected((string) old('standard_id', $selectedStandardId ?? '') === (string) $std->id)>
                                        {{ $std->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('standard_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="division" id="division"
                                class="form-control select2 @error('division') is-invalid @enderror" required>
                                <option value="">Select Class</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div }}"
                                        @selected((string) old('division', $selectedDivision ?? '') === (string) $div)>
                                        {{ $div }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">
                                Student <span class="text-danger">*</span>
                                @unless ($isEdit)
                                    <small class="text-muted">(Multiple select)</small>
                                @endunless
                            </label>

                            @if ($isEdit)
                                <select name="gr_no" id="gr_no"
                                    class="form-control select2 @error('gr_no') is-invalid @enderror" required>
                                    <option value="">Select Student</option>
                                    @if ($selectedAdmission || !empty($edit->gr_no))
                                        <option value="{{ $edit->gr_no ?? $selectedAdmission->gr_no }}" selected>
                                            {{ $edit->gr_no ?? $selectedAdmission->gr_no }} —
                                            {{ trim(($selectedAdmission->first_name ?? '') . ' ' . ($selectedAdmission->last_name ?? '') . ' ' . ($selectedAdmission->father_name ?? '')) }}
                                        </option>
                                    @endif
                                </select>
                            @else
                                <div class="card p-2" style="max-height: 260px; overflow-y: auto; border: 1px solid #ccc;">
                                    <input type="text" id="student_search" class="form-control mb-2" placeholder="Search student...">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="select_all_students">
                                        <label class="form-check-label fw-bold" for="select_all_students">Select All</label>
                                    </div>
                                    <div id="student_checkbox_list">
                                        <span class="text-muted small">Select Academic Year, Standard & Class first</span>
                                    </div>
                                </div>
                            @endif

                            @error('gr_no')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ isset($edit->date) ? \Carbon\Carbon::parse($edit->date)->format('Y-m-d') : old('date', date('Y-m-d')) }}"
                                required>
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select name="faculty_id" id="faculty_id"
                                class="form-control select2 @error('faculty_id') is-invalid @enderror" required>
                                <option value="">Select Teacher</option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}"
                                        @selected((string) old('faculty_id', $edit->faculty_id ?? '') === (string) $faculty->id)>
                                        {{ $faculty->name }}{{ $faculty->email ? ' (' . $faculty->email . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('faculty_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Complaint Details <span class="text-danger">*</span></label>
                            <textarea id="complaint" name="complaint" rows="3"
                                class="form-control @error('complaint') is-invalid @enderror"
                                placeholder="Enter complaint details..."
                                required>{{ old('complaint', $edit->complaint ?? '') }}</textarea>
                            @error('complaint')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-5">
                        <button type="submit" class="btn btn-success mt-1">
                            {{ $isEdit ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                    </div>
                </div>
            </form>
                    </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/autosize/autosize.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });
            }

            const initialGrNos = @json($oldGrNos);

            function resetStudentList(message) {
                @if ($isEdit)
                    const $student = $('#gr_no');
                    $student.empty().append(`<option value="">${message}</option>`);
                    $student.val('').trigger('change.select2');
                @else
                    $('#student_checkbox_list').html(`<span class="text-muted small">${message}</span>`);
                    $('#select_all_students').prop('checked', false);
                @endif
            }

            function loadStudents(keepSelectedGrNos) {
                const yearId = $('#academic_year_id').val();
                const standardId = $('#standard_id').val();
                const division = $('#division').val();
                const keep = Array.isArray(keepSelectedGrNos)
                    ? keepSelectedGrNos.map(String)
                    : (keepSelectedGrNos ? [String(keepSelectedGrNos)] : []);

                if (!yearId || !standardId || !division) {
                    resetStudentList('Select Academic Year, Standard & Class first');
                    return;
                }

                resetStudentList('Loading students...');

                $.ajax({
                    type: 'GET',
                    url: "{{ route('fee-receipt-entry.students') }}",
                    data: {
                        academic_year_id: yearId,
                        standard_id: standardId,
                        division: division
                    },
                    success: function(response) {
                        const rows = (response && response.success && Array.isArray(response.data)) ? response.data : [];

                        @if ($isEdit)
                            const $student = $('#gr_no');
                            $student.empty().append('<option value="">Select Student</option>');

                            if (rows.length === 0) {
                                $student.append('<option value="" disabled>No students found</option>');
                                $student.trigger('change.select2');
                                return;
                            }

                            rows.forEach(function(row) {
                                const grNo = row.gr_no || '';
                                if (!grNo) return;
                                const label = row.label || (grNo + ' — ' + (row.student_name || row.name || ''));
                                $student.append(`<option value="${grNo}">${label}</option>`);
                            });

                            if (keep.length) {
                                $student.val(keep[0] || '');
                            }
                            $student.trigger('change.select2');
                        @else
                            const container = $('#student_checkbox_list');
                            container.empty();

                            if (rows.length === 0) {
                                container.html('<span class="text-muted small">No students found</span>');
                                return;
                            }

                            rows.forEach(function(row) {
                                const grNo = row.gr_no || '';
                                if (!grNo) return;
                                const safeId = String(grNo).replace(/[^a-zA-Z0-9_-]/g, '_');
                                const label = row.label || (grNo + ' — ' + (row.student_name || row.name || ''));
                                const checked = keep.includes(String(grNo)) ? 'checked' : '';
                                container.append(`
                                    <div class="form-check student-checkbox-item mb-1">
                                        <input class="form-check-input student-checkbox" type="checkbox"
                                            name="gr_no[]" value="${grNo}" id="student_${safeId}" ${checked}>
                                        <label class="form-check-label" for="student_${safeId}">${label}</label>
                                    </div>
                                `);
                            });

                            const total = $('.student-checkbox').length;
                            const selected = $('.student-checkbox:checked').length;
                            $('#select_all_students').prop('checked', total > 0 && total === selected);
                        @endif
                    },
                    error: function() {
                        resetStudentList('Failed to load students');
                    }
                });
            }

            $('#academic_year_id, #standard_id, #division').on('change', function() {
                loadStudents(null);
            });

            @unless ($isEdit)
                $(document).on('change', '#select_all_students', function() {
                    $('.student-checkbox').prop('checked', $(this).is(':checked'));
                });

                $(document).on('change', '.student-checkbox', function() {
                    const total = $('.student-checkbox').length;
                    const selected = $('.student-checkbox:checked').length;
                    $('#select_all_students').prop('checked', total > 0 && total === selected);
                });

                $('#student_search').on('keyup', function() {
                    const value = $(this).val().toLowerCase();
                    $('#student_checkbox_list .student-checkbox-item').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });

                $('#teacherComplaintForm').on('submit', function(e) {
                    if ($('.student-checkbox').length > 0 && $('.student-checkbox:checked').length === 0) {
                        e.preventDefault();
                        alert('Please select at least one student.');
                    }
                });
            @endunless

            if ($('#academic_year_id').val() && $('#standard_id').val() && $('#division').val()) {
                loadStudents(initialGrNos);
            } else {
                resetStudentList('Select Academic Year, Standard & Class first');
            }

            const complaintTextarea = document.querySelector('#complaint');
            if (complaintTextarea && typeof autosize !== 'undefined') {
                autosize(complaintTextarea);
            }
        });
    </script>
@endsection
