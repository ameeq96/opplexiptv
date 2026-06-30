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
    <section class="blogs-wrap {{ $isRtl ? 'rtl' : '' }}">
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

                <div class="blog-content-body">
                    {!! $currentTranslation->content !!}
                </div>

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
