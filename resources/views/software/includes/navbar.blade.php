@php
    $sidebar_active = Route::current()->getName();
    $expand = explode('.', $sidebar_active);
    $sidebar_active = count($expand) > 0 ? $expand[0] : '';
    $loginUser = Auth::user();
    $navBarProfilePhoto = asset('admin/assets/images/favicon/favicon.ico');
    $navBarProfilePhoto = asset('admin/assets/img/default/profile.png');
@endphp
<nav class="layout-navbar container-fuild navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Executive School Brand Card (For Principal & Management) -->
        @php
            $activeSchool = \App\Services\SchoolDatabaseManager::getActiveSchool();
        @endphp
        <div class="navbar-nav align-items-center me-auto d-none d-md-flex">
            <div class="d-flex align-items-center gap-3 px-3 py-1 rounded-3 shadow-xs" 
                style="background: linear-gradient(135deg, rgba(25, 14, 128, 0.05) 0%, rgba(25, 14, 128, 0.01) 100%); border: 1px solid rgba(25, 14, 128, 0.14);">
                <div class="d-flex align-items-center justify-content-center rounded-circle" 
                    style="width: 36px; height: 36px; background: #190e80; color: #ffffff; box-shadow: 0 3px 8px rgba(25, 14, 128, 0.28);">
                    <i class="ti ti-school" style="font-size: 1.25rem;"></i>
                </div>
                <div class="d-flex align-items-center">
                    <span class="fw-bold" style="color: #190e80; font-size: 1.1rem; letter-spacing: 0.5px;" title="{{ $activeSchool['name'] ?? 'Ultra School' }}">
                        {{ $activeSchool['short_name'] ?? 'CAMPUS' }}
                    </span>
                </div>
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto gap-3">
            <!-- Mobile Badge -->
            <li class="nav-item d-flex d-md-none align-items-center">
                <span class="badge text-white px-2 py-1" style="background: #190e80; font-size: 0.75rem;">
                    {{ $activeSchool['short_name'] ?? 'CAMPUS' }}
                </span>
            </li>

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ $navBarProfilePhoto }}" alt class="h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" style="cursor: default;">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ $navBarProfilePhoto }}" alt class="h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ $loginUser?->name ?? '' }}</span>
                                    <small
                                        class="text-muted">{{ \App\Helpers\Helper::getLoginUserRole() ?? '' }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    {{-- <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-profile-user.html">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li> --}}
                    {{-- <li>
                        <a class="dropdown-item" href="pages-account-settings-account.html">
                            <i class="ti ti-settings me-2 ti-sm"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-account-settings-billing.html">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 ti ti-credit-card me-2 ti-sm"></i>
                                <span class="flex-grow-1 align-middle">Billing</span>
                                <span
                                    class="flex-shrink-0 badge badge-center rounded-pill bg-label-danger w-px-20 h-px-20">2</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-faq.html">
                            <i class="ti ti-help me-2 ti-sm"></i>
                            <span class="align-middle">FAQ</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-pricing.html">
                            <i class="ti ti-currency-dollar me-2 ti-sm"></i>
                            <span class="align-middle">Pricing</span>
                        </a>
                    </li> --}}
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                            <i class="ti ti-logout me-2 ti-sm"></i>
                            <span class="align-middle">Log Out</span>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>

    {{-- <!-- Search Small Screens -->
    <div class="navbar-search-wrapper search-input-wrapper d-none">
        <input type="text" class="form-control search-input container-fuild border-0" placeholder="Search..."
            aria-label="Search..." />
        <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
    </div> --}}
</nav>
