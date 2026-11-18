@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $selectedClasses = $selectedClasses ?? (isset($edit) && $edit->class_id ? array_filter(array_map('trim', explode(',', $edit->class_id))) : []);
@endphp

@section('title', $page_title)

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => (isset($edit) && $edit?->id ? 'Edit' : 'Add') . ' ' . $page_title, 'url' => ''],
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
                <div class="mst-hero-icon"><i class="ti ti-bell"></i></div>
                <div>
                    <h4>{{ isset($edit) && $edit?->id ? 'Edit' : 'Add' }} {{ $page_title }}</h4>
                    <p>{{ isset($edit) && $edit?->id ? 'Update announcement details.' : 'Create a new announcement for students.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('academic_year_id') is-invalid @enderror"
                                name="academic_year_id" id="academicYearSelect" required>
                                <option value="">Select Academic Year</option>
                                @foreach ($academicYears as $yearItem)
                                    <option value="{{ $yearItem->id }}"
                                        @selected((string) old('academic_year_id', $edit->academic_year_id ?? ($currentAcademicYear->id ?? '')) === (string) $yearItem->id)>
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
                            <select class="form-control select2 @error('standard_id') is-invalid @enderror" name="standard_id" id="standardSelect">
                                <option value="">Select Standard</option>
                                @foreach ($standards as $id => $standard_name)
                                    <option value="{{ $id }}"
                                        {{ (isset($edit) && $edit?->standard_id == $id) || old('standard_id') == $id ? 'selected' : '' }}>
                                        {{ $standard_name }}
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
                            <label class="form-label d-block">Class <span class="text-danger">*</span></label>
                            <div id="classCheckboxContainer" class="form-control d-flex flex-wrap align-items-center @error('class_id') is-invalid @enderror" style="min-height: 38px; border: 1px solid #ced4da; border-radius: 4px; padding: 6px 12px; background: #fff; gap: 15px;">
                                @forelse ($divisions as $division_name)
                                    <div class="form-check form-check-inline mb-0 me-3">
                                        <input class="form-check-input class-checkbox" type="checkbox" name="class_id[]" value="{{ $division_name }}" id="class_chk_{{ Str::slug($division_name) }}"
                                            {{ in_array($division_name, $selectedClasses) || (is_array(old('class_id')) && in_array($division_name, old('class_id'))) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="class_chk_{{ Str::slug($division_name) }}">{{ $division_name }}</label>
                                    </div>
                                @empty
                                    <span class="text-muted">No classes found</span>
                                @endforelse
                            </div>
                            @error('class_id')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Student <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-link p-0 text-danger" id="clearStudentsBtn" style="text-decoration: none;"><i class="fa-solid fa-xmark me-1"></i>Clear Selection</button>
                            </div>
                            <select class="form-control select2 @error('student_id') is-invalid @enderror"
                                name="student_id[]" id="studentDropdown" multiple>
                                <option value="all">Select All</option>
                                @if (isset($students) && count($students) > 0)
                                    @foreach ($students as $student)
                                        <option value="{{ $student['id'] }}"
                                            {{ in_array((string) $student['id'], array_map('strval', $selectedStudents ?? [])) ? 'selected' : '' }}>
                                            {{ $student['name'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            @error('student_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title"
                                value="{{ isset($edit?->title) ? $edit->title : old('title') }}" placeholder="Enter title">
                            @error('title')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Body <span class="text-danger">*</span></label>
                            <textarea id="body" name="body" rows="5" class="form-control @error('body') is-invalid @enderror"
                                placeholder="Enter message">{{ isset($edit?->body) ? $edit->body : old('body') }}</textarea>

                            @error('body')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Notification Image</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @if(isset($edit) && $edit->image)
                                <div class="mt-2">
                                    <img src="{{ asset($edit->image) }}" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            @error('image')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Notification PDF Document</label>
                            <input type="file" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept="application/pdf">
                            @if(isset($edit) && $edit->pdf)
                                <div class="mt-2">
                                    <a href="{{ asset($edit->pdf) }}" target="_blank" class="btn btn-sm btn-info">View Uploaded PDF</a>
                                </div>
                            @endif
                            @error('pdf')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12 text-center mt-5">
                        <button type="submit"
                            class="btn btn-success mt-1">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                    </div>
                </div>
            </form>
                    </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var studentDropdown = $('#studentDropdown');
            var academicYearSelect = $('#academicYearSelect');
            var standardSelect = $('#standardSelect');

            function fetchStudents() {
                let academicYearId = academicYearSelect.val();
                let standardId = standardSelect.val();
                let classIds = [];
                $('.class-checkbox:checked').each(function() {
                    classIds.push($(this).val());
                });

                if (!academicYearId || !standardId) {
                    studentDropdown.empty().append('<option value="all">Select All</option>').val(null).trigger('change.select2');
                    return;
                }

                studentDropdown.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: "{{ route('get.students.by.standard', ['standard_id' => '__SID__']) }}".replace('__SID__', standardId),
                    type: "GET",
                    data: {
                        academic_year_id: academicYearId,
                        class_id: classIds
                    },
                    success: function(data) {
                        studentDropdown.empty().append('<option value="all">Select All</option>');
                        var allIds = [];
                        $.each(data, function(key, student) {
                            studentDropdown.append('<option value="' + student.id + '">' + student.name + '</option>');
                            allIds.push(String(student.id));
                        });
                        studentDropdown.val(allIds).trigger('change.select2');
                    },
                    error: function() {
                        studentDropdown.empty().append('<option value="">Error loading students</option>');
                    }
                });
            }

            academicYearSelect.on('change', function() {
                studentDropdown.empty().append('<option value="all">Select All</option>').val(null).trigger('change.select2');
                fetchStudents();
            });

            standardSelect.on('change', function() {
                studentDropdown.empty().append('<option value="all">Select All</option>').val(null).trigger('change.select2');
                fetchStudents();
            });

            $(document).on('change', '.class-checkbox', function() {
                fetchStudents();
            });

            if (academicYearSelect.val() && standardSelect.val()) {
                fetchStudents();
            }

            $('#clearStudentsBtn').on('click', function() {
                studentDropdown.val(null).trigger('change.select2');
            });

            studentDropdown.on('select2:select', function(e) {
                if (e.params.data.id === 'all') {
                    var allIds = [];
                    studentDropdown.find('option').each(function() {
                        if ($(this).val() !== 'all') allIds.push($(this).val());
                    });
                    studentDropdown.val(allIds).trigger('change.select2');
                }
            });

            studentDropdown.on('select2:unselect', function(e) {
                if (e.params.data.id !== 'all') {
                    studentDropdown.find('option[value="all"]').prop('selected', false);
                }
            });

        });
    </script>

@endsection
