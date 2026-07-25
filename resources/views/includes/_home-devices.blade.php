@php
    $devices = __('document_home.devices');
    $deviceIcons = [
        'fa-play-circle',
        'fa-desktop',
        'fa-cube',
        'fa-mobile',
        'fa-laptop',
    ];
    $whatsAppUrl = 'https://wa.me/16393903194?text=' . urlencode($devices['whatsapp_message']);
@endphp

<section class="home-document-devices"
    dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}"
    aria-labelledby="home-document-devices-heading">
    <div class="auto-container">
        <header class="home-document-devices__header">
            <h3 id="home-document-devices-heading">{{ $devices['heading'] }}</h3>
            <p>{{ $devices['intro'] }}</p>
        </header>

        <ul class="home-document-devices__grid" role="list">
            @foreach ($devices['items'] as $itemIndex => $item)
                <li class="home-document-devices__card">
                    <span class="home-document-devices__icon" aria-hidden="true">
                        <i class="fa {{ $deviceIcons[$itemIndex] ?? 'fa-play-circle' }}"></i>
                    </span>
                    <span class="home-document-devices__name">{{ $item }}</span>
                </li>
            @endforeach
        </ul>

        <a class="home-document-devices__note"
            href="{{ $whatsAppUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="{{ $devices['note'] }}">
            <i class="fa fa-whatsapp" aria-hidden="true"></i>
            <span>{{ $devices['note'] }}</span>
            <i class="fa fa-arrow-right home-document-devices__note-arrow" aria-hidden="true"></i>
        </a>
    </div>
</section>
