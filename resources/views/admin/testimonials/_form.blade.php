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

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
