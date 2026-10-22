@php
    $displayTitle = $page_title
        ?? ($modules['title'] ?? null)
        ?? (isset($breadcrumbArray) && is_array($breadcrumbArray) && count($breadcrumbArray)
            ? (end($breadcrumbArray)['title'] ?? null)
            : null);
    $showPageTitle = $show_page_title ?? true;
@endphp

<div class="us-page-head w-100">
    @if ($showPageTitle && !empty($displayTitle))
        <h4 class="us-page-title mb-2">{{ $displayTitle }}</h4>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <!-- Basic Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('software.dashboard') }}">Dashboard</a>
                </li>
                @isset($breadcrumbArray)
                    @foreach ($breadcrumbArray as $item)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"
                            @if ($loop->last) aria-current="page" @endif>
                            @if (!$loop->last && isset($item['url']) && !empty($item['url']))
                                <a href="{{ $item['url'] }}">{{ $item['title'] ?? '' }}</a>
                            @else
                                {{ $item['title'] ?? '' }}
                            @endif
                        </li>
                    @endforeach
                @endisset
            </ol>
        </nav>
        <!-- Basic Breadcrumb -->

        <div class="ms-auto d-flex flex-wrap align-items-center gap-1">
            @if (isset($show_add_btn) && $show_add_btn && isset($route))
                <a class="btn btn-primary waves-effect waves-light text-white btn-sm mt-lg-0"
                    href="{{ route($route . '.create') }}">
                    <i class="menu-icon ti ti-plus"></i>
                    <span class="d-none d-lg-inline"> Add {{ $displayTitle ?? '' }}</span>
                </a>
            @endif

            @if (isset($show_api_attendance) && $show_api_attendance && isset($route))
                <a class="btn btn-success btn-sm waves-effect waves-light text-white " href="javascript:void(0)"
                    id="add-attendance-btn">
                    <i class="menu-icon ti ti-plus"></i> Fetch Attendance Logs
                </a>
            @endif

            @if (isset($show_filter_btn) && $show_filter_btn && isset($route))
                <button type="button" title="Search" id="show_filter"
                    class="btn btn-outline-primary btn-dm waves-effect waves-light btn-icon ms-75 me-75 "><i
                        class="ti ti-filter"></i></button>
            @endif

            @if (isset($show_report_settings_btn) && $show_report_settings_btn)
                <button type="button" class="btn btn-sm btn-primary waves-effect waves-light text-white"
                    id="reportSettingsBtn" data-bs-toggle="modal" data-bs-target="#classReportSettingsModal"
                    title="Report Settings">
                    <i class="ti ti-adjustments-horizontal me-1"></i>
                    <span class="d-none d-md-inline">Report Settings</span>
                </button>
            @endif

            @if (isset($show_export_btns) && $show_export_btns && isset($route))

                {{-- Dropdown --}}

                <button class="btn btn-sm btn-info btn-sm waves-effect waves-light text-white    " type="button"
                    id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px;">
                    <i class="ti ti-settings"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuButton">
                    @if (isset($show_excal_btn) && $show_excal_btn && isset($route))

                    {{-- <li>
                        <a class="dropdown-item" href="{{ route($route . '.export.excel') }}">
                            <i class="fa fa-file-excel me-2 text-success"></i>Export Excel
                        </a>
                    </li> --}}
                    <li>
                        <a class="dropdown-item" href="#" id="exportExcelBtn">
                            <i class="fa fa-file-excel me-2 text-success"></i>Export Excel
                        </a>
                    </li>

                    @endif

                    @if (isset($show_print_btn) && $show_print_btn && isset($route))
                        <li>
                            <a href="#" class="dropdown-item" id="print_btn">
                                <i class="fa fa-print me-2 text-primary"></i>Print
                            </a>
                        </li>
                    @endif

                    @if (isset($show_student_test_report_btn) && $show_student_test_report_btn)
                        <li>
                            <a href="#" class="dropdown-item" id="print_student_test_report_btn">
                                <i class="fa fa-print me-2 text-info"></i>Print Student Test Report
                            </a>
                        </li>
                    @endif

                    @if (isset($show_classwise_attendance_btn) && $show_classwise_attendance_btn)
                        <li>
                            <a href="#" class="dropdown-item" id="classwiseAttendanceBtn">
                                <i class="fa fa-users me-2 text-warning"></i>Classwise Attendance
                            </a>
                        </li>
                    @endif
                </ul>
            @endif


            @if (isset($show_back_btn) && $show_back_btn && isset($route))
                <a class="btn btn-primary btn-sm waves-effect waves-light text-white mt-2 mt-lg-0 d-flex align-items-center"
                    href="{{ route($route . '.index') }}">
                    <i class="menu-icon ti ti-chevrons-left"></i>
                    <span class="d-none d-lg-inline ms-2">Back</span>
                </a>
            @endif
        </div>
    </div>
</div>

<style>
    .us-page-title {
        color: #1f2580;
        font-weight: 800;
        font-size: 1.35rem;
        margin: 0;
        line-height: 1.3;
    }
    .us-page-head .breadcrumb {
        background: transparent;
        padding: 0;
    }
    .us-page-head .breadcrumb-item + .breadcrumb-item::before {
        color: #8b90b0;
    }
    .us-page-head .breadcrumb-item a {
        color: #5f6488;
        text-decoration: none;
    }
    .us-page-head .breadcrumb-item a:hover {
        color: #1f2580;
    }
    .us-page-head .breadcrumb-item.active {
        color: #1f2580;
        font-weight: 600;
    }
</style>
