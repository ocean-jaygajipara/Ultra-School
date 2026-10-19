@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? null;
    $route = $modules['route'] ?? null;
    $isEdit = isset($edit) && $edit?->id;
    $startVal = old('start_date', $isEdit ? $edit->start_date?->format('d-m-Y') : '');
    $endVal = old('end_date', $isEdit ? $edit->end_date?->format('d-m-Y') : '');
    if ($startVal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startVal)) {
        $startVal = \Carbon\Carbon::parse($startVal)->format('d-m-Y');
    }
    if ($endVal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endVal)) {
        $endVal = \Carbon\Carbon::parse($endVal)->format('d-m-Y');
    }
@endphp

@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => (isset($edit) && $edit?->id ? 'Edit' : 'Add') . ' ' . $page_title, 'url' => ''],
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
                <div class="mst-hero-icon"><i class="ti ti-calendar"></i></div>
                <div>
                    <h4>{{ isset($edit) && $edit?->id ? 'Edit' : 'Add' }} {{ $page_title }}</h4>
                    <p>{{ isset($edit) && $edit?->id ? 'Update master record details.' : 'Create a new master record.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
            @if ($errors->any() && !$errors->hasAny(['name', 'start_date', 'end_date', 'status']))
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ $isEdit ? route($route . '.update', [$edit->id]) : route($route . '.store') }}" method="POST">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-3 col-sm-12 mb-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $edit->name ?? '') }}" placeholder="e.g. YYYY-YY" autocomplete="off">
                        @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="text" name="start_date" id="start_date"
                            class="form-control ay-date @error('start_date') is-invalid @enderror"
                            value="{{ $startVal }}" placeholder="DD-MM-YYYY" autocomplete="off">
                        @error('start_date')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="text" name="end_date" id="end_date"
                            class="form-control ay-date @error('end_date') is-invalid @enderror"
                            value="{{ $endVal }}" placeholder="DD-MM-YYYY" autocomplete="off">
                        @error('end_date')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        @php $statusVal = old('status', $edit->status ?? 'active'); @endphp
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" {{ $statusVal === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $statusVal === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_current" id="is_current" value="1"
                                {{ old('is_current', $edit->is_current ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_current">Set as current academic year</label>
                        </div>
                        <small class="text-muted">Admissions and student updates are saved in the current academic year. Only one year can be current.</small>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1">{{ $isEdit ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                    </div>
                </div>
            </form>
                    </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        $(function() {
            if (typeof flatpickr === 'undefined') return;
            flatpickr('.ay-date', {
                dateFormat: 'd-m-Y',
                allowInput: true
            });
        });
    </script>
@endsection
