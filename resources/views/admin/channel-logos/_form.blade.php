@csrf

<div class="row g-3">
    <div class="col-lg-2">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $logo->sort_order ?? 0) }}">
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck"
                @checked(old('is_active', $logo->is_active ?? true))>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>
    </div>
    <div class="col-lg-8">
        <label class="form-label">Logo (webp/jpg/png/svg)</label>
        <input type="file" name="image" class="form-control" @if(!$logo->exists) required @endif>
        @if ($logo->image)
            <div class="mt-2">
                <img src="{{ asset($logo->image) }}" alt="Logo" style="height:70px;border-radius:8px;">
            </div>
        @endif
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.channel-logos.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
