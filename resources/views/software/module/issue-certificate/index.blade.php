@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Issue Certificate';
    $route = $modules['route'] ?? null;
    $certificateRoute = $modules['certificate_route'] ?? null;
    $certificateType = $modules['certificate_type'] ?? 'bonafide';
    $hideExtraColumns = in_array($certificateType, ['english-medium'], true);
    $showAcademicYear = !in_array($certificateType, ['english-medium', 'letter-recommendation'], true);
    $historyCount = collect($histories ?? [])->count();

    [$heroIcon, $heroText] = match ($certificateType) {
        'tc' => ['ti-file-export', 'Generate the Transfer Certificate (school leaving) for a student.'],
        'english-medium' => ['ti-language', 'Generate the Medium of Instruction certificate for a student.'],
        default => ['ti-certificate', 'Generate a bonafide certificate for a studying student.'],
    };
@endphp
@section('title', $page_title)

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

        .icx-stat {
            position: relative;
            z-index: 1;
            min-width: 120px;
            padding: 9px 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .22);
        }

        .icx-stat small {
            display: block;
            font-size: .68rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--sky);
        }

        .icx-stat strong {
            font-size: 1.35rem;
            color: #fff;
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

        .icx-card-body {
            padding: 20px;
        }

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

        .icx .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
        }

        .icx .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .icx .form-control:focus {
            border-color: var(--navy-2);
            box-shadow: 0 0 0 .2rem rgba(46, 58, 158, .12);
        }

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
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .icx-generate:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(225, 20, 28, .3);
            color: #fff;
        }

        .icx-generate:disabled {
            opacity: .7;
            transform: none;
        }

        .icx-search {
            position: relative;
            width: 260px;
            max-width: 100%;
        }

        .icx-search i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
        }

        .icx-search input {
            height: 38px;
            padding-left: 34px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .icx-table {
            margin: 0;
        }

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

        .icx-table tbody tr:hover td {
            background: var(--sky-soft);
        }

        .icx-who {
            font-weight: 600;
            color: #20245e !important;
        }

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
            white-space: nowrap;
        }

        .icx-muted {
            color: var(--muted);
            font-size: .84rem;
        }

        .icx-view {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 9px;
            font-size: .8rem;
            font-weight: 600;
            background: #e7e9f8;
            color: var(--navy);
            transition: all .15s ease;
        }

        .icx-view:hover {
            background: var(--navy);
            color: #fff;
        }

        .icx-empty td {
            padding: 34px 14px !important;
            text-align: center;
            color: var(--muted);
        }

        .icx-empty i {
            display: block;
            font-size: 2rem;
            color: var(--sky);
            margin-bottom: 6px;
        }

        @media (max-width: 767px) {
            .icx-hero {
                padding: 18px;
            }

            .icx-search {
                width: 100%;
            }
        }
    </style>
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

    <div class="icx my-2">
        <div class="icx-hero">
            <div class="icx-hero-main">
                <span class="icx-hero-icon"><i class="ti {{ $heroIcon }}"></i></span>
                <div>
                    <h4>{{ $page_title }}</h4>
                    <p>{{ $heroText }}</p>
                </div>
            </div>
            <div class="icx-stat">
                <small>Generated</small>
                <strong id="icxHistoryCount">{{ $historyCount }}</strong>
            </div>
        </div>

        <div class="icx-card">
            <div class="icx-card-head">
                <h5><i class="ti ti-file-plus"></i> Generate Certificate</h5>
            </div>
            <div class="icx-card-body">
                <form id="certificateForm" onsubmit="return false;">
                    <div class="row g-3 align-items-start">
                        <div class="{{ $showAcademicYear ? 'col-lg-6' : 'col-lg-8' }} col-md-12">
                            <label class="form-label" for="student_id">Student <span class="text-danger">*</span></label>
                            <select id="student_id" name="student_id" class="form-control select2" required>
                                <option value="">Select Student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student['id'] }}">{{ $student['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="icx-hint">Search by name or GR No.</div>
                        </div>

                        <div class="{{ $showAcademicYear ? 'col-lg-3' : 'col-lg-4' }} col-md-6">
                            <label class="form-label" for="issue_date">Issue Date</label>
                            <input type="date" id="issue_date" name="issue_date" class="form-control"
                                value="{{ $defaults['issue_date'] ?? now()->format('Y-m-d') }}">
                        </div>

                        @if ($showAcademicYear)
                            <div class="col-lg-3 col-md-6">
                                @php
                                    $defaultYear = $defaults['academic_year'] ?? '';
                                    $yearList = $academicYears ?? [];
                                @endphp
                                <label class="form-label" for="academic_year">Academic Year</label>
                                <select id="academic_year" name="academic_year" class="form-control select2-academic-year">
                                    <option value="">Select Academic Year</option>
                                    @foreach ($yearList as $yItem)
                                        <option value="{{ $yItem }}" {{ $defaultYear == $yItem ? 'selected' : '' }}>{{ $yItem }}</option>
                                    @endforeach
                                </select>
                                @if (empty($yearList))
                                    <small class="text-danger d-block mt-1">
                                        No academic year found. <a href="{{ route('academic-year.index') }}">Add in Academic Year Master</a>
                                    </small>
                                @endif
                            </div>
                        @endif

                        <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                            <button type="button" id="generate_certificate" class="icx-generate">
                                <i class="ti ti-file-check"></i> Generate
                            </button>
                            <span class="icx-hint m-0"><i class="ti ti-info-circle"></i> The certificate is saved in the history below. Click View there to see or print it.</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="icx-card">
            <div class="icx-card-head">
                <h5><i class="ti ti-history"></i> Generate History</h5>
                <div class="icx-search">
                    <i class="ti ti-search"></i>
                    <input type="search" id="icxHistorySearch" class="form-control" placeholder="Search student...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table icx-table" id="certificate-history-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Student Name</th>
                            <th>Issue Date</th>
                            @unless ($hideExtraColumns)
                                <th>Academic Year</th>
                                <th>Generated At</th>
                            @endunless
                            <th class="text-center" style="width: 110px;">View</th>
                        </tr>
                    </thead>
                    <tbody id="certificate-history-body">
                        @include('software.module.issue-certificate.partials.history-rows', [
                            'histories' => $histories ?? collect(),
                            'hideExtraColumns' => $hideExtraColumns,
                        ])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        const certificateType = @json($certificateType);
        const hideExtraColumns = @json($hideExtraColumns);

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function renumberHistory() {
            $('#certificate-history-body tr:not(#certificate-history-empty-row)').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
            $('#icxHistoryCount').text($('#certificate-history-body tr:not(#certificate-history-empty-row)').length);
        }

        function prependHistoryRow(row) {
            $('#certificate-history-empty-row').remove();

            const html = `
                <tr>
                    <td class="text-center">1</td>
                    <td class="icx-who">${escapeHtml(row.student_name || '-')}</td>
                    <td>${escapeHtml(row.issue_date || '-')}</td>
                    ${hideExtraColumns ? '' : `
                        <td>${row.academic_year ? `<span class="icx-pill"><i class="ti ti-calendar"></i> ${escapeHtml(row.academic_year)}</span>` : '-'}</td>
                        <td class="icx-muted">${escapeHtml(row.generated_at || '-')}</td>
                    `}
                    <td class="text-center">
                        <a href="${row.certificate_url}" target="_blank" class="icx-view" title="View Certificate">
                            <i class="ti ti-eye"></i> View
                        </a>
                    </td>
                </tr>
            `;

            $('#certificate-history-body').prepend(html);
            renumberHistory();
        }

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select Student',
                allowClear: true,
                width: '100%'
            });

            $('.select2-academic-year').select2({
                placeholder: 'Select Academic Year',
                allowClear: true,
                width: '100%'
            });

            $('#icxHistorySearch').on('input', function() {
                const term = $(this).val().toLowerCase().trim();
                $('#certificate-history-body tr:not(#certificate-history-empty-row)').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(term) !== -1);
                });
            });

            $('#generate_certificate').on('click', function() {
                const studentId = $('#student_id').val();

                if (!studentId) {
                    const message = 'Please select student.';
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(message);
                    } else {
                        alert(message);
                    }
                    $('#student_id').select2('open');
                    return;
                }

                const $btn = $(this).prop('disabled', true);

                $.ajax({
                    url: "{{ route('issue-certificate.store-history') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        certificate_type: certificateType,
                        student_id: studentId,
                        issue_date: $('#issue_date').val() || '',
                        academic_year: $('#academic_year').val() || ''
                    },
                    success: function(response) {
                        if (response?.data) {
                            prependHistoryRow(response.data);
                        }
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Certificate generated. Click View in the history to see it.');
                        }
                        $('#student_id').val(null).trigger('change');
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        const message = Object.values(errors).flat()[0] || xhr.responseJSON?.message || 'Certificate could not be generated. Please try again.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(message);
                        } else {
                            alert(message);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
