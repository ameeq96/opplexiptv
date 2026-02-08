@php
    $locales = $locales ?? admin_locales();
@endphp

<div class="row g-3">
    <div class="col-lg-4">
        <div class="admin-card">
            <h5 class="mb-3">General</h5>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="draft" @selected(old('status', $blog->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $blog->status ?? '') === 'published')>Published</option>
                    <option value="archived" @selected(old('status', $blog->status ?? '') === 'archived')>Archived</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Published At</label>
                <input type="datetime-local" name="published_at" class="form-control"
                    value="{{ old('published_at', optional($blog->published_at ?? null)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Reading Time (min)</label>
                <input type="number" name="reading_time" class="form-control" value="{{ old('reading_time', $blog->reading_time ?? null) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Featured</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $blog->is_featured ?? false))>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Author ID</label>
                <input type="number" name="author_id" class="form-control" value="{{ old('author_id', $blog->author_id ?? null) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Cover Image</label>
                <input type="file" name="cover_image" class="form-control">
                @if (!empty($blog->cover_image))
                    <div class="small text-muted mt-1">{{ $blog->cover_image }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="mb-3">Translations</h5>
            <ul class="nav nav-tabs" role="tablist">
                @foreach ($locales as $idx => $locale)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($idx === 0) active @endif" data-bs-toggle="tab"
                            data-bs-target="#tab-{{ $locale }}" type="button" role="tab">
                            {{ strtoupper($locale) }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content border border-top-0 p-3">
                @foreach ($locales as $idx => $locale)
                    @php
                        $translation = $blog->translations->firstWhere('locale', $locale) ?? null;
                        $schemaValue = $translation?->schema_json ? json_encode($translation->schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
                    @endphp
                    <div class="tab-pane fade @if($idx === 0) show active @endif" id="tab-{{ $locale }}" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="translations[{{ $locale }}][title]" class="form-control"
                                    data-slug-target="#slug-{{ $locale }}"
                                    value="{{ old("translations.$locale.title", $translation?->title) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" id="slug-{{ $locale }}" data-slug-input="1"
                                    name="translations[{{ $locale }}][slug]" class="form-control"
                                    value="{{ old("translations.$locale.slug", $translation?->slug) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Excerpt</label>
                                <textarea name="translations[{{ $locale }}][excerpt]" class="form-control" rows="2">{{ old("translations.$locale.excerpt", $translation?->excerpt) }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Content</label>
                                <textarea name="translations[{{ $locale }}][content]" class="form-control" rows="6">{{ old("translations.$locale.content", $translation?->content) }}</textarea>
                            </div>

                            <div class="accordion" id="seo-{{ $locale }}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="seo-heading-{{ $locale }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#seo-collapse-{{ $locale }}">
                                            SEO Fields
                                        </button>
                                    </h2>
                                    <div id="seo-collapse-{{ $locale }}" class="accordion-collapse collapse" data-bs-parent="#seo-{{ $locale }}">
                                        <div class="accordion-body row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">SEO Title</label>
                                                <input type="text" name="translations[{{ $locale }}][seo_title]" class="form-control" value="{{ old("translations.$locale.seo_title", $translation?->seo_title) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">SEO Description</label>
                                                <input type="text" name="translations[{{ $locale }}][seo_description]" class="form-control" value="{{ old("translations.$locale.seo_description", $translation?->seo_description) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">SEO Keywords</label>
                                                <input type="text" name="translations[{{ $locale }}][seo_keywords]" class="form-control" value="{{ old("translations.$locale.seo_keywords", $translation?->seo_keywords) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion mt-3" id="og-{{ $locale }}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="og-heading-{{ $locale }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#og-collapse-{{ $locale }}">
                                            Open Graph
                                        </button>
                                    </h2>
                                    <div id="og-collapse-{{ $locale }}" class="accordion-collapse collapse" data-bs-parent="#og-{{ $locale }}">
                                        <div class="accordion-body row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">OG Title</label>
                                                <input type="text" name="translations[{{ $locale }}][og_title]" class="form-control" value="{{ old("translations.$locale.og_title", $translation?->og_title) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">OG Description</label>
                                                <input type="text" name="translations[{{ $locale }}][og_description]" class="form-control" value="{{ old("translations.$locale.og_description", $translation?->og_description) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">OG Image</label>
                                                <input type="file" name="translations[{{ $locale }}][og_image]" class="form-control">
                                                @if ($translation?->og_image)
                                                    <input type="hidden" name="translations[{{ $locale }}][existing_og_image]" value="{{ $translation->og_image }}">
                                                    <div class="small text-muted mt-1">{{ $translation->og_image }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Canonical URL</label>
                                    <input type="url" name="translations[{{ $locale }}][canonical_url]" class="form-control"
                                        value="{{ old("translations.$locale.canonical_url", $translation?->canonical_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Schema JSON</label>
                                    <textarea name="translations[{{ $locale }}][schema_json]" class="form-control" rows="2">{{ old("translations.$locale.schema_json", $schemaValue) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-outline-secondary" href="{{ route('admin.blogs.index') }}">Cancel</a>
</div>
