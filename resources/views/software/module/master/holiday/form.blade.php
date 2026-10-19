@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp

@section('title', $page_title)


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
                <div class="mst-hero-icon"><i class="ti ti-beach"></i></div>
                <div>
                    <h4>{{ isset($edit) && $edit?->id ? 'Edit' : 'Add' }} {{ $page_title }}</h4>
                    <p>{{ isset($edit) && $edit?->id ? 'Update master record details.' : 'Create a new master record.' }}</p>
                </div>
            </div>
        </div>

        <div class="card mst-form-card">
            <div class="card-body p-4">
            <form action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}" method="POST">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row g-3">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Holiday Name / Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ isset($edit?->name) ? $edit?->name : old('name') }}" placeholder="e.g. Diwali Vacation, Uttarayan, Independence Day"
                                autocomplete="off" required>

                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Holiday Type</label>
                            @php
                                $selectedType = isset($edit?->type) ? $edit?->type : old('type', 'Public Holiday');
                                $types = ['Public Holiday', 'Festival', 'National Holiday', 'Institute Holiday', 'Optional Holiday'];
                            @endphp
                            <select name="type" class="form-select select2 @error('type') is-invalid @enderror">
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" {{ $selectedType == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>

                            @error('type')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>
                            <input type="date" id="from_date" name="from_date" class="form-control @error('from_date') is-invalid @enderror"
                                value="{{ isset($edit?->from_date) ? \Carbon\Carbon::parse($edit?->from_date)->format('Y-m-d') : old('from_date', date('Y-m-d')) }}" required>

                            @error('from_date')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>
                            <input type="date" id="to_date" name="to_date" class="form-control @error('to_date') is-invalid @enderror"
                                value="{{ isset($edit?->to_date) ? \Carbon\Carbon::parse($edit?->to_date)->format('Y-m-d') : old('to_date', date('Y-m-d')) }}" required>

                            @error('to_date')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Description / Remarks</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Enter description or details about the holiday (optional)">{{ isset($edit?->description) ? $edit?->description : old('description') }}</textarea>

                            @error('description')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                    </div>
                </div>
            </form>
                    </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: 'Select Holiday Type',
                    width: '100%'
                });
            }
        });

        document.getElementById('from_date').addEventListener('change', function() {
            var toDateInput = document.getElementById('to_date');
            if (!toDateInput.value || toDateInput.value < this.value) {
                toDateInput.value = this.value;
            }
            toDateInput.min = this.value;
        });
    </script>
@endsection
