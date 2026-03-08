@extends('admin.layouts.app')
@section('title', 'Create Delivery Payload')
@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.digital-delivery-payloads.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Product</label>
            <select class="form-select" name="digital_product_id" required>
                <option value="">Select Product</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" @selected(old('digital_product_id') == $p->id)>{{ $p->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Payload Type</label>
            <select class="form-select" name="payload_type" required>
                @foreach(['credential','code','link','file','manual'] as $type)
                    <option value="{{ $type }}" @selected(old('payload_type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="payload[username]" value="{{ old('payload.username') }}"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="payload[email]" value="{{ old('payload.email') }}"></div>
        <div class="col-md-6"><label class="form-label">Password</label><input class="form-control" name="payload[password]" value="{{ old('payload.password') }}"></div>
        <div class="col-md-6"><label class="form-label">Code</label><input class="form-control" name="payload[code]" value="{{ old('payload.code') }}"></div>
        <div class="col-md-6"><label class="form-label">URL</label><input class="form-control" name="payload[url]" value="{{ old('payload.url') }}"></div>
        <div class="col-md-6"><label class="form-label">File URL</label><input class="form-control" name="payload[file_url]" value="{{ old('payload.file_url') }}"></div>
        <div class="col-12"><label class="form-label">Note</label><textarea class="form-control" rows="3" name="payload[note]">{{ old('payload.note') }}</textarea></div>
        <div class="col-12"><label class="form-label">Internal Notes</label><textarea class="form-control" rows="3" name="notes">{{ old('notes') }}</textarea></div>

        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Create</button>
            <a href="{{ route('admin.digital-delivery-payloads.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
