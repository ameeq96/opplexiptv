@csrf

<div class="row g-3">
    <div class="col-lg-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $service->title) }}" required>
    </div>
    <div class="col-lg-6">
        <label class="form-label">Link (optional)</label>
        <input type="url" name="link" class="form-control" value="{{ old('link', $service->link) }}">
    </div>
    <div class="col-lg-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required>{{ old('description', $service->description) }}</textarea>
    </div>
    <div class="col-lg-2">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $service->sort_order ?? 0) }}">
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck"
                @checked(old('is_active', $service->is_active ?? true))>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>
    </div>
    <div class="col-lg-8">
        <label class="form-label">Icon (webp/jpg/png/svg)</label>
        <input type="file" name="icon" class="form-control" @if(!$service->exists) required @endif>
        @if ($service->icon)
            <div class="mt-2">
                <img src="{{ asset('images/icons/' . $service->icon) }}" alt="Service icon" style="height:70px;border-radius:8px;">
            </div>
        @endif
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.home-services.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
