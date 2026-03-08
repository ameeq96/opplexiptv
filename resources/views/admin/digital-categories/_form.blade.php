<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="{{ old('name', $category->name) }}" required data-slug-target="#categorySlug">
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input id="categorySlug" data-slug-input class="form-control" name="slug" value="{{ old('slug', $category->slug) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Sort Order</label>
        <input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a href="{{ route('admin.digital-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
