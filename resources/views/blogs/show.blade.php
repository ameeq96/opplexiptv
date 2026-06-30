@extends('layouts.default')

@php
    use Illuminate\Support\Facades\Storage;
    $isRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);

    $shareLabel = __('messages.share');
    if ($shareLabel === 'messages.share') {
        $shareLabel = 'Copy link';
    }

    $articleCategory = $blog->relationLoaded('categories')
        ? optional($blog->categories->first())->translation()
        : optional($blog->categories()->first())->translation();

    $authorName = $blog->author?->name;
    $authorInitials = $authorName
        ? collect(preg_split('/\s+/', trim($authorName)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('')
        : null;

    // Share targets
    $shareUrl = $pageCanonical ?? url()->current();
    $encUrl = rawurlencode($shareUrl);
    $encTitle = rawurlencode($currentTranslation->title);
    $shareLinks = [
        'x'  => "https://x.com/intent/tweet?url={$encUrl}&text={$encTitle}",
        'fb' => "https://www.facebook.com/sharer/sharer.php?u={$encUrl}",
        'in' => "https://www.linkedin.com/sharing/share-offsite/?url={$encUrl}",
        'wa' => "https://api.whatsapp.com/send?text={$encTitle}%20{$encUrl}",
    ];
@endphp

@push('styles')
    @vite('resources/css/blogs.css')
@endpush

@section('jsonld')
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    <div class="blog-progress" aria-hidden="true"><span class="blog-progress__bar" id="blogProgressBar"></span></div>

    <section class="blogs-wrap blog-single {{ $isRtl ? 'rtl' : '' }}">
        <div class="auto-container">

            <nav class="blog-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">{{ __('messages.nav_home') ?? __('messages.home') ?? 'Home' }}</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
                <a href="{{ route('blogs.index') }}">{{ __('messages.blogs') }}</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
                <span aria-current="page">{{ $currentTranslation->title }}</span>
            </nav>

            <article class="blog-article">
                <header class="blog-article__header">
                    @if ($articleCategory)
                        <span class="blog-eyebrow">{{ $articleCategory->title }}</span>
                    @endif

                    <h1 class="blog-article__title">{{ $currentTranslation->title }}</h1>

                    @if ($currentTranslation->excerpt)
                        <p class="blog-article__dek">{{ $currentTranslation->excerpt }}</p>
                    @endif

                    <div class="blog-article__meta">
                        @if ($authorName)
                            <span class="blog-meta__author">
                                <span class="blog-avatar" aria-hidden="true">{{ $authorInitials }}</span>
                                {{ $authorName }}
                            </span>
                            <span class="blog-meta__sep" aria-hidden="true"></span>
                        @endif
                        <span class="blog-meta__item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4.5" width="18" height="17" rx="2" />
                                <path d="M3 9h18M8 2.5v4M16 2.5v4" />
                            </svg>
                            {{ optional($blog->published_at)->format('M d, Y') }}
                        </span>
                        @if ($blog->reading_time)
                            <span class="blog-meta__sep" aria-hidden="true"></span>
                            <span class="blog-meta__item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                                {{ __('messages.minutes_read', ['minutes' => $blog->reading_time]) }}
                            </span>
                        @endif
                        @if ($blog->views)
                            <span class="blog-meta__sep" aria-hidden="true"></span>
                            <span class="blog-meta__item" aria-label="{{ number_format($blog->views) }} views">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                {{ number_format($blog->views) }}
                            </span>
                        @endif
                    </div>
                </header>

                <figure class="blog-article__cover">
                    @if ($blog->cover_image)
                        <img src="{{ asset(Storage::url($blog->cover_image)) }}" alt="{{ $currentTranslation->title }}"
                            decoding="async">
                    @else
                        <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $currentTranslation->title }}"
                            decoding="async">
                    @endif
                </figure>

                <div class="blog-article__layout">
                    <aside class="blog-share-rail" aria-label="{{ $shareLabel }}">
                        <span class="blog-share-rail__label">Share</span>
                        <a class="blog-share-btn" data-net="x" href="{{ $shareLinks['x'] }}" target="_blank"
                            rel="noopener nofollow" aria-label="Share on X">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.66l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                        </a>
                        <a class="blog-share-btn" data-net="fb" href="{{ $shareLinks['fb'] }}" target="_blank"
                            rel="noopener nofollow" aria-label="Share on Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                        </a>
                        <a class="blog-share-btn" data-net="in" href="{{ $shareLinks['in'] }}" target="_blank"
                            rel="noopener nofollow" aria-label="Share on LinkedIn">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0z" /></svg>
                        </a>
                        <a class="blog-share-btn" data-net="wa" href="{{ $shareLinks['wa'] }}" target="_blank"
                            rel="noopener nofollow" aria-label="Share on WhatsApp">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12.05 21.785h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884zm8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" /></svg>
                        </a>
                        <button class="blog-share-btn blog-share-btn--copy" type="button" id="blogCopyBtn"
                            aria-label="{{ $shareLabel }}">
                            <svg class="blog-share-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1" />
                                <path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1" />
                            </svg>
                            <svg class="blog-share-btn__check" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </button>
                    </aside>

                    <div class="blog-content-body">
                        {!! $currentTranslation->content !!}
                    </div>
                </div>

                @if ($authorName)
                    <div class="blog-author-card">
                        <span class="blog-avatar" aria-hidden="true">{{ $authorInitials }}</span>
                        <div class="blog-author-card__body">
                            <span class="blog-author-card__role">Written by</span>
                            <span class="blog-author-card__name">{{ $authorName }}</span>
                        </div>
                    </div>
                @endif

                <div class="blog-article__footer">
                    <a class="blog-back" href="{{ route('blogs.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M11 6l-6 6 6 6" />
                        </svg>
                        {{ __('messages.back_to_blogs') }}
                    </a>

                    <div class="blog-share">
                        <button type="button"
                            onclick="(function(b){navigator.clipboard.writeText(window.location.href).then(function(){var o=b.querySelector('.js-copy-text');if(o){var t=o.dataset.label;o.textContent='Copied!';b.classList.add('is-copied');setTimeout(function(){o.textContent=t;b.classList.remove('is-copied');},1800);}});})(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1" />
                                <path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1" />
                            </svg>
                            <span class="js-copy-text" data-label="{{ $shareLabel }}">{{ $shareLabel }}</span>
                        </button>
                    </div>
                </div>
            </article>

            @if ($related->count())
                <div class="blog-related">
                    <div class="blog-section-head">
                        <div>
                            <span class="blog-eyebrow">{{ __('messages.blog.heading') }}</span>
                            <h2>{{ __('messages.related_posts') }}</h2>
                        </div>
                    </div>

                    <div class="blog-grid">
                        @foreach ($related as $rel)
                            @include('blogs._card', ['blog' => $rel])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('script')
    <script>
        (function () {
            // Reading progress bar
            var bar = document.getElementById('blogProgressBar');
            var article = document.querySelector('.blog-article');
            if (bar && article) {
                var ticking = false;
                var update = function () {
                    var rect = article.getBoundingClientRect();
                    var total = article.offsetHeight - window.innerHeight;
                    var scrolled = Math.min(Math.max(-rect.top, 0), Math.max(total, 0));
                    bar.style.width = (total > 0 ? (scrolled / total) * 100 : 0) + '%';
                    ticking = false;
                };
                var onScroll = function () {
                    if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                window.addEventListener('resize', onScroll);
                update();
            }

            // Copy link (share rail)
            var copyBtn = document.getElementById('blogCopyBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    navigator.clipboard.writeText(window.location.href).then(function () {
                        copyBtn.classList.add('is-copied');
                        setTimeout(function () { copyBtn.classList.remove('is-copied'); }, 1800);
                    });
                });
            }
        })();
    </script>
@endsection
