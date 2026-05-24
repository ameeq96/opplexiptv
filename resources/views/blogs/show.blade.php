@extends('layouts.default')

@php
    use Illuminate\Support\Facades\Storage;
    $isRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);
    $shareLabel = __('messages.share');
    if ($shareLabel === 'messages.share') {
        $shareLabel = 'Copy link';
    }
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ v('css/blogs.css') }}">
@endpush

@section('jsonld')
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
    <section class="blogs-wrap {{ $isRtl ? 'rtl' : '' }}">
        <div class="auto-container">
            <div class="blog-breadcrumbs mb-3">
                <a href="{{ route('home') }}">{{ __('messages.nav_home') ?? __('messages.home') ?? 'Home' }}</a>
                <span>/</span>
                <a href="{{ route('blogs.index') }}">{{ __('messages.blogs') }}</a>
                <span>/</span>
                <span>{{ $currentTranslation->title }}</span>
            </div>

            <div class="blog-show-hero">
                @if ($blog->cover_image)
                    <img src="{{ asset(Storage::url($blog->cover_image)) }}" alt="{{ $currentTranslation->title }}">
                @else
                    <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $currentTranslation->title }}">
                @endif
            </div>

            <div class="blog-show-content">
                <h1>{{ $currentTranslation->title }}</h1>
                <div class="blog-card__meta">
                    <span>{{ optional($blog->published_at)->format('M d, Y') }}</span>
                    @if ($blog->reading_time)
                        <span>{{ __('messages.minutes_read', ['minutes' => $blog->reading_time]) }}</span>
                    @endif
                    @if ($blog->author)
                        <span>{{ $blog->author->name }}</span>
                    @endif
                </div>

                <div class="blog-share mb-3">
                    <button type="button" onclick="navigator.clipboard.writeText(window.location.href)">
                        {{ $shareLabel }}
                    </button>
                </div>

                <div class="blog-content-body">
                    {!! $currentTranslation->content !!}
                </div>

                <a class="btn btn-link px-0 mt-3" href="{{ route('blogs.index') }}">
                    {{ __('messages.back_to_blogs') }}
                </a>
            </div>

            @if ($related->count())
                <div class="blog-related">
                    <h3>{{ __('messages.related_posts') }}</h3>
                    <div class="blog-grid">
                        @foreach ($related as $rel)
                            @php
                                $translation = $rel->translation();
                                $slug = $translation?->slug ?? $rel->translations->first()?->slug;
                            @endphp
                            <article class="blog-card">
                                <a href="{{ $slug ? route('blogs.show', $slug) : '#' }}">
                                    @if ($rel->cover_image)
                                        <img src="{{ asset(Storage::url($rel->cover_image)) }}" alt="{{ $translation?->title }}">
                                    @else
                                        <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $translation?->title }}">
                                    @endif
                                </a>
                                <div class="blog-card__body">
                                    <h3>{{ $translation?->title }}</h3>
                                    <div class="blog-card__meta">
                                        <span>{{ optional($rel->published_at)->format('M d, Y') }}</span>
                                        @if ($rel->reading_time)
                                            <span>{{ __('messages.minutes_read', ['minutes' => $rel->reading_time]) }}</span>
                                        @endif
                                    </div>
                                    <a class="btn btn-link px-0" href="{{ $slug ? route('blogs.show', $slug) : '#' }}">
                                        {{ __('messages.read_more') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
