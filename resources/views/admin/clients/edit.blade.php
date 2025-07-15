@extends('admin.layout.app')

@section('page_title', 'Edit Client')

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
            <form action="{{ route('clients.update', $client) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ $client->name }}" class="form-control"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ $client->email }}"
                            class="form-control">
                    </div>
                </div>

                <div class="mb-3 d-flex flex-column">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="{{ $client->phone }}"
                        required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="country" class="form-label">Country</label>

                        <select id="country" name="country" class="form-select select2">
                            <option value="">-- Select Country --</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c['name'] }}"
                                    {{ old('country', $client->country) == $c['name'] ? 'selected' : '' }}>
                                    {{ $c['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-6">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="1" class="form-control">{{ $client->notes }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark">Update Client</button>
                </div>
            </form>
        </div>
    </div>

@endsection
