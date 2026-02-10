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

            @if (!empty($locales))
                <div class="mt-4">
                    <h5 class="mb-3">Translations</h5>
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($locales as $locale)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab"
                                    data-bs-target="#ps-{{ $locale }}" type="button" role="tab">
                                    {{ strtoupper($locale) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content border border-top-0 p-3">
                        @foreach ($locales as $locale)
                            @php $t = $section->translation($locale); @endphp
                            <div class="tab-pane fade @if ($loop->first) show active @endif" id="ps-{{ $locale }}" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label">Heading ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][heading]" class="form-control"
                                        value="{{ old("translations.$locale.heading", $t?->heading) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subheading ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][subheading]" class="form-control"
                                        value="{{ old("translations.$locale.subheading", $t?->subheading) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reseller Toggle Label ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][show_reseller_label]" class="form-control"
                                        value="{{ old("translations.$locale.show_reseller_label", $t?->show_reseller_label) }}">
                                </div>
                                <div>
                                    <label class="form-label">Credit Info ({{ strtoupper($locale) }})</label>
                                    <textarea name="translations[{{ $locale }}][credit_info]" class="form-control" rows="3">{{ old("translations.$locale.credit_info", $t?->credit_info) }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
