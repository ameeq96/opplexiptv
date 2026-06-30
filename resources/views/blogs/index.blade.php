@extends('layouts.default')

@php
    use Illuminate\Support\Facades\Storage;
    $isRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);
@endphp

@push('styles')
    @vite('resources/css/blogs.css')
@endpush

@push('schema')
    @php
        $blogItems = collect($blogs?->items() ?? [])
            ->map(function ($b) {
                $t = optional($b->translations)->first();
                $slug = $t->slug ?? null;
                return $slug
                    ? ['name' => (string) ($t->title ?? ''), 'url' => route('blogs.show', $slug)]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    @endphp
    {!! jsonld(seo()->collectionPage(
        __('messages.blog.heading'),
        trans('meta.blogs.index.description'),
        route('blogs.index'),
        $blogItems,
    )) !!}
@endpush

@section('content')

    {{-- Page Title --}}
    <x-page-title :title="__('messages.blog.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.blog.breadcrumb.home')],
        ['label' => __('messages.blog.breadcrumb.current')],
    ]" background="images/background/10.webp"
        :rtl="$isRtl" aria-label="Blog Page" />

    <section class="blogs-wrap {{ $isRtl ? 'rtl' : '' }}">
        <div class="auto-container">

            <h1 class="sr-only">{{ __('messages.blog.heading') }} — IPTV Guides, Setup Tips &amp; Streaming News</h1>

            {{-- Toolbar: categories + search --}}
            <div class="blog-toolbar">
                <nav class="blog-filters" aria-label="{{ __('messages.blog.heading') }}">
                    <a class="blog-filter-pill {{ $categorySlug === '' ? 'active' : '' }}"
                        href="{{ route('blogs.index', array_filter(['q' => $search ?: null])) }}">
                        {{ __('messages.all') }}
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
                </nav>

                <form class="blog-search" method="GET" action="{{ route('blogs.index') }}" role="search">
                    @if ($categorySlug !== '')
                        <input type="hidden" name="category" value="{{ $categorySlug }}">
                    @endif
                    <svg class="blog-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="text" name="q" value="{{ $search }}"
                        placeholder="{{ __('messages.search') }}" aria-label="{{ __('messages.search') }}">
                    <button type="submit">{{ __('messages.search') }}</button>
                </form>
            </div>

            {{-- Featured post --}}
            @if ($featured)
                @php
                    $featuredTranslation = $featured->translation();
                    $featuredSlug = $featuredTranslation?->slug ?? $featured->translations->first()?->slug;
                    $featuredUrl = $featuredSlug ? route('blogs.show', $featuredSlug) : '#';
                    $featuredCategory = optional($featured->categories->first())->translation();
                    $featuredAuthor = $featured->relationLoaded('author') ? $featured->author : null;
                @endphp
                <article class="blog-featured" aria-label="{{ __('messages.featured') }}">
                    <a class="blog-featured__media" href="{{ $featuredUrl }}" aria-label="{{ $featuredTranslation?->title }}">
                        @if ($featured->cover_image)
                            <img src="{{ asset(Storage::url($featured->cover_image)) }}"
                                alt="{{ $featuredTranslation?->title }}" loading="lazy" decoding="async">
                        @else
                            <img src="{{ asset('images/placeholder.webp') }}"
                                alt="{{ $featuredTranslation?->title }}" loading="lazy" decoding="async">
                        @endif
                        <span class="blog-featured__badge">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.9l-5.8 3.05 1.1-6.46-4.69-4.58 6.49-.94L12 2.5z" />
                            </svg>
                            {{ __('messages.featured') }}
                        </span>
                    </a>
                    <div class="blog-featured__content">
                        @if ($featuredCategory)
                            <span class="blog-eyebrow">{{ $featuredCategory->title }}</span>
                        @endif
                        <h2 class="blog-featured__title">
                            <a href="{{ $featuredUrl }}">{{ $featuredTranslation?->title }}</a>
                        </h2>
                        @if ($featuredTranslation?->excerpt)
                            <p class="blog-featured__excerpt">{{ $featuredTranslation->excerpt }}</p>
                        @endif

                        <div class="blog-meta">
                            @if ($featuredAuthor?->name)
                                <span class="blog-meta__author">
                                    <span class="blog-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($featuredAuthor->name, 0, 1)) }}</span>
                                    {{ $featuredAuthor->name }}
                                </span>
                                <span class="blog-meta__sep" aria-hidden="true"></span>
                            @endif
                            <span class="blog-meta__item">{{ optional($featured->published_at)->format('M d, Y') }}</span>
                            @if ($featured->reading_time)
                                <span class="blog-meta__sep" aria-hidden="true"></span>
                                <span class="blog-meta__item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                    {{ __('messages.minutes_read', ['minutes' => $featured->reading_time]) }}
                                </span>
                            @endif
                        </div>

                        <div class="blog-featured__cta">
                            <a class="blog-cta" href="{{ $featuredUrl }}">
                                {{ __('messages.read_more') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endif

            {{-- Posts grid --}}
            @php
                $featuredId = $featured?->id;
                $collection = $blogs->getCollection();
                $posts = $featuredId ? $collection->where('id', '!=', $featuredId) : $collection;
            @endphp

            @if ($posts->count())
                <div class="blog-section-head">
                    <div>
                        <span class="blog-eyebrow">{{ __('messages.blog.heading') }}</span>
                        <h2>{{ __('messages.latest_posts') }}</h2>
                    </div>
                </div>

                <div class="blog-grid">
                    @foreach ($posts as $blog)
                        @include('blogs._card', ['blog' => $blog])
                    @endforeach
                </div>

                @include('includes._pagination', ['paginator' => $blogs, 'isRtl' => $isRtl])
            @elseif (!$featured)
                <div class="blog-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5z" />
                        <path d="M14 3v6h6M9 13h6M9 17h4" />
                    </svg>
                    <h3>{{ __('messages.no_results') }}</h3>
                    <a href="{{ route('blogs.index') }}">{{ __('messages.all') }}</a>
                </div>
            @endif
        </div>
    </section>
@endsection
