@extends('admin.layouts.app')

@section('title', 'Landing Experience')
@section('page_title', 'Landing Experience')

@section('content')
    <div class="admin-card mb-4">
        <h2 class="h4">Factual updates, genuine feedback and tested guides</h2>
        <p class="text-muted">No incidents, media or reviews are pre-filled. An empty status feed does not prove that a service is healthy. Publish only updates you can substantiate; no uptime, rights or performance guarantees are generated.</p>
        <form method="GET" action="{{ route('admin.landing-experience.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
            <div><label for="experience-brand" class="form-label">Brand</label><select id="experience-brand" name="brand" class="form-select"><option value="">All brands</option>@foreach ($brands as $key => $name)<option value="{{ $key }}" @selected($brand === $key)>{{ $name }}</option>@endforeach</select></div>
            <button class="btn btn-outline-primary" type="submit">Filter records</button>
        </form>
    </div>

    <section class="admin-card mb-4" aria-labelledby="experience-incidents">
        <h2 class="h4" id="experience-incidents">Service incidents</h2>
        <p class="text-muted">Describe the affected brand and language, what is known, and the actual start/resolution times. Dates use {{ config('app.timezone') }}. Resolve an update explicitly; do not infer service status from the absence of reports. Latest 50 records are shown.</p>
        @foreach (collect([new \App\Models\ServiceIncident()])->concat($incidents) as $incident)
            <details class="border rounded p-3 mb-3">
                <summary class="fw-semibold">{{ $incident->exists ? '#'.$incident->id.' — '.$incident->title.' / '.$incident->state : 'Add an incident update' }}</summary>
                <form method="POST" action="{{ $incident->exists ? route('api.browser.admin.landing-experience.incidents.update', $incident) : route('api.browser.admin.landing-experience.incidents.store') }}" data-api-form class="row g-3 mt-1">
                    @csrf
                    @if ($incident->exists) @method('PUT') @endif
                    <div class="col-lg-6"><label class="form-label" for="incident-brand-{{ $incident->id ?? 'new' }}">Affected brand</label><select class="form-select" id="incident-brand-{{ $incident->id ?? 'new' }}" name="brand" required>@foreach ($brands as $key => $name)<option value="{{ $key }}" @selected(($incident->brand ?? $brand) === $key)>{{ $name }}</option>@endforeach</select></div>
                    <div class="col-lg-6"><label class="form-label" for="incident-locale-{{ $incident->id ?? 'new' }}">Language</label><select class="form-select" id="incident-locale-{{ $incident->id ?? 'new' }}" name="locale" required>@foreach ($locales as $locale)<option value="{{ $locale }}" @selected(($incident->locale ?? 'en') === $locale)>{{ strtoupper($locale) }}</option>@endforeach</select></div>
                    <div class="col-lg-12"><label class="form-label" for="incident-title-{{ $incident->id ?? 'new' }}">Update title</label><input class="form-control" id="incident-title-{{ $incident->id ?? 'new' }}" name="title" maxlength="160" value="{{ $incident->title }}" required></div>
                    <div class="col-lg-12"><label class="form-label" for="incident-body-{{ $incident->id ?? 'new' }}">Verified update</label><textarea class="form-control" id="incident-body-{{ $incident->id ?? 'new' }}" name="body" maxlength="4000" rows="3" required>{{ $incident->body }}</textarea></div>
                    <div class="col-lg-4"><label class="form-label" for="incident-state-{{ $incident->id ?? 'new' }}">State</label><select class="form-select" id="incident-state-{{ $incident->id ?? 'new' }}" name="state" required>@foreach (\App\Models\ServiceIncident::STATES as $state)<option value="{{ $state }}" @selected($incident->state === $state)>{{ ucfirst($state) }}</option>@endforeach</select></div>
                    <div class="col-lg-4"><label class="form-label" for="incident-start-{{ $incident->id ?? 'new' }}">Actual start</label><input class="form-control" id="incident-start-{{ $incident->id ?? 'new' }}" type="datetime-local" name="started_at" value="{{ $incident->started_at?->format('Y-m-d\TH:i') }}" required></div>
                    <div class="col-lg-4"><label class="form-label" for="incident-end-{{ $incident->id ?? 'new' }}">Resolved at (resolved only)</label><input class="form-control" id="incident-end-{{ $incident->id ?? 'new' }}" type="datetime-local" name="resolved_at" value="{{ $incident->resolved_at?->format('Y-m-d\TH:i') }}"></div>
                    <div class="col-lg-12"><input type="hidden" name="is_published" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked($incident->is_published)><span class="form-check-label">Publish this factual incident update</span></label><button class="btn btn-primary mt-2" type="submit">Save incident</button></div>
                </form>
            </details>
        @endforeach
    </section>

    <section class="admin-card mb-4" aria-labelledby="experience-media">
        <h2 class="h4" id="experience-media">Authentic setup media</h2>
        <p class="text-muted">Add only your existing, owned screenshots/videos hosted on the main website under /images/ or /storage/. Supply the actual device/player/version, test date, private evidence reference and publication permission. This screen does not upload files or manufacture testing evidence. Latest 50 records are shown.</p>
        @foreach (collect([new \App\Models\SetupMedia()])->concat($media) as $entry)
            <details class="border rounded p-3 mb-3">
                <summary class="fw-semibold">{{ $entry->exists ? '#'.$entry->id.' — '.$entry->title : 'Register genuine setup media' }}</summary>
                <form method="POST" action="{{ $entry->exists ? route('api.browser.admin.landing-experience.media.update', $entry) : route('api.browser.admin.landing-experience.media.store') }}" data-api-form class="row g-3 mt-1">
                    @csrf
                    @if ($entry->exists) @method('PUT') @endif
                    <div class="col-lg-6"><label class="form-label" for="media-brand-{{ $entry->id ?? 'new' }}">Brand</label><select class="form-select" id="media-brand-{{ $entry->id ?? 'new' }}" name="brand" required>@foreach ($brands as $key => $name)<option value="{{ $key }}" @selected(($entry->brand ?? $brand) === $key)>{{ $name }}</option>@endforeach</select></div>
                    <div class="col-lg-6"><label class="form-label" for="media-locale-{{ $entry->id ?? 'new' }}">Language</label><select class="form-select" id="media-locale-{{ $entry->id ?? 'new' }}" name="locale" required>@foreach ($locales as $locale)<option value="{{ $locale }}" @selected(($entry->locale ?? 'en') === $locale)>{{ strtoupper($locale) }}</option>@endforeach</select></div>
                    @foreach (['guide_slug' => ['Existing page slug (for example setup)', 160], 'title' => ['Tutorial title', 160], 'media_url' => ['Existing owned HTTPS asset URL', 500], 'alt_text' => ['Accessible image/video description', 240], 'device_model' => ['Actual device model', 160], 'player_name' => ['Player tested', 120], 'player_version' => ['Actual player version', 80], 'evidence_reference' => ['Private evidence reference (never public)', 191]] as $field => [$label, $max])
                        <div class="col-lg-6"><label class="form-label" for="media-{{ $field }}-{{ $entry->id ?? 'new' }}">{{ $label }}</label><input class="form-control" id="media-{{ $field }}-{{ $entry->id ?? 'new' }}" name="{{ $field }}" maxlength="{{ $max }}" value="{{ $entry->{$field} }}" required></div>
                    @endforeach
                    <div class="col-lg-12"><label class="form-label" for="media-description-{{ $entry->id ?? 'new' }}">What the media actually demonstrates</label><textarea class="form-control" id="media-description-{{ $entry->id ?? 'new' }}" name="description" maxlength="2000" rows="3" required>{{ $entry->description }}</textarea></div>
                    <div class="col-lg-6"><label class="form-label" for="media-kind-{{ $entry->id ?? 'new' }}">Media type</label><select class="form-select" id="media-kind-{{ $entry->id ?? 'new' }}" name="kind"><option value="image" @selected($entry->kind === 'image')>Image</option><option value="video" @selected($entry->kind === 'video')>Video</option></select></div>
                    <div class="col-lg-6"><label class="form-label" for="media-tested-{{ $entry->id ?? 'new' }}">Actually tested at ({{ config('app.timezone') }})</label><input class="form-control" id="media-tested-{{ $entry->id ?? 'new' }}" type="datetime-local" name="tested_at" value="{{ $entry->tested_at?->format('Y-m-d\TH:i') }}" required></div>
                    <div class="col-lg-12"><input type="hidden" name="publication_permission" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="publication_permission" value="1" @checked($entry->publication_permission_at)><span class="form-check-label">Ownership and publication permission are recorded in the private evidence</span></label><input type="hidden" name="is_published" value="0"><label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked($entry->is_published)><span class="form-check-label">Publish this tested tutorial media</span></label><button class="btn btn-primary mt-2" type="submit">Save media record</button></div>
                </form>
            </details>
        @endforeach
    </section>

    <section class="admin-card" aria-labelledby="experience-reviews">
        <h2 class="h4" id="experience-reviews">Customer purchase feedback</h2>
        <p class="text-muted">Reviews are submitted privately from an authenticated customer's owned production-paid order. Publish only with current permission and valid brand attribution. Do not edit customer wording or select only positive feedback. Legacy testimonials are not imported or relabelled. Latest 100 records are shown.</p>
        @forelse ($reviews as $review)
            <article class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap gap-2 justify-content-between"><h3 class="h6">{{ $review->display_name }} — {{ $review->rating }}/5</h3><span class="badge text-bg-secondary">{{ $review->status }}</span></div>
                <p>{{ $review->body }}</p><p class="small text-muted">{{ $brands[$review->brand] ?? $review->brand }} / {{ strtoupper($review->locale) }} · private order #{{ $review->order_id }} · publication permission {{ $review->publication_consented_at && !$review->withdrawn_at ? 'recorded' : 'not current' }}</p>
                <form method="POST" action="{{ route('api.browser.admin.landing-experience.reviews.moderate', $review) }}" data-api-form class="row g-2">
                    @csrf @method('PATCH')
                    <div class="col-lg-8"><label class="form-label" for="review-note-{{ $review->id }}">Private moderation reason</label><input class="form-control" id="review-note-{{ $review->id }}" name="moderation_note" maxlength="2000" value="{{ $review->moderation_note }}"></div>
                    <div class="col-lg-4"><label class="form-label" for="review-action-{{ $review->id }}">Moderation action</label><select class="form-select" id="review-action-{{ $review->id }}" name="action"><option value="reject">Do not publish / unpublish</option><option value="publish">Publish with current permission</option></select></div>
                    <div class="col-lg-12"><button class="btn btn-outline-primary" type="submit">Save moderation</button></div>
                </form>
            </article>
        @empty
            <p class="text-muted">No customer reviews have been submitted. No sample reviews or ratings will be shown to visitors.</p>
        @endforelse
    </section>
@endsection
