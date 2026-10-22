@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Class Wise Report';
    $route = $modules['route'] ?? null;
    $controller = app(\App\Http\Controllers\admin\ClassReportController::class);
    $dataColumns = $controller->selectedColumns($filters);
    $blankHeaders = $controller->blankColumnHeaders($filters);
    $tableColCount = count($dataColumns) + count($blankHeaders);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .crx {
            --crx-ink: #1f2580;
            --crx-ink-2: #2e3a9e;
            --crx-muted: #5f6488;
            --crx-line: #e1e4f2;
            --crx-soft: #f7f8fd;
            --crx-accent: #e1141c;
            --crx-accent-dark: #b80f16;
            --crx-accent-soft: #fff1f1;
            --crx-sky: #bfe3f7;
            --crx-sky-soft: #eef7fd;
            --crx-gold: #f5b400;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--crx-ink);
        }

        .crx-panel {
            background: #fff;
            border: 1px solid var(--crx-line);
            border-radius: 18px;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, .12);
        }

        .crx-panel > :first-child { border-top-left-radius: 17px; border-top-right-radius: 17px; }
        .crx-panel > :last-child { border-bottom-left-radius: 17px; border-bottom-right-radius: 17px; }

        .crx .select2-dropdown {
            border: 1.5px solid var(--crx-ink) !important;
            border-radius: 12px !important;
            box-shadow: 0 16px 32px -12px rgba(15, 23, 42, .25);
            overflow: hidden;
            z-index: 1060;
        }
        .crx .select2-results__option--highlighted[aria-selected],
        .crx .select2-results__option--highlighted {
            background: var(--crx-ink) !important;
            color: #fff !important;
        }

        .crx-banner {
            background:
                radial-gradient(circle at 88% 15%, rgba(245, 180, 0, .35), transparent 42%),
                radial-gradient(circle at 5% 130%, rgba(191, 227, 247, .35), transparent 45%),
                linear-gradient(135deg, #171c6b 0%, var(--crx-ink-2) 100%);
            color: #fff;
            padding: 26px 28px;
        }

        .crx-banner h3, .crx-banner h4 { color: #fff; margin: 0; font-weight: 700; letter-spacing: -.3px; }
        .crx-banner p { color: #d6e6f7; margin: 4px 0 0; font-size: .875rem; }

        .crx-badge-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(245, 180, 0, .18);
            border: 1px solid rgba(245, 180, 0, .6);
            color: var(--crx-gold);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
        }

        .crx-body { padding: 24px 28px; }

        .crx-label {
            display: block; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--crx-muted); margin-bottom: 8px;
        }

        .crx .select2-container--default .select2-selection--single,
        .crx-input {
            height: 46px !important;
            border: 1.5px solid var(--crx-line) !important;
            border-radius: 12px !important;
            background: var(--crx-soft) !important;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .crx .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 43px !important; padding-left: 14px !important;
            color: var(--crx-ink) !important; font-weight: 500;
        }

        .crx .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px !important; }

        .crx .select2-container--focus .select2-selection--single,
        .crx .select2-container--open .select2-selection--single,
        .crx-input:focus {
            border-color: var(--crx-ink) !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(191, 227, 247, .7) !important;
            outline: none;
        }

        .crx-input { width: 100%; padding: 0 14px 0 42px; font-size: .9rem; color: var(--crx-ink); }
        .crx-field { position: relative; }
        .crx-field > i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--crx-ink-2); font-size: 1.1rem; pointer-events: none;
        }

        .crx-btn-clear {
            height: 46px; padding: 0 20px; border-radius: 12px;
            border: 1.5px solid #f7c3c5; background: var(--crx-accent-soft); color: var(--crx-accent);
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
            transition: all .2s; text-decoration: none;
        }
        .crx-btn-clear:hover { background: var(--crx-accent); color: #fff; border-color: var(--crx-accent); }

        .crx-stats { display: flex; flex-wrap: wrap; gap: 10px; }
        .crx-stat {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(191, 227, 247, .35);
            border-radius: 12px; padding: 8px 14px; min-width: 96px;
        }
        .crx-stat small { display: block; color: var(--crx-sky); font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; }
        .crx-stat strong { color: #fff; font-size: 1.05rem; }
        .crx-stat.is-accent { background: var(--crx-accent); border-color: var(--crx-accent); }
        .crx-stat.is-accent small { color: #ffd9db; }

        .crx-table-wrap {
            border: 1px solid var(--crx-line); border-radius: 14px;
            max-height: 65vh; overflow: auto;
        }
        .crx-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .crx-table thead th {
            position: sticky; top: 0; z-index: 2;
            background: var(--crx-ink); color: #fff;
            border-bottom: 0 !important;
            font-size: .72rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            padding: 14px 16px; white-space: nowrap; border-top: 0; border-left: 0; border-right: 0;
        }
        .crx-table tbody td {
            padding: 12px 16px; border-bottom: 1px solid #eef0f8;
            font-size: .9rem; vertical-align: middle;
        }
        .crx-table tbody tr { transition: background .15s; }
        .crx-table tbody tr:nth-child(even) { background: #fafbff; }
        .crx-table tbody tr:hover { background: var(--crx-sky-soft); }
        .crx-table tbody tr:last-child td { border-bottom: 0; }

        .crx-table thead th.extra-col { background: var(--crx-accent); color: #fff; text-align: center; }
        .crx-table td.extra-col { border-left: 1px dashed #f7c3c5; background: #fffafa; }

        .crx-roll {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 30px; padding: 0 8px; border-radius: 9px;
            background: var(--crx-ink); color: #fff; font-weight: 700; font-size: .8rem;
        }
        .crx-roll.is-empty { background: #eef0f8; color: #9aa0c3; }

        .crx-name { font-weight: 600; color: var(--crx-ink); }

        .crx-gr {
            font-family: 'JetBrains Mono', Consolas, monospace;
            background: var(--crx-sky-soft); color: var(--crx-ink); border: 1px solid var(--crx-sky);
            padding: 4px 10px; border-radius: 8px; font-size: .82rem; font-weight: 600;
        }

        .crx-empty { text-align: center; padding: 56px 20px; }
        .crx-empty-icon {
            width: 88px; height: 88px; border-radius: 24px; margin: 0 auto 18px;
            background: linear-gradient(135deg, var(--crx-sky-soft), #fff4d1);
            color: var(--crx-ink); font-size: 2.4rem;
            border: 2px solid var(--crx-sky);
            display: flex; align-items: center; justify-content: center;
        }
        .crx-empty h5 { font-weight: 700; color: var(--crx-ink); margin-bottom: 6px; }
        .crx-empty p { color: var(--crx-muted); margin: 0; }

        #classReportSettingsModal .modal-content {
            border: 0; border-radius: 16px; overflow: hidden;
            box-shadow: 0 24px 48px -16px rgba(15, 23, 42, .35);
        }
        #classReportSettingsModal .modal-header {
            background: linear-gradient(135deg, #171c6b 0%, #2e3a9e 100%);
            color: #fff !important; border-bottom: 0; padding: 16px 20px;
        }
        #classReportSettingsModal .modal-title {
            font-weight: 700; font-size: 1.05rem;
            display: flex; align-items: center; gap: 8px;
            color: #fff !important;
        }
        #classReportSettingsModal .modal-title i {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(245, 180, 0, .2); border: 1px solid rgba(245, 180, 0, .45);
            color: #f5b400; display: inline-flex; align-items: center; justify-content: center;
        }
        #classReportSettingsModal .btn-close {
            background: rgba(255,255,255,.15); opacity: 1; border-radius: 8px;
            width: 32px; height: 32px; padding: 0;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e");
            background-size: 12px; background-repeat: no-repeat; background-position: center;
        }
        #classReportSettingsModal .btn-close:hover { background-color: rgba(255,255,255,.28); }
        #classReportSettingsModal .modal-body { padding: 22px 22px 8px; background: #fafbff; }
        #classReportSettingsModal .modal-footer {
            border-top: 1px solid #e8ebf5; background: #fff; padding: 14px 20px;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 10px;
        }
        #classReportSettingsModal .modal-footer .btn {
            min-height: 38px; padding: 8px 16px; border-radius: 10px;
            font-weight: 600; font-size: .875rem;
        }
        #classReportSettingsModal .btn-reset {
            margin-right: auto;
            background: #fff1f1; border: 1.5px solid #f7c3c5; color: #e1141c;
        }
        #classReportSettingsModal .btn-reset:hover {
            background: #e1141c; border-color: #e1141c; color: #fff;
        }
        #classReportSettingsModal .btn-cancel {
            background: #fff; border: 1.5px solid #d5dae8; color: #5f6488;
        }
        #classReportSettingsModal .btn-cancel:hover {
            background: #f7f8fd; border-color: #1f2580; color: #1f2580;
        }
        #classReportSettingsModal .btn-save {
            background: #1f2580; border: 1.5px solid #1f2580; color: #fff !important;
        }
        #classReportSettingsModal .btn-save:hover {
            background: #171c6b; border-color: #171c6b; color: #fff !important;
        }

        .crx-set-hint {
            font-size: .85rem; color: var(--crx-muted); margin: 0 0 16px;
            line-height: 1.45;
        }
        .crx-set-block {
            background: #fff; border: 1px solid var(--crx-line);
            border-radius: 14px; padding: 16px 16px 14px; margin-bottom: 14px;
        }
        .crx-set-block .crx-label { margin-bottom: 12px; }

        .crx-col-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }
        @media (max-width: 767.98px) {
            .crx-col-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .crx-col-check {
            display: flex; align-items: center; gap: 8px;
            min-height: 44px; padding: 10px 12px; border-radius: 10px;
            border: 1.5px solid var(--crx-line); background: var(--crx-soft);
            cursor: pointer; margin: 0; user-select: none;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .crx-col-check:hover {
            border-color: #c5cce8; background: #fff;
            box-shadow: 0 2px 8px rgba(31, 37, 128, .06);
        }
        .crx-col-check.is-on,
        .crx-col-check:has(input:checked) {
            border-color: var(--crx-ink); background: var(--crx-sky-soft);
            box-shadow: inset 0 0 0 1px rgba(31, 37, 128, .08);
        }
        .crx-col-check input {
            width: 15px; height: 15px; accent-color: var(--crx-ink);
            flex-shrink: 0; margin: 0;
        }
        .crx-col-check span {
            font-size: .82rem; font-weight: 600; color: var(--crx-ink);
            line-height: 1.2; white-space: nowrap;
        }

        #classReportSettingsModal .crx-input {
            background: #fff !important;
            border-color: #d5dae8 !important;
        }
        #classReportSettingsModal .crx-field > i { color: #7a82a8; }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => 'Report', 'url' => ''], ['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_report_settings_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="crx my-3">
        <form id="filterForm" action="{{ route('class-report.filter') }}" method="POST" class="mb-4">
            @csrf
            <div class="crx-panel">
                <div class="crx-banner d-flex align-items-center gap-3">
                    <span class="crx-badge-icon"><i class="ti ti-report-analytics"></i></span>
                    <div>
                        <h3>Class Wise Report</h3>
                        <p>Pick a class to view, print or export its student list.</p>
                    </div>
                </div>
                <div class="crx-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 col-sm-12">
                            <label class="crx-label" for="standard">Standard</label>
                            <select id="standard" name="standard" class="form-control select2 report_filter">
                                <option value="">Select Standard</option>
                                @foreach ($standards as $std)
                                    <option value="{{ $std }}" {{ $filters['standard'] === $std ? 'selected' : '' }}>{{ $std }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <label class="crx-label" for="division">Division</label>
                            <select id="division" name="division" class="form-control select2 report_filter">
                                <option value="">All Divisions</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div }}" {{ $filters['division'] === $div ? 'selected' : '' }}>{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <button type="submit" name="clear" value="1" class="crx-btn-clear">
                                <i class="ti ti-rotate"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if ($filters['standard'])
            <div class="crx-panel">
                <div class="crx-banner">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="crx-badge-icon"><i class="ti ti-users-group"></i></span>
                            <div>
                                <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">Student List</p>
                                <h4>{{ $controller->classLabel($filters) }}</h4>
                            </div>
                        </div>
                        <div class="crx-stats">
                            <div class="crx-stat"><small>Standard</small><strong>{{ $filters['standard'] }}</strong></div>
                            <div class="crx-stat"><small>Division</small><strong>{{ $filters['division'] ?: 'All' }}</strong></div>
                            <div class="crx-stat is-accent"><small>Students</small><strong>{{ $students->count() }}</strong></div>
                        </div>
                    </div>
                </div>

                <div class="crx-body">
                    <div class="crx-table-wrap">
                        <table class="crx-table" id="classStudentTable">
                            <thead>
                                <tr>
                                    @foreach ($dataColumns as $key => $label)
                                        <th class="{{ $key === 'roll_no' ? 'text-center' : '' }}"
                                            @if ($key === 'roll_no') style="width:100px" @endif
                                            @if ($key === 'gr_no') style="width:140px" @endif>
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                    @foreach ($blankHeaders as $heading)
                                        <th class="extra-col" style="min-width:110px">{{ $heading ?: 'Column' }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $i => $s)
                                    <tr>
                                        @foreach ($dataColumns as $key => $label)
                                            <td class="{{ $key === 'roll_no' ? 'text-center' : '' }}">
                                                @if ($key === 'roll_no')
                                                    <span class="crx-roll {{ $s->roll_no ? '' : 'is-empty' }}">{{ $s->roll_no ?: '—' }}</span>
                                                @elseif ($key === 'student_name')
                                                    <span class="crx-name">{{ $controller->columnValue($s, $key) }}</span>
                                                @elseif ($key === 'gr_no')
                                                    <span class="crx-gr">{{ $controller->columnValue($s, $key) }}</span>
                                                @else
                                                    {{ $controller->columnValue($s, $key) }}
                                                @endif
                                            </td>
                                        @endforeach
                                        @foreach ($blankHeaders as $heading)
                                            <td class="extra-col"></td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr class="no-students">
                                        <td colspan="{{ max(1, $tableColCount) }}">
                                            <div class="crx-empty">
                                                <div class="crx-empty-icon"><i class="ti ti-user-off"></i></div>
                                                <h5>No students in this class</h5>
                                                <p>Try another Standard or Division.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="crx-panel">
                <div class="crx-empty">
                    <div class="crx-empty-icon"><i class="ti ti-list-search"></i></div>
                    <h5>Select a class to get started</h5>
                    <p>Choose <strong>Standard</strong> and <strong>Division</strong> above to see the student list.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Report Settings Modal --}}
    <div class="modal fade" id="classReportSettingsModal" tabindex="-1" aria-labelledby="classReportSettingsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
            <form method="POST" action="{{ route('class-report.settings') }}" class="modal-content crx">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="classReportSettingsLabel">
                        <i class="ti ti-adjustments-horizontal"></i>
                        Report Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="crx-set-hint">Choose which columns appear in the list, print and Excel export.</p>

                    <div class="crx-set-block">
                        <label class="crx-label">Show Columns</label>
                        <div class="crx-col-grid">
                            @foreach ($availableColumns as $key => $label)
                                <label class="crx-col-check {{ in_array($key, $filters['columns'], true) ? 'is-on' : '' }}">
                                    <input type="checkbox" name="columns[]" value="{{ $key }}"
                                        {{ in_array($key, $filters['columns'], true) ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="crx-set-block mb-0">
                        <label class="crx-label">Extra Blank Columns</label>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="crx-field">
                                    <i class="ti ti-hash"></i>
                                    <input type="number" id="extra_columns" name="extra_columns" class="crx-input" min="0"
                                        max="{{ \App\Http\Controllers\admin\ClassReportController::MAX_EXTRA_COLUMNS }}"
                                        placeholder="Count e.g. 4"
                                        value="{{ $filters['extra_columns'] ?: '' }}">
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="crx-field">
                                    <i class="ti ti-tags"></i>
                                    <input type="text" id="column_names" name="column_names" class="crx-input"
                                        placeholder="Names (optional): Sign, Remark, Attendance"
                                        value="{{ $filters['column_names'] }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-reset" id="resetReportColumns">
                        <i class="ti ti-rotate me-1"></i> Reset Default
                    </button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save">
                        <i class="ti ti-device-floppy me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $(function() {
            var defaultColumns = @json(\App\Http\Controllers\admin\ClassReportController::DEFAULT_COLUMNS);
            var maxExtra = {{ \App\Http\Controllers\admin\ClassReportController::MAX_EXTRA_COLUMNS }};

            $('.report_filter').on('change', function() {
                $('#filterForm').submit();
            });

            function syncColCheckState() {
                $('#classReportSettingsModal input[name="columns[]"]').each(function() {
                    $(this).closest('.crx-col-check').toggleClass('is-on', this.checked);
                });
            }

            $('#classReportSettingsModal').on('change', 'input[name="columns[]"]', syncColCheckState);

            $('#resetReportColumns').on('click', function() {
                $('#classReportSettingsModal input[name="columns[]"]').each(function() {
                    $(this).prop('checked', defaultColumns.indexOf($(this).val()) !== -1);
                });
                $('#extra_columns').val('');
                $('#column_names').val('');
                syncColCheckState();
            });

            $('#classReportSettingsModal form').on('submit', function(e) {
                if (!$('#classReportSettingsModal input[name="columns[]"]:checked').length) {
                    e.preventDefault();
                    var msg = 'Please select at least one column.';
                    typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                    return false;
                }
            });

            function selectedColumns() {
                return $('#classReportSettingsModal input[name="columns[]"]:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            function extraColumnCount() {
                var n = parseInt($('#extra_columns').val(), 10);
                if (isNaN(n) || n < 0) n = 0;
                return Math.min(n, maxExtra);
            }

            function currentParams() {
                return {
                    standard: $('#standard').val() || '',
                    division: $('#division').val() || '',
                    columns: selectedColumns().join(','),
                    extra_columns: extraColumnCount(),
                    column_names: $('#column_names').val() || ''
                };
            }

            function postTo(url, target) {
                var $form = $('<form>', { method: 'POST', action: url, target: target }).hide();
                $form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
                $.each(currentParams(), function(name, value) {
                    $form.append($('<input>', { type: 'hidden', name: name, value: value }));
                });
                $form.appendTo('body').trigger('submit');
                setTimeout(function() { $form.remove(); }, 1000);
            }

            function hasStandard() {
                if ($('#standard').val()) return true;
                var msg = 'Please select Standard first.';
                typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                return false;
            }

            function openPrint(e) {
                e.preventDefault();
                if (!hasStandard()) return;
                postTo("{{ route('class-report.print') }}", '_blank');
            }

            function exportExcel(e) {
                e.preventDefault();
                if (!hasStandard()) return;
                postTo("{{ route('class-report.export-excel') }}", '_self');
            }

            $('#print_btn').on('click', openPrint);
            $('#exportExcelBtn').on('click', exportExcel);
        });
    </script>
@endsection
