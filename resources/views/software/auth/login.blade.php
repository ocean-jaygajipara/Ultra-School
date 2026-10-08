@extends('software.layout.app')

@section('title', 'Admin Login')

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/css/pages/page-auth.css') }}" />

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <style>
        :root {
            --brand-royal-blue: #190e80;
            --brand-royal-blue-hover: #120963;
            --brand-royal-blue-light: rgba(25, 14, 128, 0.08);
        }
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 8px);
            padding: 0.45rem 0.85rem;
            border: 1px solid #dbdade;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease-in-out;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--brand-royal-blue) !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 14, 128, 0.15);
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b465c;
            padding-left: 0;
            font-size: 0.9375rem;
            line-height: 1.5;
            font-weight: 500;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 0;
            right: 10px;
        }
        .select2-dropdown {
            border: 1px solid #dbdade;
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(25, 14, 128, 0.14);
            z-index: 9999;
            overflow: hidden;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #dbdade;
            border-radius: 0.375rem;
            padding: 0.4rem 0.75rem;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--brand-royal-blue);
            outline: none;
            box-shadow: 0 0 0 0.15rem rgba(25, 14, 128, 0.15);
        }
        .select2-results__option {
            padding: 8px 14px;
            font-size: 0.92rem;
            transition: background-color 0.15s ease;
        }
        .select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--brand-royal-blue) !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: var(--brand-royal-blue-light);
            color: var(--brand-royal-blue);
            font-weight: 600;
        }
        .btn-brand-primary {
            background-color: var(--brand-royal-blue) !important;
            border-color: var(--brand-royal-blue) !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(25, 14, 128, 0.25);
            transition: all 0.2s ease;
        }
        .btn-brand-primary:hover {
            background-color: var(--brand-royal-blue-hover) !important;
            border-color: var(--brand-royal-blue-hover) !important;
            box-shadow: 0 6px 16px rgba(25, 14, 128, 0.35);
        }
    </style>
@endsection

@section('content')
    <!-- Content -->
    <div class="authentication-wrapper authentication-cover position-relative"
        style="min-height: 100vh; background-image: url('{{ asset('admin/assets/img/illustrations/vector-privacy-protection-and-software-for-development.jpg') }}'); background-size: cover; background-position: center;">

        <!-- Dark Overlay -->
        <div style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1;"></div>

        <div class="authentication-inner row" style="position:relative; z-index:2;">
            <!-- Login -->
            <div class="d-flex col-12 col-lg-5 align-items-center justify-content-center p-sm-5 p-4">
                <div class="w-100"
                    style="max-width: 420px; background: rgb(255 255 255); border-radius: 15px; padding: 30px;"
                    data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200">

                    <!-- Logo -->
                    <div class="app-brand mb-4 text-center">
                        <a href="{{ route('login') }}" class="app-brand-link gap-2">
                            <img class="w-100" src="{{ asset('admin/assets/images/logo-light.png') }}" />
                        </a>
                    </div>

                    <!-- Login Form -->
                    <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="post">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger p-2 mb-3" style="font-size: 0.85rem;">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3 position-relative">
                            <label for="school_key" class="form-label fw-bold">Select School <span class="text-danger">*</span></label>
                            <select name="school_key" id="school_key" class="select2 form-select @error('school_key') is-invalid @enderror" data-placeholder="-- Select School --" required>
                                <option value="">-- Select School --</option>
                                @foreach ($schools as $sCode => $sInfo)
                                    <option value="{{ $sCode }}" {{ (old('school_key', $selectedSchool) === $sCode) ? 'selected' : '' }}>
                                        🏫 {{ $sInfo['name'] }} ({{ $sInfo['short_name'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('school_key')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email"
                                autofocus value="{{ old('email') }}" />
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="••••••••••" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>

                        <button class="btn btn-brand-primary d-grid w-100 py-2">Sign in</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('software.privacy-policy') }}" target="_blank" class="">
                            Privacy Policy
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Login -->
        </div>
    </div>
    <!-- / Content -->
@endsection

@section('page_leavel_script')
    <script src="{{ asset('admin/assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('admin/assets/js/pages-auth.js') }}"></script>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        $(document).ready(function () {
            if ($.fn.select2) {
                $('#school_key').select2({
                    placeholder: 'Select School',
                    width: '100%',
                    dropdownParent: $('#school_key').closest('.mb-3')
                });
            }
        });
    </script>
@endsection