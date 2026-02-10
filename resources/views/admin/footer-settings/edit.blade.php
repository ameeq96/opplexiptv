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

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
