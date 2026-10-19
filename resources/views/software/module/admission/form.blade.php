@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <style>
        .swal2-popup.swal-wide {
            width: 550px !important;
        }

        .afx {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --red: #e1141c;
            --sky: #bfe3f7;
            --sky-soft: #eef7fd;
            --gold: #f5b400;
            --line: #e1e4f2;
            --muted: #5f6488;
        }

        .afx-hero {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 22px 26px;
            margin-top: 1rem;
            background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 100%);
            color: #fff;
            box-shadow: 0 10px 28px rgba(31, 37, 128, .22);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .afx-hero::after {
            content: "";
            position: absolute;
            right: -50px;
            top: -70px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
        }

        .afx-year {
            position: relative;
            z-index: 1;
            margin-left: auto;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 14px;
            padding: 8px 16px;
            text-align: right;
            line-height: 1.3;
        }

        .afx-year small {
            display: block;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--gold);
            font-weight: 700;
        }

        .afx-year strong {
            display: block;
            color: #fff;
            font-size: 1.1rem;
        }

        .afx-year span,
        .afx-year a {
            font-size: .76rem;
            color: rgba(255, 255, 255, .8);
        }

        .afx-year a {
            text-decoration: underline;
        }

        .afx-year.is-missing {
            background: var(--red);
            border-color: var(--red);
        }

        @media (max-width: 767px) {
            .afx-hero { flex-wrap: wrap; }
            .afx-year { margin-left: 0; text-align: left; }
        }

        .afx-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: var(--gold);
            flex-shrink: 0;
        }

        .afx-hero h4 {
            color: #fff;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .afx-hero p {
            margin: 0;
            color: rgba(255, 255, 255, .78);
            font-size: .9rem;
        }

        .afx-card {
            border: 1px solid var(--line) !important;
            border-radius: 18px !important;
            box-shadow: 0 4px 18px rgba(31, 37, 128, .06) !important;
        }

        .afx-card > .card-body {
            margin: 0 !important;
            padding: 22px;
        }

        .afx hr {
            display: none;
        }

        .afx-sec {
            display: flex !important;
            align-items: center;
            gap: 10px;
            margin: 22px 0 14px !important;
            padding: 11px 14px;
            border-radius: 12px;
            background: var(--sky-soft);
            border-left: 4px solid var(--navy);
            color: var(--navy) !important;
            font-size: .98rem;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
            text-decoration: none;
        }

        .afx-sec.is-static {
            cursor: default;
        }

        .afx form > .row > div:first-child .afx-sec,
        .afx-sec.first {
            margin-top: 0 !important;
        }

        .afx-sec-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .afx-sec-title {
            flex: 1;
        }

        .afx-sec-chev {
            transition: transform .2s ease;
            font-size: 1.2rem;
        }

        .afx-sec.collapsed .afx-sec-chev {
            transform: rotate(-90deg);
        }

        .afx .form-label {
            font-weight: 600;
            color: #3a3f6b;
            font-size: .82rem;
            margin-bottom: 5px;
        }

        .afx .form-label:has(#copy_address) {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .afx .form-control,
        .afx .form-select,
        .afx .select2-container--default .select2-selection--single,
        .afx .select2-container--default .select2-selection--multiple {
            border-radius: 10px !important;
            border-color: #d6d9ec !important;
            min-height: 40px;
        }

        .afx .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .afx .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .afx .form-control:focus,
        .afx .select2-container--focus .select2-selection,
        .afx .select2-container--open .select2-selection {
            border-color: var(--navy-2) !important;
            box-shadow: 0 0 0 3px rgba(46, 58, 158, .12) !important;
        }

        .afx .form-control.is-invalid {
            border-color: var(--red) !important;
        }

        .afx .input-group > .form-control {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .afx .input-group-text {
            border-radius: 0 10px 10px 0 !important;
            background: var(--sky-soft);
            color: var(--navy);
            border-color: #d6d9ec;
        }

        .afx textarea.form-control {
            min-height: 90px;
        }

        .afx .form-control[readonly] {
            background: #f7f8fd !important;
        }

        .afx-photo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .afx-photo-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid var(--sky);
            background: #e9ebf8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--navy);
            font-size: 1.1rem;
            overflow: hidden;
        }

        .afx-photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .afx-photo .form-control {
            flex: 1;
        }

        #previous_school_main > .d-flex {
            padding: 11px 14px;
            border-radius: 12px;
            background: var(--sky-soft);
            border-left: 4px solid var(--navy);
            margin-top: 22px;
        }

        #previous_school_main > .d-flex h6 {
            color: var(--navy) !important;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .98rem;
        }

        .afx-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin: 24px -22px -22px !important;
            padding: 14px 22px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(4px);
            border-top: 1px solid var(--line);
            border-radius: 0 0 18px 18px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .afx-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: .9rem;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all .15s ease;
        }

        .afx-btn-primary {
            background: var(--navy);
            color: #fff;
        }

        .afx-btn-primary:hover {
            background: var(--navy-2);
            color: #fff;
        }

        .afx-btn-light {
            background: #fff;
            color: var(--muted);
            border-color: var(--line);
        }

        .afx-btn-light:hover {
            border-color: var(--red);
            color: var(--red);
        }

        @media (max-width: 575px) {
            .afx-actions .afx-btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>

    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
@endsection


@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>



    <div class="afx">
    <div class="afx-hero">
        <div class="afx-hero-icon"><i class="ti {{ isset($edit) && $edit?->id ? 'ti-user-edit' : 'ti-user-plus' }}"></i></div>
        <div>
            <h4>{{ isset($edit) && $edit?->id ? 'Edit Student' : 'New Admission' }}</h4>
            <p>
                @if (isset($edit) && $edit?->id)
                    Update details of {{ trim(($edit->first_name ?? '') . ' ' . ($edit->last_name ?? '') . ' ' . ($edit->father_name ?? '')) }}
                @else
                    Fill in the student details below. Fields marked <span style="color:#ffb3b6;">*</span> are required.
                @endif
            </p>
        </div>
        @php
            $afxYearId = old('academic_year_id', $edit->academic_year_id ?? null);
            $afxYear = $afxYearId
                ? \App\Models\Master\MasterAcademicYear::withTrashed()->find($afxYearId)
                : \App\Models\Master\MasterAcademicYear::current();
        @endphp
        <div class="afx-year {{ $afxYear ? '' : 'is-missing' }}">
            @if ($afxYear)
                <small>Academic Year</small>
                <strong id="afxYearName">{{ $afxYear->name }}</strong>
                <span id="afxYearRange">{{ $afxYear->start_date->format('d-m-Y') }} to {{ $afxYear->end_date->format('d-m-Y') }}</span>
            @else
                <small>Academic Year</small>
                <strong>Not set</strong>
                <a href="{{ route('academic-year.index') }}">Add in Academic Year Master</a>
            @endif
        </div>
    </div>

    <div class="card afx-card my-3">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset
                <div class="row">
                    <!-- Hidden fields to pass to controller -->
                    <input type="hidden" name="aadhar_card_no" id="aadhar_hidden">
                    <input type="hidden" name="admission_id" id="admission_hidden">

                    <div>
                        <h6 class="afx-sec first" data-bs-toggle="collapse" href="#aadharSection" role="button"
                            aria-expanded="true" aria-controls="aadharSection">
                            <span class="afx-sec-icon"><i class="ti ti-school"></i></span>
                            <span class="afx-sec-title">Admission Details</span>
                            <i class="ti ti-chevron-down afx-sec-chev"></i>
                        </h6>
                    </div>
                    <div class="collapse show" id="aadharSection">
                        <div class="row">
                            {{-- GR No. --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">GR No.</label>
                                    <input id="gr_no" type="text"
                                        class="form-control @error('gr_no') is-invalid @enderror"
                                        name="gr_no"
                                        value="{{ isset($edit?->gr_no) ? $edit?->gr_no : old('gr_no') }}"
                                        placeholder="Enter GR No." autocomplete="off">
                                    @error('gr_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Bus Route Village (Dropdown / Type New Entry) --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Bus Route Village</label>
                                    @php
                                        $villageList = isset($villages)
                                            ? $villages
                                            : \App\Models\Master\MasterBusRouteVillage::where('status', 'active')->orderBy('name')->get();
                                        $selectedVillage = old('bus_route_village', $edit->bus_route_village ?? '');
                                        $villageNames = $villageList->pluck('name')->toArray();
                                    @endphp
                                    <select name="bus_route_village" id="bus_route_village" class="form-control select2-tags">
                                        <option value="">Select or Type Bus Route Village</option>
                                        @foreach ($villageList as $villageItem)
                                            @php $vName = is_object($villageItem) ? $villageItem->name : $villageItem; @endphp
                                            <option value="{{ $vName }}"
                                                {{ ($selectedVillage == $vName) ? 'selected' : '' }}>
                                                {{ $vName }}
                                            </option>
                                        @endforeach
                                        @if(!empty($selectedVillage) && !in_array($selectedVillage, $villageNames))
                                            <option value="{{ $selectedVillage }}" selected>{{ $selectedVillage }}</option>
                                        @endif
                                    </select>
                                    <small class="text-muted d-block mt-1">Select from list or type a new village to auto-add to master.</small>
                                    @error('bus_route_village')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Admission Date --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Admission Date</label>
                                    @php
                                        $admDateVal = old('admission_date', $edit->admission_date ?? '');
                                        $admDateFormatted = '';
                                        if (!empty($admDateVal)) {
                                            try {
                                                $admDateFormatted = \Carbon\Carbon::parse($admDateVal)->format('d-m-Y');
                                            } catch (\Exception $e) {
                                                $admDateFormatted = $admDateVal;
                                            }
                                        } else {
                                            $admDateFormatted = date('d-m-Y');
                                        }
                                    @endphp
                                    <div class="input-group">
                                        <input id="admission_date" type="text"
                                            class="form-control flatpickr-adm-date @error('admission_date') is-invalid @enderror"
                                            name="admission_date" placeholder="DD-MM-YYYY" autocomplete="off"
                                            value="{{ $admDateFormatted }}">
                                        <span class="input-group-text cursor-pointer" id="adm_date_picker_btn" title="Choose Date">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </span>
                                    </div>
                                    @error('admission_date')
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Academic Year --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    @php
                                        $academicYearList = isset($academicYears)
                                            ? $academicYears
                                            : \App\Models\Master\MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
                                        $selectedAcademicYear = old(
                                            'academic_year_id',
                                            $edit->academic_year_id
                                                ?? \App\Models\Master\MasterAcademicYear::current()?->id
                                        );
                                    @endphp
                                    <select name="academic_year_id" id="academic_year_id"
                                        class="form-control select2 @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach ($academicYearList as $yearItem)
                                            <option value="{{ $yearItem->id }}"
                                                data-name="{{ $yearItem->name }}"
                                                data-range="{{ $yearItem->start_date->format('d-m-Y') }} to {{ $yearItem->end_date->format('d-m-Y') }}"
                                                {{ (string) $selectedAcademicYear === (string) $yearItem->id ? 'selected' : '' }}>
                                                {{ $yearItem->name }}{{ $yearItem->is_current ? ' (Current)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            @php
                                $selectedAdmStd = (string) old('admission_std_id', $edit->admission_std_id ?? '');
                                $selectedCurrentStd = (string) old('current_std_id', $edit->current_std_id ?? '');
                                $stdOptions = \App\Models\Master\MasterStandard::withTrashed()
                                    ->where(function ($q) use ($selectedAdmStd, $selectedCurrentStd) {
                                        $q->where(fn ($a) => $a->where('status', 'active')->whereNull('deleted_at'))
                                            ->orWhereIn('id', array_filter([$selectedAdmStd, $selectedCurrentStd]));
                                    })
                                    ->orderBy('id')
                                    ->pluck('name', 'id');
                            @endphp

                            {{-- Admission Std --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Admission Std</label>
                                    <select name="admission_std_id" id="admission_std" class="form-control select2">
                                        <option value="">Select Admission Std</option>
                                        @foreach($stdOptions as $stdId => $stdName)
                                            <option value="{{ $stdId }}" {{ $selectedAdmStd === (string) $stdId ? 'selected' : '' }}>{{ $stdName }}</option>
                                        @endforeach
                                    </select>
                                    @error('admission_std_id')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Current Std --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Current Std</label>
                                    <select name="current_std_id" id="current_std" class="form-control select2">
                                        <option value="">Select Current Std</option>
                                        @foreach($stdOptions as $stdId => $stdName)
                                            <option value="{{ $stdId }}" {{ $selectedCurrentStd === (string) $stdId ? 'selected' : '' }}>{{ $stdName }}</option>
                                        @endforeach
                                    </select>
                                    @error('current_std_id')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Division --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Division</label>
                                    @php
                                        $divisionList = isset($divisions)
                                            ? $divisions
                                             : \App\Models\Master\MasterDivision::where('status', 'active')->orderBy('name')->get();
                                    @endphp
                                    @php
                                        $selectedDivision = old('division', $edit->division ?? '');
                                        $divisionNames = collect($divisionList)->map(fn ($d) => is_object($d) ? $d->name : $d);
                                    @endphp
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach ($divisionNames as $dName)
                                            <option value="{{ $dName }}" {{ $selectedDivision == $dName ? 'selected' : '' }}>
                                                {{ $dName }}
                                            </option>
                                        @endforeach
                                        @if ($selectedDivision !== '' && !$divisionNames->contains($selectedDivision))
                                            <option value="{{ $selectedDivision }}" selected>{{ $selectedDivision }}</option>
                                        @endif
                                    </select>
                                    @error('division')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Profile Picture --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Profile Picture <span class="text-danger">*</span></label>
                                    <div class="afx-photo">
                                        <div class="afx-photo-preview" id="profilePicPreview">
                                            @if (!empty($edit?->profile_pic))
                                                <img src="{{ asset($edit->profile_pic) }}" alt="Profile Picture">
                                            @else
                                                <i class="ti ti-camera"></i>
                                            @endif
                                        </div>
                                        <input type="file" id="profile_pic_input"
                                            class="form-control @error('profile_pic') is-invalid @enderror"
                                            name="profile_pic" accept="image/*">
                                    </div>
                                    @error('profile_pic')
                                        <span class="invalid-feedback d-block">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Biometric ID --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Biometric ID</label>
                                    <input id="biometric_id" type="text"
                                        class="form-control @error('biometric_id') is-invalid @enderror"
                                        name="biometric_id"
                                        value="{{ isset($edit?->biometric_id) ? $edit?->biometric_id : old('biometric_id') }}"
                                        placeholder="Enter Biometric ID" autocomplete="off">
                                    @error('biometric_id')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <hr>

                    <div class="text-left">
                        <h6 class="afx-sec" data-bs-toggle="collapse" href="#personalDetails" role="button"
                            aria-expanded="true" aria-controls="personalDetails">
                            <span class="afx-sec-icon"><i class="ti ti-user"></i></span>
                            <span class="afx-sec-title">Personal Details</span>
                            <i class="ti ti-chevron-down afx-sec-chev"></i>
                        </h6>
                    </div>



                    <div class="collapse show mt-2" id="personalDetails">
                        <div class="row">
                            {{-- First Name --}}
                            {{-- First Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Surname <span class="text-danger">*</span> </label>
                                    <input id="first_name" type="text"
                                        class="form-control text-uppercase @error('first_name') is-invalid @enderror"
                                        name="first_name" autocapitalize="off" oninput="updateFullNames()"
                                        value="{{ isset($edit?->first_name) ? $edit?->first_name : old('first_name') }}"
                                        placeholder="Enter Surname">
                                    @error('first_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Last Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Name <span class="text-danger">*</span> </label>
                                    <input id="last_name" type="text"
                                        class="form-control text-uppercase @error('last_name') is-invalid @enderror "
                                        name="last_name" autocapitalize="off"
                                        value="{{ isset($edit?->last_name) ? $edit?->last_name : old('last_name') }}"
                                        placeholder="Enter Name">
                                    @error('last_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Father Name --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Father Name <span class="text-danger">*</span> </label>
                                    <input id="father_name" type="text"
                                        class="form-control text-uppercase @error('father_name') is-invalid @enderror "
                                        name="father_name" autocapitalize="off" oninput="updateFullNames()"
                                        value="{{ isset($edit?->father_name) ? $edit?->father_name : old('father_name') }}"
                                        placeholder="Enter Father Name">
                                    @error('father_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Surname with Father Name --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Surname with Father Name </label>
                                    <input id="father_full_name" type="text"
                                        class="form-control text-uppercase bg-white" readonly style="background-color: #fff !important;"
                                        value="{{ isset($edit) ? $edit?->father_full_name : trim(((old('first_name') ?? '') . ' ' . (old('father_name') ?? ''))) }}"
                                       >
                                </div>
                            </div>

                             {{-- Father Occupation --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Father Occupation</label>
                                    <input id="occupation" type="text"
                                        class="form-control text-uppercase @error('occupation') is-invalid @enderror"
                                        name="occupation" autocapitalize="off"
                                        value="{{ isset($edit?->occupation) ? $edit?->occupation : old('occupation') }}"
                                        placeholder="Enter Father Occupation">
                                    @error('occupation')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            {{-- Mother Name --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Mother Name <span class="text-danger">*</span> </label>
                                    <input id="mother_name" type="text"
                                        class="form-control text-uppercase @error('mother_name') is-invalid @enderror "
                                        name="mother_name" autocapitalize="off" oninput="updateFullNames()"
                                        value="{{ isset($edit?->mother_name) ? $edit?->mother_name : old('mother_name') }}"
                                        placeholder="Enter Mother Name">
                                    @error('mother_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Surname with Mother Name --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Surname with Mother Name </label>
                                    <input id="mother_full_name" type="text"
                                        class="form-control text-uppercase bg-white" readonly style="background-color: #fff !important;"
                                        value="{{ isset($edit) ? $edit?->mother_full_name : trim(((old('first_name') ?? '') . ' ' . (old('mother_name') ?? ''))) }}"
                                        >
                                </div>
                            </div>

                         

                            {{-- Mother Occupation --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Mother Occupation</label>
                                    <input id="mother_occupation" type="text"
                                        class="form-control text-uppercase @error('mother_occupation') is-invalid @enderror"
                                        name="mother_occupation" autocapitalize="off"
                                        value="{{ isset($edit?->mother_occupation) ? $edit?->mother_occupation : old('mother_occupation') }}"
                                        placeholder="Enter Mother Occupation">
                                    @error('mother_occupation')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Religion (Dropdown from Religion Master) --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Religion</label>
                                    @php
                                        $religionList = isset($religions)
                                            ? $religions
                                             : \App\Models\Master\MasterReligion::where('status', 'active')->orderBy('id')->get();
                                        $selectedReligion = old('religion', $edit->religion ?? '');
                                    @endphp
                                    <select name="religion" id="religion" class="form-control select2 @error('religion') is-invalid @enderror">
                                        <option value="">Select Religion</option>
                                        @foreach ($religionList as $rItem)
                                            @php $rName = is_object($rItem) ? $rItem->name : $rItem; @endphp
                                            <option value="{{ $rName }}" {{ ($selectedReligion == $rName) ? 'selected' : '' }}>
                                                {{ $rName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('religion')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Birth Place --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Birth Place</label>
                                    <input id="birth_place" type="text"
                                        class="form-control text-uppercase @error('birth_place') is-invalid @enderror"
                                        name="birth_place"
                                        value="{{ isset($edit?->birth_place) ? $edit?->birth_place : old('birth_place') }}"
                                        placeholder="Enter Birth Place" autocomplete="off">
                                    @error('birth_place')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Cast (Dropdown from Category Master) --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Cast</label>
                                    @php
                                        $castList = isset($categories)
                                            ? $categories
                                            : \App\Models\Master\MasterCast::where('status', 'active')->orderBy('id')->get();
                                        $selectedCast = old('cast', $edit->cast ?? '');
                                    @endphp
                                    <select name="cast" id="cast" class="form-control select2 @error('cast') is-invalid @enderror">
                                        <option value="">Select Cast</option>
                                        @php $castNames = collect($castList)->map(fn ($c) => is_object($c) ? $c->name : $c); @endphp
                                        @foreach ($castNames as $castName)
                                            <option value="{{ $castName }}" {{ ($selectedCast == $castName) ? 'selected' : '' }}>
                                                {{ $castName }}
                                            </option>
                                        @endforeach
                                        @if ($selectedCast !== '' && !$castNames->contains($selectedCast))
                                            <option value="{{ $selectedCast }}" selected>{{ $selectedCast }}</option>
                                        @endif
                                    </select>
                                    @error('cast')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Category (Radio Buttons) --}}
                            <div class="col-md-8 col-sm-12 mb-3">
                                <div class="form-group">
                                    <small class="fw-medium d-block @error('category') is-invalid @enderror">Category <span class="text-danger">*</span></small>
                                    <div class="d-flex flex-wrap gap-3 mt-1">
                                        @foreach (['Baxipanch', 'SC', 'ST', 'Handicaped', 'Vichar Vimukti Jati', 'Ex-Serviceman', 'General', 'Low-Profession', 'Minority'] as $categoryItem)
                                            <div class="form-check form-check-inline">
                                                <input type="radio" name="category" class="form-check-input"
                                                    value="{{ $categoryItem }}" id="category_{{ \Illuminate\Support\Str::slug($categoryItem) }}"
                                                    {{ strtolower(trim((string)old('category', $edit->category ?? ''))) === strtolower(trim($categoryItem)) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="category_{{ \Illuminate\Support\Str::slug($categoryItem) }}">{{ $categoryItem }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('category')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Gender --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <small class="fw-medium d-block">Gender <span class="text-danger">*</span></small>
                                    @foreach (['Male', 'Female', 'Other'] as $gender)
                                        <div class="form-check form-check-inline mt-2">
                                            <input type="radio" name="gender" class="form-check-input"
                                                value="{{ $gender }}" id="gender_{{ $gender }}"
                                                {{ old('gender', $edit->gender ?? '') === $gender ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gender_{{ $gender }}">
                                                {{ $gender }} </label>
                                        </div>
                                    @endforeach
                                    @error('gender')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- House (Dropdown from House Master) --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">House</label>
                                    @php
                                        $houseList = isset($houses)
                                            ? $houses
                                            : \App\Models\Master\MasterHouse::where('status', 'active')->orderBy('id')->get();
                                        $selectedHouse = old('house', $edit->house ?? '');
                                    @endphp
                                    <select name="house" id="house" class="form-control select2 @error('house') is-invalid @enderror">
                                        <option value="">Select House</option>
                                        @php $houseNames = collect($houseList)->map(fn ($h) => is_object($h) ? $h->name : $h); @endphp
                                        @foreach ($houseNames as $hName)
                                            <option value="{{ $hName }}" {{ ($selectedHouse == $hName) ? 'selected' : '' }}>
                                                {{ $hName }}
                                            </option>
                                        @endforeach
                                        @if ($selectedHouse !== '' && !$houseNames->contains($selectedHouse))
                                            <option value="{{ $selectedHouse }}" selected>{{ $selectedHouse }}</option>
                                        @endif
                                    </select>
                                    @error('house')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Stream --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Stream</label>
                                    @php
                                        $streamList = ['General', 'RTE Students'];
                                        $selectedStream = old('stream', $edit->stream ?? '');
                                    @endphp
                                    <select name="stream" id="stream" class="form-control select2 @error('stream') is-invalid @enderror">
                                        <option value="">Select Stream</option>
                                        @foreach ($streamList as $streamItem)
                                            <option value="{{ $streamItem }}"
                                                {{ ($selectedStream == $streamItem) ? 'selected' : '' }}>
                                                {{ $streamItem }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('stream')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- DOB --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Date of Birth <span class="text-danger">*</span> </label>
                                    @php
                                        $dobVal = old('date_of_birth', $edit->date_of_birth ?? '');
                                        $dobFormatted = '';
                                        if (!empty($dobVal)) {
                                            try {
                                                $dobFormatted = \Carbon\Carbon::parse($dobVal)->format('d-m-Y');
                                            } catch (\Exception $e) {
                                                $dobFormatted = $dobVal;
                                            }
                                        }
                                    @endphp
                                    <div class="input-group">
                                        <input id="date_of_birth" type="text"
                                            class="form-control flatpickr-dob @error('date_of_birth') is-invalid @enderror"
                                            name="date_of_birth" placeholder="DD-MM-YYYY" autocomplete="off"
                                            value="{{ $dobFormatted }}">
                                        <span class="input-group-text cursor-pointer" id="dob_picker_btn" title="Choose Date">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </span>
                                    </div>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- DOB In Words --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Date of Birth (In Words) </label>
                                    <input type="text" id="date_of_birth_words" class="form-control bg-white text-capitalize"
                                        placeholder="Date of Birth in Words" readonly style="background-color: #fff !important;">
                                </div>
                            </div>


                            {{--
                            @if (isset($edit) && $edit?->gdrivefolderurl)
                                <div class="col-md-6 col-sm-12 mb-3" id="google_drive_rename_folder_col">
                                    <div class="form-group">
                                        <label class="form-label"> </label>
                                        <a href="javascript:void(0)"
                                            class="btn rounded-pill btn-success waves-effect waves-light  google_drive_rename_folder">Google
                                            Drive Rename Folder </a>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12 mb-3" id="google_drive_rename_folder_url_col">
                                    <div class="form-group">
                                        <label class="form-label">Google Drive Folder URL </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="gdrivefolderurl"
                                                id="GoogleDriveFolderUrl"
                                                value="{{ isset($edit?->gdrivefolderurl) ? $edit?->gdrivefolderurl : old('gdrivefolderurl') }}"
                                                aria-describedby="GoogleDriveFolderUrlCopy" readonly />
                                            <input type="hidden" name="gdrivefoldername" id="GoogleDriveFolderName"
                                                value="{{ isset($edit?->gdrivefoldername) ? $edit?->gdrivefoldername : old('gdrivefoldername') }}" />
                                            <input type="hidden" name="gdrivefolderid" id="GoogleDriveFolderId"
                                                value="{{ isset($edit?->gdrivefolderid) ? $edit?->gdrivefolderid : old('gdrivefolderid') }}" />
                                            <span id="GoogleDriveFolderUrlCopy" class="input-group-text cursor-pointer"><i
                                                    class="icon-base bx bx-copy-alt"></i></span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            --}}
                        </div>
                    </div>
                    <hr>
                    <div class="text-left">
                        <h6 class="afx-sec" data-bs-toggle="collapse" href="#ContactDetails" role="button"
                            aria-expanded="true" aria-controls="ContactDetails">
                            <span class="afx-sec-icon"><i class="ti ti-address-book"></i></span>
                            <span class="afx-sec-title">Contact Details</span>
                            <i class="ti ti-chevron-down afx-sec-chev"></i>
                        </h6>
                    </div>
                    <div class="collapse show mt-2" id="ContactDetails">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Temporary Address <span class="text-danger">*</span></label>
                                    <textarea rows="3" id="temporary_address" type="text"
                                        class="form-control @error('temporary_address') is-invalid @enderror" name="temporary_address"
                                        placeholder="Enter Temporary Address">{{ isset($edit?->temporary_address) ? $edit?->temporary_address : old('temporary_address') }}</textarea>
                                    @error('temporary_address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span>Permanent Address <span class="text-danger">*</span></span>
                                        <span>
                                            <input type="checkbox" id="copy_address" onclick="copyTemporaryAddress()" />
                                            <label for="copy_address" class="mb-0 ms-1" style="font-weight: normal;">Same
                                                as Temporary</label>
                                        </span>
                                    </label>
                                    <textarea rows="3" id="permanent_address" type="text"
                                        class="form-control @error('permanent_address') is-invalid @enderror" name="permanent_address"
                                        placeholder="Enter Permanent Address">{{ isset($edit?->permanent_address) ? $edit?->permanent_address : old('permanent_address') }}</textarea>
                                    @error('permanent_address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Mobile No. <span class="text-danger">*</span> </label>
                                    <input id="mobile_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('mobile_no') is-invalid @enderror num_only"
                                        name="mobile_no" autocapitalize="off"
                                        value="{{ isset($edit?->mobile_no) ? $edit?->mobile_no : old('mobile_no') }}"
                                        placeholder="Enter Mobile No.">

                                    @error('mobile_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Parent's Mobile No. </label>
                                    <input id="parent_mobile_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('parent_mobile_no') is-invalid @enderror num_only"
                                        name="parent_mobile_no" autocapitalize="off"
                                        value="{{ isset($edit?->parent_mobile_no) ? $edit?->parent_mobile_no : old('parent_mobile_no') }}"
                                        placeholder="Enter Parent's Mobile No.">

                                    @error('parent_mobile_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Other Mobile No. </label>
                                    <input id="other_mobile_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('other_mobile_no') is-invalid @enderror num_only"
                                        name="other_mobile_no" autocapitalize="off"
                                        value="{{ isset($edit?->other_mobile_no) ? $edit?->other_mobile_no : old('other_mobile_no') }}"
                                        placeholder="Enter Other Mobile No.">
                                    @error('other_mobile_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Whatsapp No. </label>
                                    <input id="whatsapp_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('whatsapp_no') is-invalid @enderror num_only"
                                        name="whatsapp_no" autocapitalize="off"
                                        value="{{ isset($edit?->whatsapp_no) ? $edit?->whatsapp_no : old('whatsapp_no') }}"
                                        placeholder="Enter Whatsapp No.">

                                    @error('whatsapp_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                    <hr>
                    <div class="text-left">
                        <h6 class="afx-sec" data-bs-toggle="collapse" href="#Academy" role="button"
                            aria-expanded="true" aria-controls="Academy">
                            <span class="afx-sec-icon"><i class="ti ti-id-badge-2"></i></span>
                            <span class="afx-sec-title">Academic Details</span>
                            <i class="ti ti-chevron-down afx-sec-chev"></i>
                        </h6>
                    </div>

                    <div class="collapse show mt-2" id="Academy">
                        <div class="row">
                            <div class="col-md-4 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> APAAR ID </label>
                                    <input id="apaar_id_abc_id" type="text"
                                        class="form-control @error('apaar_id_abc_id') is-invalid @enderror "
                                        autocapitalize="off" name="apaar_id_abc_id"
                                        value="{{ isset($edit?->apaar_id_abc_id) ? $edit?->apaar_id_abc_id : old('apaar_id_abc_id') }}"
                                        placeholder="Enter APAAR ID">

                                    @error('apaar_id_abc_id')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label">UDISE</label>
                                    <input id="udise" type="text"
                                        class="form-control @error('udise') is-invalid @enderror" autocapitalize="off"
                                        name="udise" value="{{ isset($edit?->udise) ? $edit?->udise : old('udise') }}"
                                        placeholder="Enter UDISE">

                                    @error('udise')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-md-4 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> SPID </label>
                                    <input id="spid" type="text"
                                        class="form-control @error('spid') is-invalid @enderror " name="spid"
                                        autocapitalize="off"
                                        value="{{ isset($edit?->spid) ? $edit?->spid : old('spid') }}"
                                        placeholder="Enter SPID">

                                    @error('spid')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Aadhaar Card No. --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Aadhaar Card No. <span class="text-danger">*</span> </label>
                                    <input id="aadhar_card_no" type="text"
                                        class="form-control @error('aadhar_card_no') is-invalid @enderror num_only"
                                        minlength="14" maxlength="14" name="aadhar_card_no"
                                        value="{{ isset($edit?->aadhar_card_no) ? $edit?->aadhar_card_no : old('aadhar_card_no') }}"
                                        autocomplete="aadhar_card_no"
                                        placeholder="Enter Aadhaar Card No. (xxxx xxxx xxxx)" autocapitalize="off">

                                    @error('aadhar_card_no')
                                        <span class="invalid-feedback">
                                             <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- PEN No. --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> PEN No. </label>
                                    <input id="pen_no" type="text"
                                        class="form-control text-uppercase @error('pen_no') is-invalid @enderror"
                                        maxlength="20" name="pen_no"
                                        value="{{ isset($edit?->pen_no) ? $edit?->pen_no : old('pen_no') }}"
                                        placeholder="Enter PEN No." autocapitalize="characters">

                                    @error('pen_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>




                        </div>
                    </div>
                    <hr>

                    <div class="text-left">
                        <h6 class="afx-sec" data-bs-toggle="collapse" href="#bankDetails" role="button"
                            aria-expanded="true" aria-controls="bankDetails">
                            <span class="afx-sec-icon"><i class="ti ti-building-bank"></i></span>
                            <span class="afx-sec-title">Bank Details</span>
                            <i class="ti ti-chevron-down afx-sec-chev"></i>
                        </h6>
                    </div>

                    <div class="collapse show mt-2" id="bankDetails">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Bank Name </label>
                                    <input id="bank_name" type="text"
                                        class="form-control @error('bank_name') is-invalid @enderror"
                                        name="bank_name"
                                        value="{{ isset($edit?->bank_name) ? $edit?->bank_name : old('bank_name') }}"
                                        placeholder="Enter Bank Name" autocapitalize="off">
                                    @error('bank_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Bank Account Number </label>
                                    <input id="bank_account_no" type="text"
                                        class="form-control @error('bank_account_no') is-invalid @enderror num_only"
                                        name="bank_account_no"
                                        value="{{ isset($edit?->bank_account_no) ? $edit?->bank_account_no : old('bank_account_no') }}"
                                        placeholder="Enter Bank Account Number" autocapitalize="off">
                                    @error('bank_account_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>

                    {{-- Previous / Last School Details --}}
                    <div class="col-sm-12" id="previous_school_main">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">
                                <span class="afx-sec-icon"><i class="ti ti-building-community"></i></span>
                                Previous / Last School Details
                            </h6>
                            <div class="form-check form-check-inline mb-0">
                                <input type="checkbox" name="is_new_admission" class="form-check-input"
                                    id="is_new_admission" value="1"
                                    {{ old('is_new_admission', $edit->is_new_admission ?? 0) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_new_admission">
                                    New Admission
                                </label>
                            </div>
                        </div>

                        <div id="previous_school_section" class="row">
                            {{-- Last School Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Last School Name</label>
                                    <input type="text" id="last_school_name" name="last_school_name"
                                        class="form-control text-uppercase @error('last_school_name') is-invalid @enderror"
                                        value="{{ old('last_school_name', $edit->last_school_name ?? '') }}"
                                        placeholder="Enter Last School Name" autocomplete="off">
                                    @error('last_school_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Old GR No --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Old GR No</label>
                                    <input type="text" id="old_gr_no" name="old_gr_no"
                                        class="form-control @error('old_gr_no') is-invalid @enderror"
                                        value="{{ old('old_gr_no', $edit->old_gr_no ?? '') }}"
                                        placeholder="Enter Old GR No" autocomplete="off">
                                    @error('old_gr_no')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Passed Standard (from Standard Master) --}}
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Passed Standard</label>
                                    @php
                                        $selectedPassedStd = old('passed_standard', $edit->passed_standard ?? '');
                                        $passedStdList = \App\Models\Master\MasterStandard::where('status', 'active')
                                            ->orderBy('id')
                                            ->pluck('name')
                                            ->filter()
                                            ->values()
                                            ->all();
                                    @endphp
                                    <select name="passed_standard" id="passed_standard" class="form-control select2 @error('passed_standard') is-invalid @enderror">
                                        <option value="">Select Passed Std</option>
                                        @foreach ($passedStdList as $stdVal)
                                            <option value="{{ $stdVal }}"
                                                {{ ($selectedPassedStd == $stdVal) ? 'selected' : '' }}>
                                                {{ $stdVal }}
                                            </option>
                                        @endforeach
                                        @if(!empty($selectedPassedStd) && !in_array($selectedPassedStd, $passedStdList))
                                            <option value="{{ $selectedPassedStd }}" selected>{{ $selectedPassedStd }}</option>
                                        @endif
                                    </select>
                                    @error('passed_standard')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- LC No --}}
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">LC No</label>
                                    <input type="text" id="lc_no" name="lc_no"
                                        class="form-control @error('lc_no') is-invalid @enderror"
                                        value="{{ old('lc_no', $edit->lc_no ?? '') }}"
                                        placeholder="Enter LC No" autocomplete="off">
                                    @error('lc_no')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- LC Date --}}
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">LC Date</label>
                                    @php
                                        $lcDateVal = old('lc_date', $edit->lc_date ?? '');
                                        $lcDateFormatted = '';
                                        if (!empty($lcDateVal)) {
                                            try {
                                                $lcDateFormatted = \Carbon\Carbon::parse($lcDateVal)->format('d-m-Y');
                                            } catch (\Exception $e) {
                                                $lcDateFormatted = $lcDateVal;
                                            }
                                        }
                                    @endphp
                                    <div class="input-group">
                                        <input type="text" id="lc_date" name="lc_date"
                                            class="form-control flatpickr-date @error('lc_date') is-invalid @enderror"
                                            placeholder="DD-MM-YYYY" autocomplete="off"
                                            value="{{ $lcDateFormatted }}">
                                        <span class="input-group-text cursor-pointer" id="lc_date_picker_btn" title="Choose Date">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </span>
                                    </div>
                                    @error('lc_date')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Attendance --}}
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Attendance</label>
                                    <input type="text" id="attendance" name="attendance"
                                        class="form-control @error('attendance') is-invalid @enderror"
                                        value="{{ old('attendance', $edit->attendance ?? '') }}"
                                        placeholder="Enter Attendance" autocomplete="off">
                                    @error('attendance')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="afx-actions">
                    <a href="{{ route($route . '.index') }}" class="afx-btn afx-btn-light"><i class="ti ti-x"></i> Cancel</a>
                    <button type="submit" class="afx-btn afx-btn-primary">
                        <i class="ti ti-device-floppy"></i> {{ isset($edit) ? 'Update Student' : 'Save Admission' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection

@section('page_script_file')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <script src="{{ asset('admin/assets/vendor/libs/cleavejs/cleave.js') }}"></script>
@endsection

@section('page_leavel_script')

    <script>
        $(function() {
            $('#profile_pic_input').on('change', function() {
                var file = this.files && this.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#profilePicPreview').html($('<img>', { src: e.target.result, alt: 'Profile Picture' }));
                };
                reader.readAsDataURL(file);
            });
        });
    </script>

    <script>
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        function resetScrollToTop() {
            window.scrollTo(0, 0);
            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;
            $('.content-wrapper, .layout-page, .layout-container').scrollTop(0);
        }
        resetScrollToTop();

        $(function() {
            resetScrollToTop();
            setTimeout(resetScrollToTop, 50);
            setTimeout(resetScrollToTop, 200);

            if ($('#academic_year_id').length && $.fn.select2) {
                $('#academic_year_id').select2({ width: '100%', placeholder: 'Select Academic Year' });
                $('#academic_year_id').on('change', function() {
                    var opt = $(this).find('option:selected');
                    if (opt.val()) {
                        $('#afxYearName').text(opt.data('name') || opt.text());
                        $('#afxYearRange').text(opt.data('range') || '');
                    }
                });
            }

            // Bus Route Village select2 with custom tag/typing support
            if ($('#bus_route_village').length) {
                $('#bus_route_village').select2({
                    tags: true,
                    placeholder: 'Select or Type Bus Route Village',
                    allowClear: true
                });
            }

            [
                ['#cast', 'Select or Type Cast'],
                ['#house', 'Select or Type House'],
                ['#division', 'Select or Type Division'],
            ].forEach(function([selector, placeholder]) {
                var $el = $(selector);
                if (!$el.length) return;
                if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                $el.select2({
                    tags: true,
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%'
                });
            });



            // When Admission Std is selected, auto-select same in Current Std if Current Std is not yet set
            $('#admission_std').on('change', function() {
                let admStd = $(this).val();
                if (admStd) {
                    let currentStdVal = $('#current_std').val();
                    if (!currentStdVal || $('#current_std option[value="' + admStd + '"]').length > 0) {
                        $('#current_std').val(admStd).trigger('change.select2');
                    }
                }
            });

            // Cleave.js for formatting Aadhaar number
            var cleave = new Cleave('#aadhar_card_no', {
                delimiters: [' ', ' ', ' '],
                blocks: [4, 4, 4],
                numericOnly: true
            });

            // Real-time duplicate check on Aadhaar input
            let aadharCheckTimeout;
            $('#aadhar_card_no').on('input', function() {
                clearTimeout(aadharCheckTimeout);

                let cleanAadhar = $(this).val().replace(/\s/g, '');

                // Only check if Aadhaar is complete (12 digits)
                if (cleanAadhar.length === 12) {
                    aadharCheckTimeout = setTimeout(function() {
                        checkAadharDuplicate(cleanAadhar);
                    }, 500); // 500ms delay to avoid excessive API calls
                } else {
                    // Clear any previous error messages if Aadhaar is incomplete
                    $('#aadhar_card_no').removeClass('is-invalid');
                    $('#aadhar_card_no').siblings('.invalid-feedback.duplicate-error').remove();
                }
            });

            // Function to check Aadhaar duplication
            function checkAadharDuplicate(aadhar) {
                // Get edit ID if exists (to exclude current record from duplicate check)
                let editId = '{{ isset($edit) && $edit?->id ? $edit->id : '' }}';

                $.ajax({
                    url: "{{ route($route . '.check-aadhar-duplicate') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        aadhar_card_no: aadhar,
                        edit_id: editId
                    },
                    success: function(response) {
                        // Remove previous duplicate error message if exists
                        $('#aadhar_card_no').siblings('.invalid-feedback.duplicate-error').remove();

                        if (response.exists) {
                            // Show error
                            $('#aadhar_card_no').addClass('is-invalid');

                            // Add new error message with specific class
                            let errorMsg = `<span class="invalid-feedback duplicate-error" style="display: block;">
                                <strong>This Aadhaar card number is already registered!</strong>
                            </span>`;
                            $('#aadhar_card_no').after(errorMsg);

                            // Show toast notification
                            // toastr.error('This Aadhaar card number is already registered!');
                        } else {
                            // Remove duplicate error only (keep validation errors)
                            $('#aadhar_card_no').removeClass('is-invalid');

                            // Only remove invalid class if no other validation errors exist
                            if ($('#aadhar_card_no').siblings('.invalid-feedback').not(
                                    '.duplicate-error').length === 0) {
                                $('#aadhar_card_no').removeClass('is-invalid');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Aadhaar duplicate check error:', error);
                    }
                });
            }

            // Real-time duplicate check on Mobile input
            let mobileCheckTimeout;
            $('#mobile_no').on('input', function() {
                clearTimeout(mobileCheckTimeout);

                let mobile = $(this).val().trim();

                // Only check when length is 10 digits
                if (mobile.length === 10) {
                    mobileCheckTimeout = setTimeout(function() {
                        checkMobileDuplicate(mobile);
                    }, 500);
                } else {
                    $('#mobile_no').removeClass('is-invalid');
                    $('#mobile_no').siblings('.invalid-feedback.duplicate-error').remove();
                }
            });

            // Function to check Mobile duplication
            function checkMobileDuplicate(mobile) {
                let editId = '{{ isset($edit) && $edit?->id ? $edit->id : '' }}';

                $.ajax({
                    url: "{{ route($route . '.check-mobile-duplicate') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        mobile_no: mobile,
                        edit_id: editId
                    },
                    success: function(response) {
                        $('#mobile_no').siblings('.invalid-feedback.duplicate-error').remove();

                        if (response.exists) {
                            $('#mobile_no').addClass('is-invalid');

                            let errorMsg = `<span class="invalid-feedback duplicate-error" style="display: block;">
                    <strong>This mobile number is already registered!</strong>
                </span>`;
                            $('#mobile_no').after(errorMsg);

                            // toastr.error('This mobile number is already registered!');
                        } else {
                            $('#mobile_no').removeClass('is-invalid');

                            if ($('#mobile_no').siblings('.invalid-feedback').not('.duplicate-error')
                                .length === 0) {
                                $('#mobile_no').removeClass('is-invalid');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Mobile duplicate check error:', error);
                    }
                });
            }


            // Handle Autocomplete
            $("#aadhar_card_no").autocomplete({
                source: function(request, response) {
                    let cleanTerm = request.term.replace(/\s/g, '');

                    if (cleanTerm.length < 4) return;

                    // $.ajax({
                    //     url: "{{ route($route . '.aadhar-card-no-suggestion') }}",
                    //     data: {
                    //         term: cleanTerm
                    //     },
                    //     dataType: 'json',
                    //     success: function(data) {
                    //         response(data);
                    //     },
                    //     error: function(xhr, status, error) {
                    //         console.error('Suggestion AJAX error:', error);
                    //     }
                    // });
                },
                minLength: 4,
                select: function(event, ui) {
                    event.preventDefault();

                    $('#aadhar_card_no').val(ui.item.value).trigger('input');

                    $.ajax({
                        url: "{{ route($route . '.aadhar-card-no-to-data') }}",
                        data: {
                            aadhar: ui.item.value.replace(/\s/g, '')
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.redirect === true && data.redirect_url) {
                                let alertTitle = 'Info';
                                if (data.message.includes(
                                        'already have a course registration')) {
                                    alertTitle = 'Course Already Registered';
                                } else if (data.message.includes(
                                        'Admission form complete but course registration not done yet'
                                    )) {
                                    alertTitle = 'Course Registration Pending';
                                }

                                Swal.fire({
                                    title: alertTitle,
                                    html: data.message,
                                    icon: 'info',
                                    confirmButtonText: 'Proceed',
                                    showCancelButton: true,
                                    cancelButtonText: 'Cancel',
                                    buttonsStyling: false,
                                    customClass: {
                                        popup: 'swal-wide',
                                        confirmButton: 'btn btn-info',
                                        cancelButton: 'btn btn-secondary'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = data.redirect_url;
                                    }
                                });
                            }

                            if (data.user) {
                                $('#first_name').val(data.user.first_name || '');
                                $('#last_name').val(data.user.last_name || '');
                                $('#father_name').val(data.user.father_name || '');
                                $('#mother_name').val(data.user.mother_name || '');
                                prevSurname = (data.user.first_name || '').trim();
                                $('#mobile_no').val(data.user.mobile_no || '');
                                $('#date_of_birth').val(data.user.date_of_birth || '');

                                $("input[name='gender'][value='" + (data.user.gender ||
                                    '') + "']").prop("checked", true);
                                $("input[name='category'][value='" + (data.user.category ||
                                    '') + "']").prop("checked", true);

                                $('#temporary_address').val(data.user.temporary_address ||
                                    '');
                                $('#permanent_address').val(data.user.permanent_address ||
                                    '');
                                $('#parent_mobile_no').val(data.user.parent_mobile_no ||
                                    '');
                                $('#other_mobile_no').val(data.user.other_mobile_no || '');
                                $('#whatsapp_no').val(data.user.whatsapp_no || '');
                                $('#cast').val(data.user.cast || '').trigger('change');
                                $('#occupation').val(data.user.occupation || '');
                                $('#gr_no').val(data.user.gr_no || '');
                                $('#biometric_id').val(data.user.biometric_id || '');
                                $('#bus_route_village').val(data.user.bus_route_village || '').trigger('change');
                                $('#admission_date').val(data.user.admission_date || '');
                                $('#admission_std').val(data.user.admission_std_id || '').trigger('change');
                                $('#current_std').val(data.user.current_std_id || '').trigger('change');
                                $('#division').val(data.user.division || '').trigger('change');
                                $('#spid').val(data.user.spid || '');
                                $('#apaar_id_abc_id').val(data.user.apaar_id_abc_id || '');
                                $('#bank_name').val(data.user.bank_name || '');
                                $('#bank_account_no').val(data.user.bank_account_no || '');

                                $('#education_details_table tbody').empty();
                                if (Array.isArray(data.education_details)) {
                                    data.education_details.forEach(function(item) {
                                        $('#education_details_table tbody').append(`
                                            <tr>
                                                <td>
                                                    <input type="text" name="education[]" class="form-control" placeholder="Education" value="${item.education || ''}" readonly>
                                                    <input type="hidden" name="education_id[]" value="${item.id || ''}">
                                                </td>
                                                <td><input type="text" name="percentage_cgpa[]" class="form-control num_only" value="${item.percentage_cgpa || ''}"></td>
                                                <td><input type="text" name="seat_no_nrollment_no[]" class="form-control" value="${item.seat_no_nrollment_no || ''}"></td>
                                                <td><input type="text" name="board_university[]" class="form-control" value="${item.board_university || ''}"></td>
                                                <td><input type="text" name="passing_year[]" class="form-control" value="${item.passing_year || ''}"></td>
                                                <td><input type="text" name="school_name_college_name[]" class="form-control" value="${item.school_name_college_name || ''}"></td>
                                                <td>
                                                    <button type="button" class="btn btn-danger remove_row">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `);
                                    });
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Data fetch AJAX error:', error);
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#mobile_no').on('keyup', function() {
                var mobile_no = $(this).val();
                $('#whatsapp_no').val(mobile_no);
            });

            // Event delegation for dynamically added remove buttons
            $(document).on('click', '.remove_row', function() {
                var education_id = $(this).data('education_id');
                var $row = $(this).closest('tr');

                if (education_id) {
                    $.ajax({
                        url: "{{ route($route . '.education-detail-delete') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: education_id,
                        },
                        success: function(response) {
                            console.log(response);
                            $row.remove();
                            toastr.success('Education detail deleted successfully');
                        },
                        error: function(error) {
                            console.log(error);
                            toastr.error('Error deleting education detail');
                        }
                    });
                } else {
                    $row.remove();
                }
            });

        });

        function showMessage(message, type) {
            toastr[type](message);
        }
    </script>

    <script>
        function copyTemporaryAddress() {
            const tempAddress = document.getElementById('temporary_address');
            const permAddress = document.getElementById('permanent_address');
            const checkbox = document.getElementById('copy_address');

            if (checkbox.checked) {
                permAddress.value = tempAddress.value;
                permAddress.setAttribute('readonly', true);
            } else {
                permAddress.removeAttribute('readonly');
            }

            // Optional: live update if temp address changes while checkbox is checked
            tempAddress.addEventListener('input', function() {
                if (checkbox.checked) {
                    permAddress.value = this.value;
                }
            });
        }
    </script>

    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        function convertDateToWords(dateInput) {
            if (!dateInput) return '';

            let day, month, year;
            const str = String(dateInput).trim();

            // Match DD-MM-YYYY or DD/MM/YYYY or DD.MM.YYYY
            if (/^\d{1,2}[-\/\.]\d{1,2}[-\/\.]\d{4}$/.test(str)) {
                const parts = str.split(/[-\/\.]/);
                day = parseInt(parts[0], 10);
                month = parseInt(parts[1], 10);
                year = parseInt(parts[2], 10);
            }
            // Match YYYY-MM-DD or YYYY/MM/DD or YYYY.MM.DD
            else if (/^\d{4}[-\/\.]\d{1,2}[-\/\.]\d{1,2}$/.test(str)) {
                const parts = str.split(/[-\/\.]/);
                year = parseInt(parts[0], 10);
                month = parseInt(parts[1], 10);
                day = parseInt(parts[2], 10);
            } else {
                const d = new Date(str);
                if (isNaN(d.getTime())) return '';
                day = d.getDate();
                month = d.getMonth() + 1;
                year = d.getFullYear();
            }

            if (!day || !month || !year || month < 1 || month > 12 || day < 1 || day > 31 || year < 1900 || year > 2100) {
                return '';
            }

            const dayWords = [
                "", "First", "Second", "Third", "Fourth", "Fifth", "Sixth", "Seventh", "Eighth", "Ninth", "Tenth",
                "Eleventh", "Twelfth", "Thirteenth", "Fourteenth", "Fifteenth", "Sixteenth", "Seventeenth", "Eighteenth", "Nineteenth", "Twentieth",
                "Twenty First", "Twenty Second", "Twenty Third", "Twenty Fourth", "Twenty Fifth", "Twenty Sixth", "Twenty Seventh", "Twenty Eighth", "Twenty Ninth", "Thirtieth", "Thirty First"
            ];

            const months = [
                "", "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            function numToWords(n) {
                const ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                    "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
                const tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

                if (n === 0) return "";
                if (n < 20) return ones[n];
                if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 !== 0 ? " " + ones[n % 10] : "");
                if (n < 1000) return ones[Math.floor(n / 100)] + " Hundred" + (n % 100 !== 0 ? " " + numToWords(n % 100) : "");
                if (n < 100000) return numToWords(Math.floor(n / 1000)) + " Thousand" + (n % 1000 !== 0 ? " " + numToWords(n % 1000) : "");
                return n.toString();
            }

            const dayText = dayWords[day] || numToWords(day);
            const monthText = months[month] || "";
            const yearText = numToWords(year);

            return (dayText + " " + monthText + " " + yearText).trim();
        }

        function updateDobInWords(val) {
            const words = convertDateToWords(val);
            $('#date_of_birth_words').val(words);
        }

        $(document).ready(function() {
            let fpDOB = null;
            let fpAdmDate = null;
            let fpLC = null;
            if (typeof flatpickr !== 'undefined') {
                fpDOB = flatpickr("#date_of_birth", {
                    dateFormat: "d-m-Y",
                    allowInput: true,
                    maxDate: "today",
                    onChange: function(selectedDates, dateStr, instance) {
                        updateDobInWords(dateStr);
                    }
                });

                $('#dob_picker_btn').on('click', function() {
                    if (fpDOB) {
                        fpDOB.open();
                    }
                });

                fpAdmDate = flatpickr("#admission_date", {
                    dateFormat: "d-m-Y",
                    allowInput: true
                });

                $('#adm_date_picker_btn').on('click', function() {
                    if (fpAdmDate) {
                        fpAdmDate.open();
                    }
                });

                fpLC = flatpickr("#lc_date", {
                    dateFormat: "d-m-Y",
                    allowInput: true,
                    maxDate: "today"
                });

                $('#lc_date_picker_btn').on('click', function() {
                    if (fpLC) {
                        fpLC.open();
                    }
                });
            }

            $(document).on('input keyup change blur', '#date_of_birth', function() {
                updateDobInWords($(this).val());
            });

            // Initial convert on load
            updateDobInWords($('#date_of_birth').val());
        });

        function updateFullNames() {
            var firstNameEl = document.getElementById('first_name');
            var fatherNameEl = document.getElementById('father_name');
            var motherNameEl = document.getElementById('mother_name');
            var fatherFullNameEl = document.getElementById('father_full_name');
            var motherFullNameEl = document.getElementById('mother_full_name');

            var surname = (firstNameEl ? firstNameEl.value : '').trim();
            var fatherName = (fatherNameEl ? fatherNameEl.value : '').trim();
            var motherName = (motherNameEl ? motherNameEl.value : '').trim();

            var cleanFather = fatherName;
            if (surname && fatherName) {
                var regex = new RegExp('^' + surname.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '\\s+', 'i');
                cleanFather = fatherName.replace(regex, '');
            }
            var fatherFullName = [surname, cleanFather].filter(Boolean).join(' ');

            var cleanMother = motherName;
            if (surname && motherName) {
                var regex = new RegExp('^' + surname.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '\\s+', 'i');
                cleanMother = motherName.replace(regex, '');
            }
            var motherFullName = [surname, cleanMother].filter(Boolean).join(' ');

            if (fatherFullNameEl) {
                fatherFullNameEl.value = fatherFullName.toUpperCase();
            }
            if (motherFullNameEl) {
                motherFullNameEl.value = motherFullName.toUpperCase();
            }
        }
        window.updateFullNames = updateFullNames;

        $(document).on('input keyup change blur paste', '#first_name, #father_name, #mother_name', function() {
            updateFullNames();
        });

        document.addEventListener('DOMContentLoaded', function() {
            updateFullNames();
        });
        window.addEventListener('load', function() {
            updateFullNames();
        });
        setTimeout(updateFullNames, 300);
        setTimeout(updateFullNames, 1000);
    </script>

    {{--
    @if (isset($edit))
        <script>
            // $('#google_drive_rename_folder_url_col').hide();
            $('.google_drive_rename_folder').on('click', function() {
                var first_name = $('#first_name').val();
                var father_name = $('#father_name').val();
                var last_name = $('#last_name').val();

                let isValid = true;

                if (!first_name) {
                    isValid = false;
                    showMessage("First Name is required", "error");
                }

                if (!father_name) {
                    isValid = false;
                    showMessage("Father Name is required", "error");
                }

                if (!last_name) {
                    isValid = false;
                    showMessage("Last Name is required", "error");
                }

                var id = '{{ $edit?->id }}';
                var folderName = [first_name, last_name, father_name].map(function(v) {
                    return $.trim(v || '');
                }).filter(Boolean).join(' ');
                var OldfolderName = '{{ $edit?->gdrivefoldername }}';
                var OldfolderId = '{{ $edit?->gdrivefolderid }}';
                if (isValid) {
                    $.ajax({
                        url: "{{ route($route . '.google-drive-rename-folder') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            foldername: folderName,
                            OldfolderName: OldfolderName,
                            OldfolderId: OldfolderId,
                        },
                        success: function(response) {
                            console.log(response.folder_id);
                            console.log(response.folder_url);
                            $('#GoogleDriveFolderUrl').val(response.folder_url);
                            $('#GoogleDriveFolderId').val(response.folder_id);
                            $('#GoogleDriveFolderName').val(response.folder_name);
                            $('#google_drive_rename_folder_col').hide();
                            $('#google_drive_rename_folder_url_col').show();
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });

            $('#GoogleDriveFolderUrlCopy').on('click', function() {
                var url = $('#GoogleDriveFolderUrl').val();

                if (url) {
                    navigator.clipboard.writeText(url).then(function() {
                        console.log('URL copied to clipboard:', url);

                        // Optional: Feedback (e.g., temporary text/icon change)
                        $('#GoogleDriveFolderUrlCopy i').removeClass('bx-copy-alt').addClass('bx-check');
                        setTimeout(function() {
                            $('#GoogleDriveFolderUrlCopy i').removeClass('bx-check').addClass(
                                'bx-copy-alt');
                        }, 1500);
                    }).catch(function(err) {
                        console.error('Failed to copy URL:', err);
                    });
                }
            });
        </script>
    @endif
    --}}
@endsection
