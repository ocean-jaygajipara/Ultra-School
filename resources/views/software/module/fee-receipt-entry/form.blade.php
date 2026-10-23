@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Fee Receipt Entry';
    $route = $modules['route'] ?? 'fee-receipt-entry';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .fre-hero {
            background: linear-gradient(120deg, #1f2580, #2e3a9e);
            color: #fff !important;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 18px;
        }
        .fre-hero h4 { margin: 0; font-weight: 800; color: #fff !important; }
        .fre-hero p { margin: 4px 0 0; color: rgba(255,255,255,.9) !important; font-size: .9rem; }
        .fre-table th {
            background: #f7f8fd;
            color: #1f2580;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .fre-table td { vertical-align: middle; }
        .fre-table .fee-label { font-weight: 700; color: #1f2580; }
        .fre-readonly {
            background: #fff8e6 !important;
            color: #c0392b !important;
            font-weight: 700;
        }
        .fre-total-row td {
            background: #eef7fd;
            font-weight: 800;
        }
        .fre-student-wrap { position: relative; }
        .fre-student-list {
            display: none;
            position: absolute;
            z-index: 40;
            left: 0;
            right: 0;
            top: calc(100% + 4px);
            max-height: 360px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #d5dae8;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, .14);
        }
        .fre-student-item {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 8px 12px;
            min-height: 36px;
            cursor: pointer;
            border-bottom: 1px solid #eef0f8;
            color: #1f2580;
            font-weight: 600;
            font-size: .9rem;
        }
        .fre-student-item:last-child { border-bottom: 0; }
        .fre-student-item:hover,
        .fre-student-item.is-active { background: #eef7fd; }
        .fre-student-item small {
            min-width: 64px;
            color: #5f6488;
            font-weight: 700;
        }
        .fre-student-empty {
            padding: 10px 12px;
            color: #5f6488;
            font-size: .85rem;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => 'New Entry', 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="my-3">
        <div class="fre-hero">
            <h4>Fee Receipt Entry</h4>
            <p>Select student by GR No, load Fee Master amounts, then save / print receipt.</p>
        </div>

        <form method="POST" action="{{ route($route . '.store') }}" id="feeReceiptForm">
            @csrf
            <input type="hidden" name="admission_id" id="admission_id" value="">

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                            <select name="academic_year_id" id="academic_year_id" class="form-select select2" required>
                                <option value="">Select Academic Year</option>
                                @foreach ($academicYears as $yearItem)
                                    <option value="{{ $yearItem->id }}"
                                        {{ (string) old('academic_year_id', $currentAcademicYear->id ?? '') === (string) $yearItem->id ? 'selected' : '' }}>
                                        {{ $yearItem->name }}{{ $yearItem->is_current ? ' (Current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Std. <span class="text-danger">*</span></label>
                            <select name="standard_id" id="standard_id" class="form-select select2" required>
                                <option value="">Select Standard</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std->id }}" {{ (string) old('standard_id') === (string) $std->id ? 'selected' : '' }}>
                                        {{ $std->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Class</label>
                            <select name="division" id="division" class="form-select select2">
                                <option value="">Select Class</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div }}" {{ old('division') === $div ? 'selected' : '' }}>{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                            <select name="term" id="term" class="form-select select2" required>
                                <option value="">Select Term</option>
                                @foreach ($terms as $key => $label)
                                    <option value="{{ $key }}" {{ old('term') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Student Name <span class="text-danger">*</span></label>
                            <div class="fre-student-wrap">
                                <input type="text" name="student_name" id="student_name" class="form-control"
                                    autocomplete="off"
                                    placeholder="Select Std and Class, then pick student"
                                    value="{{ old('student_name') }}" required>
                                <div id="studentList" class="fre-student-list"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">G.R. No</label>
                            <div class="input-group">
                                <input type="text" name="gr_no" id="gr_no" class="form-control"
                                    value="{{ old('gr_no') }}" placeholder="GR No">
                                <button type="button" class="btn btn-outline-primary" id="btnFindStudent" title="Find Student">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Roll Number</label>
                            <input type="text" name="roll_no" id="roll_no" class="form-control"
                                value="{{ old('roll_no') }}" readonly placeholder="Auto">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control"
                                value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered fre-table mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:180px">Fee Type</th>
                                    <th class="text-end" style="min-width:140px">Fee</th>
                                    <th class="text-end" style="min-width:140px">Term Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fee-label">Last Yr. Pending Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="last_yr_pending" id="last_yr_pending"
                                            class="form-control text-end fee-input" value="{{ old('last_yr_pending', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_last_yr" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">Admission Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="admission_fee" id="admission_fee"
                                            class="form-control text-end fee-input" value="{{ old('admission_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_admission" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">Term Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="term_fee" id="term_fee"
                                            class="form-control text-end fee-input" value="{{ old('term_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_term" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">School Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="school_fee" id="school_fee"
                                            class="form-control text-end fee-input" value="{{ old('school_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_school" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">Vehicle Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="vehicle_fee" id="vehicle_fee"
                                            class="form-control text-end fee-input" value="{{ old('vehicle_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_vehicle" value="0.00" readonly></td>
                                </tr>
                                <tr class="fre-total-row">
                                    <td>Total</td>
                                    <td><input type="text" id="total_display" class="form-control text-end fre-readonly" value="0.00" readonly></td>
                                    <td><input type="text" id="term_total_display" class="form-control text-end fre-readonly" value="0.00" readonly></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3 mt-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Less</label>
                            <input type="number" step="0.01" min="0" name="less_amount" id="less_amount"
                                class="form-control" value="{{ old('less_amount', 0) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Net Amt</label>
                            <input type="text" id="net_amount_display" class="form-control fre-readonly fw-bold" value="0.00" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Mode</label>
                            <select name="mode" id="mode" class="form-select select2">
                                @foreach (['Cash', 'Online', 'UPI', 'Cheque'] as $mode)
                                    <option value="{{ $mode }}" {{ old('mode', 'Cash') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Remark</label>
                            <input type="text" name="remark" class="form-control" value="{{ old('remark') }}" placeholder="Remark">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="ti ti-device-floppy me-1"></i> Save
                        </button>
                        <button type="submit" name="print_after_save" value="1" class="btn btn-primary px-4">
                            <i class="ti ti-printer me-1"></i> Save & Print
                        </button>
                        <button type="reset" class="btn btn-outline-secondary px-4" id="btnClear">
                            <i class="ti ti-x me-1"></i> Clear
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger px-4">
                            <i class="ti ti-door-exit me-1"></i> Exit
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            function money(n) {
                var v = parseFloat(n);
                if (isNaN(v) || v < 0) v = 0;
                return Math.round(v * 100) / 100;
            }

            function calc() {
                var last = money($('#last_yr_pending').val());
                var adm = money($('#admission_fee').val());
                var term = money($('#term_fee').val());
                var school = money($('#school_fee').val());
                var vehicle = money($('#vehicle_fee').val());
                var less = money($('#less_amount').val());

                $('#term_last_yr').val(last.toFixed(2));
                $('#term_admission').val(adm.toFixed(2));
                $('#term_term').val(term.toFixed(2));
                $('#term_school').val(school.toFixed(2));
                $('#term_vehicle').val(vehicle.toFixed(2));

                var total = last + adm + term + school + vehicle;
                $('#total_display').val(total.toFixed(2));
                $('#term_total_display').val(total.toFixed(2));
                $('#net_amount_display').val(Math.max(0, total - less).toFixed(2));
            }

            function loadFeeMaster() {
                var yearId = $('#academic_year_id').val();
                var stdId = $('#standard_id').val();
                var term = $('#term').val();
                if (!yearId || !stdId || !term) return;

                $.get("{{ route('fee-receipt-entry.fee-master') }}", {
                    academic_year_id: yearId,
                    standard_id: stdId,
                    term: term
                }).done(function (res) {
                    if (!res || !res.data) return;
                    $('#admission_fee').val(money(res.data.admission_fee));
                    $('#term_fee').val(money(res.data.term_fee));
                    $('#school_fee').val(money(res.data.school_fee));
                    calc();
                    if (!res.success && typeof toastr !== 'undefined') {
                        toastr.warning(res.message || 'Fee Master not found');
                    }
                });
            }

            var studentRows = [];

            function hideStudentList() {
                $('#studentList').hide().empty();
            }

            function pickStudent(s) {
                if (!s) return;
                $('#admission_id').val(s.admission_id || '');
                $('#gr_no').val(s.gr_no || '');
                $('#roll_no').val(s.roll_no || '');
                $('#student_name').val(s.student_name || '');
                hideStudentList();
                loadFeeMaster();
            }

            function renderStudentList() {
                var q = $.trim($('#student_name').val()).toLowerCase();
                var $box = $('#studentList').empty();
                var rows = studentRows.filter(function (s) {
                    if (!q) return true;
                    return (s.label || '').toLowerCase().indexOf(q) !== -1
                        || (s.student_name || '').toLowerCase().indexOf(q) !== -1
                        || String(s.gr_no || '').toLowerCase().indexOf(q) !== -1;
                });

                if (!rows.length) {
                    $box.append('<div class="fre-student-empty">No student found.</div>').show();
                    return;
                }

                rows.forEach(function (s) {
                    var $item = $('<div class="fre-student-item">');
                    $item.append($('<small>').text(s.gr_no || '—'));
                    $item.append($('<span>').text(s.student_name));
                    $item.on('mousedown', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        pickStudent(s);
                    });
                    $box.append($item);
                });
                $box.show();
            }

            function loadStudentList() {
                var stdId = $('#standard_id').val();
                var division = $('#division').val();
                studentRows = [];
                hideStudentList();
                if (!stdId || !division) return;

                $.get("{{ route('fee-receipt-entry.students') }}", {
                    standard_id: stdId,
                    division: division
                }).done(function (res) {
                    studentRows = res.data || [];
                });
            }

            function findStudent() {
                var gr = $.trim($('#gr_no').val());
                if (!gr) {
                    typeof toastr !== 'undefined' ? toastr.warning('Enter GR No') : alert('Enter GR No');
                    return;
                }
                $.get("{{ route('fee-receipt-entry.find-student') }}", { gr_no: gr })
                    .done(function (res) {
                        if (!res.success) {
                            typeof toastr !== 'undefined' ? toastr.error(res.message) : alert(res.message);
                            return;
                        }
                        var d = res.data;
                        if (d.standard_id && String($('#standard_id').val()) !== String(d.standard_id)) {
                            $('#standard_id').val(d.standard_id).trigger('change');
                        }
                        if (d.division && $('#division').val() !== d.division) {
                            $('#division').val(d.division).trigger('change');
                        }
                        pickStudent(d);
                    });
            }

            $('.fee-input, #less_amount').on('input change', calc);
            $('#academic_year_id, #standard_id, #term').on('change', loadFeeMaster);
            $('#standard_id, #division').on('change', loadStudentList);
            $('#student_name').on('focus click', function () {
                if (!$('#standard_id').val() || !$('#division').val()) {
                    var msg = 'Please select Standard and Class first.';
                    typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                    return;
                }
                renderStudentList();
            }).on('input', function () {
                if (studentRows.length) renderStudentList();
            });
            $(document).on('mousedown', function (e) {
                if (!$(e.target).closest('.fre-student-wrap').length) hideStudentList();
            });
            $('#btnFindStudent').on('click', findStudent);
            $('#gr_no').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    findStudent();
                }
            });

            $('#btnClear').on('click', function () {
                setTimeout(function () {
                    $('#admission_id').val('');
                    calc();
                }, 50);
            });

            calc();
            loadStudentList();
        });
    </script>
@endsection
