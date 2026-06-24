@extends('layouts.default')
@section('title', 'Legal IPTV Guide')

@push('schema')
    {!! jsonld(seo()->howTo(
        'How to activate your Opplex IPTV subscription',
        'Activate your IPTV subscription in a few simple steps after purchase.',
        [
            ['name' => 'Enter your order number', 'text' => 'Open the activation page and type the order number you received after purchase.'],
            ['name' => 'Send your activation request', 'text' => 'Tap Activate to send your order number to our support team on WhatsApp.'],
            ['name' => 'We verify and activate', 'text' => 'Our team verifies your order and activates your IPTV line, usually within minutes.'],
            ['name' => 'Start streaming', 'text' => 'Open your IPTV app, sign in with the details we send you, and start watching on any device.'],
        ],
    )) !!}
@endpush

@section('content')
    <section class="section sec-activation d-flex justify-content-center align-items-center py-5"
        style="background-image:url('{{ asset('images/background/pattern-6.webp') }}')" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="container" style="max-width:720px;">
            <!-- H1 -->
            <h1 class="text-center mb-2" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                Legal IPTV Guide
            </h1>
            <!-- H2 -->
            <h2 class="text-muted text-center mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                Enter your order number
            </h2>

            <div class="card p-4 shadow-sm">
                <label for="orderNumber" class="form-label">{{ __('Order number') }}</label>
                <input type="text" id="orderNumber" class="form-control mb-3 {{ $isRtl ? 'text-end' : '' }}"
                    placeholder="{{ __('e.g. 12345 or ABC-789') }}" inputmode="text" autocomplete="off" required
                    aria-describedby="orderHelp">
                <div id="orderHelp" class="form-text">{{ __('We’ll send your activation request.') }}</div>

                <button id="waBtn" class="btn btn-danger mt-3 w-100" type="button" aria-label="Send"
                    disabled>
                    <i class="fa fa me-1"></i> {{ __('Activate') }}
                </button>

                <small id="errorText" class="text-danger d-none mt-2">{{ __('Please enter a valid order number.') }}</small>
            </div>

            <p class="text-center mt-3">
                <small>{{ __('Having trouble? You can also message us directly after entering your code.') }}</small>
            </p>
        </div>
    </section>

    <script>
        (function() {
            const phone = '16393903194';
            const input = document.getElementById('orderNumber');
            const btn = document.getElementById('waBtn');
            const err = document.getElementById('errorText');
            const rtl = {{ $isRtl ? 'true' : 'false' }};

            const isValid = (val) => /^[A-Za-z0-9\-\_]{3,32}$/.test(val.trim());

            function toggle() {
                btn.disabled = !isValid(input.value);
                err.classList.add('d-none');
            }

            input.addEventListener('input', toggle);
            toggle();

            btn.addEventListener('click', function() {
                const code = (input.value || '').trim();
                if (!isValid(code)) {
                    err.classList.remove('d-none');
                    return;
                }
                const msgLines = [
                    'Activation request',
                    'Order #: ' + code,
                    'From: ' + window.location.origin
                ];
                const url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(msgLines.join('\n'));
                window.open(url, '_blank');
            });
        })();
    </script>
@endsection
