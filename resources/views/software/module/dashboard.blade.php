@extends('software.layout.app')

@section('page_style_file')
    <style>
        .dash-stats {
            --navy: #1f2580;
            --muted: #5f6488;
            --line: #e1e4f2;
        }

        .dash-link {
            display: block;
            height: 100%;
            color: inherit;
            text-decoration: none;
        }

        .dash-tile {
            position: relative;
            overflow: hidden;
            height: 100%;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px 18px 16px 20px;
            box-shadow: 0 10px 28px -18px rgba(31, 37, 128, .55);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .dash-tile:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 34px -16px rgba(31, 37, 128, .45);
        }

        .dash-tile::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--accent, #1f2580);
        }

        .dash-tile::after {
            content: "";
            position: absolute;
            right: -36px;
            top: -36px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: var(--accent, #1f2580);
            opacity: .08;
        }

        .dash-ico {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 14px;
            background: var(--tint, #eef7fd);
            color: var(--accent, #1f2580);
            position: relative;
            z-index: 1;
        }

        .dash-label {
            display: block;
            color: var(--muted);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
        }

        .dash-value {
            color: var(--navy);
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .dash-list {
            position: relative;
            z-index: 1;
            margin-top: 2px;
        }

        .dash-item {
            display: block;
            color: var(--navy);
            font-size: .82rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 7px;
            text-decoration: none;
        }

        .dash-item:hover { color: #e1141c; }

        .dash-empty {
            display: inline-flex;
            align-items: center;
            margin-top: 8px;
            background: #f4f6fb;
            color: var(--muted);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: .78rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .is-navy { --accent: #1f2580; --tint: #eef7fd; }
        .is-gold { --accent: #d79a00; --tint: #fff6dc; }
        .is-green { --accent: #1a9b5c; --tint: #e8f8ef; }
        .is-red { --accent: #e1141c; --tint: #fde8ea; }
        .is-sky { --accent: #1d7aa8; --tint: #e7f6fd; }

        .bday {
            position: relative;
            overflow: hidden;
            height: 100%;
            background: #fff;
            border: 1px solid #e1e4f2;
            border-radius: 16px;
            padding: 16px 18px 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            box-shadow: 0 10px 28px -18px rgba(31, 37, 128, .55);
        }

        .bday::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--accent, #1f2580);
        }

        .bday::after {
            content: "";
            position: absolute;
            right: -42px;
            bottom: -50px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: var(--accent, #1f2580);
            opacity: .08;
        }

        .bday-main {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            position: relative;
            z-index: 1;
        }

        .bday-ico {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: var(--tint, #eef7fd);
        }

        .bday-title {
            margin: 0 0 4px;
            color: #1f2580;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .bday-count {
            display: inline-flex;
            margin-left: 6px;
            background: var(--tint, #eef7fd);
            color: var(--accent, #1f2580);
            border-radius: 999px;
            padding: 1px 8px;
            font-size: .75rem;
            vertical-align: 1px;
        }

        .bday-names {
            margin: 0;
            color: #5f6488;
            font-size: .88rem;
            line-height: 1.45;
        }

        .bday-names strong { color: #1f2580; }

        .bday-badge {
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            background: #fff6dc;
            color: #9a7200;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: .78rem;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    @include('software.partials.flash_messages')

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="bday is-navy">
                <div class="bday-main">
                    <div class="bday-ico">🎉</div>
                    <div>
                        @if($studentBirthdaysCount > 0)
                            <h5 class="bday-title">Today's Student Birthdays <span class="bday-count">{{ $studentBirthdaysCount }}</span></h5>
                            <p class="bday-names">
                                @foreach($studentBirthdays as $birthday)
                                    <strong>{{ $birthday->first_name }} {{ $birthday->last_name }}</strong>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        @else
                            <h5 class="bday-title">Today's Student Birthdays</h5>
                            <p class="bday-names">No student birthdays today</p>
                        @endif
                    </div>
                </div>
                @if($studentBirthdaysCount > 0)
                    <span class="bday-badge">Happy Birthday 🎂</span>
                @endif
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="bday is-gold">
                <div class="bday-main">
                    <div class="bday-ico">🏫</div>
                    <div>
                        @if($facultyBirthdaysCount > 0)
                            <h5 class="bday-title">Today's Faculty Birthdays <span class="bday-count">{{ $facultyBirthdaysCount }}</span></h5>
                            <p class="bday-names">
                                @foreach($facultyBirthdays as $birthday)
                                    <strong>{{ $birthday->name }}</strong>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        @else
                            <h5 class="bday-title">Today's Faculty Birthdays</h5>
                            <p class="bday-names">No faculty birthdays today</p>
                        @endif
                    </div>
                </div>
                @if($facultyBirthdaysCount > 0)
                    <span class="bday-badge">Happy Birthday 🎂</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row dash-stats">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="dash-tile is-navy">
                <div class="dash-ico"><i class="bx bx-group"></i></div>
                <span class="dash-label">Total Students</span>
                <p class="dash-value">{{ $totalStudents }}</p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="dash-tile is-gold">
                <div class="dash-ico"><i class="bx bx-layer"></i></div>
                <span class="dash-label">Total Standards</span>
                <p class="dash-value">{{ $totalStandards }}</p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <a href="{{ route('student-requests.index') }}" class="dash-link">
                <div class="dash-tile is-green">
                    <div class="dash-ico"><i class="bx bx-envelope"></i></div>
                    <span class="dash-label">Student Requests</span>
                    <p class="dash-value">{{ $studentRequestsCount }}</p>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
            <div class="dash-tile is-red">
                <div class="dash-ico"><i class="bx bx-calendar-event"></i></div>
                <span class="dash-label">Today's Tests</span>
                <div class="dash-list">
                    @forelse($todayTests as $test)
                        <span class="dash-item" title="{{ $test->course->course_name ?? '-' }} ({{ $test->subject_name ?? '-' }})">
                            {{ $loop->iteration }}. {{ $test->course->course_name ?? '-' }} ({{ $test->subject_name ?? '-' }})
                        </span>
                    @empty
                        <span class="dash-empty">No tests today</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
            <div class="dash-tile is-sky">
                <div class="dash-ico"><i class="bx bx-table"></i></div>
                <span class="dash-label">Timetables</span>
                <div class="dash-list">
                    @forelse($timetables as $timetable)
                        <a href="{{ asset($timetable->attachment) }}" target="_blank" class="dash-item" title="{{ $timetable->course->course_name ?? '-' }}({{ $timetable->batch->batch_name ?? '-' }}) {{ $timetable->class->class ?? '-' }}">
                            <i class="bx bx-link-alt me-1"></i>{{ $timetable->course->course_name ?? '-' }}({{ $timetable->batch->batch_name ?? '-' }}) {{ $timetable->class->class ?? '-' }}
                        </a>
                    @empty
                        <span class="dash-empty">No active timetables</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if ($isFaculty)
        <!-- Tasks Section for Faculty -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0 fw-bold d-flex align-items-center">
                            <i class="bx bx-list-check me-2 text-primary fs-4"></i> My Assigned Tasks
                        </h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Task</th>
                                    <th>Assign Date</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th>Remarks</th>
                                    <th>Response</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($tasks as $task)
                                    @php
                                        $statusText = 'Pending';
                                        $class = 'danger';
                                        if ($task->status == 1) {
                                            $statusText = 'In Progress';
                                            $class = 'warning';
                                        } elseif ($task->status == 2) {
                                            $statusText = 'Completed';
                                            $class = 'success';
                                        } elseif ($task->status == 3) {
                                            $statusText = 'Done';
                                            $class = 'info';
                                        } elseif ($task->status == 4) {
                                            $statusText = 'Repass';
                                            $class = 'secondary';
                                        } elseif ($task->status == 5) {
                                            $statusText = 'Cancelled';
                                            $class = 'dark';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $task->title }}</strong></td>
                                        <td>{{ $task->date ? \Carbon\Carbon::parse($task->date)->format('d-m-Y h:i A') : '-' }}</td>
                                        <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d-m-Y h:i A') : '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $class }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ $task->remarks ?? '-' }}</td>
                                        <td>{{ $task->response ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No tasks assigned to you.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
