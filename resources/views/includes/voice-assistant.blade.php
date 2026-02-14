@php
    $vaGuidePrefix = 'messages.voice_guide.';
    $vaUiPrefix = 'messages.voice_assistant.';

    $assistantRoutes = [
        'home' => route('home'),
        'pricing' => route('pricing'),
        'packages' => route('packages'),
        'about' => route('about'),
        'reseller' => route('reseller-panel'),
        'apps' => route('iptv-applications'),
        'movies' => route('movies'),
        'shop' => route('shop'),
        'blogs' => route('blogs.index'),
        'faqs' => route('faqs'),
        'contact' => route('contact'),
        'activate' => route('activate'),
        'configure' => route('configure'),
        'checkout' => route('checkout'),
        'terms' => route('terms-of-service'),
        'privacy' => route('privacy-policy'),
        'refund' => route('refund-policy'),
    ];
@endphp

<div id="voice-assistant"
    class="va-container"
    data-routes='@json($assistantRoutes)'
    data-locale="{{ app()->getLocale() }}"
    data-guide-complete="{{ __($vaGuidePrefix . 'done_message') }}"
    data-welcome-message="{{ __($vaUiPrefix . 'welcome_message') }}"
    data-help-message="{{ __($vaUiPrefix . 'help_message') }}"
    data-voice-on="{{ __($vaUiPrefix . 'voice_on') }}"
    data-voice-off="{{ __($vaUiPrefix . 'voice_off') }}"
    data-rtl="{{ $isRtl ? '1' : '0' }}">
    <button type="button" class="va-fab" aria-label="{{ __($vaUiPrefix . 'open_aria') }}">
        <span class="va-fab-icon">{{ __($vaUiPrefix . 'fab_mic') }}</span>
        <span class="va-fab-text">{{ __($vaUiPrefix . 'fab_ask') }}</span>
    </button>

    <div class="va-panel" role="dialog" aria-label="{{ __($vaUiPrefix . 'panel_aria') }}">
        <div class="va-header">
            <div class="va-title">
                <div class="va-name">{{ __($vaUiPrefix . 'name') }}</div>
                <div class="va-sub">{{ __($vaUiPrefix . 'subtitle') }}</div>
            </div>
            <button type="button" class="va-close" aria-label="{{ __($vaUiPrefix . 'close_aria') }}">&times;</button>
        </div>

        <div class="va-messages" aria-live="polite"></div>

        <div class="va-quick">
            <button type="button" data-quick="pricing">{{ __($vaUiPrefix . 'quick_pricing') }}</button>
            <button type="button" data-quick="packages">{{ __($vaUiPrefix . 'quick_packages') }}</button>
            <button type="button" data-quick="checkout">{{ __($vaUiPrefix . 'quick_checkout') }}</button>
            <button type="button" data-quick="contact">{{ __($vaUiPrefix . 'quick_contact') }}</button>
        </div>

        <div class="va-input">
            <button type="button" class="va-mic" aria-label="{{ __($vaUiPrefix . 'start_voice_aria') }}">{{ __($vaUiPrefix . 'mic_btn') }}</button>
            <input type="text" class="va-text" placeholder="{{ __($vaUiPrefix . 'input_placeholder') }}" />
            <button type="button" class="va-send" aria-label="{{ __($vaUiPrefix . 'send_aria') }}">{{ __($vaUiPrefix . 'send_btn') }}</button>
        </div>

        <div class="va-controls">
            <button type="button" class="va-speak-toggle" aria-pressed="true">{{ __($vaUiPrefix . 'voice_on') }}</button>
            <button type="button" class="va-help">{{ __($vaUiPrefix . 'help_btn') }}</button>
            <button type="button" class="va-guide">{{ __($vaUiPrefix . 'guide_btn') }}</button>
        </div>
    </div>
</div>

<div id="va-onboarding" class="va-onboarding" aria-hidden="true">
    <div class="va-onboarding__backdrop" data-close="1"></div>
    <div class="va-onboarding__dialog" role="dialog" aria-label="{{ __($vaGuidePrefix . 'dialog_aria') }}">
        <button type="button" class="va-onboarding__close" data-close="1" aria-label="{{ __($vaGuidePrefix . 'close_aria') }}">&times;</button>

        <div class="va-onboarding__badge">{{ __($vaGuidePrefix . 'badge') }}</div>
        <h3 class="va-onboarding__title">{{ __($vaGuidePrefix . 'title') }}</h3>

        <div class="va-onboarding__steps">
            <section class="va-step active" data-step="0">
                <h4>{{ __($vaGuidePrefix . 'step1_title') }}</h4>
                <p>{!! __($vaGuidePrefix . 'step1_text') !!}</p>
            </section>
            <section class="va-step" data-step="1">
                <h4>{{ __($vaGuidePrefix . 'step2_title') }}</h4>
                <p>{!! __($vaGuidePrefix . 'step2_text') !!}</p>
            </section>
            <section class="va-step" data-step="2">
                <h4>{{ __($vaGuidePrefix . 'step3_title') }}</h4>
                <p>{!! __($vaGuidePrefix . 'step3_text') !!}</p>
            </section>
            <section class="va-step" data-step="3">
                <h4>{{ __($vaGuidePrefix . 'step4_title') }}</h4>
                <p>{!! __($vaGuidePrefix . 'step4_text') !!}</p>
            </section>
            <section class="va-step" data-step="4">
                <h4>{{ __($vaGuidePrefix . 'step5_title') }}</h4>
                <p>{!! __($vaGuidePrefix . 'step5_text') !!}</p>
            </section>
        </div>

        <div class="va-onboarding__footer">
            <button type="button" class="va-onboarding__btn va-onboarding__btn--ghost" data-prev="1">{{ __($vaGuidePrefix . 'back') }}</button>
            <button type="button" class="va-onboarding__btn va-onboarding__btn--skip" data-skip="1">{{ __($vaGuidePrefix . 'skip') }}</button>
            <button type="button" class="va-onboarding__btn va-onboarding__btn--next" data-next="1">{{ __($vaGuidePrefix . 'next') }}</button>
        </div>

        <p class="va-onboarding__hint">{{ __($vaGuidePrefix . 'hint') }}</p>
    </div>
</div>
