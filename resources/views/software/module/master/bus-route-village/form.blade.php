@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $isEdit = isset($edit) && $edit?->id;
@endphp

@section('title', ($isEdit ? 'Edit' : 'Add') . ' ' . $page_title)

@section('page_style_file')
    <style>
        .brv-form {
            --navy: #1f2580;
            --navy-2: #2e3a9e;
            --gold: #f5b400;
            --sky: #eef7fd;
            --line: #e1e4f2;
        }
        .brv-form-hero {
            background: linear-gradient(120deg, var(--navy), var(--navy-2));
            color: #fff;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brv-form-hero .icon {
            width: 48px; height: 48px; border-radius: 14px;
            background: rgba(255,255,255,.14); color: var(--gold);
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .brv-form-hero h4 { margin: 0; font-weight: 800; color: #fff; }
        .brv-form-hero p { margin: 2px 0 0; opacity: .82; font-size: .88rem; }
        .brv-form-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 8px 22px rgba(31, 37, 128, .08);
        }
        .brv-form-card .form-label { color: var(--navy); font-weight: 700; }
        .brv-form-card .form-control,
        .brv-form-card .input-group-text {
            border-radius: 10px;
            border-color: var(--line);
        }
        .brv-form-card .input-group-text {
            background: var(--sky);
            color: var(--navy);
            font-weight: 700;
        }
    </style>
@endsection

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
    <div class="brv-form my-2">
        <div class="brv-form-hero">
            <div class="icon"><i class="ti ti-{{ $isEdit ? 'edit' : 'plus' }}"></i></div>
            <div>
                <h4>{{ $isEdit ? 'Edit' : 'Add' }} Bus Route Village</h4>
                <p>{{ $isEdit ? 'Update village name and transport charge.' : 'Add a new bus stop village with transport charge.' }}</p>
            </div>
        </div>

        <div class="card brv-form-card">
            <div class="card-body p-4">
                <form
                    action="{{ $isEdit ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Village Name <span class="text-danger">*</span></label>
                                <input id="name" type="text"
                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ isset($edit?->name) ? $edit?->name : old('name') }}"
                                    placeholder="Enter village name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Transport Charge (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input id="charge" type="number" step="0.01" min="0"
                                        class="form-control @error('charge') is-invalid @enderror" name="charge"
                                        value="{{ isset($edit?->charge) ? $edit?->charge : old('charge', '0') }}"
                                        placeholder="Enter transport charge">
                                </div>
                                @error('charge')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary px-4">
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
