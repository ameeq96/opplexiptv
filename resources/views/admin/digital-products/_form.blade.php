<div class="row g-3">
    <div class="col-lg-6">
        <label class="form-label">Title</label>
        <input class="form-control" name="title" value="{{ old('title', $product->title) }}" required data-slug-target="#productSlug">
    </div>
    <div class="col-lg-6">
        <label class="form-label">Slug</label>
        <input id="productSlug" data-slug-input class="form-control" name="slug" value="{{ old('slug', $product->slug) }}" required>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Category</label>
        <select class="form-select" name="digital_category_id">
            <option value="">None</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((int) old('digital_category_id', $product->digital_category_id) === (int) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Price</label>
        <input class="form-control" type="number" min="0" step="0.01" name="price" value="{{ old('price', $product->price) }}" required>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Compare Price</label>
        <input class="form-control" type="number" min="0" step="0.01" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}">
    </div>
    <div class="col-lg-4">
        <label class="form-label">Currency</label>
        <input class="form-control" name="currency" value="{{ old('currency', $product->currency ?: 'USD') }}" required>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Delivery Type</label>
        <select class="form-select" name="delivery_type" required>
            @foreach(['credential','code','link','file','manual'] as $type)
                <option value="{{ $type }}" @selected(old('delivery_type', $product->delivery_type ?: 'manual') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2">
        <label class="form-label">Min Qty</label>
        <input class="form-control" type="number" min="1" name="min_qty" value="{{ old('min_qty', $product->min_qty ?: 1) }}">
    </div>
    <div class="col-lg-2">
        <label class="form-label">Max Qty</label>
        <input class="form-control" type="number" min="1" name="max_qty" value="{{ old('max_qty', $product->max_qty) }}">
    </div>
    <div class="col-lg-2">
        <label class="form-label">Sort</label>
        <input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order ?: 0) }}">
    </div>
    <div class="col-lg-4">
        <label class="form-label">Image</label>
        <input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        @if($product->image)
            <div class="mt-2"><img src="{{ asset('images/digital-products/' . $product->image) }}" style="height:60px;border-radius:8px;"></div>
        @endif
    </div>
    <div class="col-12">
        <label class="form-label">Short Description</label>
        <input class="form-control" name="short_description" value="{{ old('short_description', $product->short_description) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Full Description</label>
        <textarea class="form-control" rows="5" name="full_description">{{ old('full_description', $product->full_description) }}</textarea>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Metadata (Region)</label>
        <input class="form-control" name="metadata[region]" value="{{ old('metadata.region', data_get($product->metadata, 'region')) }}">
    </div>
    <div class="col-lg-4">
        <label class="form-label">Metadata (Validity)</label>
        <input class="form-control" name="metadata[validity]" value="{{ old('metadata.validity', data_get($product->metadata, 'validity')) }}">
    </div>
    <div class="col-lg-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" @checked(old('is_active', $product->is_active ?? true))>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a href="{{ route('admin.digital-products.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
