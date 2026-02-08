@extends('layouts.default')

@php
    use Illuminate\Support\Facades\Storage;
    $isRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ v('css/blogs.css') }}">
@endpush

@section('content')
    <section class="blogs-wrap {{ $isRtl ? 'rtl' : '' }}">
        <div class="auto-container">
            <div class="blog-breadcrumbs mb-3">
                <a href="{{ route('home') }}">{{ __('messages.nav_home') ?? __('messages.home') ?? 'Home' }}</a>
                <span>/</span>
                <span>{{ __('messages.blogs') }}</span>
            </div>

            <div class="blog-hero">
                <h1>{{ __('messages.blogs') }}</h1>
                <p>{{ __('messages.latest_posts') }}</p>

                <div class="blog-filters" aria-label="Blog filters">
                    <a class="blog-filter-pill {{ $categorySlug === '' ? 'active' : '' }}"
                       href="{{ route('blogs.index', array_filter(['q' => $search ?: null])) }}">
                        All
                    </a>
                    @foreach ($categories as $cat)
                        @php $catT = $cat->translation(); @endphp
                        @if ($catT)
                            <a class="blog-filter-pill {{ $categorySlug === $catT->slug ? 'active' : '' }}"
                               href="{{ route('blogs.index', array_filter(['q' => $search ?: null, 'category' => $catT->slug])) }}">
                                {{ $catT->title }}
                            </a>
                        @endif
                    @endforeach
                </div>

                <form class="blog-search" method="GET" action="{{ route('blogs.index') }}" role="search">
                    <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('messages.search') }}" aria-label="{{ __('messages.search') }}">
                    <button type="submit">{{ __('messages.search') }}</button>
                </form>
            </div>

            @if ($featured)
                @php $featuredTranslation = $featured->translation(); @endphp
                <div class="blog-featured" aria-label="{{ __('messages.featured') }}">
                    <div class="blog-featured__content">
                        <span class="blog-tag">{{ __('messages.featured') }}</span>
                        <h2 class="mt-3 text-white">{{ $featuredTranslation?->title }}</h2>
                        <p class="mt-2">{{ $featuredTranslation?->excerpt }}</p>
                        <div class="blog-card__meta mt-3">
                            <span>{{ optional($featured->published_at)->format('M d, Y') }}</span>
                            @if ($featured->reading_time)
                                <span>{{ __('messages.minutes_read', ['minutes' => $featured->reading_time]) }}</span>
                            @endif
                        </div>
                        <a class="blog-featured__cta mt-3" href="{{ route('blogs.show', $featuredTranslation?->slug) }}">
                            <span>?</span>
                            {{ __('messages.read_more') }}
                        </a>
                    </div>
                    <div class="blog-featured__media">
                        @if ($featured->cover_image)
                            <img src="{{ asset(Storage::url($featured->cover_image)) }}" alt="{{ $featuredTranslation?->title }}">
                        @else
                            <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $featuredTranslation?->title }}">
                        @endif
                    </div>
                </div>
            @endif

            @if ($blogs->count())
                @php
                    $featuredId = $featured?->id;
                    $collection = $blogs->getCollection();
                    $nonFeatured = $featuredId ? $collection->where('id', '!=', $featuredId) : $collection;
                    $topThree = $nonFeatured->take(3);
                    $rest = $nonFeatured->slice(3);
                @endphp

                @if ($topThree->count())
                    <div class="blog-grid mb-4">
                        @foreach ($topThree as $blog)
                            @php $translation = $blog->translation(); @endphp
                            <article class="blog-card">
                                <a href="{{ route('blogs.show', $translation?->slug) }}">
                                    @if ($blog->cover_image)
                                        <img src="{{ asset(Storage::url($blog->cover_image)) }}" alt="{{ $translation?->title }}">
                                    @else
                                        <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $translation?->title }}">
                                    @endif
                                </a>
                                <div class="blog-card__body">
                                    <h3>{{ $translation?->title }}</h3>
                                    <div class="blog-card__meta">
                                        <span>{{ optional($blog->published_at)->format('M d, Y') }}</span>
                                        @if ($blog->reading_time)
                                            <span>{{ __('messages.minutes_read', ['minutes' => $blog->reading_time]) }}</span>
                                        @endif
                                    </div>
                                    <p class="blog-card__excerpt">{{ $translation?->excerpt }}</p>
                                    <div class="blog-card__footer">
                                        <span class="blog-tag">{{ __('messages.featured') }}</span>
                                        <a href="{{ route('blogs.show', $translation?->slug) }}">
                                            {{ __('messages.read_more') }} ?
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="blog-grid">
                    @foreach ($rest as $blog)
                        @php $translation = $blog->translation(); @endphp
                        <article class="blog-card">
                            <a href="{{ route('blogs.show', $translation?->slug) }}">
                                @if ($blog->cover_image)
                                    <img src="{{ asset(Storage::url($blog->cover_image)) }}" alt="{{ $translation?->title }}">
                                @else
                                    <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $translation?->title }}">
                                @endif
                            </a>
                            <div class="blog-card__body">
                                <h3>{{ $translation?->title }}</h3>
                                <div class="blog-card__meta">
                                    <span>{{ optional($blog->published_at)->format('M d, Y') }}</span>
                                    @if ($blog->reading_time)
                                        <span>{{ __('messages.minutes_read', ['minutes' => $blog->reading_time]) }}</span>
                                    @endif
                                </div>
                                <p class="blog-card__excerpt">{{ $translation?->excerpt }}</p>
                                <div class="blog-card__footer">
                                    <span class="blog-tag">{{ __('messages.featured') }}</span>
                                    <a href="{{ route('blogs.show', $translation?->slug) }}">
                                        {{ __('messages.read_more') }} ?
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @include('includes._pagination', ['paginator' => $blogs, 'isRtl' => $isRtl])
            @else
                <div class="alert alert-light mt-4">{{ __('messages.no_results') }}</div>
            @endif
        </div>
    </section>
@endsection
