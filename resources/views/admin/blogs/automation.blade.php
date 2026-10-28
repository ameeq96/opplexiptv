@extends('admin.layouts.app')

@section('title', 'Daily Blog Publishing')
@section('page_title', 'Daily Blog Publishing')

@section('content')
    <div class="admin-card mb-4">
        <h2 class="h4">Daily articles across your IPTV websites</h2>
        <p class="text-muted">The daily agent prepares one original article per enabled website with ten language versions. The planned time is 10:00 in Asia/Karachi. The computer and app must be running for the agent to work. Articles stay unpublished when checks or website publication fail.</p>
        <a class="btn btn-outline-primary" href="{{ route('admin.blogs.index') }}">Manage articles</a>
    </div>

    <section class="admin-card mb-4" aria-labelledby="blog-publishing-sites">
        <h2 class="h4" id="blog-publishing-sites">Website preferences</h2>
        @if (!$canManage)<p class="text-muted">Only the owner can pause, enable or retry daily publication.</p>@endif
        <div class="row g-3">
            @foreach ($settings as $setting)
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('api.browser.admin.blogs.automation.update', $setting->site_key) }}" data-api-form class="border rounded p-3 h-100">
                        @csrf @method('PATCH')
                        <h3 class="h6">{{ $sites[$setting->site_key]['name'] ?? $setting->site_key }}</h3>
                        <p class="small text-muted">{{ $sites[$setting->site_key]['domain'] ?? '' }} · {{ $setting->publish_time }} {{ $setting->timezone }} · {{ count($setting->locales ?? []) }} languages</p>
                        <input type="hidden" name="enabled" value="0">
                        <label class="form-check"><input class="form-check-input" type="checkbox" name="enabled" value="1" @checked($setting->enabled) @disabled(!$canManage)><span class="form-check-label">Enable daily articles for this website</span></label>
                        @if ($canManage)<button class="btn btn-primary mt-2" type="submit">Save preference</button>@endif
                    </form>
                </div>
            @endforeach
        </div>
    </section>

    <section class="admin-card" aria-labelledby="blog-publishing-runs">
        <h2 class="h4" id="blog-publishing-runs">Latest publication runs</h2>
        <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
            <label class="visually-hidden" for="blog-run-site">Website</label>
            <select id="blog-run-site" name="site_key" class="form-select w-auto"><option value="">All websites</option>@foreach ($sites as $key => $site)<option value="{{ $key }}" @selected($siteKey === $key)>{{ $site['name'] }}</option>@endforeach</select>
            <button class="btn btn-outline-primary" type="submit">Filter runs</button>
        </form>
        <p class="small text-muted">Latest 50 matching runs. A successful search-engine notification confirms receipt; it does not confirm indexing or ranking.</p>
        @forelse ($runs as $run)
            <article class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2"><h3 class="h6">#{{ $run->id }} · {{ $sites[$run->site_key]['name'] ?? $run->site_key }} · {{ $run->topic }}</h3><span class="badge text-bg-secondary">{{ $run->status }}</span></div>
                <p class="small text-muted">{{ $run->publishing_date?->format('Y-m-d') ?? 'Manual publication' }} · Attempts: {{ $run->attempts }} @if ($run->published_at) · Published {{ $run->published_at->format('Y-m-d H:i') }} {{ config('app.timezone') }} @endif</p>
                @if ($run->errors)<ul>@foreach ($run->errors as $error)<li>{{ is_string($error) ? $error : ($error['code'] ?? 'validation_failed') }}</li>@endforeach</ul>@endif
                @if ($run->deliveries->isNotEmpty())<ul class="small">@foreach ($run->deliveries as $delivery)<li>{{ $delivery->channel }}: {{ $delivery->status }} @if ($delivery->http_status) (HTTP {{ $delivery->http_status }}) @endif @if ($delivery->error_code) — {{ $delivery->error_code }} @endif</li>@endforeach</ul>@endif
                @if ($canManage && in_array($run->status, ['failed', 'blocked'], true))
                    <form method="POST" action="{{ route('api.browser.admin.blogs.automation.retry', $run) }}" data-api-form>@csrf<button class="btn btn-outline-primary" type="submit">Queue retry</button></form>
                @endif
            </article>
        @empty
            <p class="text-muted">No publication runs yet.</p>
        @endforelse
    </section>
@endsection
