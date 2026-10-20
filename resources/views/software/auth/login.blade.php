@extends('software.layout.app')

@section('title', 'Login')

@php
    $schoolIcons = [
        'ues' => 'ti-language',
        'ups' => 'ti-backpack',
        'uv'  => 'ti-building-community',
        'us'  => 'ti-certificate',
    ];
    $currentSchool = old('school_key', $selectedSchool);
@endphp

@section('page_style_file')
    <style>
        :root {
            --lg-navy: #1f2580;
            --lg-navy-2: #2e3a9e;
            --lg-red: #e1141c;
            --lg-sky: #bfe3f7;
            --lg-sky-soft: #eef7fd;
            --lg-gold: #f5b400;
            --lg-line: #e1e4f2;
            --lg-muted: #5f6488;
        }

        body { background: #f4f6fc; }

        .lg-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(circle at 10% 10%, rgba(191, 227, 247, .6), transparent 35%),
                radial-gradient(circle at 90% 90%, rgba(245, 180, 0, .18), transparent 35%),
                #f4f6fc;
        }

        .lg-card {
            width: 100%;
            max-width: 1040px;
            background: #fff;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 30px 70px -25px rgba(31, 37, 128, .35);
            display: grid;
            grid-template-columns: 1fr 1.1fr;
        }

        /* Brand side */
        .lg-brand {
            position: relative;
            color: #fff;
            padding: 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                radial-gradient(circle at 85% 10%, rgba(245, 180, 0, .38), transparent 40%),
                radial-gradient(circle at 0% 100%, rgba(191, 227, 247, .3), transparent 45%),
                linear-gradient(160deg, #171c6b 0%, var(--lg-navy-2) 100%);
        }
        .lg-logo {
            width: 200px; max-width: 70%;
            background: #fff; border-radius: 24px; padding: 14px;
            box-shadow: 0 0 0 5px rgba(245, 180, 0, .65), 0 20px 40px -15px rgba(0, 0, 0, .5);
            margin: 0 auto 26px; display: block;
        }
        .lg-brand h1 { color: #fff; font-size: 1.9rem; font-weight: 800; margin: 0; text-align: center; letter-spacing: -.5px; }
        .lg-brand .lg-place {
            text-align: center; color: var(--lg-gold); font-weight: 700;
            letter-spacing: .3em; text-transform: uppercase; font-size: .8rem; margin-top: 4px;
        }
        .lg-motto {
            margin: 22px auto 0; max-width: 340px; text-align: center;
            background: rgba(255, 255, 255, .1); border: 1px solid rgba(191, 227, 247, .35);
            border-radius: 14px; padding: 12px 16px; color: #e6f2fc; font-size: .95rem;
        }
        .lg-points { list-style: none; padding: 0; margin: 28px 0 0; display: grid; gap: 10px; }
        .lg-points li { display: flex; align-items: center; gap: 10px; color: #d6e6f7; font-size: .9rem; }
        .lg-points i {
            width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(245, 180, 0, .18); color: var(--lg-gold); font-size: 1rem;
        }

        /* Form side */
        .lg-form { padding: 44px 46px; }
        .lg-form h2 { color: var(--lg-navy); font-weight: 800; font-size: 1.6rem; margin: 0; }
        .lg-form .lg-sub { color: var(--lg-muted); margin: 6px 0 26px; }

        .lg-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
            color: var(--lg-muted); margin-bottom: 10px;
        }

        .lg-schools { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 22px; }
        .lg-school input { position: absolute; opacity: 0; pointer-events: none; }
        .lg-school label {
            display: flex; align-items: center; gap: 10px; cursor: pointer;
            border: 1.5px solid var(--lg-line); border-radius: 14px; padding: 11px 12px;
            background: #fafbff; transition: all .18s; height: 100%; position: relative;
        }
        .lg-school label:hover { border-color: var(--lg-sky); background: var(--lg-sky-soft); }
        .lg-school .lg-ico {
            width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: #e8eafb; color: var(--lg-navy); font-size: 1.15rem; transition: all .18s;
        }
        .lg-school .lg-code { display: block; color: var(--lg-navy); font-weight: 800; font-size: .95rem; line-height: 1.1; }
        .lg-school .lg-name { display: block; color: var(--lg-muted); font-size: .74rem; line-height: 1.25; }
        .lg-school .lg-check {
            position: absolute; top: 8px; right: 8px; width: 20px; height: 20px; border-radius: 50%;
            background: var(--lg-navy); color: #fff; font-size: .75rem;
            display: none; align-items: center; justify-content: center;
        }
        .lg-school input:checked + label {
            border-color: var(--lg-navy); background: #fff;
            box-shadow: 0 0 0 4px rgba(191, 227, 247, .7);
        }
        .lg-school input:checked + label .lg-ico { background: var(--lg-navy); color: var(--lg-gold); }
        .lg-school input:checked + label .lg-check { display: inline-flex; }
        .lg-school input:focus-visible + label { outline: 2px solid var(--lg-navy); outline-offset: 2px; }

        .lg-field { position: relative; margin-bottom: 18px; }
        .lg-field > i.lg-lead {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: var(--lg-navy-2); font-size: 1.15rem; pointer-events: none;
        }
        .lg-input {
            width: 100%; height: 50px; border-radius: 14px;
            border: 1.5px solid var(--lg-line); background: #fafbff;
            padding: 0 46px 0 46px; font-size: .95rem; color: var(--lg-navy);
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .lg-input::placeholder { color: #a5a9c7; }
        .lg-input:focus { outline: none; border-color: var(--lg-navy); background: #fff; box-shadow: 0 0 0 4px rgba(191, 227, 247, .7); }
        .lg-input.is-invalid { border-color: var(--lg-red); }
        .lg-eye {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 36px; height: 36px; border: 0; border-radius: 10px; background: transparent;
            color: var(--lg-muted); font-size: 1.15rem; cursor: pointer;
        }
        .lg-eye:hover { background: var(--lg-sky-soft); color: var(--lg-navy); }
        .lg-caps {
            display: none; margin-top: 6px; font-size: .78rem; color: #a86b00;
            background: #fff6dc; border: 1px solid #ffe39a; border-radius: 8px; padding: 4px 10px;
        }

        .lg-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .lg-remember { display: flex; align-items: center; gap: 8px; color: var(--lg-muted); font-size: .88rem; cursor: pointer; margin: 0; }
        .lg-remember input { width: 18px; height: 18px; accent-color: var(--lg-navy); cursor: pointer; }

        .lg-btn {
            width: 100%; height: 52px; border: 0; border-radius: 14px;
            background: linear-gradient(135deg, var(--lg-navy), var(--lg-navy-2));
            color: #fff; font-weight: 700; font-size: 1rem; letter-spacing: .3px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 12px 24px -10px rgba(31, 37, 128, .7);
            transition: transform .15s, box-shadow .15s;
        }
        .lg-btn:hover { transform: translateY(-1px); box-shadow: 0 16px 28px -10px rgba(31, 37, 128, .8); }
        .lg-btn:disabled { opacity: .8; transform: none; cursor: wait; }

        .lg-alert {
            display: flex; gap: 10px; align-items: flex-start;
            background: #fff1f1; border: 1px solid #f7c3c5; color: #b80f16;
            border-radius: 12px; padding: 10px 14px; margin-bottom: 18px; font-size: .88rem;
        }
        .lg-alert-success { background: #eefbf3; border-color: #bbf0d0; color: #157347; }
        .lg-alert-warning { background: #fff9e6; border-color: #ffe69c; color: #996500; }
        .lg-alert-info    { background: #eef7fd; border-color: #bfe3f7; color: #17548d; }
        .lg-alert i { font-size: 1.15rem; margin-top: 1px; flex-shrink: 0; }
        .lg-alert ul { margin: 0; padding-left: 16px; }

        .lg-error { color: var(--lg-red); font-size: .8rem; margin-top: -12px; margin-bottom: 14px; }

        .lg-foot { text-align: center; margin-top: 22px; color: var(--lg-muted); font-size: .82rem; }
        .lg-foot a { color: var(--lg-navy); font-weight: 600; }

        @media (max-width: 991.98px) {
            .lg-card { grid-template-columns: 1fr; max-width: 520px; }
            .lg-brand { padding: 28px 24px; }
            .lg-points { display: none; }
            .lg-motto { display: none; }
            .lg-logo { width: 130px; margin-bottom: 14px; }
            .lg-brand h1 { font-size: 1.4rem; }
            .lg-form { padding: 28px 22px; }
        }
        @media (max-width: 420px) {
            .lg-schools { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    <div class="lg-wrap">
        <div class="lg-card">
            {{-- Brand side --}}
            <div class="lg-brand">
                <div>
                    <img class="lg-logo" src="{{ asset('admin/assets/images/logo-light.png') }}" alt="Ultra School Logo">
                    <h1>Ultra School</h1>
                    <div class="lg-place">Keshod</div>
                    <div class="lg-motto">न हि ज्ञानेन सदृशं पवित्रमिह विद्यते</div>
                </div>
                <ul class="lg-points">
                    <li><i class="ti ti-users"></i> Admissions, students &amp; class reports</li>
                    <li><i class="ti ti-certificate"></i> Certificates, results &amp; attendance</li>
                    <li><i class="ti ti-shield-lock"></i> Secure login for every school</li>
                </ul>
            </div>

            {{-- Form side --}}
            <div class="lg-form">
                <h2>Welcome back 👋</h2>
                <p class="lg-sub">Choose your school and sign in to continue.</p>

                <form id="formAuthentication" action="{{ route('login') }}" method="post" novalidate>
                    @csrf

                    @if (session('success'))
                        <div class="lg-alert lg-alert-success">
                            <i class="ti ti-circle-check"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="lg-alert lg-alert-warning">
                            <i class="ti ti-alert-triangle"></i>
                            <div>{{ session('warning') }}</div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="lg-alert">
                            <i class="ti ti-alert-circle"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="lg-alert lg-alert-info">
                            <i class="ti ti-info-circle"></i>
                            <div>{{ session('status') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="lg-alert">
                            <i class="ti ti-alert-circle"></i>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="lg-label">
                        <span>1. Select School</span>
                    </div>
                    <div class="lg-schools">
                        @foreach ($schools as $sCode => $sInfo)
                            <div class="lg-school">
                                <input type="radio" name="school_key" id="school_{{ $sCode }}" value="{{ $sCode }}"
                                    {{ $currentSchool === $sCode ? 'checked' : '' }} required>
                                <label for="school_{{ $sCode }}">
                                    <span class="lg-ico"><i class="ti {{ $schoolIcons[$sCode] ?? 'ti-school' }}"></i></span>
                                    <span>
                                        <span class="lg-code">{{ $sInfo['short_name'] }}</span>
                                        <span class="lg-name">{{ $sInfo['name'] }}</span>
                                    </span>
                                    <span class="lg-check"><i class="ti ti-check"></i></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="lg-error d-none" id="schoolError">Please select your school.</div>

                    <div class="lg-label"><span>2. Login Details</span></div>

                    <div class="lg-field">
                        <i class="ti ti-mail lg-lead"></i>
                        <input type="email" class="lg-input @error('email') is-invalid @enderror" id="email" name="email"
                            placeholder="Email address" value="{{ old('email') }}" autocomplete="username" required>
                    </div>

                    <div class="lg-field">
                        <i class="ti ti-lock lg-lead"></i>
                        <input type="password" class="lg-input @error('password') is-invalid @enderror" id="password"
                            name="password" placeholder="Password" autocomplete="current-password" required>
                        <button type="button" class="lg-eye" id="togglePassword" title="Show / hide password">
                            <i class="ti ti-eye"></i>
                        </button>
                        <div class="lg-caps" id="capsWarning"><i class="ti ti-alert-triangle"></i> Caps Lock is ON</div>
                    </div>

                    <div class="lg-row">
                        <label class="lg-remember">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="lg-btn" id="loginBtn">
                        <i class="ti ti-login"></i> <span>Sign in</span>
                    </button>
                </form>

                <div class="lg-foot">
                    © {{ date('Y') }} Ultra School, Keshod ·
                    <a href="{{ route('software.privacy-policy') }}" target="_blank">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        (function() {
            var $form = $('#formAuthentication');
            var $pwd = $('#password');

            $('#togglePassword').on('click', function() {
                var show = $pwd.attr('type') === 'password';
                $pwd.attr('type', show ? 'text' : 'password');
                $(this).find('i').attr('class', show ? 'ti ti-eye-off' : 'ti ti-eye');
                $pwd.trigger('focus');
            });

            $pwd.on('keyup keydown', function(e) {
                var on = e.originalEvent && e.originalEvent.getModifierState && e.originalEvent.getModifierState('CapsLock');
                $('#capsWarning').toggle(!!on);
            });

            $('input[name="school_key"]').on('change', function() {
                $('#schoolError').addClass('d-none');
                if (!$('#email').val()) $('#email').trigger('focus');
            });

            $form.on('submit', function(e) {
                if (!$('input[name="school_key"]:checked').length) {
                    e.preventDefault();
                    $('#schoolError').removeClass('d-none');
                    return;
                }
                $('#loginBtn').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> <span>Signing in...</span>');
            });

            if (!$('input[name="school_key"]:checked').length) {
                $('input[name="school_key"]').first().trigger('focus');
            } else if (!$('#email').val()) {
                $('#email').trigger('focus');
            } else {
                $pwd.trigger('focus');
            }
        })();
    </script>
@endsection
