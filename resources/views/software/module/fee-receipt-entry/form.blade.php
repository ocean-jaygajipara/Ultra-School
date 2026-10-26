@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Fee Receipt Entry';
    $route = $modules['route'] ?? 'fee-receipt-entry';
    $edit = $edit ?? null;
    $isEdit = (bool) ($edit?->id);
    $field = function ($name, $default = '') use ($edit, $isEdit) {
        $current = $isEdit ? data_get($edit, $name) : $default;
        if ($current instanceof \Carbon\Carbon) {
            $current = $current->format('Y-m-d');
        }
        return old($name, $current);
    };
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
        .fre-sheet {
            border: 1px solid #e1e4f2;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 24px -16px rgba(31, 37, 128, .35);
        }
        .fre-table { margin: 0; }
        .fre-table thead th {
            background: #1f2580 !important;
            color: #fff !important;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            border: 0 !important;
            padding: 13px 16px;
        }
        .fre-table thead th:first-child { border-top-left-radius: 15px; }
        .fre-table thead th:last-child { border-top-right-radius: 15px; }
        .fre-table td {
            vertical-align: middle;
            border-color: #eef0f8;
            padding: 10px 14px;
        }
        .fre-table tbody tr:nth-child(even) { background: #fafbff; }
        .fre-table .fee-label { font-weight: 700; color: #1f2580; }
        .fre-table .form-control,
        .fre-table .form-select {
            height: 42px;
            border-radius: 10px;
            border: 1.5px solid #e1e4f2;
            background: #f7f8fd;
            color: #1f2580;
            font-weight: 600;
        }
        .fre-table .form-control:focus,
        .fre-table .form-select:focus {
            border-color: #1f2580;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(191, 227, 247, .8);
        }
        .fre-readonly {
            background: #fff8e6 !important;
            color: #9a3412 !important;
            font-weight: 800;
            border-color: #f5d98a !important;
        }
        .fre-total-row td {
            background: #eef7fd !important;
            font-weight: 800;
            color: #1f2580;
        }
        .fre-parts {
            display: inline-flex;
            padding: 3px;
            border-radius: 12px;
            background: #eef1f8;
            gap: 3px;
        }
        .fre-parts button {
            border: 0;
            background: transparent;
            color: #5f6488;
            font-size: .75rem;
            font-weight: 700;
            border-radius: 9px;
            padding: 6px 10px;
            line-height: 1;
        }
        .fre-parts button.is-on {
            background: #1f2580;
            color: #fff;
            box-shadow: 0 4px 10px -6px rgba(31, 37, 128, .8);
        }
        .fre-parts button:disabled {
            opacity: .35;
            cursor: not-allowed;
        }
        .fre-route {
            display: block;
            margin-top: 4px;
            color: #5f6488;
            font-size: .75rem;
            font-weight: 600;
        }
        .fre-pay {
            margin-top: 18px;
            padding: 16px;
            border-radius: 16px;
            background: #f7f8fd;
            border: 1px solid #e1e4f2;
        }
        .fre-pay .form-control,
        .fre-pay .form-select {
            height: 46px;
            border-radius: 12px;
            border: 1.5px solid #e1e4f2;
            background: #fff;
            font-weight: 600;
            color: #1f2580;
        }
        .fre-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
        }
        .fre-btn {
            height: 46px;
            padding: 0 18px;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1.5px solid transparent;
            text-decoration: none;
        }
        .fre-btn:disabled { opacity: .45; }
        .fre-btn-save { background: #1f2580; color: #fff; border-color: #1f2580; }
        .fre-btn-save:hover { background: #171c6b; color: #fff; }
        .fre-btn-print { background: #fff; color: #1f2580; border-color: #1f2580; }
        .fre-btn-print:hover { background: #eef7fd; color: #1f2580; }
        .fre-btn-clear { background: #fff; color: #5f6488; border-color: #d5dae8; }
        .fre-btn-clear:hover { border-color: #1f2580; color: #1f2580; }
        .fre-btn-exit { background: #fff1f1; color: #e1141c; border-color: #f7c3c5; }
        .fre-btn-exit:hover { background: #e1141c; color: #fff; border-color: #e1141c; }
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
        .fre-student-item.is-active {
            background: #1f2580 !important;
            color: #ffffff !important;
            font-weight: 700;
        }
        .fre-student-item:hover small,
        .fre-student-item:hover span,
        .fre-student-item.is-active small,
        .fre-student-item.is-active span {
            color: #ffffff !important;
            font-weight: 700;
        }
        .fre-student-item small {
            min-width: 64px;
            color: #5f6488;
            font-weight: 700;
        }
        .fre-paid {
            margin-top: 14px;
            background: #fff1f1;
            border: 1px solid #f7c3c5;
            color: #b80f16;
            border-radius: 12px;
            padding: 12px 14px;
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
                ['title' => $isEdit ? 'Edit Receipt' : 'New Entry', 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="my-3">
        <div class="fre-hero">
            <h4>{{ $isEdit ? 'Edit Fee Receipt' : 'Fee Receipt Entry' }}</h4>
            <p>{{ $isEdit ? 'Update this receipt. Receipt number stays the same.' : 'Select student by GR No, load Fee Master amounts, then save / print receipt.' }}</p>
        </div>

        <form method="POST" action="{{ $isEdit ? route($route . '.update', $edit->id) : route($route . '.store') }}" id="feeReceiptForm">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="admission_id" id="admission_id" value="{{ $field('admission_id') }}">

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                            <select name="academic_year_id" id="academic_year_id" class="form-select select2" required>
                                <option value="">Select Academic Year</option>
                                @foreach ($academicYears as $yearItem)
                                    <option value="{{ $yearItem->id }}"
                                        {{ (string) $field('academic_year_id', $currentAcademicYear->id ?? '') === (string) $yearItem->id ? 'selected' : '' }}>
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
                                    <option value="{{ $std->id }}" {{ (string) $field('standard_id') === (string) $std->id ? 'selected' : '' }}>
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
                                    <option value="{{ $div }}" {{ (string) $field('division') === (string) $div ? 'selected' : '' }}>{{ $div }}</option>
                                @endforeach
                                @if ($isEdit && $edit->division && !$divisions->contains($edit->division))
                                    <option value="{{ $edit->division }}" selected>{{ $edit->division }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                            <select name="term" id="term" class="form-select select2" required>
                                <option value="">Select Term</option>
                                @foreach ($terms as $key => $label)
                                    <option value="{{ $key }}" {{ (string) $field('term') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Student Name <span class="text-danger">*</span></label>
                            <div class="fre-student-wrap">
                                <input type="text" name="student_name" id="student_name" class="form-control"
                                    autocomplete="off"
                                    placeholder="Select Std and Class, then pick student"
                                    value="{{ $field('student_name') }}" required>
                                <div id="studentList" class="fre-student-list"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">G.R. No</label>
                            <div class="input-group">
                                <input type="text" name="gr_no" id="gr_no" class="form-control"
                                    value="{{ $field('gr_no') }}" placeholder="GR No">
                                <button type="button" class="btn btn-outline-primary" id="btnFindStudent" title="Find Student">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Roll Number</label>
                            <input type="text" name="roll_no" id="roll_no" class="form-control"
                                value="{{ $field('roll_no') }}" readonly placeholder="Auto">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control"
                                value="{{ $field('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div id="termPaidAlert" class="fre-paid d-none"></div>
                    @error('term')
                        <div class="fre-paid">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="table-responsive fre-sheet">
                        <table class="table fre-table mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:180px">Fee Type</th>
                                    <th class="text-end" style="min-width:140px">Fee</th>
                                    <th class="text-end" style="min-width:140px">Term Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fee-label">
                                        Last Yr. Pending Fee
                                        <small class="d-block text-muted fw-normal d-none" id="lastYrPendingHelp"></small>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="last_yr_pending" id="last_yr_pending"
                                            class="form-control text-end fee-input" value="{{ $field('last_yr_pending', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_last_yr" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">Admission Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="admission_fee" id="admission_fee"
                                            class="form-control text-end fee-input" value="{{ $field('admission_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_admission" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">Term Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="term_fee" id="term_fee"
                                            class="form-control text-end fee-input" value="{{ $field('term_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_term" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">School Fee</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="school_fee" id="school_fee"
                                            class="form-control text-end fee-input" value="{{ $field('school_fee', 0) }}">
                                    </td>
                                    <td><input type="text" class="form-control text-end fre-readonly" id="term_school" value="0.00" readonly></td>
                                </tr>
                                <tr>
                                    <td class="fee-label">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <span>Vehicle Fee</span>
                                            <div class="fre-parts" id="vehicleFeeParts">
                                                <button type="button" data-part="fee_i" class="is-on">Fee I</button>
                                                <button type="button" data-part="fee_ii">Fee II</button>
                                                <button type="button" data-part="both">Both</button>
                                            </div>
                                            <select id="vehicle_fee_part" name="vehicle_fee_part" class="d-none">
                                                <option value="fee_i" selected>Fee I</option>
                                                <option value="fee_ii">Fee II</option>
                                                <option value="both">Both</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="vehicle_fee" id="vehicle_fee"
                                            class="form-control text-end fee-input" value="{{ $field('vehicle_fee', 0) }}">
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

                    <div class="row g-3 align-items-end fre-pay">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Less</label>
                            <input type="number" step="0.01" min="0" name="less_amount" id="less_amount"
                                class="form-control" value="{{ $field('less_amount', 0) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Net Amt</label>
                            <input type="text" id="net_amount_display" class="form-control fre-readonly fw-bold" value="0.00" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Mode</label>
                            <select name="mode" id="mode" class="form-select select2">
                                @foreach (['Cash', 'Online', 'UPI', 'Cheque'] as $mode)
                                    <option value="{{ $mode }}" {{ (string) $field('mode', 'Cash') === (string) $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Remark</label>
                            <input type="text" name="remark" class="form-control" value="{{ $field('remark') }}" placeholder="Remark">
                        </div>
                    </div>

                    <div class="fre-actions">
                        <button type="submit" class="fre-btn fre-btn-save" id="btnSave">
                            <i class="ti ti-device-floppy"></i> {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="fre-btn fre-btn-exit">
                            <i class="ti ti-x"></i> Cancel
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
                $('#btnBusPrint').prop('disabled', isCurrentTermPaid() || vehicle <= 0);
            }

            function isCurrentTermPaid() {
                var term = $('#term').val();
                return paidTerms.some(function (row) { return row.term === term; });
            }

            function clearPaidFees() {
                $('#last_yr_pending, #admission_fee, #term_fee, #school_fee, #vehicle_fee, #less_amount').val('');
                calc();
            }

            function loadFeeMaster() {
                var yearId = $('#academic_year_id').val();
                var stdId = $('#standard_id').val();
                var term = $('#term').val();
                var admId = $('#admission_id').val();
                var grNo = $.trim($('#gr_no').val());
                if (!yearId || !stdId || !term) return;
                if (isCurrentTermPaid()) {
                    clearPaidFees();
                    return;
                }

                $.get("{{ route('fee-receipt-entry.fee-master') }}", {
                    academic_year_id: yearId,
                    standard_id: stdId,
                    term: term,
                    admission_id: admId,
                    gr_no: grNo,
                    ignore_id: editingReceiptId || ''
                }).done(function (res) {
                    if ($('#term').val() !== term || isCurrentTermPaid()) {
                        clearPaidFees();
                        return;
                    }
                    if (!res || !res.data) return;
                    $('#admission_fee').val(money(res.data.admission_fee));
                    $('#term_fee').val(money(res.data.term_fee));
                    $('#school_fee').val(money(res.data.school_fee));
                    if (!lockSavedFees && res.data.last_yr_pending !== undefined) {
                        $('#last_yr_pending').val(money(res.data.last_yr_pending));
                    }
                    if (res.data.last_yr_pending_help) {
                        $('#lastYrPendingHelp').text(res.data.last_yr_pending_help).removeClass('d-none');
                    } else {
                        $('#lastYrPendingHelp').text('').addClass('d-none');
                    }
                    calc();
                    if (!res.success && typeof toastr !== 'undefined') {
                        toastr.warning(res.message || 'Fee Master not found');
                    }
                });
            }

            var editingReceiptId = {{ $isEdit ? (int) $edit->id : 'null' }};
            var paidTerms = [];
            var lastPaidToast = '';
            var vehicleRoute = { route: '', fee_i: 0, fee_ii: 0 };
            var vehiclePaid = { fee_i: false, fee_ii: false };

            function paintVehiclePart() {
                var part = $('#vehicle_fee_part').val() || 'fee_i';
                $('#vehicleFeeParts button').each(function () {
                    $(this).toggleClass('is-on', $(this).data('part') === part && !$(this).prop('disabled'));
                });
            }

            function applyVehicleSequence() {
                if (!vehicleRoute.route) {
                    $('#vehicle_fee').prop('readonly', true);
                    $('#vehicleFeeParts button').prop('disabled', true);
                    if (!(typeof lockSavedFees !== 'undefined' && lockSavedFees)) {
                        $('#vehicle_fee').val('0');
                    }
                    paintVehiclePart();
                    calc();
                    return;
                }
                $('#vehicle_fee').prop('readonly', false);
                var iPaid = !!vehiclePaid.fee_i;
                var iiPaid = !!vehiclePaid.fee_ii;
                $('#vehicleFeeParts button[data-part="fee_i"]').prop('disabled', iPaid);
                $('#vehicleFeeParts button[data-part="fee_ii"]').prop('disabled', !iPaid || iiPaid);
                $('#vehicleFeeParts button[data-part="both"]').prop('disabled', iPaid || iiPaid);
                if (!iPaid) {
                    $('#vehicle_fee_part').val('fee_i');
                } else if (!iiPaid) {
                    $('#vehicle_fee_part').val('fee_ii');
                } else {
                    $('#vehicle_fee').val('');
                    calc();
                }
                paintVehiclePart();
                if (!iPaid || !iiPaid) applyVehicleFee(true);
            }

            function applyVehicleFee(force) {
                if (!force && typeof lockSavedFees !== 'undefined' && lockSavedFees) return;
                if (isCurrentTermPaid()) return;
                if (!vehicleRoute.route) {
                    $('#vehicle_fee').val('0');
                    calc();
                    return;
                }
                var part = $('#vehicle_fee_part').val();
                var amount = part === 'fee_ii'
                    ? vehicleRoute.fee_ii
                    : (part === 'both' ? vehicleRoute.fee_i + vehicleRoute.fee_ii : vehicleRoute.fee_i);
                $('#vehicle_fee').val(money(amount));
                calc();
            }

            function setVehicleRoute(s) {
                vehicleRoute = {
                    route: s && s.bus_route ? s.bus_route : '',
                    fee_i: money(s && s.fee_i),
                    fee_ii: money(s && s.fee_ii)
                };
                applyVehicleSequence();
            }

            function showPaidTerm() {
                var term = $('#term').val();
                var hit = paidTerms.find(function (row) { return row.term === term; });
                var $box = $('#termPaidAlert');
                $('#btnSave, #btnSavePrint, #btnBusPrint').prop('disabled', !!hit);
                if (!hit) {
                    lastPaidToast = '';
                    $box.addClass('d-none').empty();
                    applyVehicleFee(false);
                    return;
                }
                clearPaidFees();
                var msg = hit.term + ' term is already paid. Receipt No ' + (hit.receipt_no || '—')
                    + (hit.date ? ', Date ' + hit.date : '')
                    + ', Net ₹ ' + hit.net_amount + '.';
                $box.removeClass('d-none').text(msg);
                var toastKey = hit.term + '|' + (hit.receipt_no || '');
                if (toastKey !== lastPaidToast && typeof toastr !== 'undefined') {
                    lastPaidToast = toastKey;
                    toastr.error(msg);
                }
            }

            function checkPaidTerm() {
                var yearId = $('#academic_year_id').val();
                var gr = $.trim($('#gr_no').val());
                var admissionId = $('#admission_id').val();
                if (!yearId || (!gr && !admissionId)) {
                    paidTerms = [];
                    vehiclePaid = { fee_i: false, fee_ii: false };
                    applyVehicleSequence();
                    showPaidTerm();
                    return;
                }
                $.get("{{ route('fee-receipt-entry.paid-terms') }}", {
                    academic_year_id: yearId,
                    admission_id: admissionId,
                    gr_no: gr,
                    ignore_id: editingReceiptId || ''
                }).done(function (res) {
                    paidTerms = (res && res.data) ? res.data : [];
                    vehiclePaid = (res && res.vehicle) ? res.vehicle : { fee_i: false, fee_ii: false };
                    applyVehicleSequence();
                    showPaidTerm();
                });
            }

            var studentRows = [];
            var currentFilteredRows = [];
            var activeStudentIndex = -1;

            function hideStudentList() {
                activeStudentIndex = -1;
                currentFilteredRows = [];
                $('#studentList').hide().empty();
            }

            function setActiveStudentItem(index, scroll) {
                var $items = $('#studentList .fre-student-item');
                if (!$items.length) {
                    activeStudentIndex = -1;
                    return;
                }
                if (index < 0) index = 0;
                if (index >= $items.length) index = $items.length - 1;
                activeStudentIndex = index;
                $items.removeClass('is-active');
                var $active = $items.eq(index).addClass('is-active');
                if (scroll && $active.length) {
                    var el = $active[0];
                    if (el && el.scrollIntoView) {
                        el.scrollIntoView({ block: 'nearest' });
                    }
                }
            }

            function pickStudent(s) {
                if (!s) return;
                $('#admission_id').val(s.admission_id || '');
                $('#gr_no').val(s.gr_no || '');
                $('#roll_no').val(s.roll_no || '');
                $('#student_name').val(s.student_name || '');
                hideStudentList();
                setVehicleRoute(s);
                if (!lockSavedFees && s.last_yr_pending !== undefined) {
                    $('#last_yr_pending').val(money(s.last_yr_pending));
                }
                if (s.last_yr_pending_help) {
                    $('#lastYrPendingHelp').text(s.last_yr_pending_help).removeClass('d-none');
                }
                loadFeeMaster();
                checkPaidTerm();
            }

            function renderStudentList() {
                var q = $.trim($('#student_name').val()).toLowerCase();
                var $box = $('#studentList').empty();
                activeStudentIndex = -1;
                currentFilteredRows = studentRows.filter(function (s) {
                    if (!q) return true;
                    return (s.label || '').toLowerCase().indexOf(q) !== -1
                        || (s.student_name || '').toLowerCase().indexOf(q) !== -1
                        || String(s.gr_no || '').toLowerCase().indexOf(q) !== -1;
                });

                if (!currentFilteredRows.length) {
                    $box.append('<div class="fre-student-empty">No student found.</div>').show();
                    return;
                }

                currentFilteredRows.forEach(function (s, idx) {
                    var $item = $('<div class="fre-student-item" data-index="' + idx + '">');
                    $item.append($('<small>').text(s.gr_no || '—'));
                    $item.append($('<span>').text(s.student_name));
                    $item.on('mouseenter', function () {
                        setActiveStudentItem(idx, false);
                    });
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
                    division: division,
                    academic_year_id: $('#academic_year_id').val()
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
                $.get("{{ route('fee-receipt-entry.find-student') }}", {
                    gr_no: gr,
                    academic_year_id: $('#academic_year_id').val()
                })
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

            $('#feeReceiptForm').on('submit', function (e) {
                var term = $('#term').val();
                var hit = paidTerms.find(function (row) { return row.term === term; });
                if (!hit) return;
                e.preventDefault();
                var msg = hit.term + ' term is already paid. Receipt cannot be saved again.';
                typeof toastr !== 'undefined' ? toastr.error(msg) : alert(msg);
            });
            $('.fee-input, #less_amount').on('input change', calc);
            var lockSavedFees = {{ $isEdit ? 'true' : 'false' }};
            $('#academic_year_id, #standard_id, #term').on('change', function () {
                if (lockSavedFees) {
                    return;
                }
                loadFeeMaster();
                if (this.id === 'academic_year_id' || this.id === 'term') {
                    checkPaidTerm();
                }
            });
            $('#vehicle_fee_part').on('change', function () {
                paintVehiclePart();
                applyVehicleFee(true);
            });
            $('#vehicleFeeParts').on('click', 'button', function () {
                if ($(this).prop('disabled')) return;
                $('#vehicle_fee_part').val($(this).data('part')).trigger('change');
            });
            paintVehiclePart();
            $('#gr_no').on('change', function () {
                checkPaidTerm();
                loadFeeMaster();
            });
            if (lockSavedFees) {
                setTimeout(function () { lockSavedFees = false; }, 1200);
            }
            $('#academic_year_id, #standard_id, #division').on('change', loadStudentList);
            $('#student_name').on('focus click', function () {
                if (!$('#standard_id').val() || !$('#division').val()) {
                    var msg = 'Please select Standard and Class first.';
                    typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                    return;
                }
                renderStudentList();
            }).on('input', function () {
                if (!$.trim($(this).val())) {
                    $('#admission_id').val('');
                    $('#gr_no').val('');
                    $('#roll_no').val('');
                    $('#last_yr_pending').val('0');
                    $('#lastYrPendingHelp').text('').addClass('d-none');
                    calc();
                    if (studentRows.length) renderStudentList();
                } else if (studentRows.length) {
                    renderStudentList();
                }
            }).on('keydown', function (e) {
                var $box = $('#studentList');
                var isVisible = $box.is(':visible') && currentFilteredRows.length > 0;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!isVisible) {
                        renderStudentList();
                        return;
                    }
                    var nextIndex = activeStudentIndex + 1;
                    if (nextIndex >= currentFilteredRows.length) {
                        nextIndex = 0;
                    }
                    setActiveStudentItem(nextIndex, true);
                    return;
                }

                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!isVisible) {
                        renderStudentList();
                        return;
                    }
                    var prevIndex = activeStudentIndex - 1;
                    if (prevIndex < 0) {
                        prevIndex = currentFilteredRows.length - 1;
                    }
                    setActiveStudentItem(prevIndex, true);
                    return;
                }

                if (e.key === 'Enter') {
                    if (isVisible) {
                        e.preventDefault();
                        var targetStudent = null;
                        if (activeStudentIndex >= 0 && currentFilteredRows[activeStudentIndex]) {
                            targetStudent = currentFilteredRows[activeStudentIndex];
                        } else if (currentFilteredRows.length > 0) {
                            targetStudent = currentFilteredRows[0];
                        }
                        if (targetStudent) {
                            pickStudent(targetStudent);
                        }
                    }
                    return;
                }

                if (e.key === 'Tab') {
                    if (isVisible && activeStudentIndex >= 0 && currentFilteredRows[activeStudentIndex]) {
                        pickStudent(currentFilteredRows[activeStudentIndex]);
                    } else {
                        hideStudentList();
                    }
                    return;
                }

                if (e.key === 'Escape') {
                    hideStudentList();
                }
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
            calc();
            loadStudentList();
            checkPaidTerm();
            loadFeeMaster();
        });
    </script>
@endsection
