@if (session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
