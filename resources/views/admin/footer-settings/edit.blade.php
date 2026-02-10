@extends('admin.layouts.app')

@section('title', 'Footer Settings')
@section('page_title', 'Footer Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Footer Settings</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.footer-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Brand Text</label>
                    <input type="text" name="brand_text" class="form-control" value="{{ old('brand_text', $setting->brand_text) }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Crypto Note</label>
                    <input type="text" name="crypto_note" class="form-control" value="{{ old('crypto_note', $setting->crypto_note) }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone) }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" value="{{ old('email', $setting->email) }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $setting->address) }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Rights Text</label>
                    <input type="text" name="rights_text" class="form-control" value="{{ old('rights_text', $setting->rights_text) }}">
                </div>
                <div class="col-lg-12">
                    <label class="form-label">Legal Note</label>
                    <textarea name="legal_note" class="form-control" rows="3">{{ old('legal_note', $setting->legal_note) }}</textarea>
                </div>
            </div>

            @if (!empty($locales))
                <div class="mt-4">
                    <h5 class="mb-3">Translations</h5>
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($locales as $locale)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab"
                                    data-bs-target="#fs-{{ $locale }}" type="button" role="tab">
                                    {{ strtoupper($locale) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content border border-top-0 p-3">
                        @foreach ($locales as $locale)
                            @php $t = $setting->translation($locale); @endphp
                            <div class="tab-pane fade @if ($loop->first) show active @endif" id="fs-{{ $locale }}" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label">Brand Text ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][brand_text]" class="form-control"
                                        value="{{ old("translations.$locale.brand_text", $t?->brand_text) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Crypto Note ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][crypto_note]" class="form-control"
                                        value="{{ old("translations.$locale.crypto_note", $t?->crypto_note) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][address]" class="form-control"
                                        value="{{ old("translations.$locale.address", $t?->address) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Rights Text ({{ strtoupper($locale) }})</label>
                                    <input type="text" name="translations[{{ $locale }}][rights_text]" class="form-control"
                                        value="{{ old("translations.$locale.rights_text", $t?->rights_text) }}">
                                </div>
                                <div>
                                    <label class="form-label">Legal Note ({{ strtoupper($locale) }})</label>
                                    <textarea name="translations[{{ $locale }}][legal_note]" class="form-control" rows="3">{{ old("translations.$locale.legal_note", $t?->legal_note) }}</textarea>
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
