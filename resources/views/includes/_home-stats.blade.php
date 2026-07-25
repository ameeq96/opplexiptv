@php
    $stats = __('document_home.stats');
@endphp

<section class="home-document-stats"
    dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}"
    aria-labelledby="home-document-stats-heading">
    <div class="auto-container">
        <header class="home-document-stats__header">
            <h3 id="home-document-stats-heading">{{ $stats['heading'] }}</h3>
        </header>

        <div class="home-document-stats__grid" role="list">
            @foreach ($stats['items'] as $stat)
                <div class="home-document-stats__card" role="listitem">
                    <span class="home-document-stats__value">{{ $stat['value'] }}</span>
                    <span class="home-document-stats__label">{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
