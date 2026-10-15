@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Class Wise Report';
    $route = $modules['route'] ?? null;
    $controller = app(\App\Http\Controllers\admin\ClassReportController::class);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .crx {
            /* Colours taken from the school logo */
            --crx-ink: #1f2580;          /* navy border */
            --crx-ink-2: #2e3a9e;
            --crx-muted: #5f6488;
            --crx-line: #e1e4f2;
            --crx-soft: #f7f8fd;
            --crx-accent: #e1141c;       /* red lettering */
            --crx-accent-dark: #b80f16;
            --crx-accent-soft: #fff1f1;
            --crx-sky: #bfe3f7;          /* ribbon */
            --crx-sky-soft: #eef7fd;
            --crx-gold: #f5b400;         /* halo */
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

        .crx-config {
            background: linear-gradient(180deg, var(--crx-sky-soft), #fff);
            border: 1.5px dashed var(--crx-sky); border-radius: 14px;
            padding: 18px 20px; margin-bottom: 22px;
        }
        .crx-config-title { font-weight: 700; color: var(--crx-ink); display: flex; align-items: center; gap: 8px; }
        .crx-config-title i { color: var(--crx-gold); }
        .crx-config-hint { color: var(--crx-muted); font-size: .8rem; }

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
                    <div class="crx-config">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <span class="crx-config-title"><i class="ti ti-columns-3"></i> Extra Columns for Excel / Print</span>
                            <span class="crx-config-hint"><i class="ti ti-settings"></i> Use the settings button on top to Export Excel or Print</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <label class="crx-label" for="extra_columns">Extra Columns</label>
                                <div class="crx-field">
                                    <i class="ti ti-hash"></i>
                                    <input type="number" id="extra_columns" class="crx-input" min="0"
                                        max="{{ \App\Http\Controllers\admin\ClassReportController::MAX_EXTRA_COLUMNS }}"
                                        placeholder="e.g. 4"
                                        value="{{ $filters['extra_columns'] ?: '' }}">
                                </div>
                            </div>
                            <div class="col-md-9 col-sm-12">
                                <label class="crx-label" for="column_names">Column Names <span style="text-transform:none;letter-spacing:0;font-weight:500">(optional, comma separated)</span></label>
                                <div class="crx-field">
                                    <i class="ti ti-tags"></i>
                                    <input type="text" id="column_names" class="crx-input"
                                        placeholder="e.g. Unit Test 1, Unit Test 2, Sign, Remark"
                                        value="{{ $filters['column_names'] }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="crx-table-wrap">
                        <table class="crx-table" id="classStudentTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:100px">Roll No</th>
                                    <th>Student Name</th>
                                    <th style="width:140px">GR No</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $i => $s)
                                    @php $name = $controller->fullName($s); @endphp
                                    <tr>
                                        <td class="text-center">
                                            <span class="crx-roll {{ $s->roll_no ? '' : 'is-empty' }}">{{ $s->roll_no ?: '—' }}</span>
                                        </td>
                                        <td><span class="crx-name">{{ $name }}</span></td>
                                        <td><span class="crx-gr">{{ $s->gr_no }}</span></td>
                                    </tr>
                                @empty
                                    <tr class="no-students">
                                        <td colspan="3">
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
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $(function() {
            $('.report_filter').on('change', function() {
                $('#filterForm').submit();
            });

            var maxExtra = {{ \App\Http\Controllers\admin\ClassReportController::MAX_EXTRA_COLUMNS }};

            function extraColumnCount() {
                var n = parseInt($('#extra_columns').val(), 10);
                if (isNaN(n) || n < 0) n = 0;
                return Math.min(n, maxExtra);
            }

            function currentParams() {
                var params = {
                    standard: $('#standard').val() || '',
                    division: $('#division').val() || ''
                };
                if ($('#extra_columns').length) {
                    params.extra_columns = extraColumnCount();
                    params.column_names = $('#column_names').val() || '';
                }
                return params;
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

            function renderExtraColumns() {
                var $table = $('#classStudentTable');
                if (!$table.length) return;

                var count = extraColumnCount();
                var names = ($('#column_names').val() || '').split(',').map(function(v) {
                    return $.trim(v);
                });

                $table.find('.extra-col').remove();
                var $headRow = $table.find('thead tr');
                for (var i = 0; i < count; i++) {
                    $('<th class="extra-col" style="min-width:110px">').text(names[i] || 'Column ' + (i + 1)).appendTo($headRow);
                }
                $table.find('tbody tr').not('.no-students').each(function() {
                    for (var i = 0; i < count; i++) {
                        $(this).append('<td class="extra-col"></td>');
                    }
                });
                $table.find('tr.no-students td').attr('colspan', 3 + count);
            }

            $('#extra_columns, #column_names').on('input change', renderExtraColumns);
            renderExtraColumns();

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
