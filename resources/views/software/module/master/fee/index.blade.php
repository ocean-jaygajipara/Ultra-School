@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Fee';
    $route = $modules['route'] ?? 'fees';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .fee-filter-bar {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .form-mode-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    </style>
@endsection

@section('breadcrumb')
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
@endsection

@section('content')
    <div class="mst my-2">
        <div class="mst-hero">
            <div class="mst-hero-l">
                <div class="mst-hero-icon"><i class="ti ti-currency-rupee"></i></div>
                <div>
                    <h4>{{ $page_title }}</h4>
                    <p>Manage standard-wise and general fee details for collection.</p>
                </div>
            </div>
        </div>

        {{-- Add / Edit Fee Form Card --}}
        <div class="card mst-card mst-form-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="card-title mb-0" id="form-card-title">
                    <i class="ti ti-plus me-1 text-primary"></i> <span id="form-heading-text">Add Fee Detail</span>
                </h6>
                <span class="badge bg-label-primary form-mode-badge" id="form-mode-badge">Add Mode</span>
            </div>
            <div class="card-body pt-2">
                <form id="fee-form" method="POST">
                    @csrf
                    <input type="hidden" id="fee_id">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-semibold" for="standard_id">
                                Standard <span class="text-muted small">(ધોરણ)</span>
                            </label>
                            <select name="standard_id" id="standard_id" class="form-select select2">
                                <option value="">All Standards (General Fee)</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std->id }}">{{ $std->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <label class="form-label fw-semibold" for="fee_name">
                                Fee Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="fee_name" id="fee_name" class="form-control"
                                placeholder="e.g. Tuition Fee, Exam Fee, Admission Fee" required />
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-semibold" for="amount">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" min="0" name="amount" id="amount"
                                    class="form-control" placeholder="0.00" required />
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1" id="submit-btn">
                                    <i class="ti ti-plus me-1"></i> Add Fee
                                </button>
                                <button type="button" class="btn btn-outline-secondary d-none" id="cancel-btn" title="Cancel edit">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Fees List Card --}}
        <div class="card mst-card">
            <div class="card-header py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="card-title mb-0">Fee Details List</h6>
                            <span class="badge bg-label-primary rounded-pill" id="fee-count">
                                Total: {{ count($fees) }}
                            </span>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-center gap-1">
                            <label for="filter_standard" class="form-label mb-0 small text-muted text-nowrap">Filter Standard:</label>
                            <select id="filter_standard" class="form-select form-select-sm select2">
                                <option value="all">All Standards (Show All)</option>
                                <option value="general">General (All Standards Only)</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std->id }}">{{ $std->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" id="filter_search" class="form-control" placeholder="Search by fee name or amount...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover w-100 mst-table mb-0" id="fee-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">Sr no</th>
                                <th style="width: 220px;">Standard</th>
                                <th>Fee Name</th>
                                <th style="width: 180px;">Amount</th>
                                <th style="width: 130px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fees as $index => $fee)
                                <tr data-id="{{ $fee->id }}" data-standard-id="{{ $fee->standard_id ?? 'general' }}">
                                    <td class="text-center serial-no">{{ $index + 1 }}</td>
                                    <td>
                                        @if ($fee->standard)
                                            <span class="badge bg-label-primary">
                                                <i class="ti ti-school me-1"></i> {{ $fee->standard->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-info">
                                                <i class="ti ti-world me-1"></i> All Standards
                                            </span>
                                        @endif
                                    </td>
                                    <td class="fee-name-cell">
                                        <span class="fw-semibold">{{ $fee->fee_name }}</span>
                                    </td>
                                    <td class="fee-amount-cell">
                                        <span class="fw-bold text-success">₹{{ number_format((float) $fee->amount, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-icon btn-label-primary edit-fee"
                                                data-id="{{ $fee->id }}"
                                                data-standard_id="{{ $fee->standard_id ?? '' }}"
                                                data-fee_name="{{ $fee->fee_name }}"
                                                data-amount="{{ $fee->amount }}"
                                                title="Edit Fee">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-icon btn-label-danger delete-fee"
                                                data-id="{{ $fee->id }}"
                                                data-fee_name="{{ $fee->fee_name }}"
                                                title="Delete Fee">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-records-row">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="ti ti-currency-rupee fs-2 mb-2 d-block"></i>
                                        No fee details configured yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            // Re-initialize select2 if needed
            if ($.fn.select2) {
                $('#standard_id, #filter_standard').select2({
                    dropdownAutoWidth: true,
                    width: '100%'
                });
            }

            // Client-side filter function
            function filterTableRows() {
                let selectedStd = $('#filter_standard').val();
                let searchText = $('#filter_search').val().toLowerCase().trim();
                let visibleCount = 0;

                $('#fee-table tbody tr').not('.no-records-row').each(function() {
                    let rowStd = $(this).attr('data-standard-id');
                    let rowText = $(this).text().toLowerCase();

                    let stdMatch = (selectedStd === 'all') ||
                                   (selectedStd === 'general' && rowStd === 'general') ||
                                   (rowStd === selectedStd);

                    let searchMatch = searchText === '' || rowText.indexOf(searchText) !== -1;

                    if (stdMatch && searchMatch) {
                        $(this).show();
                        visibleCount++;
                        $(this).find('.serial-no').text(visibleCount);
                    } else {
                        $(this).hide();
                    }
                });

                // Handle no visible rows message
                $('#fee-table tbody .no-filter-records-row').remove();
                if (visibleCount === 0 && $('#fee-table tbody tr').not('.no-records-row').length > 0) {
                    $('#fee-table tbody').append(`
                        <tr class="no-filter-records-row">
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="ti ti-filter-off fs-2 mb-2 d-block"></i>
                                No fee details match the selected filters.
                            </td>
                        </tr>
                    `);
                }
            }

            // Events for filter and search
            $('#filter_standard').on('change', function() {
                filterTableRows();
            });

            $('#filter_search').on('keyup', function() {
                filterTableRows();
            });

            // Helper to render table after AJAX responses
            function renderFeesTable(fees) {
                let tbody = $('#fee-table tbody');
                tbody.empty();

                if (!fees || fees.length === 0) {
                    tbody.append(`
                        <tr class="no-records-row">
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="ti ti-currency-rupee fs-2 mb-2 d-block"></i>
                                No fee details configured yet.
                            </td>
                        </tr>
                    `);
                    $('#fee-count').text('Total: 0');
                    return;
                }

                $('#fee-count').text('Total: ' + fees.length);

                $.each(fees, function(i, fee) {
                    let standardBadge = '';
                    let standardIdAttr = fee.standard_id ? fee.standard_id : 'general';

                    if (fee.standard && fee.standard.name) {
                        standardBadge = `<span class="badge bg-label-primary"><i class="ti ti-school me-1"></i> ${fee.standard.name}</span>`;
                    } else {
                        standardBadge = `<span class="badge bg-label-info"><i class="ti ti-world me-1"></i> All Standards</span>`;
                    }

                    let amountFormatted = Number(fee.amount).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    tbody.append(`
                        <tr data-id="${fee.id}" data-standard-id="${standardIdAttr}">
                            <td class="text-center serial-no">${i + 1}</td>
                            <td>${standardBadge}</td>
                            <td class="fee-name-cell">
                                <span class="fw-semibold">${escapeHtml(fee.fee_name)}</span>
                            </td>
                            <td class="fee-amount-cell">
                                <span class="fw-bold text-success">₹${amountFormatted}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-primary edit-fee"
                                        data-id="${fee.id}"
                                        data-standard_id="${fee.standard_id || ''}"
                                        data-fee_name="${escapeHtml(fee.fee_name)}"
                                        data-amount="${fee.amount}"
                                        title="Edit Fee">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-label-danger delete-fee"
                                        data-id="${fee.id}"
                                        data-fee_name="${escapeHtml(fee.fee_name)}"
                                        title="Delete Fee">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                filterTableRows();
            }

            function escapeHtml(text) {
                if (!text) return '';
                return $('<div>').text(text).html();
            }

            function resetForm() {
                $('#fee-form')[0].reset();
                $('#fee_id').val('');
                $('#standard_id').val('').trigger('change');
                $('#submit-btn').html('<i class="ti ti-plus me-1"></i> Add Fee');
                $('#cancel-btn').addClass('d-none');
                $('#form-heading-text').text('Add Fee Detail');
                $('#form-card-title i').attr('class', 'ti ti-plus me-1 text-primary');
                $('#form-mode-badge').removeClass('bg-label-warning').addClass('bg-label-primary').text('Add Mode');
            }

            // Cancel Edit button
            $('#cancel-btn').on('click', function() {
                resetForm();
            });

            // Form Submit (Store or Update)
            $('#fee-form').submit(function(e) {
                e.preventDefault();

                let feeId = $('#fee_id').val();
                let isEdit = feeId !== '';
                let method = isEdit ? 'PUT' : 'POST';
                let url = isEdit ?
                    '{{ route("$route.update", ":id") }}'.replace(':id', feeId) :
                    '{{ route("$route.store") }}';

                let payload = {
                    standard_id: $('#standard_id').val() || null,
                    fee_name: $('#fee_name').val(),
                    amount: $('#amount').val(),
                };

                let $btn = $('#submit-btn');
                let originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...');

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                        'Accept': 'application/json'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(data) {
                        $btn.prop('disabled', false).html(originalText);
                        if (data.success) {
                            renderFeesTable(data.fees);
                            resetForm();
                            toastr.success(data.message || (isEdit ? 'Fee updated successfully!' : 'Fee added successfully!'));
                        } else {
                            toastr.error(data.message || 'Something went wrong!');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(originalText);
                        toastr.error(xhr.responseJSON?.message || 'Server error while saving fee.');
                    }
                });
            });

            // Click Edit Fee
            $(document).on('click', '.edit-fee', function() {
                let id = $(this).data('id');
                let standardId = $(this).data('standard_id');
                let feeName = $(this).data('fee_name');
                let amount = $(this).data('amount');

                $('#fee_id').val(id);
                $('#standard_id').val(standardId ? String(standardId) : '').trigger('change');
                $('#fee_name').val(feeName);
                $('#amount').val(amount);

                $('#submit-btn').html('<i class="ti ti-check me-1"></i> Update Fee');
                $('#cancel-btn').removeClass('d-none');
                $('#form-heading-text').text('Edit Fee Detail (ID: #' + id + ')');
                $('#form-card-title i').attr('class', 'ti ti-edit me-1 text-warning');
                $('#form-mode-badge').removeClass('bg-label-primary').addClass('bg-label-warning').text('Edit Mode');

                // Smooth scroll to form
                $('html, body').animate({
                    scrollTop: $('#fee-form').offset().top - 120
                }, 300);
            });

            // Click Delete Fee
            $(document).on('click', '.delete-fee', function() {
                let feeId = $(this).data('id');
                let feeName = $(this).data('fee_name');
                let deleteUrl = '{{ route("$route.destroy", ":id") }}'.replace(':id', feeId);

                let confirmDeletion = function() {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                            'Accept': 'application/json'
                        },
                        data: {
                            _token: $('input[name="_token"]').val(),
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            if (res.success) {
                                renderFeesTable(res.fees);
                                // If the fee currently being edited was deleted, reset form
                                if ($('#fee_id').val() == feeId) {
                                    resetForm();
                                }
                                toastr.success(res.message || 'Fee deleted successfully!');
                            } else {
                                toastr.error(res.message || 'Could not delete fee.');
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Deletion failed.');
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Delete Fee?",
                        text: `Are you sure you want to delete "${feeName}"?`,
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
                            confirmDeletion();
                        }
                    });
                } else {
                    if (confirm(`Are you sure you want to delete "${feeName}"?`)) {
                        confirmDeletion();
                    }
                }
            });
        });
    </script>
@endsection
