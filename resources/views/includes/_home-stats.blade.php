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

        <dl class="home-document-stats__grid">
            @foreach ($stats['items'] as $stat)
                <div class="home-document-stats__card">
                    <dt class="home-document-stats__label">{{ $stat['label'] }}</dt>
                    <dd class="home-document-stats__value">{{ $stat['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
