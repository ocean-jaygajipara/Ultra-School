@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Roll Number List';
    $route = $modules['route'] ?? 'roll-number-list';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .rnl {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --gold: #f5b400;
            --line: #e1e4f2;
            --muted: #5f6488;
            --sky: #eef7fd;
            --red: #e1141c;
        }
        .rnl-hero {
            background: linear-gradient(120deg, var(--navy), var(--navy-2));
            color: #fff;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .rnl-hero h4 { margin: 0; font-weight: 800; color: #ffffff !important; }
        .rnl-hero p { margin: 4px 0 0; color: rgba(255, 255, 255, 0.9) !important; font-size: .9rem; }
        .rnl-year {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            padding: 8px 14px;
            text-align: right;
        }
        .rnl-year small { display:block; color: var(--gold); font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .rnl-year strong { color: #fff; }
        .rnl-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 28px -18px rgba(31,37,128,.45);
        }
        .rnl-label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .rnl-table thead th {
            background: var(--navy) !important;
            color: #fff !important;
            font-weight: 700;
            white-space: nowrap;
        }
        .rnl-table tbody td { vertical-align: middle; }
        .rnl-roll {
            display: inline-flex;
            min-width: 36px;
            justify-content: center;
            background: var(--sky);
            color: var(--navy);
            border-radius: 8px;
            padding: 4px 10px;
            font-weight: 800;
        }
        .rnl-roll.is-empty {
            background: #fde8ea;
            color: var(--red);
            font-weight: 600;
            font-size: .8rem;
        }
        .rnl-empty {
            text-align: center;
            color: var(--muted);
            padding: 28px 12px;
        }
    </style>
@endsection

@section('content')
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

    <div class="rnl my-3">
        <div class="rnl-hero">
            <div>
                <h4>Roll Number List</h4>
                <p>View students by Standard and Division, ordered by roll number.</p>
            </div>
            @if ($currentYear)
                <div class="rnl-year">
                    <small>Academic Year</small>
                    <strong>{{ $currentYear }}</strong>
                </div>
            @endif
        </div>

        <div class="rnl-panel mb-3">
            <form method="POST" action="{{ route($route . '.filter') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="rnl-label">Standard <span class="text-danger">*</span></label>
                    <select name="standard_id" id="standard_id" class="form-select select2" required>
                        <option value="">Select Standard</option>
                        @foreach ($standards as $std)
                            <option value="{{ $std->id }}" {{ (string) $standardId === (string) $std->id ? 'selected' : '' }}>
                                {{ $std->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="rnl-label">Division</label>
                    <select name="division" id="division" class="form-select select2">
                        <option value="">All Divisions</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $division === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i> Show List
                    </button>
                    <button type="submit" name="clear" value="1" class="btn btn-outline-secondary" formnovalidate>
                        Clear
                    </button>
                    @if ($standardId && (!empty($modules['permission_print']) || \App\Helpers\Helper::directCan('roll-number-list-print') || \App\Helpers\Helper::directCan('roll-number-list-list')))
                        <a href="{{ route($route . '.print') }}" target="_blank" class="btn btn-outline-primary">
                            <i class="ti ti-printer me-1"></i> Print
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="rnl-panel">
            @if (!$currentYear)
                <div class="rnl-empty">Please set Current Academic Year in Academic Year Master.</div>
            @elseif (!$standardId)
                <div class="rnl-empty">Select Standard and click Show List.</div>
            @elseif ($students->isEmpty())
                <div class="rnl-empty">
                    No students for <strong>{{ $currentYear }}</strong>
                    — {{ $selectedStandard->name ?? '' }}{{ $division ? ' / ' . $division : '' }}.
                    <br><span class="small">Data will appear after admission in this year, or after Class Promotion.</span>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <strong style="color:#1f2580;">
                            {{ $selectedStandard->name ?? '' }}{{ $division ? ' — Div ' . $division : '' }}
                        </strong>
                        <span class="text-muted ms-2">{{ $students->count() }} students</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover rnl-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">Sr</th>
                                <th style="width:110px;">Roll No</th>
                                <th style="width:120px;">GR No</th>
                                <th>Student Name</th>
                                <th style="width:120px;">Standard</th>
                                <th style="width:100px;">Division</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $i => $student)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        @if (!empty($student->roll_no))
                                            <span class="rnl-roll">{{ $student->roll_no }}</span>
                                        @else
                                            <span class="rnl-roll is-empty">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $student->gr_no ?: '—' }}</td>
                                    <td><strong>{{ $student->full_name }}</strong></td>
                                    <td>{{ $student->current_std ?: '—' }}</td>
                                    <td>{{ $student->division ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('page_leavel_script')
<script>
    $(function() {
        if ($.fn.select2) {
            $('#standard_id, #division').select2({ width: '100%' });
        }
    });
</script>
@endsection
