@if (session('success'))
    <div class="alert alert-success admin-feedback mt-3" role="status">
        <i class="bi bi-check-circle" aria-hidden="true"></i>
        <div><strong>Action completed</strong><p class="mb-0">{{ session('success') }}</p></div>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger admin-feedback mt-3" role="alert" tabindex="-1" data-admin-feedback-error>
        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <div><strong>Action could not be completed</strong><p class="mb-0">{{ session('error') }}</p></div>
    </div>
@endif
@if (session('warning'))
    <div class="alert alert-warning admin-feedback mt-3" role="status">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
        <div><strong>Please review</strong><p class="mb-0">{{ session('warning') }}</p></div>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger admin-feedback mt-3" role="alert" tabindex="-1" data-admin-feedback-error>
        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <div>
            <strong>Check the highlighted details</strong>
            <p class="mb-2">The form could not be submitted. Review these details and try again.</p>
            <ul class="mb-0">
                @foreach ($errors->messages() as $field => $messages)
                    @foreach ($messages as $error)
                        <li><span data-admin-field-error="{{ $field }}">{{ $error }}</span></li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
@endif
