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
