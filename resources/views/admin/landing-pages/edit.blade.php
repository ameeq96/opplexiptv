@extends('admin.layouts.app')

@section('title', 'Landing Page Content')
@section('page_title', 'Landing Page Content')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Landing pages</li>
@endsection

@section('content')
    <div class="admin-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="badge text-bg-light mb-2">CONTENT &amp; CATALOG</span>
                <h2 class="h4 mb-2">One place for your landing page content</h2>
                <p class="text-muted mb-0">Edit one brand at a time. Prices stay in Packages; these fields only control the landing page headings, guides, FAQs and helpful highlights.</p>
            </div>
            <span class="badge {{ $section['is_published'] ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $section['is_published'] ? 'Published' : 'Draft' }}</span>
        </div>
        <nav class="nav nav-pills gap-2 mt-4" aria-label="Landing page brands">
            @foreach ($brands as $brandKey => $brandName)
                <a class="nav-link {{ $brandKey === $brand ? 'active' : '' }}" href="{{ route('admin.landing-pages.edit', ['brand' => $brandKey, 'locale' => $locale]) }}" @if ($brandKey === $brand) aria-current="page" @endif>{{ $brandName }}</a>
            @endforeach
        </nav>
        <form method="GET" action="{{ route('admin.landing-pages.edit', ['brand' => $brand]) }}" class="d-flex flex-wrap align-items-end gap-2 mt-3">
            <div>
                <label for="landing-content-locale" class="form-label">Content language</label>
                <select id="landing-content-locale" name="locale" class="form-select" style="min-width: 12rem">
                    @foreach (['en' => 'English', 'es' => 'Español', 'fr' => 'Français', 'it' => 'Italiano', 'nl' => 'Nederlands', 'pt' => 'Português', 'ru' => 'Русский', 'ur' => 'اردو', 'ar' => 'العربية', 'hi' => 'हिन्दी'] as $languageCode => $languageName)
                        <option value="{{ $languageCode }}" @selected($locale === $languageCode)>{{ $languageName }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-outline-primary">Open language</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.landing-pages.update', ['brand' => $brand]) }}" lang="{{ $locale }}" @if (in_array($locale, ['ar', 'ur'], true)) dir="rtl" @endif>
        @csrf
        @method('PUT')
        <input type="hidden" name="locale" value="{{ $locale }}">

        <div class="admin-card mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="h5 mb-0">{{ $brands[$brand] }} <span class="text-muted">/ {{ strtoupper($locale) }}</span></h3>
                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary btn-sm">Manage package prices</a>
            </div>
            <div class="alert alert-info mb-4">Use plain, factual text. Do not add unsupported channel counts, quality guarantees, free trials, reviews or rights claims. HTML, scripts and embedded widgets are not accepted.</div>
            <div class="row g-3">
                <div class="col-lg-12">
                    <label class="form-label" for="landing-hero-heading">Main heading</label>
                    <input id="landing-hero-heading" name="hero_heading" class="form-control" maxlength="160" value="{{ old('hero_heading', $section['hero_heading']) }}" required>
                </div>
                <div class="col-lg-12">
                    <label class="form-label" for="landing-hero-description">Intro below the main heading</label>
                    <textarea id="landing-hero-description" name="hero_description" class="form-control" rows="3" maxlength="1200" required>{{ old('hero_description', $section['hero_description']) }}</textarea>
                </div>
                <div class="col-lg-12">
                    <label class="form-label" for="landing-guide-heading">Guide heading</label>
                    <input id="landing-guide-heading" name="guide_heading" class="form-control" maxlength="160" value="{{ old('guide_heading', $section['guide_heading']) }}" required>
                </div>
                <div class="col-lg-12">
                    <label class="form-label" for="landing-guide-intro">Guide introduction</label>
                    <textarea id="landing-guide-intro" name="guide_intro" class="form-control" rows="4" maxlength="2000" required>{{ old('guide_intro', $section['guide_intro']) }}</textarea>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <h3 class="h5">Guide sections</h3>
            <p class="text-muted">Up to eight sections, in the order shown. Keep at least one complete section. Leave both fields empty to remove a section.</p>
            @for ($index = 0; $index < 8; $index++)
                @php $entry = $section['guide_sections'][$index] ?? ['heading' => '', 'body' => '']; @endphp
                <details class="border rounded p-3 mb-3" @if ($index === 0 || $errors->has("guide_sections.$index.*")) open @endif>
                    <summary class="fw-semibold">Section {{ $index + 1 }}@if ($entry['heading']) — {{ $entry['heading'] }}@else <span class="text-muted"> / unused</span>@endif</summary>
                    <div class="mt-3">
                        <label class="form-label" for="landing-section-heading-{{ $index }}">Section heading</label>
                        <input id="landing-section-heading-{{ $index }}" name="guide_sections[{{ $index }}][heading]" class="form-control" maxlength="160" value="{{ old("guide_sections.$index.heading", $entry['heading']) }}">
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="landing-section-body-{{ $index }}">Section text</label>
                        <textarea id="landing-section-body-{{ $index }}" name="guide_sections[{{ $index }}][body]" class="form-control" rows="5" maxlength="4000">{{ old("guide_sections.$index.body", $entry['body']) }}</textarea>
                    </div>
                </details>
            @endfor
        </div>

        <div class="admin-card mb-4">
            <h3 class="h5">Brand FAQs</h3>
            <p class="text-muted">Answers for this brand only, not copied from another provider. Leave both fields empty to remove a question.</p>
            @for ($index = 0; $index < 8; $index++)
                @php $entry = $section['faqs'][$index] ?? ['question' => '', 'answer' => '']; @endphp
                <details class="border rounded p-3 mb-3" @if ($index === 0 || $errors->has("faqs.$index.*")) open @endif>
                    <summary class="fw-semibold">Question {{ $index + 1 }}@if ($entry['question']) — {{ $entry['question'] }}@else <span class="text-muted"> / unused</span>@endif</summary>
                    <div class="mt-3">
                        <label class="form-label" for="landing-question-{{ $index }}">Question</label>
                        <input id="landing-question-{{ $index }}" name="faqs[{{ $index }}][question]" class="form-control" maxlength="240" value="{{ old("faqs.$index.question", $entry['question']) }}">
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="landing-answer-{{ $index }}">Answer</label>
                        <textarea id="landing-answer-{{ $index }}" name="faqs[{{ $index }}][answer]" class="form-control" rows="4" maxlength="2000">{{ old("faqs.$index.answer", $entry['answer']) }}</textarea>
                    </div>
                </details>
            @endfor
        </div>

        <div class="admin-card mb-4">
            <h3 class="h5">Helpful highlights</h3>
            <p class="text-muted">Short buying or setup guidance, not unverified service promises. Blank items are hidden.</p>
            <div class="row g-3">
                @for ($index = 0; $index < 6; $index++)
                    <div class="col-lg-6">
                        <label class="form-label" for="landing-highlight-{{ $index }}">Highlight {{ $index + 1 }}</label>
                        <input id="landing-highlight-{{ $index }}" name="highlights[{{ $index }}]" class="form-control" maxlength="180" value="{{ old("highlights.$index", $section['highlights'][$index] ?? '') }}">
                    </div>
                @endfor
            </div>
        </div>

        <div class="admin-card">
            <input type="hidden" name="is_published" value="0">
            <div class="form-check mb-2">
                <input id="landing-published" name="is_published" value="1" type="checkbox" class="form-check-input" @checked(old('is_published', $section['is_published']))>
                <label class="form-check-label fw-semibold" for="landing-published">Publish this brand's content through the landing API</label>
            </div>
            <p class="text-muted small">Unpublishing hides this custom API content; it does not disable plans, payments or the landing page's static guide.</p>
            <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
                <button type="submit" class="btn btn-primary">Save {{ $brands[$brand] }} content</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.landing-pages.edit', ['brand' => $brand, 'locale' => $locale]) }}">Reload saved version</a>
            </div>
        </div>
    </form>
@endsection
