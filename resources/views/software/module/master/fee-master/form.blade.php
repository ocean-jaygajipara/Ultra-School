@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $isEdit = isset($edit) && $edit?->id;
@endphp

@section('title', $page_title)

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => ($isEdit ? 'Edit' : 'Add') . ' ' . $page_title, 'url' => ''],
            ],
            'page_title' => $page_title,
            'show_page_title' => false,
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>
@endsection

@section('content')
    <div class="mst my-2">
        <div class="mst-hero">
            <div class="mst-hero-l">
                <div class="mst-hero-icon"><i class="ti ti-cash"></i></div>
                <div>
                    <h4>{{ $isEdit ? 'Edit' : 'Add' }} {{ $page_title }}</h4>
                    <p>{{ $isEdit ? 'Update fee structure for standard and term.' : 'Create a new standard-wise term fee structure.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
                <form
                    action="{{ $isEdit ? route($route . '.update', [$edit->id]) : route($route . '.store') }}"
                    method="POST" id="feeMasterForm">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                @php
                                    $selectedAcademicYear = old(
                                        'academic_year_id',
                                        $edit->academic_year_id ?? ($currentAcademicYear->id ?? null)
                                    );
                                @endphp
                                <select name="academic_year_id" id="academic_year_id"
                                    class="form-select select2 @error('academic_year_id') is-invalid @enderror" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach ($academicYears as $yearItem)
                                        <option value="{{ $yearItem->id }}"
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

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Standard <span class="text-danger">*</span></label>
                                <select name="standard_id" id="standard_id"
                                    class="form-select select2 @error('standard_id') is-invalid @enderror" required>
                                    <option value="">Select Standard</option>
                                    @foreach ($standards as $std)
                                        <option value="{{ $std->id }}"
                                            {{ (string) old('standard_id', $edit->standard_id ?? '') === (string) $std->id ? 'selected' : '' }}>
                                            {{ $std->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('standard_id')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Term <span class="text-danger">*</span></label>
                                <select name="term" id="term"
                                    class="form-select select2 @error('term') is-invalid @enderror" required>
                                    <option value="">Select Term</option>
                                    @foreach ($terms as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ (string) old('term', $edit->term ?? '') === (string) $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('term')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label class="form-label">Admission Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="admission_fee" id="admission_fee"
                                        class="form-control fee-input @error('admission_fee') is-invalid @enderror"
                                        value="{{ old('admission_fee', $edit->admission_fee ?? 0) }}">
                                </div>
                                @error('admission_fee')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label class="form-label">Deposit Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="deposit_fee" id="deposit_fee"
                                        class="form-control fee-input @error('deposit_fee') is-invalid @enderror"
                                        value="{{ old('deposit_fee', $edit->deposit_fee ?? 0) }}">
                                </div>
                                @error('deposit_fee')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label class="form-label">Term Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="term_fee" id="term_fee"
                                        class="form-control fee-input @error('term_fee') is-invalid @enderror"
                                        value="{{ old('term_fee', $edit->term_fee ?? 0) }}">
                                </div>
                                @error('term_fee')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label class="form-label">School Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="school_fee" id="school_fee"
                                        class="form-control fee-input @error('school_fee') is-invalid @enderror"
                                        value="{{ old('school_fee', $edit->school_fee ?? 0) }}">
                                </div>
                                @error('school_fee')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label class="form-label">Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" id="total" class="form-control fw-bold" value="0.00" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success mt-1">
                                {{ $isEdit ? 'Update' : 'Submit' }}
                            </button>
                            @if (!$isEdit)
                                <button type="submit" name="action" value="save_next" class="btn btn-primary mt-1">
                                    Save & Next
                                </button>
                            @endif
                            <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            function money(n) {
                var v = parseFloat(n);
                if (isNaN(v) || v < 0) v = 0;
                return Math.round(v * 100) / 100;
            }

            function calcTotal() {
                var total = money($('#admission_fee').val())
                    + money($('#deposit_fee').val())
                    + money($('#term_fee').val())
                    + money($('#school_fee').val());
                $('#total').val(total.toFixed(2));
            }

            $('.fee-input').on('input change', calcTotal);
            calcTotal();
        });
    </script>
@endsection
