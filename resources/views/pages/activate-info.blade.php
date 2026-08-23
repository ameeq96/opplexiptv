@extends('layouts.default')
@section('title', __('interface.activation.page_title'))

@push('schema')
    {!! jsonld(seo()->howTo(
        __('interface.activation.howto_title'),
        __('interface.activation.howto_description'),
        [
            ['name' => __('interface.activation.steps.enter_name'), 'text' => __('interface.activation.steps.enter_text')],
            ['name' => __('interface.activation.steps.request_name'), 'text' => __('interface.activation.steps.request_text')],
            ['name' => __('interface.activation.steps.verify_name'), 'text' => __('interface.activation.steps.verify_text')],
            ['name' => __('interface.activation.steps.stream_name'), 'text' => __('interface.activation.steps.stream_text')],
        ],
    )) !!}
@endpush

@section('content')
    <section class="section sec-activation d-flex justify-content-center align-items-center py-5"
        style="background-image:url('{{ asset('images/background/pattern-6.webp') }}')" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="container" style="max-width:720px;">
            <!-- H1 -->
            <h1 class="text-center mb-2" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                {{ __('interface.activation.page_title') }}
            </h1>
            <!-- H2 -->
            <h2 class="text-muted text-center mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                {{ __('interface.activation.instructions_heading') }}
            </h2>

            <div class="card p-4 shadow-sm">
                <label for="orderNumber" class="form-label">{{ __('interface.activation.order_number') }}</label>
                <input type="text" id="orderNumber" class="form-control mb-3 {{ $isRtl ? 'text-end' : '' }}"
                    placeholder="{{ __('interface.activation.order_placeholder') }}" inputmode="text" autocomplete="off" required
                    aria-describedby="orderHelp">
                <div id="orderHelp" class="form-text">{{ __('interface.activation.request_help') }}</div>

                <button id="waBtn" class="btn btn-danger mt-3 w-100" type="button"
                    aria-label="{{ __('interface.activation.send_label') }}"
                    disabled>
                    <i class="fa fa me-1"></i> {{ __('interface.activation.activate') }}
                </button>

                <small id="errorText" class="text-danger d-none mt-2">{{ __('interface.activation.valid_order') }}</small>
            </div>

            <p class="text-center mt-3">
                <small>{{ __('interface.activation.trouble') }}</small>
            </p>
        </div>
    </section>

    <script>
        (function() {
            const phone = '16393903194';
            const input = document.getElementById('orderNumber');
            const btn = document.getElementById('waBtn');
            const err = document.getElementById('errorText');
            const requestLabel = @json(__('interface.activation.whatsapp_request'));
            const orderTemplate = @json(__('interface.activation.whatsapp_order', ['order' => ':order']));
            const fromTemplate = @json(__('interface.activation.whatsapp_from', ['url' => ':url']));

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
                    requestLabel,
                    orderTemplate.replace(':order', code),
                    fromTemplate.replace(':url', window.location.origin)
                ];
                const url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(msgLines.join('\n'));
                window.open(url, '_blank');
            });
        })();
    </script>
@endsection
