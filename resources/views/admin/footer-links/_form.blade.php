@csrf

<div class="row g-3">
    <div class="col-lg-3">
        <label class="form-label">Group</label>
        <select name="group" class="form-select" required>
            @foreach (['explore','company','legal','deeplink'] as $g)
                <option value="{{ $g }}" @selected(old('group', $link->group) === $g)>{{ ucfirst($g) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4">
        <label class="form-label">Label</label>
        <input type="text" name="label" class="form-control" value="{{ old('label', $link->label) }}" required>
    </div>
    <div class="col-lg-5">
        <label class="form-label">URL</label>
        <input type="text" name="url" class="form-control" value="{{ old('url', $link->url) }}" required>
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

@if (!empty($locales))
    <div class="mt-4">
        <h5 class="mb-3">Translations</h5>
        <ul class="nav nav-tabs" role="tablist">
            @foreach ($locales as $locale)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab"
                        data-bs-target="#fl-{{ $locale }}" type="button" role="tab">
                        {{ strtoupper($locale) }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content border border-top-0 p-3">
            @foreach ($locales as $locale)
                @php $t = $link->translation($locale); @endphp
                <div class="tab-pane fade @if ($loop->first) show active @endif" id="fl-{{ $locale }}" role="tabpanel">
                    <label class="form-label">Label ({{ strtoupper($locale) }})</label>
                    <input type="text" name="translations[{{ $locale }}][label]" class="form-control"
                        value="{{ old("translations.$locale.label", $t?->label) }}">
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.footer-links.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
