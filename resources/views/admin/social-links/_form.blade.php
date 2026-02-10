@csrf

<div class="row g-3">
    <div class="col-lg-4">
        <label class="form-label">Platform</label>
        <input type="text" name="platform" class="form-control" value="{{ old('platform', $link->platform) }}" required>
    </div>
    <div class="col-lg-5">
        <label class="form-label">URL</label>
        <input type="text" name="url" class="form-control" value="{{ old('url', $link->url) }}" required>
    </div>
    <div class="col-lg-3">
        <label class="form-label">Icon Class</label>
        <input type="text" name="icon_class" class="form-control" value="{{ old('icon_class', $link->icon_class) }}">
        <div class="small text-muted mt-1">Example: fa fa-facebook-f</div>
    </div>
    <div class="col-lg-2">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $link->sort_order ?? 0) }}">
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck"
                @checked(old('is_active', $link->is_active ?? true))>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.social-links.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
