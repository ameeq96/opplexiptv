@extends('admin.layouts.app')

@section('title', 'Pricing Section')
@section('page_title', 'Pricing Section')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Pricing Section</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.pricing-section.update') }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-12">
                    <label class="form-label">Heading</label>
                    <input type="text" name="heading" class="form-control" value="{{ old('heading', $section->heading) }}" required>
                </div>
                <div class="col-lg-12">
                    <label class="form-label">Subheading</label>
                    <input type="text" name="subheading" class="form-control" value="{{ old('subheading', $section->subheading) }}">
                </div>
                <div class="col-lg-12">
                    <label class="form-label">Reseller Toggle Label</label>
                    <input type="text" name="show_reseller_label" class="form-control" value="{{ old('show_reseller_label', $section->show_reseller_label) }}">
                </div>
                <div class="col-lg-12">
                    <label class="form-label">Credit Info (HTML allowed)</label>
                    <textarea name="credit_info" class="form-control" rows="3">{{ old('credit_info', $section->credit_info) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
