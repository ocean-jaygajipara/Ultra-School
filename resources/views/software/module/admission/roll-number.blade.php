@extends('software.layout.app')

@php
    $page_title = 'Roll Number';
    $route = isset($modules['route']) ? $modules['route'] : null;
    $canGenerate = !empty($admission->current_std) && !empty($admission->division);
@endphp

@section('title', $page_title)

@section('breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('software.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">Registered Student</a></li>
                        <li class="breadcrumb-item active">{{ $page_title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card shadow my-4">
        <div class="card-header">
            <h5 class="mb-0">Generate Roll Number</h5>
        </div>
        <div class="card-body">
            @if (!$canGenerate)
                <div class="alert alert-warning">
                    Current Std and Division are not set for this student.
                    <a href="{{ route($route . '.edit', $admission->id) }}">Edit the student</a> to set them first.
                </div>
            @endif

            <form action="{{ route('admission.roll-number.store', $admission->id) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">Standard</label>
                        <input type="text" class="form-control" value="{{ $admission->current_std }}" readonly>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">Division</label>
                        <input type="text" class="form-control" value="{{ $admission->division }}" readonly>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">GR No.</label>
                        <input type="text" class="form-control" value="{{ $admission->gr_no }}" readonly>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">Surname</label>
                        <input type="text" class="form-control" value="{{ $admission->first_name }}" readonly>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" value="{{ $admission->last_name }}" readonly>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">Gender</label>
                        <input type="text" class="form-control" value="{{ $admission->gender }}" readonly>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="form-label">Roll No. <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="roll_no"
                            class="form-control fw-bold @error('roll_no') is-invalid @enderror"
                            value="{{ old('roll_no', $suggestedRollNo) }}" {{ $canGenerate ? '' : 'disabled' }}>
                        <small class="text-muted">Next available roll number for {{ $admission->current_std }} - {{ $admission->division }} is filled automatically. You can change it if needed.</small>
                        @error('roll_no')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1" {{ $canGenerate ? '' : 'disabled' }}>Save Roll No.</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Skip</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
