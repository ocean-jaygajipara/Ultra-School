@extends('software.layout.app')

@php
    $isEdit = $isEdit ?? !empty($entry?->id);
    $route = $modules['route'] ?? 'leaving-certificate-entry';
    $page_title = $modules['title'] ?? 'Leaving Certi. Entry';
    $formTitle = $isEdit ? 'Edit ' . $page_title : 'Add ' . $page_title;
    $formAction = $isEdit
        ? route($route . '.update', $entry->id)
        : route($route . '.store');
@endphp
@section('title', $formTitle)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}">
    <style>
        .lce-photo {
            border: 1.5px dashed #c5cae8;
            border-radius: 14px;
            background: #eef7fd;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .lce-photo img { width: 100%; height: 220px; object-fit: cover; }
        .lce-photo-empty { color: #5f6488; font-size: .85rem; text-align: center; padding: 16px; }
        .mst-form-card .form-control[readonly] {
            background: #fff8e6;
            color: #9a3412;
            border-color: #f5d98a;
            font-weight: 700;
        }
    </style>
@endsection

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
                <div class="mst-hero-icon"><i class="ti ti-certificate"></i></div>
                <div>
                    <h4>{{ $formTitle }}</h4>
                    <p>{{ $isEdit ? 'Update leaving certificate details.' : 'Select GR No., auto fill details, then Submit.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
                <form id="lceForm" action="{{ $formAction }}" method="POST" autocomplete="off">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="id" id="entry_id" value="{{ old('id', $entry->id ?? '') }}">
                    <input type="hidden" name="admission_id" id="admission_id" value="{{ old('admission_id', $entry->admission_id ?? '') }}">
                    <input type="hidden" name="photo" id="photo" value="{{ old('photo', $entry->photo ?? '') }}">
                    <input type="hidden" name="mode" id="mode" value="{{ old('mode', $entry->mode ?? 'single') }}">

                    <div class="row g-3">
                        <div class="col-lg-9">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Academic Year</label>
                                    <select class="form-select select2" name="academic_year" id="academic_year" data-placeholder="Select Academic Year">
                                        <option value="">Select Academic Year</option>
                                        @foreach ($academicYears ?? [] as $year)
                                            <option value="{{ $year }}" @selected(old('academic_year', $entry->academic_year ?? $defaultAcademicYear ?? '') == $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Standard</label>
                                    <select id="filter_standard_id" class="form-select select2" data-placeholder="All Standards">
                                        <option value="">All Standards</option>
                                        @foreach ($standards ?? [] as $std)
                                            <option value="{{ $std->id }}">{{ $std->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gr No. <span class="text-danger">*</span></label>
                                    <select id="student_id" class="form-select select2 select2-student" required data-placeholder="Search GR / Name">
                                        <option value="">Search GR / Name</option>
                                    </select>
                                    <input type="hidden" name="gr_no" id="gr_no" value="{{ old('gr_no', $entry->gr_no ?? '') }}">
                                    <small class="text-muted">Search by GR No. or student name</small>
                                    @error('gr_no')
                                        <span class="text-danger small d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">UID No</label>
                                    <input type="text" class="form-control" name="uid_no" id="uid_no" value="{{ old('uid_no', $entry->uid_no ?? '') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="student_name" id="student_name" value="{{ old('student_name', $entry->student_name ?? '') }}" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Caste</label>
                                    <input type="text" class="form-control" name="caste" id="caste" value="{{ old('caste', $entry->caste ?? '') }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category</label>
                                    <input type="text" class="form-control" name="category" id="category" value="{{ old('category', $entry->category ?? '') }}" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Birthdate</label>
                                    <input type="text" class="form-control" name="birthdate" id="birthdate" value="{{ old('birthdate', $entry?->formatDate('birthdate') ?? '') }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Birthplace</label>
                                    <input type="text" class="form-control" name="birthplace" id="birthplace" value="{{ old('birthplace', $entry->birthplace ?? '') }}" readonly>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Last School</label>
                                    <input type="text" class="form-control" name="last_school" id="last_school" value="{{ old('last_school', $entry->last_school ?? '') }}" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Adm. Date</label>
                                    <input type="text" class="form-control" name="admission_date" id="admission_date" value="{{ old('admission_date', $entry?->formatDate('admission_date') ?? '') }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ad. Std.</label>
                                    <input type="text" class="form-control" name="admission_std" id="admission_std" value="{{ old('admission_std', $entry->admission_std ?? '') }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Last Std.</label>
                                    <input type="text" class="form-control" name="last_std" id="last_std" value="{{ old('last_std', $entry->last_std ?? '') }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Div</label>
                                    <input type="text" class="form-control" name="last_division" id="last_division" value="{{ old('last_division', $entry->last_division ?? '') }}" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Leave Date</label>
                                    <input type="text" class="form-control flatpickr-date" name="leave_date" id="leave_date" placeholder="DD-MM-YYYY" value="{{ old('leave_date', $entry?->formatDate('leave_date') ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">In which std studied</label>
                                    <select class="form-select select2" name="studied_std" id="studied_std" data-placeholder="Select Standard">
                                        <option value="">-</option>
                                        @foreach ($standards as $std)
                                            <option value="{{ $std->name }}" @selected(old('studied_std', $entry->studied_std ?? '') == $std->name)>{{ $std->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Leave Reason</label>
                                    <select class="form-select select2" name="leave_reason" id="leave_reason" data-placeholder="Select Leave Reason">
                                        @foreach ($leaveReasons as $reason)
                                            <option value="{{ $reason }}" @selected(old('leave_reason', $entry->leave_reason ?? 'TO GO ELSEWHERE') == $reason)>{{ $reason }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Behavior</label>
                                    <select class="form-select select2" name="behavior" id="behavior" data-placeholder="Select Behavior">
                                        @foreach ($behaviorOptions as $opt)
                                            <option value="{{ $opt }}" @selected(old('behavior', $entry->behavior ?? 'Good') == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Progress</label>
                                    <select class="form-select select2" name="progress" id="progress" data-placeholder="Select Progress">
                                        @foreach ($progressOptions as $opt)
                                            <option value="{{ $opt }}" @selected(old('progress', $entry->progress ?? 'Good') == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Atten.</label>
                                    <input type="text" class="form-control" name="attendance" id="attendance" value="{{ old('attendance', $entry->attendance ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From</label>
                                    <input type="text" class="form-control" name="attendance_from" id="attendance_from" value="{{ old('attendance_from', $entry->attendance_from ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Leaving Certi No.</label>
                                    <input type="text" class="form-control" name="leaving_certi_no" id="leaving_certi_no" value="{{ old('leaving_certi_no', $entry->leaving_certi_no ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Certi. Date</label>
                                    <input type="text" class="form-control flatpickr-date" name="certi_date" id="certi_date" placeholder="DD-MM-YYYY" value="{{ old('certi_date', $entry?->formatDate('certi_date') ?? '') }}">
                                </div>

                                <div class="col-md-9">
                                    <label class="form-label">Note</label>
                                    <input type="text" class="form-control" name="note" id="note" value="{{ old('note', $entry->note ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fee</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="fee" id="fee" value="{{ old('fee', $entry->fee ?? 0) }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Photo</label>
                            <div class="lce-photo" id="photoBox">
                                @php $photoPath = old('photo', $entry->photo ?? ''); @endphp
                                @if ($photoPath)
                                    <img src="{{ asset($photoPath) }}" alt="Student">
                                @else
                                    <div class="lce-photo-empty"><i class="ti ti-user mb-1 d-block" style="font-size:1.6rem"></i>Photo</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-center">
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
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        (function () {
            const findStudentUrl = @json(route('leaving-certificate-entry.find-student'));
            const initialAdmissionId = @json(old('admission_id', $entry->admission_id ?? null));
            const allStudents = @json(collect($students ?? [])->values());
            let loadingStudent = false;
            let syncingFilters = false;

            document.querySelectorAll('.flatpickr-date').forEach(function (el) {
                flatpickr(el, { dateFormat: 'd-m-Y', allowInput: true });
            });

            function initSelect2($el, options) {
                if (!(window.jQuery && jQuery.fn.select2) || !$el.length) return;
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                if (!$el.parent().hasClass('position-relative')) {
                    $el.wrap('<div class="position-relative"></div>');
                }
                const hasEmpty = $el.find('option[value=""]').length > 0;
                $el.select2($.extend({
                    width: '100%',
                    allowClear: hasEmpty,
                    placeholder: $el.data('placeholder') || 'Select',
                    dropdownParent: $el.parent()
                }, options || {}));
            }

            function initStudentSelect2() {
                initSelect2(jQuery('#student_id'), {
                    placeholder: 'Search GR / Name',
                    matcher: function (params, data) {
                        if ($.trim(params.term) === '') return data;
                        if (typeof data.text === 'undefined') return null;
                        const term = params.term.toLowerCase();
                        const text = (data.text || '').toLowerCase();
                        const gr = String($(data.element).data('gr') || '').toLowerCase();
                        if (text.indexOf(term) > -1 || gr.indexOf(term) > -1) {
                            return data;
                        }
                        return null;
                    }
                });
            }

            function renderStudentsByStandard(standardId, selectedId) {
                if (!window.jQuery) return;
                const $student = jQuery('#student_id');
                const filtered = (allStudents || []).filter(function (s) {
                    return !standardId || String(s.standard_id || '') === String(standardId);
                });
                const groups = {};
                filtered.forEach(function (s) {
                    const key = s.standard_name || 'Other';
                    if (!groups[key]) groups[key] = [];
                    groups[key].push(s);
                });

                if ($student.hasClass('select2-hidden-accessible')) {
                    $student.select2('destroy');
                }
                $student.empty().append('<option value="">Search GR / Name</option>');
                Object.keys(groups).forEach(function (groupName) {
                    const $group = jQuery('<optgroup>').attr('label', groupName);
                    groups[groupName].forEach(function (s) {
                        $group.append(
                            jQuery('<option>')
                                .val(s.id)
                                .attr('data-gr', s.gr_no || '')
                                .attr('data-standard', s.standard_id || '')
                                .text(s.label)
                        );
                    });
                    $student.append($group);
                });
                initStudentSelect2();
                if (selectedId) {
                    loadingStudent = true;
                    $student.val(String(selectedId)).trigger('change.select2');
                    loadingStudent = false;
                } else {
                    $student.val(null).trigger('change');
                }
            }

            function setPhoto(url) {
                const box = document.getElementById('photoBox');
                if (url) {
                    box.innerHTML = '<img src="' + url + '" alt="Student">';
                } else {
                    box.innerHTML = '<div class="lce-photo-empty"><i class="ti ti-user mb-1 d-block" style="font-size:1.6rem"></i>Photo</div>';
                }
            }

            function fillForm(data) {
                if (!data) return;
                const map = [
                    'admission_id', 'academic_year', 'uid_no', 'gr_no', 'student_name', 'caste', 'category',
                    'birthdate', 'birthplace', 'last_school', 'admission_date', 'admission_std', 'last_std',
                    'last_division', 'leave_date', 'studied_std', 'leave_reason', 'behavior',
                    'progress', 'attendance', 'attendance_from', 'leaving_certi_no', 'certi_date', 'note', 'fee', 'photo'
                ];
                map.forEach(function (elId) {
                    const el = document.getElementById(elId);
                    if (el && data[elId] !== undefined && data[elId] !== null) {
                        el.value = data[elId];
                    }
                });
                if (window.jQuery && jQuery.fn.select2) {
                    ['academic_year', 'studied_std', 'leave_reason', 'behavior', 'progress'].forEach(function (id) {
                        const $sel = jQuery('#' + id);
                        if ($sel.length && $sel.hasClass('select2-hidden-accessible')) {
                            $sel.trigger('change.select2');
                        }
                    });
                }
                setPhoto(data.photo_url || '');
            }

            function toast(msg, ok) {
                if (window.toastr) {
                    ok ? toastr.success(msg) : toastr.error(msg);
                } else if (window.Swal) {
                    Swal.fire({ icon: ok ? 'success' : 'error', title: ok ? 'Done' : 'Error', text: msg });
                } else {
                    alert(msg);
                }
            }

            function loadStudentById(admissionId) {
                if (!admissionId) return;
                const excludeId = document.getElementById('entry_id').value || '';
                let url = findStudentUrl + '?admission_id=' + encodeURIComponent(admissionId);
                if (excludeId) {
                    url += '&exclude_id=' + encodeURIComponent(excludeId);
                }
                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res.success) {
                            toast(res.message || 'Not found', false);
                            if (window.jQuery) {
                                jQuery('#student_id').val(null).trigger('change.select2');
                            }
                            document.getElementById('admission_id').value = '';
                            document.getElementById('gr_no').value = '';
                            document.getElementById('student_name').value = '';
                            setPhoto('');
                            return;
                        }
                        fillForm(res.data);
                    })
                    .catch(function () { toast('Unable to load student.', false); });
            }

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#lceForm select.select2').not('#student_id').each(function () {
                    initSelect2(jQuery(this));
                });

                let selectedId = initialAdmissionId || null;
                let stdId = '';
                if (selectedId) {
                    const meta = (allStudents || []).find(function (s) {
                        return String(s.id) === String(selectedId);
                    });
                    stdId = meta ? (meta.standard_id || '') : '';
                    if (stdId) {
                        syncingFilters = true;
                        jQuery('#filter_standard_id').val(String(stdId)).trigger('change.select2');
                        syncingFilters = false;
                    }
                }
                renderStudentsByStandard(stdId, selectedId);

                jQuery('#filter_standard_id').on('change', function () {
                    if (syncingFilters) return;
                    renderStudentsByStandard(jQuery(this).val(), null);
                    document.getElementById('admission_id').value = '';
                    document.getElementById('gr_no').value = '';
                    document.getElementById('student_name').value = '';
                    setPhoto('');
                });

                jQuery(document).on('change', '#student_id', function () {
                    if (loadingStudent) return;
                    const admissionId = jQuery(this).val();
                    if (!admissionId) {
                        document.getElementById('admission_id').value = '';
                        document.getElementById('gr_no').value = '';
                        document.getElementById('student_name').value = '';
                        setPhoto('');
                        return;
                    }
                    document.getElementById('admission_id').value = admissionId;
                    const gr = jQuery(this).find(':selected').data('gr') || '';
                    if (gr) document.getElementById('gr_no').value = gr;
                    loadStudentById(admissionId);
                });
            }

            document.getElementById('lceForm').addEventListener('submit', function () {
                const $stu = window.jQuery ? jQuery('#student_id') : null;
                if ($stu && $stu.val()) {
                    document.getElementById('admission_id').value = $stu.val();
                    const gr = $stu.find(':selected').data('gr') || document.getElementById('gr_no').value;
                    if (gr) document.getElementById('gr_no').value = gr;
                }
            });
        })();
    </script>
@endsection
