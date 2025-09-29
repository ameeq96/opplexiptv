@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'ur']);
    $percents = '5% • 10% • 15% • 20% • 25%';
@endphp

<div id="dw-overlay" class="dw-overlay" aria-hidden="true">
    <div class="dw-modal" role="dialog" aria-modal="true" aria-labelledby="dw-title" aria-describedby="dw-desc"
        dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <button class="dw-close" type="button" aria-label="{{ __('Close') }}">&times;</button>

        <div class="dw-head">
            <h3 id="dw-title">{{ __('messages.discount_title') }}</h3>
            <p id="dw-desc" class="dw-sub">{{ __('messages.discount_sub', ['percents' => $percents]) }}</p>

            {{-- ✅ Always-visible note (sabka nazar par) --}}
            <p class="dw-global-note text-danger">
                {{ __('messages.require_screenshot') }}
            </p>
        </div>

        <div class="dw-body">
            <div class="dw-wheel-wrap">
                <div class="dw-pointer" aria-hidden="true"></div>
                <canvas id="dw-canvas" width="320" height="320"></canvas>
            </div>
            <div class="dw-cta">
                <button id="dw-spin" class="dw-btn">{{ __('messages.spin_now') }}</button>
                <div class="dw-note" id="dw-note"></div>
            </div>
        </div>

        <div class="dw-result" id="dw-result" hidden>
            <div class="d-flex justify-content-center align-items-center">
                <div class="dw-result-badge">🎁</div>
                <div class="dw-result-text">
                    <strong id="dw-result-value">10%</strong> {{ __('messages.off') }}
                </div>
            </div>
            <button id="dw-copy" class="dw-btn-outline" data-wa-phone="{{ config('services.discount.phone') }}"
                data-wa-template="{{ __('messages.whatsapp_message', ['discount' => ':discount']) }}">{{ __('messages.whatsapp_btn') }}</button>
        </div>
    </div>
</div>


{{-- Pass i18n strings to JS --}}
<script>
    window.DW_I18N = {
        good_luck: @json(__('messages.good_luck')),
        congrats: @json(__('messages.congrats')),
        already_unlocked: @json(__('messages.already_unlocked', ['value' => ':value'])),
        spin_center: @json(__('messages.spin_center')),
        off: @json(__('messages.off'))
    };
</script>
