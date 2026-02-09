@csrf

<div class="row g-3">
    <div class="col-lg-6">
        <label class="form-label">Label</label>
        <input type="text" name="label" class="form-control" value="{{ old('label', $item->label) }}" required>
    </div>
    <div class="col-lg-6">
        <label class="form-label">URL</label>
        <input type="text" name="url" class="form-control" value="{{ old('url', $item->url) }}" required>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Parent</label>
        <select name="parent_id" class="form-select">
            <option value="">Top level</option>
            @foreach ($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $item->parent_id) == $p->id)>{{ $p->label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck"
                @checked(old('is_active', $item->is_active ?? true))>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>
    </div>
    <div class="col-lg-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="open_new_tab" value="1" id="tabCheck"
                @checked(old('open_new_tab', $item->open_new_tab ?? false))>
            <label class="form-check-label" for="tabCheck">New Tab</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
