@php
    use Illuminate\Support\Facades\Storage;

    /** @var \App\Models\Blog $blog */
    $cardT = $blog->translation();
    $cardSlug = $cardT?->slug ?? $blog->translations->first()?->slug;
    $cardUrl = $cardSlug ? route('blogs.show', $cardSlug) : '#';

    $cardCategory = $blog->relationLoaded('categories')
        ? optional($blog->categories->first())->translation()
        : null;

    $cardAuthor = $blog->relationLoaded('author') ? $blog->author : null;
    $cardAuthorName = $cardAuthor?->name;
    $cardInitials = $cardAuthorName
        ? collect(preg_split('/\s+/', trim($cardAuthorName)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('')
        : null;
@endphp

<article class="blog-card">
    <a class="blog-card__media" href="{{ $cardUrl }}" aria-label="{{ $cardT?->title }}">
        @if ($blog->cover_image)
            <img src="{{ asset(Storage::url($blog->cover_image)) }}"
                alt="{{ $cardT?->title }}" loading="lazy" decoding="async">
        @else
            <img src="{{ asset('images/placeholder.webp') }}"
                alt="{{ $cardT?->title }}" loading="lazy" decoding="async">
        @endif
        @if ($cardCategory)
            <span class="blog-chip">{{ $cardCategory->title }}</span>
        @endif
    </a>

    <div class="blog-card__body">
        <h3 class="blog-card__title">
            <a href="{{ $cardUrl }}">{{ $cardT?->title }}</a>
        </h3>

        @if ($cardT?->excerpt)
            <p class="blog-card__excerpt">{{ $cardT->excerpt }}</p>
        @endif

        <div class="blog-card__footer">
            <div class="blog-meta">
                @if ($cardAuthorName)
                    <span class="blog-meta__author">
                        <span class="blog-avatar" aria-hidden="true">{{ $cardInitials }}</span>
                        {{ $cardAuthorName }}
                    </span>
                    <span class="blog-meta__sep" aria-hidden="true"></span>
                @endif
                <span class="blog-meta__item">
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

            <a class="blog-readmore" href="{{ $cardUrl }}" aria-label="{{ __('messages.read_more') }}: {{ $cardT?->title }}">
                {{ __('messages.read_more') }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </div>
    </div>
</article>
