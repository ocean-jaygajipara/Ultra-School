@extends('software.layout.app')

@php
    $page_title = 'Import Student';
    $route = isset($modules['route']) ? $modules['route'] : null;
    $report = session()->pull('import_report');
    $schoolTitle = strtoupper(\App\Services\SchoolDatabaseManager::getActiveSchool()['name'] ?? 'ULTRA SCHOOL');
@endphp
@section('title', $page_title)

@section('content')
    <style>
        .imx {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --red: #e1141c;
            --sky: #bfe3f7;
            --sky-soft: #eef7fd;
            --gold: #f5b400;
            --line: #e1e4f2;
            --muted: #5f6488;
            color: #23264d;
        }

        .imx-hero {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 22px 26px;
            background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 100%);
            color: #fff;
            box-shadow: 0 10px 28px rgba(31, 37, 128, .22);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .imx-hero::after {
            content: "";
            position: absolute;
            right: -50px;
            top: -70px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
        }

        .imx-hero-l {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            z-index: 1;
        }

        .imx-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: var(--gold);
        }

        .imx-hero h4 {
            color: #fff;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .imx-hero p {
            margin: 0;
            color: rgba(255, 255, 255, .78);
            font-size: .9rem;
        }

        .imx-back {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            color: #fff;
            font-weight: 600;
            font-size: .86rem;
            text-decoration: none;
        }

        .imx-back:hover {
            background: #fff;
            color: var(--navy);
        }

        .imx-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            margin-bottom: 20px;
        }

        .imx-card-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
        }

        .imx-card-head h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
        }

        .imx-step {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            font-weight: 700;
            font-size: .82rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .imx-card-body {
            padding: 20px;
        }

        .imx-drop {
            position: relative;
            border: 2px dashed #c3c7e3;
            border-radius: 14px;
            background: #f7f8fd;
            padding: 34px 20px;
            text-align: center;
            cursor: pointer;
            transition: all .15s ease;
        }

        .imx-drop:hover,
        .imx-drop.is-drag {
            border-color: var(--navy-2);
            background: var(--sky-soft);
        }

        .imx-drop.has-file {
            border-style: solid;
            border-color: #28a26b;
            background: #effaf4;
        }

        .imx-drop.is-invalid {
            border-color: var(--red);
            background: #fff5f5;
        }

        .imx-drop input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .imx-drop-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background: #e9ebf8;
            color: var(--navy);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .imx-drop.has-file .imx-drop-icon {
            background: #d6f3e4;
            color: #1f8a57;
        }

        .imx-drop-title {
            font-weight: 700;
            color: var(--navy);
            font-size: 1rem;
        }

        .imx-drop-sub {
            font-size: .82rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .imx-file-name {
            display: none;
            margin-top: 10px;
            font-weight: 600;
            color: #1f8a57;
            word-break: break-all;
        }

        .imx-drop.has-file .imx-file-name {
            display: block;
        }

        .imx-error {
            color: var(--red);
            font-size: .84rem;
            margin-top: 8px;
            font-weight: 600;
        }

        .imx-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .imx-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: .9rem;
            border: 1px solid transparent;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s ease;
        }

        .imx-btn-primary {
            background: var(--navy);
            color: #fff;
        }

        .imx-btn-primary:hover {
            background: var(--navy-2);
            color: #fff;
        }

        .imx-btn-primary:disabled {
            opacity: .7;
            cursor: not-allowed;
        }

        .imx-btn-light {
            background: #fff;
            color: var(--muted);
            border-color: var(--line);
        }

        .imx-btn-light:hover {
            border-color: var(--red);
            color: var(--red);
        }

        .imx-sample {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border-radius: 12px;
            background: #fff8e1;
            border: 1px solid #f7e0a0;
            margin-bottom: 16px;
        }

        .imx-sample i.big {
            font-size: 1.8rem;
            color: #b98700;
        }

        .imx-sample-txt {
            flex: 1;
            font-size: .84rem;
            color: #6b5300;
        }

        .imx-sample-txt b {
            display: block;
            color: #4d3b00;
            font-size: .9rem;
        }

        .imx-sample a {
            white-space: nowrap;
            background: var(--gold);
            color: #2a2000;
            padding: 7px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: .8rem;
            text-decoration: none;
        }

        .imx-sample a:hover {
            background: #e0a500;
        }

        .imx-rules {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .imx-rules li {
            display: flex;
            gap: 10px;
            font-size: .86rem;
            line-height: 1.5;
        }

        .imx-rules li i {
            color: var(--navy-2);
            font-size: 1.05rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .imx code {
            background: var(--sky-soft);
            color: var(--navy);
            padding: 1px 6px;
            border-radius: 5px;
            font-size: .8rem;
        }

        .imx-cols {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        .imx-cols span {
            background: var(--navy);
            color: #fff;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: .74rem;
            font-weight: 600;
        }

        .imx-std {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .imx-std span {
            background: var(--sky-soft);
            color: var(--navy);
            border: 1px solid var(--sky);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: .8rem;
            font-weight: 600;
        }

        .imx-muted {
            font-size: .8rem;
            color: var(--muted);
        }

        .imx-result {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .imx-res {
            border-radius: 14px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--line);
        }

        .imx-res i {
            font-size: 1.6rem;
        }

        .imx-res .n {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .imx-res .l {
            font-size: .8rem;
            color: var(--muted);
        }

        .imx-res.ok { background: #effaf4; color: #1f8a57; }
        .imx-res.skip { background: #fff8e1; color: #b98700; }
        .imx-res.err { background: #fdecec; color: var(--red); }

        .imx-rows {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .imx-row {
            display: flex;
            gap: 12px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--line);
            font-size: .86rem;
        }

        .imx-row:last-child {
            border-bottom: 0;
        }

        .imx-row-no {
            flex-shrink: 0;
            min-width: 64px;
            font-weight: 700;
            color: var(--navy);
        }

        .imx-list-title {
            font-size: .92rem;
            font-weight: 700;
            margin: 16px 0 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 575px) {
            .imx-result { grid-template-columns: 1fr; }
            .imx-actions .imx-btn { flex: 1; justify-content: center; }
        }
    </style>

    <div class="imx">
        <div class="imx-hero">
            <div class="imx-hero-l">
                <div class="imx-hero-icon"><i class="ti ti-file-spreadsheet"></i></div>
                <div>
                    <h4>Import Student</h4>
                    <p>Upload an Excel file to add many students at once.</p>
                </div>
            </div>
            <a href="{{ route('admission.index') }}" class="imx-back"><i class="ti ti-arrow-left"></i> Back to Students</a>
        </div>

        @if ($errors->any() && !$errors->has('import_file'))
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @if ($report)
            <div class="imx-card">
                <div class="imx-card-head">
                    <i class="ti ti-circle-check" style="font-size:1.3rem;color:#1f8a57;"></i>
                    <h5>Import Result</h5>
                </div>
                <div class="imx-card-body">
                    <div class="imx-result">
                        <div class="imx-res ok">
                            <i class="ti ti-user-check"></i>
                            <div>
                                <div class="n">{{ count($report['inserted']) }}</div>
                                <div class="l">Inserted</div>
                            </div>
                        </div>
                        <div class="imx-res skip">
                            <i class="ti ti-player-skip-forward"></i>
                            <div>
                                <div class="n">{{ count($report['skipped']) }}</div>
                                <div class="l">Skipped</div>
                            </div>
                        </div>
                        <div class="imx-res err">
                            <i class="ti ti-alert-triangle"></i>
                            <div>
                                <div class="n">{{ count($report['errors']) }}</div>
                                <div class="l">Errors</div>
                            </div>
                        </div>
                    </div>

                    @if (count($report['skipped']))
                        <div class="imx-list-title" style="color:#b98700;"><i class="ti ti-player-skip-forward"></i> Skipped Rows</div>
                        <div class="imx-rows">
                            @foreach ($report['skipped'] as $r)
                                <div class="imx-row">
                                    <span class="imx-row-no">Row {{ $r['row'] }}</span>
                                    <span>{{ $r['reason'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (count($report['errors']))
                        <div class="imx-list-title" style="color:#e1141c;"><i class="ti ti-alert-triangle"></i> Rows With Errors</div>
                        <div class="imx-rows">
                            @foreach ($report['errors'] as $e)
                                <div class="imx-row">
                                    <span class="imx-row-no">Row {{ $e['row'] }}</span>
                                    <span>{{ $e['message'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="imx-card h-100 mb-0">
                    <div class="imx-card-head">
                        <span class="imx-step">1</span>
                        <h5>Upload Excel File</h5>
                    </div>
                    <div class="imx-card-body">
                        <div class="imx-sample">
                            <i class="ti ti-file-download big"></i>
                            <div class="imx-sample-txt">
                                <b>Need the format?</b>
                                Download the sample file, fill in your students and upload it here.
                            </div>
                            <a href="{{ route('admission.import-sample-download') }}">
                                <i class="ti ti-download"></i> Sample File
                            </a>
                        </div>

                        <form action="{{ route('admission.import-store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf
                            <label class="imx-drop @error('import_file') is-invalid @enderror" id="importDrop">
                                <input id="import_file" type="file" name="import_file" required accept=".xlsx,.xls">
                                <div class="imx-drop-icon"><i class="ti ti-cloud-upload" id="importDropIcon"></i></div>
                                <div class="imx-drop-title" id="importDropTitle">Click to choose or drag &amp; drop your file</div>
                                <div class="imx-drop-sub">Only .xlsx or .xls files</div>
                                <div class="imx-file-name" id="importFileName"></div>
                            </label>
                            @error('import_file')
                                <div class="imx-error"><i class="ti ti-alert-circle"></i> {{ $message }}</div>
                            @enderror

                            <div class="imx-actions">
                                <a href="{{ route('admission.index') }}" class="imx-btn imx-btn-light"><i class="ti ti-x"></i> Cancel</a>
                                <button type="submit" id="submitBtn" class="imx-btn imx-btn-primary">
                                    <i class="ti ti-upload"></i> Import Students
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="imx-card h-100 mb-0">
                    <div class="imx-card-head">
                        <span class="imx-step">2</span>
                        <h5>Available Standards</h5>
                    </div>
                    <div class="imx-card-body">
                        <div class="imx-std">
                            @forelse ($standards ?? [] as $std)
                                <span>{{ $std }}</span>
                            @empty
                                <div class="text-danger small">No active standards found. Please add them in Standard Master first.</div>
                            @endforelse
                        </div>
                        <p class="imx-muted mt-3 mb-0">
                            <i class="ti ti-info-circle"></i>
                            If the standard in the class title is not in this list, those students will be skipped.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="imx-card mt-3">
            <div class="imx-card-head">
                <span class="imx-step">3</span>
                <h5>How the Excel file should look</h5>
            </div>
            <div class="imx-card-body">
                <ul class="imx-rules">
                    <li>
                        <i class="ti ti-heading"></i>
                        <div>
                            Each class starts with a <b>title row</b> that ends with <b>Standard-Division</b>, for example
                            <code>{{ $schoolTitle }} 1-A</code>, <code>{{ $schoolTitle }} SKG-A</code>,
                            <code>{{ $schoolTitle }} BALVATIKA-B</code>.
                        </div>
                    </li>
                    <li>
                        <i class="ti ti-school"></i>
                        <div>
                            All students below that title go into the <b>Standard and Division in the title</b>
                            (e.g. <code>2-A</code> goes to <b>2nd</b>, Division <b>A</b>). The same value is set as Admission Std and Current Std.
                        </div>
                    </li>
                    <li>
                        <i class="ti ti-columns"></i>
                        <div>
                            The next row must be the column header. A serial number column without a header is allowed.
                            <div class="imx-cols">
                                <span>NAME</span><span>GR No.</span><span>PHONE</span><span>BIRTHDATE</span>
                                <span>ADMDATE</span><span>MOTHERNAME</span><span>CAST</span><span>RELIGION</span>
                            </div>
                        </div>
                    </li>
                    <li>
                        <i class="ti ti-user"></i>
                        <div>
                            <b>NAME</b> is read as <i>Surname Name Father Name</i> (e.g. <code>DHADUK ANSHI SAMIRBHAI</code>).
                            Dates can be <code>16-03-20</code> or <code>16-03-2020</code>.
                        </div>
                    </li>
                    <li>
                        <i class="ti ti-stack-2"></i>
                        <div>
                            One file can contain many classes, one below another or on separate sheets.
                            Every class needs its own title row and header row.
                        </div>
                    </li>
                    <li>
                        <i class="ti ti-copy-off"></i>
                        <div>Students whose <b>GR No.</b> already exists are skipped, and the reason is shown after import.</div>
                    </li>
                    <li>
                        <i class="ti ti-building"></i>
                        <div>Students are imported into the school you are currently logged into.</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $(function() {
            var $drop = $('#importDrop');
            var $input = $('#import_file');

            function showFile() {
                var file = $input[0].files[0];
                if (file) {
                    $drop.addClass('has-file').removeClass('is-invalid');
                    $('#importDropIcon').attr('class', 'ti ti-file-check');
                    $('#importDropTitle').text('File selected');
                    $('#importFileName').text(file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)');
                } else {
                    $drop.removeClass('has-file');
                    $('#importDropIcon').attr('class', 'ti ti-cloud-upload');
                    $('#importDropTitle').html('Click to choose or drag &amp; drop your file');
                    $('#importFileName').text('');
                }
            }

            $input.on('change', showFile);
            $drop.on('dragenter dragover', function() {
                $drop.addClass('is-drag');
            }).on('dragleave drop', function() {
                $drop.removeClass('is-drag');
            });

            $('#importForm').on('submit', function() {
                if (!$input[0].files.length) {
                    $drop.addClass('is-invalid');
                    return false;
                }
                $('#submitBtn').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Importing...');
            });
        });
    </script>
@endsection
