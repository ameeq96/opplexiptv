@csrf

<div class="row g-3">
    <div class="col-lg-6">
        <label class="form-label">Author Name</label>
        <input type="text" name="author_name" class="form-control" value="{{ old('author_name', $testimonial->author_name) }}" required>
    </div>
    <div class="col-lg-2">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck"
                @checked(old('is_active', $testimonial->is_active ?? true))>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="verifiedCheck"
                @checked(old('is_verified', $testimonial->is_verified ?? false))>
            <label class="form-check-label" for="verifiedCheck">Verified</label>
        </div>
    </div>
    <div class="col-lg-12">
        <label class="form-label">Review Text</label>
        <textarea name="text" class="form-control" rows="3" required>{{ old('text', $testimonial->text) }}</textarea>
    </div>
    <div class="col-lg-8">
        <label class="form-label">Photo (webp/jpg/png)</label>
        <input type="file" name="image" class="form-control" @if(!$testimonial->exists) required @endif>
        @if ($testimonial->image)
            <div class="mt-2">
                <img src="{{ asset($testimonial->image) }}" alt="Author photo" style="height:70px;border-radius:8px;">
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <label class="form-label">Review Date</label>
        <input type="date" name="review_date" class="form-control"
            value="{{ old('review_date', $testimonial->review_date?->format('Y-m-d')) }}">
    </div>
    <div class="col-lg-4">
        <label class="form-label">Customer Country</label>
        <input type="text" name="country" class="form-control" maxlength="100"
            value="{{ old('country', $testimonial->country) }}">
    </div>
    <div class="col-lg-4">
        <label class="form-label">Device Used</label>
        <input type="text" name="device" class="form-control" maxlength="100"
            value="{{ old('device', $testimonial->device) }}">
    </div>
    <div class="col-lg-6">
        <label class="form-label">Verification Source</label>
        <input type="text" name="verification_source" class="form-control" maxlength="120"
            placeholder="Paid order, support conversation, or survey"
            value="{{ old('verification_source', $testimonial->verification_source) }}">
    </div>
    <div class="col-lg-6">
        <label class="form-label">Internal Proof Reference</label>
        <input type="text" name="proof_reference" class="form-control" maxlength="191"
            placeholder="Order ID or private evidence reference"
            value="{{ old('proof_reference', $testimonial->proof_reference) }}">
        <small class="text-muted">Internal only. Do not enter payment details or passwords.</small>
    </div>
    <div class="col-lg-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="publication_consent" value="1" id="publicationConsent"
                @checked(old('publication_consent', (bool) $testimonial->publication_consented_at))>
            <label class="form-check-label" for="publicationConsent">
                I have recorded the customer&rsquo;s permission to publish this name, review and photo.
            </label>
        </div>
    </div>
</div>

@if (!empty($locales))
    <div class="mt-4">
        <h5 class="mb-3">Translations</h5>
        <ul class="nav nav-tabs" role="tablist">
            @foreach ($locales as $locale)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab"
                        data-bs-target="#ts-{{ $locale }}" type="button" role="tab">
                        {{ strtoupper($locale) }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content border border-top-0 p-3">
            @foreach ($locales as $locale)
                @php $t = $testimonial->translation($locale); @endphp
                <div class="tab-pane fade @if ($loop->first) show active @endif" id="ts-{{ $locale }}" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label">Author Name ({{ strtoupper($locale) }})</label>
                        <input type="text" name="translations[{{ $locale }}][author_name]" class="form-control"
                            value="{{ old("translations.$locale.author_name", $t?->author_name) }}">
                    </div>
                    <div>
                        <label class="form-label">Review Text ({{ strtoupper($locale) }})</label>
                        <textarea name="translations[{{ $locale }}][text]" class="form-control" rows="3">{{ old("translations.$locale.text", $t?->text) }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
