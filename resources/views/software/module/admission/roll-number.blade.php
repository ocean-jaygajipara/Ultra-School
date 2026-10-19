@extends('software.layout.app')

@php
    $page_title = 'Roll Number';
    $route = isset($modules['route']) ? $modules['route'] : null;
    $canGenerate = !empty($admission->current_std) && !empty($admission->division);

    $admissionDate = '';
    if (!empty($admission->admission_date)) {
        try {
            $admissionDate = \Carbon\Carbon::parse($admission->admission_date)->format('d-m-Y');
        } catch (\Exception $e) {
            $admissionDate = $admission->admission_date;
        }
    }

    $isCurrent = fn ($row) => $currentYear
        ? (int) $row->academic_year_id === (int) $currentYear->id
        : $row->is($history->last());
    $hasCurrentRow = $history->contains($isCurrent);
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .rnh-card {
            border: 1px solid #e1e4f2;
            border-radius: 14px;
            overflow: hidden;
        }

        .rnh-head {
            background: linear-gradient(120deg, #1f2580 0%, #2e3a9e 100%);
            color: #fff;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .rnh-head i {
            color: #f5b400;
            font-size: 1.2rem;
        }

        .rnh-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 14px 18px;
            background: #eef7fd;
            border-bottom: 1px solid #e1e4f2;
        }

        .rnh-info small {
            display: block;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #5f6488;
            font-weight: 600;
        }

        .rnh-info strong {
            color: #1f2580;
            font-size: .95rem;
        }

        .rnh-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .rnh-table th {
            background: #f7f8fd;
            color: #5f6488;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 9px 12px;
            border-bottom: 1px solid #e1e4f2;
        }

        .rnh-table td {
            padding: 9px 12px;
            border-bottom: 1px solid #eef0f8;
            font-size: .88rem;
            color: #23264d;
        }

        .rnh-table tr:last-child td {
            border-bottom: 0;
        }

        .rnh-table tr.is-current td {
            background: #fff8e1;
            font-weight: 600;
        }

        .rnh-roll {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 26px;
            padding: 0 7px;
            border-radius: 7px;
            background: #1f2580;
            color: #fff;
            font-weight: 700;
            font-size: .78rem;
        }

        .rnh-roll.is-empty {
            background: #eef0f8;
            color: #9aa0c3;
        }

        .rnh-tag {
            font-size: .65rem;
            background: #f5b400;
            color: #2a2000;
            border-radius: 5px;
            padding: 1px 6px;
            margin-left: 4px;
            font-weight: 700;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('software.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">Registered Student</a></li>
                        <li class="breadcrumb-item active">{{ $page_title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row my-4 g-3">
        <div class="col-lg-8">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h5 class="mb-0">Generate Roll Number</h5>
                </div>
                <div class="card-body">
                    @if (!$canGenerate)
                        <div class="alert alert-warning">
                            Current Std and Division are not set for this student.
                            <a href="{{ route($route . '.edit', $admission->id) }}">Edit the student</a> to set them first.
                        </div>
                    @endif

                    <form action="{{ route('admission.roll-number.store', $admission->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Standard</label>
                                <input type="text" class="form-control" value="{{ $admission->current_std }}" readonly>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Division</label>
                                <input type="text" class="form-control" value="{{ $admission->division }}" readonly>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="{{ $admission->full_name }}" readonly>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">GR No.</label>
                                <input type="text" class="form-control" value="{{ $admission->gr_no }}" readonly>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Gender</label>
                                <input type="text" class="form-control" value="{{ $admission->gender }}" readonly>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Roll No. <span class="text-danger">*</span></label>
                                <input type="number" min="1" name="roll_no"
                                    class="form-control fw-bold @error('roll_no') is-invalid @enderror"
                                    value="{{ old('roll_no', $suggestedRollNo) }}" {{ $canGenerate ? '' : 'disabled' }}>
                                <small class="text-muted">Next available roll number for {{ $admission->current_std }} - {{ $admission->division }} is filled automatically. You can change it if needed.</small>
                                @error('roll_no')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-success mt-1" {{ $canGenerate ? '' : 'disabled' }}>Submit</button>
                                <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow rnh-card h-100">
                <div class="rnh-head"><i class="ti ti-history"></i> Class History</div>
                @unless ($currentYear)
                    <div class="alert alert-warning m-3 mb-0 small">
                        No current academic year is set.
                        <a href="{{ route('academic-year.index') }}">Add it in Academic Year Master</a>.
                    </div>
                @endunless
                <div class="rnh-info">
                    <div>
                        <small>Admission Date</small>
                        <strong>{{ $admissionDate ?: '—' }}</strong>
                    </div>
                    <div>
                        <small>Admission Std</small>
                        <strong>{{ $admission->admission_std ?: '—' }}</strong>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="rnh-table">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Std</th>
                                <th>Div</th>
                                <th class="text-center">Roll No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $row)
                                <tr class="{{ $isCurrent($row) ? 'is-current' : '' }}">
                                    <td>{{ $row->academicYear?->name ?? '—' }}</td>
                                    <td>
                                        {{ $row->standard?->name ?? '—' }}
                                        @if ($isCurrent($row))
                                            <span class="rnh-tag">Current</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->division ?: '—' }}</td>
                                    <td class="text-center">
                                        <span class="rnh-roll {{ $row->roll_no ? '' : 'is-empty' }}">{{ $row->roll_no ?: '—' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            @if (!$hasCurrentRow && $admission->current_std && $currentYear)
                                <tr class="is-current">
                                    <td>{{ $currentYear->name }}</td>
                                    <td>{{ $admission->current_std }} <span class="rnh-tag">Current</span></td>
                                    <td>{{ $admission->division ?: '—' }}</td>
                                    <td class="text-center">
                                        <span class="rnh-roll {{ $admission->roll_no ? '' : 'is-empty' }}">{{ $admission->roll_no ?: '—' }}</span>
                                    </td>
                                </tr>
                            @endif
                            @if ($history->isEmpty() && (!$admission->current_std || !$currentYear))
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No class history yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
