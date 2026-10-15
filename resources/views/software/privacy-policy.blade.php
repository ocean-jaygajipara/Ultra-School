@extends('software.layout.app')

@section('title', 'Privacy Policy')

@php
    $appName = config('app.name', 'Ultra School');
    $sections = [
        'introduction' => ['Introduction', 'ti-info-circle'],
        'collect' => ['Information We May Collect', 'ti-database'],
        'use' => ['What We Do With The Information', 'ti-settings-check'],
        'cookies' => ['How We Use Cookies', 'ti-cookie'],
        'control' => ['Controlling Your Information', 'ti-user-shield'],
        'disclaimer' => ['Disclaimer', 'ti-alert-triangle'],
    ];
@endphp

@section('page_style_file')
    <style>
        .pp {
            --pp-navy: #1f2580;
            --pp-navy-2: #2e3a9e;
            --pp-red: #e1141c;
            --pp-sky: #bfe3f7;
            --pp-sky-soft: #eef7fd;
            --pp-gold: #f5b400;
            --pp-line: #e1e4f2;
            --pp-muted: #5f6488;
            --pp-text: #3a3f66;
            background:
                radial-gradient(circle at 5% 0%, rgba(191, 227, 247, .55), transparent 30%),
                radial-gradient(circle at 95% 100%, rgba(245, 180, 0, .12), transparent 30%),
                #f4f6fc;
            min-height: 100vh;
            padding: 28px 16px 48px;
        }
        .pp-inner { max-width: 1120px; margin: 0 auto; }

        .pp-hero {
            border-radius: 24px; color: #fff; padding: 34px 36px;
            display: flex; align-items: center; gap: 24px; flex-wrap: wrap;
            background:
                radial-gradient(circle at 88% 15%, rgba(245, 180, 0, .38), transparent 40%),
                radial-gradient(circle at 0% 120%, rgba(191, 227, 247, .3), transparent 45%),
                linear-gradient(135deg, #171c6b 0%, var(--pp-navy-2) 100%);
            box-shadow: 0 24px 50px -24px rgba(31, 37, 128, .6);
            margin-bottom: 26px;
        }
        .pp-logo {
            width: 96px; height: 96px; object-fit: contain; flex-shrink: 0;
            background: #fff; border-radius: 20px; padding: 8px;
            box-shadow: 0 0 0 4px rgba(245, 180, 0, .65);
        }
        .pp-hero h1 { color: #fff; font-weight: 800; font-size: 2rem; margin: 0; letter-spacing: -.5px; }
        .pp-hero p { color: #d6e6f7; margin: 6px 0 0; max-width: 560px; }
        .pp-hero-meta { margin-left: auto; display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }
        .pp-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255, 255, 255, .12); border: 1px solid rgba(191, 227, 247, .4);
            border-radius: 50rem; padding: 6px 14px; font-size: .82rem; font-weight: 600; color: #fff;
        }
        .pp-chip.is-gold { background: rgba(245, 180, 0, .2); border-color: rgba(245, 180, 0, .6); color: #ffe08a; }

        .pp-grid { display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start; }

        .pp-toc {
            position: sticky; top: 20px;
            background: #fff; border: 1px solid var(--pp-line); border-radius: 18px; padding: 18px;
            box-shadow: 0 10px 30px -14px rgba(31, 37, 128, .2);
        }
        .pp-toc h6 {
            font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em;
            color: var(--pp-muted); margin: 0 0 12px;
        }
        .pp-toc a {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 11px;
            color: var(--pp-text); font-size: .88rem; font-weight: 600; text-decoration: none;
            transition: background .15s, color .15s;
        }
        .pp-toc a i { color: var(--pp-navy-2); font-size: 1.05rem; }
        .pp-toc a:hover { background: var(--pp-sky-soft); color: var(--pp-navy) !important; }
        .pp-toc a.active { background: var(--pp-navy); color: #fff !important; }
        .pp-toc a.active i { color: var(--pp-gold); }
        .pp-back {
            margin-top: 14px; display: flex !important; justify-content: center;
            background: var(--pp-sky-soft); border: 1px solid var(--pp-sky); color: var(--pp-navy) !important;
        }

        .pp-section {
            background: #fff; border: 1px solid var(--pp-line); border-radius: 18px;
            padding: 26px 28px; margin-bottom: 18px; scroll-margin-top: 20px;
            box-shadow: 0 10px 30px -18px rgba(31, 37, 128, .2);
        }
        .pp-section-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .pp-section-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: #e8eafb; color: var(--pp-navy); font-size: 1.3rem;
        }
        .pp-section h2 { color: var(--pp-navy); font-size: 1.2rem; font-weight: 800; margin: 0; }
        .pp-section p { color: var(--pp-text); line-height: 1.75; margin-bottom: 12px; }
        .pp-section p:last-child { margin-bottom: 0; }

        .pp-list { list-style: none; padding: 0; margin: 4px 0 14px; display: grid; gap: 10px; }
        .pp-list li {
            display: flex; gap: 10px; align-items: flex-start; color: var(--pp-text); line-height: 1.6;
            background: #fafbff; border: 1px solid var(--pp-line); border-left: 4px solid var(--pp-sky);
            border-radius: 12px; padding: 10px 14px;
        }
        .pp-list li i { color: var(--pp-navy-2); margin-top: 3px; }
        .pp-list.is-grid { grid-template-columns: 1fr 1fr; }

        .pp-note {
            display: flex; gap: 12px; align-items: flex-start;
            background: #fff8e1; border: 1px solid #ffe08a; border-radius: 14px; padding: 14px 16px;
            color: #6b5100; margin-top: 6px;
        }
        .pp-note i { color: var(--pp-gold); font-size: 1.3rem; }
        .pp-note.is-safe { background: var(--pp-sky-soft); border-color: var(--pp-sky); color: var(--pp-navy); }
        .pp-note.is-safe i { color: var(--pp-navy-2); }

        .pp-foot { text-align: center; color: var(--pp-muted); font-size: .85rem; margin-top: 10px; }

        @media (max-width: 991.98px) {
            .pp-grid { grid-template-columns: 1fr; }
            .pp-toc { position: static; }
            .pp-hero-meta { margin-left: 0; align-items: flex-start; }
            .pp-list.is-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) {
            .pp-hero { padding: 24px 20px; }
            .pp-hero h1 { font-size: 1.5rem; }
            .pp-section { padding: 20px 18px; }
        }
    </style>
@endsection

@section('content')
    <div class="pp">
        <div class="pp-inner">
            <div class="pp-hero">
                <img class="pp-logo" src="{{ asset('admin/assets/images/logo-light.png') }}" alt="Ultra School Logo">
                <div>
                    <h1>Privacy Policy</h1>
                    <p>How {{ $appName }} collects, uses and protects the information you share with us.</p>
                </div>
                <div class="pp-hero-meta">
                    <span class="pp-chip is-gold"><i class="ti ti-calendar"></i> Last updated: 15 Oct 2026</span>
                    <span class="pp-chip"><i class="ti ti-shield-check"></i> Your data is safe with us</span>
                </div>
            </div>

            <div class="pp-grid">
                <aside class="pp-toc">
                    <h6>Contents</h6>
                    @foreach ($sections as $id => [$title, $icon])
                        <a href="#{{ $id }}" class="{{ $loop->first ? 'active' : '' }}"><i class="ti {{ $icon }}"></i> {{ $title }}</a>
                    @endforeach
                    @auth
                        <a href="{{ route('software.dashboard') }}" class="pp-back"><i class="ti ti-arrow-left"></i> Back to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="pp-back"><i class="ti ti-arrow-left"></i> Back to Login</a>
                    @endauth
                </aside>

                <main>
                    <section class="pp-section" id="introduction">
                        <div class="pp-section-head">
                            <span class="pp-section-icon"><i class="ti ti-info-circle"></i></span>
                            <h2>Introduction</h2>
                        </div>
                        <p>
                            This privacy policy sets out how <strong>{{ $appName }}</strong> uses and protects any information
                            that you give {{ $appName }} when you visit their website and/or agree to purchase from them.
                        </p>
                        <p>
                            <strong>{{ $appName }}</strong> is committed to ensuring that your privacy is protected. Should we ask you to provide
                            certain information by which you can be identified when using this website, then you can be assured that
                            it will only be used in accordance with this privacy statement.
                        </p>
                        <div class="pp-note">
                            <i class="ti ti-refresh"></i>
                            <div>
                                <strong>{{ $appName }}</strong> may change this policy from time to time by updating this page. You should check
                                this page from time to time to ensure that you adhere to these changes.
                            </div>
                        </div>
                    </section>

                    <section class="pp-section" id="collect">
                        <div class="pp-section-head">
                            <span class="pp-section-icon"><i class="ti ti-database"></i></span>
                            <h2>Information We May Collect</h2>
                        </div>
                        <ul class="pp-list is-grid">
                            <li><i class="ti ti-user"></i> Name</li>
                            <li><i class="ti ti-mail"></i> Contact information including email address</li>
                            <li><i class="ti ti-map-pin"></i> Demographic information such as postcode, preferences and interests</li>
                            <li><i class="ti ti-clipboard-list"></i> Other information relevant to customer surveys and/or offers</li>
                        </ul>
                    </section>

                    <section class="pp-section" id="use">
                        <div class="pp-section-head">
                            <span class="pp-section-icon"><i class="ti ti-settings-check"></i></span>
                            <h2>What We Do With The Information We Gather</h2>
                        </div>
                        <p>
                            We require this information to understand your needs and provide you with a better service,
                            and in particular for the following reasons:
                        </p>
                        <ul class="pp-list">
                            <li><i class="ti ti-folder"></i> Internal record keeping.</li>
                            <li><i class="ti ti-trending-up"></i> We may use the information to improve our products and services.</li>
                            <li><i class="ti ti-mail-forward"></i> We may periodically send promotional emails about new products, special offers or other
                                information which we think you may find interesting using the email address you have provided.</li>
                            <li><i class="ti ti-phone-call"></i> From time to time, we may also use your information to contact you for market research purposes.
                                We may contact you by email, phone, fax or mail.</li>
                            <li><i class="ti ti-adjustments"></i> We may use the information to customise the website according to your interests.</li>
                        </ul>
                        <div class="pp-note is-safe">
                            <i class="ti ti-lock"></i>
                            <div>
                                We are committed to ensuring that your information is secure. In order to prevent unauthorised
                                access or disclosure we have put in suitable measures.
                            </div>
                        </div>
                    </section>

                    <section class="pp-section" id="cookies">
                        <div class="pp-section-head">
                            <span class="pp-section-icon"><i class="ti ti-cookie"></i></span>
                            <h2>How We Use Cookies</h2>
                        </div>
                        <p>
                            A cookie is a small file which asks permission to be placed on your computer's hard drive.
                            Once you agree, the file is added and the cookie helps analyze web traffic or lets you know
                            when you visit a particular site.
                        </p>
                        <p>
                            Cookies allow web applications to respond to you as an individual. The web application can
                            tailor its operations to your needs, likes and dislikes by gathering and remembering
                            information about your preferences.
                        </p>
                        <p>
                            We use traffic log cookies to identify which pages are being used. This helps us analyze data
                            about webpage traffic and improve our website in order to tailor it to customer needs.
                            We only use this information for statistical analysis purposes and then the data is removed
                            from the system.
                        </p>
                        <p>
                            Overall, cookies help us provide you with a better website by enabling us to monitor which
                            pages you find useful and which you do not. A cookie in no way gives us access to your computer
                            or any information about you other than the data you choose to share with us.
                        </p>
                        <div class="pp-note">
                            <i class="ti ti-toggle-right"></i>
                            <div>
                                You can choose to accept or decline cookies. Most web browsers automatically accept cookies,
                                but you can usually modify your browser setting to decline cookies if you prefer. This may
                                prevent you from taking full advantage of the website.
                            </div>
                        </div>
                    </section>

                    <section class="pp-section" id="control">
                        <div class="pp-section-head">
                            <span class="pp-section-icon"><i class="ti ti-user-shield"></i></span>
                            <h2>Controlling Your Personal Information</h2>
                        </div>
                        <p>You may choose to restrict the collection or use of your personal information in the following ways:</p>
                        <ul class="pp-list">
                            <li><i class="ti ti-square-check"></i> Whenever you are asked to fill in a form on the website, look for the box that you can
                                click to indicate that you do not want the information to be used by anybody for direct
                                marketing purposes.</li>
                            <li><i class="ti ti-message-circle"></i> If you have previously agreed to us using your personal information for direct marketing
                                purposes, you may change your mind at any time by writing to or emailing us.</li>
                        </ul>
                        <div class="pp-note is-safe">
                            <i class="ti ti-shield-check"></i>
                            <div>
                                We will not sell, distribute or lease your personal information to third parties unless we
                                have your permission or are required by law to do so.
                            </div>
                        </div>
                        <p class="mt-3">
                            If you believe that any information we are holding on you is incorrect or incomplete,
                            please write to us. We will promptly correct any information found to be incorrect.
                        </p>
                    </section>

                    <section class="pp-section" id="disclaimer">
                        <div class="pp-section-head">
                            <span class="pp-section-icon"><i class="ti ti-alert-triangle"></i></span>
                            <h2>Disclaimer</h2>
                        </div>
                        <p>
                            The above content is created for {{ $appName }}. Razorpay shall not be
                            liable for any content provided here and shall not be responsible for any claims and liability
                            that may arise due to merchant’s non-adherence to it.
                        </p>
                    </section>

                    <div class="pp-foot">© {{ date('Y') }} {{ $appName }}, Keshod. All rights reserved.</div>
                </main>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        (function() {
            var links = document.querySelectorAll('.pp-toc a[href^="#"]');
            var sections = Array.prototype.map.call(links, function(a) {
                return document.querySelector(a.getAttribute('href'));
            });

            links.forEach(function(a) {
                a.addEventListener('click', function(e) {
                    var target = document.querySelector(a.getAttribute('href'));
                    if (!target) return;
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            function setActive() {
                var current = 0;
                sections.forEach(function(sec, i) {
                    if (sec && sec.getBoundingClientRect().top <= 120) current = i;
                });
                links.forEach(function(a, i) { a.classList.toggle('active', i === current); });
            }
            window.addEventListener('scroll', setActive, { passive: true });
            document.addEventListener('scroll', setActive, { passive: true, capture: true });
        })();
    </script>
@endsection
