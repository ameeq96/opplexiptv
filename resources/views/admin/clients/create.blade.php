@extends('admin.layouts.app')

@section('page_title', 'Add New Client')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                </div>

                <div class="mb-3 d-flex flex-column">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="country" class="form-label">Country</label>
                        <select id="country" name="country" class="form-select select2" style="width: 100%">
                            <option value="">-- Select Country --</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c['name'] }}"
                                    {{ old('country') == $c['name'] ? 'selected' : '' }}>
                                    {{ $c['name'] }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="col-md-6">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="1" class="form-control"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark">Save Client</button>
                </div>
            </form>
        </div>
    </div>

@endsection

