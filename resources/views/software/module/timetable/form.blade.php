@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Timetable';
    $route = $modules['route'] ?? 'timetable';
    $isEdit = !empty($edit?->id);
@endphp
@section('title', ($isEdit ? 'Edit ' : 'Create ') . $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => $isEdit ? 'Edit' : 'Create', 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form
                action="{{ $isEdit ? route($route . '.update', [$edit->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Standard <span class="text-danger">*</span></label>
                            <select name="standard_id" class="form-control select2 @error('standard_id') is-invalid @enderror" required>
                                <option value="">Select Standard</option>
                                @foreach ($standards ?? [] as $std)
                                    <option value="{{ $std->id }}"
                                        @selected((int) old('standard_id', $edit->standard_id ?? 0) === (int) $std->id)>
                                        {{ $std->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('standard_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Attachment @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                                   name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.png,.jpeg,.webp"
                                   @required(!$isEdit)>
                            @if (!empty($edit?->attachment))
                                <p class="mt-2 mb-0">
                                    <a href="{{ asset($edit->attachment) }}" target="_blank">View Current Attachment</a>
                                </p>
                            @endif
                            @error('attachment')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1">{{ $isEdit ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(function () {
            if ($.fn.select2) {
                $('.select2').select2({ width: '100%', allowClear: true, placeholder: 'Select Standard' });
            }
        });
    </script>
@endsection
