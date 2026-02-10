@csrf

<div class="row g-3">
    <div class="col-lg-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
            <option value="iptv" @selected(old('type', $package->type) === 'iptv')>IPTV</option>
            <option value="reseller" @selected(old('type', $package->type) === 'reseller')>Reseller</option>
        </select>
    </div>
    <div class="col-lg-3">
        <label class="form-label">Vendor</label>
        <select name="vendor" class="form-select" required>
            <option value="opplex" @selected(old('vendor', $package->vendor) === 'opplex')>Opplex</option>
            <option value="starshare" @selected(old('vendor', $package->vendor) === 'starshare')>Starshare</option>
        </select>
    </div>
    <div class="col-lg-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $package->title) }}" required>
    </div>

    <div class="col-lg-6">
        <label class="form-label">Display Price (e.g. $2.99 / 1 month)</label>
        <input type="text" name="display_price" class="form-control" value="{{ old('display_price', $package->display_price) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Price Amount</label>
        <input type="number" step="0.01" min="0" name="price_amount" class="form-control" value="{{ old('price_amount', $package->price_amount) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Duration Months (IPTV)</label>
        <input type="number" min="1" name="duration_months" class="form-control" value="{{ old('duration_months', $package->duration_months) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Credits (Reseller)</label>
        <input type="number" min="1" name="credits" class="form-control" value="{{ old('credits', $package->credits) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Sort Order</label>
        <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $package->sort_order ?? 0) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Active</label>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="active" value="1" id="activeCheck"
                @checked(old('active', $package->active ?? true))>
            <label class="form-check-label" for="activeCheck">Enabled</label>
        </div>
    </div>
    <div class="col-lg-6">
        <label class="form-label">Features (one per line)</label>
        <textarea name="features" class="form-control" rows="4">{{ old('features', is_array($package->features) ? implode("\n", $package->features) : '') }}</textarea>
    </div>
    <div class="col-lg-6">
        <label class="form-label">Icons (Reseller, one per line)</label>
        <textarea name="icons" class="form-control" rows="4">{{ old('icons', is_array($package->icons) ? implode("\n", $package->icons) : '') }}</textarea>
        <div class="small text-muted mt-1">Example: images/icons/service-1.svg</div>
    </div>
    <div class="col-lg-6">
        <label class="form-label">Icon (single)</label>
        <input type="text" name="icon" class="form-control" value="{{ old('icon', $package->icon) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Button Link</label>
        <input type="text" name="button_link" class="form-control" value="{{ old('button_link', $package->button_link) }}">
    </div>
    <div class="col-lg-3">
        <label class="form-label">Delay</label>
        <input type="text" name="delay" class="form-control" value="{{ old('delay', $package->delay) }}">
    </div>
</div>

@if (!empty($locales))
    <div class="mt-4">
        <h5 class="mb-3">Translations</h5>
        <ul class="nav nav-tabs" role="tablist">
            @foreach ($locales as $locale)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab"
                        data-bs-target="#pkg-{{ $locale }}" type="button" role="tab">
                        {{ strtoupper($locale) }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content border border-top-0 p-3">
            @foreach ($locales as $locale)
                @php $t = $package->translation($locale); @endphp
                <div class="tab-pane fade @if ($loop->first) show active @endif" id="pkg-{{ $locale }}" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label">Title ({{ strtoupper($locale) }})</label>
                        <input type="text" name="translations[{{ $locale }}][title]" class="form-control"
                            value="{{ old("translations.$locale.title", $t?->title) }}">
                    </div>
                    <div>
                        <label class="form-label">Features ({{ strtoupper($locale) }})</label>
                        <textarea name="translations[{{ $locale }}][features]" class="form-control" rows="4">{{ old("translations.$locale.features", is_array($t?->features) ? implode("\n", $t->features) : '') }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
