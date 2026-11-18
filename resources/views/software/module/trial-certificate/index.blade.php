@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Trial Certificate';
    $route = $modules['route'] ?? 'trial-certificate';
    $historyCount = $historyCount ?? collect($entries ?? [])->count();
@endphp
@section('title', $page_title)

@section('page_style_file')
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

        .icx-preview {
            height: 44px;
            padding: 0 20px;
            border: 1px solid var(--navy);
            border-radius: 11px;
            font-weight: 700;
            color: var(--navy);
            background: #fff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .icx-preview:hover {
            background: var(--sky-soft);
            color: var(--navy);
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

        .icx-delete {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 9px;
            font-size: .8rem;
            font-weight: 600;
            background: #fde8e8;
            color: var(--red);
            border: 0;
            cursor: pointer;
            transition: all .15s ease;
        }

        .icx-delete:hover {
            background: var(--red);
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
            'show_add_btn' => true,
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
                <span class="icx-hero-icon"><i class="ti ti-certificate"></i></span>
                <div>
                    <h4>{{ $page_title }}</h4>
                    <p>Generated trial certificates history — click Add to generate.</p>
                </div>
            </div>
            <div class="icx-stat">
                <small>Generated</small>
                <strong>{{ $historyCount }}</strong>
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
                <table class="table icx-table" id="trial-history-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Student Name</th>
                            <th class="text-center">GR No.</th>
                            <th class="text-center">Std</th>
                            <th class="text-center">Trial</th>
                            <th class="text-center">Exam</th>
                            <th class="text-center">Date</th>
                            <th class="text-center" style="width: 175px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="trial-history-body">
                        @forelse ($entries as $i => $row)
                            <tr>
                                <td class="text-center">{{ $entries->firstItem() + $i }}</td>
                                <td class="icx-who text-uppercase">{{ $row->student_name ?: '-' }}</td>
                                <td class="text-center">{{ $row->gr_no ?: '-' }}</td>
                                <td class="text-center">{{ $row->std ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="icx-pill">{{ $row->trial ?: '-' }}</span>
                                </td>
                                <td class="text-center">{{ $row->exam_label ?: '-' }}</td>
                                <td class="icx-muted text-center">{{ $row->print_date ?: '-' }}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route($route . '.print', ['id' => $row->id]) }}" target="_blank" class="icx-view" title="View Certificate">
                                            <i class="ti ti-eye"></i> View
                                        </a>
                                        <button type="button" class="icx-delete btn-delete" data-id="{{ $row->id }}" title="Delete">
                                            <i class="ti ti-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="trial-history-empty-row" class="icx-empty">
                                <td colspan="8">
                                    <i class="ti ti-file-off"></i>
                                    No certificate generated yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($entries->hasPages())
                <div class="p-3">{{ $entries->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            $('#icxHistorySearch').on('input', function () {
                var term = $(this).val().toLowerCase().trim();
                $('#trial-history-body tr:not(#trial-history-empty-row)').each(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(term) !== -1);
                });
            });

            $(document).on('click', '.btn-delete', function () {
                var id = $(this).data('id');
                var $row = $(this).closest('tr');
                var $btn = $(this);

                var doDelete = function () {
                    $btn.prop('disabled', true);
                    $.ajax({
                        url: @json(url('trial-certificate')) + '/' + id,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: @json(csrf_token()) },
                        success: function (res) {
                            if (res.success) {
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(res.message || 'Deleted successfully.');
                                }
                                $row.fadeOut(200, function () {
                                    $(this).remove();
                                    if ($('#trial-history-body tr').length === 0) {
                                        $('#trial-history-body').html(
                                            '<tr id="trial-history-empty-row" class="icx-empty"><td colspan="8"><i class="ti ti-file-off"></i> No certificate generated yet.</td></tr>'
                                        );
                                    }
                                });
                            } else {
                                if (typeof toastr !== 'undefined') toastr.error(res.message || 'Delete failed');
                                else alert(res.message || 'Delete failed');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed';
                            if (typeof toastr !== 'undefined') toastr.error(msg);
                            else alert(msg);
                            $btn.prop('disabled', false);
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This certificate record will be deleted!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true,
                        customClass: {
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then(function (result) {
                        if (result.isConfirmed) doDelete();
                    });
                } else if (confirm('Delete this certificate?')) {
                    doDelete();
                }
            });
        });
    </script>
@endsection
