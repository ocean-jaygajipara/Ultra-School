@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Bus Transport Fee';
    $route = $modules['route'] ?? 'bus-fee-collection';
@endphp

@section('title', 'Collect ' . $page_title)

@section('page_style_file')
    <style>
        .bfx {
            --navy: #1f2580;
            --line: #e1e4f2;
            --muted: #5f6488;
            --sky: #eef7fd;
        }
        .bfx-hero {
            background: linear-gradient(120deg, #1f2580, #2e3a9e);
            color: #fff !important;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 18px;
        }
        .bfx-hero h4 {
            margin: 0;
            font-weight: 800;
            color: #fff !important;
            font-size: 1.25rem;
        }
        .bfx-hero p {
            margin: 4px 0 0;
            color: rgba(255, 255, 255, .9) !important;
            font-size: .9rem;
        }
        .bfx-empty {
            color: #e1141c;
            font-weight: 600;
            font-size: .85rem;
            display: none;
            margin-top: 6px;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => 'Collect Bus Fee', 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="bfx my-3">
        <div class="bfx-hero">
            <h4>Bus Transport Fee Receipt</h4>
            <p>First select Standard, then choose student with bus route and print receipt.</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route($route . '.store') }}" id="busFeeForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Standard <span class="text-danger">*</span></label>
                            <select id="standard_id" class="form-select select2" required>
                                <option value="">Select Standard</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std->id }}">{{ $std->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Student <span class="text-danger">*</span></label>
                            <select name="admission_id" id="admission_id" class="form-select select2" required disabled>
                                <option value="">Select Standard first</option>
                            </select>
                            @error('admission_id')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                            <div class="bfx-empty" id="noStudentMsg">No students found in this standard.</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year" id="academic_year" class="form-select select2">
                                <option value="">Select Year</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year }}" {{ old('academic_year', $currentYear) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Bus Route Village</label>
                            <input type="hidden" name="bus_route_village" id="bus_route_village" value="">
                            <input type="hidden" id="base_charge" value="">
                            <input type="text" id="bus_route_display" class="form-control" value="" readonly
                                placeholder="Select student first" style="background:#eef7fd;color:#1f2580;font-weight:600;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fee Type <span class="text-danger">*</span></label>
                            <select name="period_label" id="period_label" class="form-select select2" required>
                                <option value="">Select Type</option>
                                <option value="Monthly" {{ old('period_label') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Yearly" {{ old('period_label') == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('period_label') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">Yearly = Monthly charge × 12</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="mode" id="mode" class="form-select select2" required>
                                @foreach (['Cash', 'Online', 'UPI', 'Cheque'] as $mode)
                                    <option value="{{ $mode }}" {{ old('mode', 'Cash') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3" id="upiWrap" style="display:none;">
                            <label class="form-label">UPI / Online Ref</label>
                            <input type="text" name="upi_id" class="form-control" value="{{ old('upi_id') }}" placeholder="UPI / Transaction ID">
                        </div>
                        <div class="col-md-3" id="chequeWrap" style="display:none;">
                            <label class="form-label">Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}" placeholder="Cheque number">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Remark</label>
                            <input type="text" name="remark" class="form-control" value="{{ old('remark') }}" placeholder="e.g. April 2026 / Full year paid">
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary px-4" id="saveBtn" disabled>
                            <i class="ti ti-receipt me-1"></i> Save & Print Receipt
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
<script>
    function toggleModeFields() {
        var mode = $('#mode').val();
        $('#upiWrap').toggle(mode === 'Online' || mode === 'UPI');
        $('#chequeWrap').toggle(mode === 'Cheque');
    }

    function applyFeeAmount() {
        var base = parseFloat($('#base_charge').val());
        if (isNaN(base) || base < 0) return;
        var type = $('#period_label').val();
        if (type === 'Yearly') {
            $('#amount').val((base * 12).toFixed(2));
        } else if (type === 'Monthly') {
            $('#amount').val(base.toFixed(2));
        }
    }

    function resetStudent() {
        $('#admission_id').html('<option value="">Select Student</option>').val('').trigger('change');
        $('#noStudentMsg').hide();
        $('#saveBtn').prop('disabled', true);
        $('#amount').val('');
        $('#base_charge').val('');
        $('#bus_route_village').val('');
        $('#bus_route_display').val('');
        $('#period_label').val('').trigger('change');
    }

    function loadStudentsByStandard(standardId) {
        resetStudent();

        if (!standardId) {
            $('#admission_id').prop('disabled', true).html('<option value="">Select Standard first</option>').trigger('change');
            return;
        }

        $('#admission_id').prop('disabled', true).html('<option value="">Loading...</option>').trigger('change');

        $.ajax({
            url: "{{ route('bus-fee-collection.students-by-standard') }}",
            type: 'GET',
            data: {
                standard_id: standardId,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                var options = '<option value="">Select Student</option>';
                if (res.success && res.data && res.data.length) {
                    res.data.forEach(function(item) {
                        options += '<option value="' + item.id + '">' + item.name + '</option>';
                    });
                    $('#noStudentMsg').hide();
                    $('#admission_id').prop('disabled', false).html(options).trigger('change');
                } else {
                    $('#noStudentMsg').show();
                    $('#admission_id').prop('disabled', true).html('<option value="">No students</option>').trigger('change');
                }
            },
            error: function() {
                $('#noStudentMsg').text('Could not load students. Please try again.').show();
                $('#admission_id').prop('disabled', true).html('<option value="">Error</option>').trigger('change');
            }
        });
    }

    function loadStudent(id) {
        if (!id) {
            $('#saveBtn').prop('disabled', true);
            $('#amount').val('');
            $('#base_charge').val('');
            $('#bus_route_village').val('');
            $('#bus_route_display').val('');
            return;
        }

        $.ajax({
            url: "{{ route('bus-fee-collection.student', ['id' => '__ID__']) }}".replace('__ID__', id),
            type: 'GET',
            success: function(res) {
                if (!res.success) return;
                var d = res.data;
                $('#saveBtn').prop('disabled', false);

                var routeName = d.bus_route_village || '';
                $('#bus_route_village').val(routeName);
                $('#bus_route_display').val(routeName || 'Not set in admission');

                var charge = (d.charge !== null && d.charge !== undefined) ? parseFloat(d.charge) : 0;
                $('#base_charge').val(isNaN(charge) ? '' : charge);
                if (!$('#period_label').val()) {
                    $('#period_label').val('Monthly').trigger('change');
                } else {
                    applyFeeAmount();
                }
            },
            error: function() {
                toastr.error('Could not load student details.');
                $('#saveBtn').prop('disabled', true);
            }
        });
    }

    $(function() {
        if ($.fn.select2) {
            $('.select2').select2({ width: '100%' });
        }

        toggleModeFields();
        $('#mode').on('change', toggleModeFields);
        $('#period_label').on('change', applyFeeAmount);

        $('#standard_id').on('change', function() {
            loadStudentsByStandard($(this).val());
        });

        $('#admission_id').on('change', function() {
            loadStudent($(this).val());
        });
    });
</script>
@endsection
