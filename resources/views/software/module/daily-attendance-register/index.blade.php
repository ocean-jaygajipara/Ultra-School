@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Daily Attendance Register';
    $route = $modules['route'] ?? 'daily-attendance-register';
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
            --crx-accent-soft: #fff1f1;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--crx-ink);
        }
        .crx-panel {
            background: #fff;
            border: 1px solid var(--crx-line);
            border-radius: 18px;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, .12);
            margin-bottom: 16px;
        }
        .crx-banner {
            background: linear-gradient(135deg, #171c6b 0%, var(--crx-ink-2) 100%);
            color: #fff;
            padding: 22px 26px;
            border-radius: 17px 17px 0 0;
        }
        .crx-banner h3, .crx-banner h4 { color: #fff; margin: 0; font-weight: 700; }
        .crx-banner p { color: #d6e6f7; margin: 4px 0 0; font-size: .875rem; }
        .crx-body { padding: 22px 26px; }
        .crx-label {
            display: block; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--crx-muted); margin-bottom: 8px;
        }
        .crx .select2-container { width: 100% !important; }
        .crx .select2-container--default .select2-selection--single,
        .crx-input {
            height: 46px !important;
            border: 1.5px solid var(--crx-line) !important;
            border-radius: 12px !important;
            background: var(--crx-soft) !important;
        }
        .crx .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 43px !important; padding-left: 14px !important;
            color: var(--crx-ink) !important; font-weight: 500;
        }
        .crx .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px !important; }
        .crx-input { width: 100%; padding: 0 14px; font-size: .9rem; color: var(--crx-ink); }
        .crx-btn-clear {
            height: 46px; padding: 0 20px; border-radius: 12px;
            border: 1.5px solid #f7c3c5; background: var(--crx-accent-soft); color: var(--crx-accent);
            font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
        }
        .crx-stats { display: flex; gap: 10px; flex-wrap: wrap; }
        .crx-stat {
            background: rgba(255,255,255,.1); border: 1px solid rgba(191,227,247,.35);
            border-radius: 12px; padding: 8px 14px; min-width: 100px;
        }
        .crx-stat small { display: block; color: #bfe3f7; font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; }
        .crx-stat strong { color: #fff; }
        .crx-stat.is-accent { background: var(--crx-accent); border-color: var(--crx-accent); }
        .crx-table-wrap { border: 1px solid var(--crx-line); border-radius: 14px; overflow: auto; max-height: 68vh; }
        .crx-table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; min-width: 1100px; }
        .crx-table thead th {
            position: sticky; top: 0; z-index: 2;
            background: #1f2580 !important; color: #fff !important;
            font-size: .68rem; letter-spacing: .03em; text-transform: uppercase;
            padding: 10px 8px; border: 0; white-space: nowrap; text-align: center;
        }
        .crx-table td {
            padding: 8px; border-bottom: 1px solid #eef0f8; vertical-align: middle;
            font-size: .82rem; text-align: center;
        }
        .crx-table td.name { text-align: left; font-weight: 600; color: var(--crx-ink); white-space: nowrap; }
        .crx-table .day-col { min-width: 22px; padding: 6px 2px; font-weight: 700; }
        .crx-table .sun { background: #f5f5f8; color: #94a3b8; }
        .crx-empty { text-align: center; padding: 48px 20px; color: var(--crx-muted); }
        .crx-empty-icon {
            width: 56px; height: 56px; border-radius: 16px; margin: 0 auto 12px;
            background: var(--crx-soft); display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: var(--crx-ink);
        }
        .mark-p { color: #15803d; font-weight: 800; }
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
        <form id="filterForm" action="{{ route('daily-attendance-register.filter') }}" method="POST" class="crx-panel">
            @csrf
            <div class="crx-banner d-flex align-items-center gap-3">
                <div>
                    <h3>Daily Attendance Register</h3>
                    <p>Monthly class register — print landscape like the school book.</p>
                </div>
            </div>
            <div class="crx-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2 col-sm-6">
                        <label class="crx-label" for="month">Month</label>
                        <select id="month" name="month" class="form-control select2">
                            @foreach ($months as $num => $label)
                                <option value="{{ $num }}" @selected((int) $filters['month'] === (int) $num)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="crx-label" for="year">Year</label>
                        <select id="year" name="year" class="form-control select2">
                            @foreach ($years as $y)
                                <option value="{{ $y }}" @selected((int) $filters['year'] === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="crx-label" for="standard">Standard</label>
                        <select id="standard" name="standard" class="form-control select2">
                            <option value="">Select Standard</option>
                            @foreach ($standards as $std)
                                <option value="{{ $std }}" @selected($filters['standard'] === $std)>{{ $std }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="crx-label" for="division">Class</label>
                        <select id="division" name="division" class="form-control select2">
                            <option value="">All</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div }}" @selected($filters['division'] === $div)>{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="crx-label" for="class_teacher">Class Teacher</label>
                        <input type="text" id="class_teacher" name="class_teacher" class="crx-input"
                               value="{{ $filters['class_teacher'] ?? '' }}" placeholder="Teacher name">
                    </div>
                    <div class="col-md-2 col-sm-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="height:46px;border-radius:12px;font-weight:600;">
                            <i class="ti ti-search"></i> Show
                        </button>
                        <button type="submit" name="clear" value="1" class="crx-btn-clear">
                            <i class="ti ti-rotate"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if ($report)
            <div class="crx-panel">
                <div class="crx-banner">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <p class="mt-0 mb-1 text-uppercase" style="letter-spacing:.1em;font-size:.7rem">Register Preview</p>
                            <h4>
                                Std {{ $report['standard_label'] }}{{ $filters['division'] ? '-' . $filters['division'] : '' }}
                                · {{ $report['month_name'] }} {{ $filters['year'] }}
                            </h4>
                        </div>
                        <div class="crx-stats">
                            <div class="crx-stat"><small>Boys</small><strong>{{ $report['counts']['boys'] }}</strong></div>
                            <div class="crx-stat"><small>Girls</small><strong>{{ $report['counts']['girls'] }}</strong></div>
                            <div class="crx-stat is-accent"><small>Students</small><strong>{{ $report['counts']['total'] }}</strong></div>
                            <div class="crx-stat"><small>Working Days</small><strong>{{ $report['working_this_month'] }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="crx-body">
                    <div class="crx-table-wrap">
                        <table class="crx-table">
                            <thead>
                                <tr>
                                    <th>Roll</th>
                                    <th>GR No</th>
                                    <th style="text-align:left">Name</th>
                                    <th>Caste</th>
                                    <th>Birthdate</th>
                                    @foreach ($report['days'] as $day)
                                        <th class="day-col {{ $day['is_sunday'] ? 'sun' : '' }}" title="{{ $day['dow'] }}">
                                            {{ $day['day'] }}
                                        </th>
                                    @endforeach
                                    <th>This Month</th>
                                    <th>Pre. Month</th>
                                    <th>End Month</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report['students'] as $student)
                                    <tr>
                                        <td>{{ $student['roll_no'] }}</td>
                                        <td>{{ $student['gr_no'] }}</td>
                                        <td class="name">{{ $student['name'] }}</td>
                                        <td>{{ $student['caste'] }}</td>
                                        <td>{{ $student['birthdate'] }}</td>
                                        @foreach ($report['days'] as $day)
                                            <td class="day-col {{ $day['is_sunday'] ? 'sun' : '' }}">
                                                @if (($student['marks'][$day['day']] ?? '') === '/')
                                                    <span class="mark-p">/</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 8 + $report['days_in_month'] }}" class="crx-empty">No students in this class.</td>
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
                    <div class="crx-empty-icon"><i class="ti ti-calendar-stats"></i></div>
                    <h5>Select Standard, Month &amp; Year</h5>
                    <p>Then print the landscape Daily Attendance Register.</p>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('page_script_file')
    <script>
        $(function() {
            $('.select2').select2({ width: '100%' });

            function currentParams() {
                return {
                    month: $('#month').val() || '',
                    year: $('#year').val() || '',
                    standard: $('#standard').val() || '',
                    division: $('#division').val() || ''
                };
            }

            function ready() {
                if ($('#standard').val() && $('#month').val() && $('#year').val()) return true;
                var msg = 'Please select Standard, Month and Year first.';
                typeof toastr !== 'undefined' ? toastr.warning(msg) : alert(msg);
                return false;
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

            $('#print_btn').on('click', function(e) {
                e.preventDefault();
                if (!ready()) return;
                postTo("{{ route('daily-attendance-register.print') }}", '_blank');
            });

            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                if (!ready()) return;
                postTo("{{ route('daily-attendance-register.export-excel') }}", '_self');
            });
        });
    </script>
@endsection
