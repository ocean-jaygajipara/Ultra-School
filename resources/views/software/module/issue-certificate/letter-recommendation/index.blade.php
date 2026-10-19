@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Letter of Recommendation';
    $route = $modules['route'] ?? null;
@endphp

@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/select2/select2.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="letterRecommendationForm" method="POST" action="{{ route('issue-certificate.store-history') }}">
                        @csrf
                        <input type="hidden" name="certificate_type" value="letter-recommendation">

                        <!-- Row 1: Standard, Student, Date, Recommender Type -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label class="form-label">Standard</label>
                                    <select class="form-select select2-standard" id="filter_standard_id">
                                        <option value="">All Standards</option>
                                        @foreach ($standards ?? [] as $std)
                                            <option value="{{ $std->id }}">{{ $std->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label class="form-label">Student <span class="text-danger">*</span></label>
                                    <select class="form-select select2-student" id="student" name="student_id" required>
                                        <option value="">-- Select Student --</option>
                                        @php
                                            $groupedStudents = collect($students)->groupBy('standard_name');
                                        @endphp
                                        @foreach ($groupedStudents as $stdGroup => $stdStudents)
                                            <optgroup label="{{ $stdGroup }}">
                                                @foreach ($stdStudents as $student)
                                                    <option value="{{ $student['id'] }}"
                                                            data-standard="{{ $student['standard_id'] ?? '' }}"
                                                            data-standard-name="{{ $student['standard_name'] ?? '' }}">
                                                        {{ $student['label'] }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="issue_date"
                                        value="{{ $defaults['issue_date'] ?? now()->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label class="form-label">Recommender Type</label>

                                    <div class="d-flex gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="recommender_type" value="principal"
                                                id="principal" checked>

                                            <label class="form-check-label" for="principal">
                                                Principal
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="recommender_type" value="faculty"
                                                id="faculty">

                                            <label class="form-check-label" for="faculty">
                                                Faculty
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Faculty Section -->
                        <div class="row d-none" id="facultySection">
                        {{-- <div class="row" id="facultySection" style="display:none;"> --}}
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Recommender Designation</label>
                                    <input type="text"
                                           class="form-control"
                                           id="recommenderDesignation"
                                           name="recommender_designation"
                                           value="Faculty"
                                           readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Select Faculty</label>
                                    <select class="form-select select2" id="selectFaculty" name="faculty_id">
                                        <option value="">Select Faculty</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" id="generate_certificate" class="btn btn-primary">
                                    <i class="ti ti-certificate"></i> Generate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Generate History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0" id="certificate-history-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Student Name</th>
                                    <th>Date</th>
                                    <th>Recommender</th>
                                    <th class="text-center" style="width: 140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="certificate-history-body">
                                @forelse ($histories as $index => $history)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $history['student_name'] ?? '-' }}</td>
                                        <td>{{ $history['issue_date'] ?? '-' }}</td>
                                        <td>{{ $history['recommender_name'] ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a href="{{ $history['certificate_url'] }}" target="_blank" class="btn btn-sm btn-secondary" title="View Certificate">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger delete-history-btn" data-id="{{ $history['id'] }}" title="Delete History">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="certificate-history-empty-row">
                                        <td colspan="5" class="text-center text-muted py-3">No history found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('admin/assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const principalRadio = document.getElementById("principal");
        const facultyRadio = document.getElementById("faculty");

        const facultySection = document.getElementById("facultySection");
        const designation = document.getElementById("recommenderDesignation");

        // function toggleSection() {
        //     if (principalRadio.checked) {
        //         facultySection.style.display = "none";
        //     } else {
        //         facultySection.style.display = "block";
        //         designation.value = "Faculty";
        //     }
        // }
        function toggleSection() {

    if (principalRadio.checked) {

        facultySection.classList.add('d-none');

        $('#selectFaculty').val('').trigger('change');

    } else {

        facultySection.classList.remove('d-none');

        designation.value = "Faculty";
    }
}


        principalRadio.addEventListener("change", toggleSection);
        facultyRadio.addEventListener("change", toggleSection);

        toggleSection();

        const rawStudents = @json($students ?? []);

        $('.select2-standard').select2({
            placeholder: 'All Standards',
            allowClear: true,
            width: '100%'
        });

        $('#student').select2({
            placeholder: '-- Select Student --',
            allowClear: true,
            width: '100%'
        });

        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        });

        $('#filter_standard_id').on('change', function() {
            const selectedStdId = $(this).val();
            const $studentSelect = $('#student');
            $studentSelect.empty().append(new Option('-- Select Student --', '', true, true));

            const filtered = selectedStdId
                ? rawStudents.filter(s => String(s.standard_id) === String(selectedStdId))
                : rawStudents;

            const groups = {};
            filtered.forEach(s => {
                const group = s.standard_name || 'Other';
                if (!groups[group]) groups[group] = [];
                groups[group].push(s);
            });

            Object.keys(groups).forEach(groupName => {
                const $group = $('<optgroup>').attr('label', groupName);
                groups[groupName].forEach(s => {
                    const $opt = $('<option>')
                        .val(s.id)
                        .text(s.label)
                        .attr('data-standard', s.standard_id || '')
                        .attr('data-standard-name', s.standard_name || '');
                    $group.append($opt);
                });
                $studentSelect.append($group);
            });

            $studentSelect.val(null).trigger('change');
        });

        // Form submission
        $('#letterRecommendationForm').on('submit', function(e) {
            e.preventDefault();

            const studentId = $('#student').val();
            if (!studentId) {
                alert('Please select a student');
                return;
            }

            const recommenderType = $('input[name="recommender_type"]:checked').val();
            const facultyId = $('#selectFaculty').val();

            if (recommenderType === 'faculty' && !facultyId) {
                alert('Please select a faculty member');
                return;
            }

            const formData = {
                _token: '{{ csrf_token() }}',
                certificate_type: 'letter-recommendation',
                student_id: studentId,
                issue_date: $('#date').val(),
                recommender_type: recommenderType,
                faculty_id: facultyId || null,
                recommender_name: recommenderType === 'principal' ? 'Hitesh Vadalia' : $('#selectFaculty option:selected').text(),
                recommender_designation: recommenderType === 'principal' ? 'I/C. Principal' : $('#recommenderDesignation').val(),
            };

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        // Add new row to history table
                        const rowNum = $('#certificate-history-body tr:not(#certificate-history-empty-row)').length + 1;
                        const row = `
                            <tr>
                                <td class="text-center">${rowNum}</td>
                                <td>${response.data.student_name || '-'}</td>
                                <td>${response.data.issue_date || '-'}</td>
                                <td>${response.data.recommender_name || '-'}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="${response.data.certificate_url}" target="_blank" class="btn btn-sm btn-secondary" title="View Certificate">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-history-btn" data-id="${response.data.id}" title="Delete History">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;

                        $('#certificate-history-empty-row').remove();
                        $('#certificate-history-body').prepend(row);

                        // Reindex rows
                        $('#certificate-history-body tr').each(function(index) {
                            $(this).find('td:first').text(index + 1);
                        });

                        // Open certificate in new tab
                        window.open(response.data.certificate_url, '_blank');

                        // Reset form
                        $('#letterRecommendationForm')[0].reset();
                        $('#student').val('').trigger('change');
                        $('#selectFaculty').val('').trigger('change');
                        $('#principal').prop('checked', true).trigger('change');

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Certificate generated successfully');
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    const message = response?.message || 'An error occurred';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert(message);
                    }
                }
            });
        });

        $(document).on('click', '.delete-history-btn', function() {
            const $btn = $(this);
            const id = $btn.data('id');
            const $row = $btn.closest('tr');

            const doDelete = function() {
                $btn.prop('disabled', true);
                $.ajax({
                    url: "{{ url('issue-certificate/history') }}/" + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE',
                        certificate_type: 'letter-recommendation'
                    },
                    success: function(response) {
                        if (response.status) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Record deleted successfully.');
                            }
                            $row.fadeOut(200, function() {
                                $(this).remove();
                                $('#certificate-history-body tr').each(function(index) {
                                    $(this).find('td:first').text(index + 1);
                                });
                                if ($('#certificate-history-body tr').length === 0) {
                                    $('#certificate-history-body').html(`
                                        <tr id="certificate-history-empty-row">
                                            <td colspan="5" class="text-center text-muted py-3">No history found</td>
                                        </tr>
                                    `);
                                }
                            });
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'Failed to delete record.');
                            } else {
                                alert(response.message || 'Failed to delete record.');
                            }
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Could not delete record. Please try again.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(message);
                        } else {
                            alert(message);
                        }
                        $btn.prop('disabled', false);
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "Are you sure?",
                    text: "This certificate history record will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            } else {
                if (confirm("Are you sure you want to delete this certificate history?")) {
                    doDelete();
                }
            }
        });
    });
    </script>
@endsection
