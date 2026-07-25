

<section class="trial-cta {{ request()->routeIs('home') ? 'home-document-trial' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    aria-label="{{ __('document_ui.home.trial_aria') }}"
    role="region">
    @php
        $isDocumentEnglishTrial = request()->routeIs('home');
        $documentTrial = $isDocumentEnglishTrial ? __('document_home.trial') : [];
    @endphp

    <div class="trial-cta__shell">
        <div class="trial-cta__panel">
            <div class="trial-cta__grid {{ $isRtl ? 'rtl-row' : '' }}">
                <div class="trial-cta__content" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <span class="trial-cta__eyebrow">{{ __('messages.home_trial_eyebrow') }}</span>
                    <h3 class="trial-cta__title">{{ $isDocumentEnglishTrial ? $documentTrial['heading'] : __('messages.trial_title') }}</h3>
                    <p class="trial-cta__text">{{ $isDocumentEnglishTrial ? $documentTrial['text'] : __('messages.home_trial_text') }}</p>
                    <div class="trial-cta__meta">
                        @if ($isDocumentEnglishTrial)
                            @foreach ($documentTrial['chips'] as $chip)
                                <span class="trial-cta__chip">{{ $chip }}</span>
                            @endforeach
                        @else
                            <span class="trial-cta__chip">{{ __('messages.home_trial_chip_streaming') }}</span>
                            <span class="trial-cta__chip">{{ __('messages.home_trial_chip_channels') }}</span>
                            <span class="trial-cta__chip">{{ __('messages.home_trial_chip_activation') }}</span>
                        @endif
                    </div>
                </div>

                <div class="trial-cta__actions" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="trial-cta__action-card">
                        <span class="trial-cta__action-label">{{ $isDocumentEnglishTrial ? $documentTrial['action_label'] : __('messages.home_trial_action_label') }}</span>
                        @if ($isDocumentEnglishTrial)
                            <div class="trial-cta__buttons">
                                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="trial-cta__button"
                                    data-trial
                                    data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                    aria-label="{{ __('document_ui.home.trial_action_aria') }}">
                                    <span>{{ $documentTrial['primary_cta'] }}</span>
                                    <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                                </a>
                                <a href="#pricing-section" class="trial-cta__button trial-cta__button--secondary">
                                    <span>{{ $documentTrial['secondary_cta'] }}</span>
                                    <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                                </a>
                            </div>
                        @else
                            <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                target="_blank"
                                rel="noopener"
                                class="trial-cta__button"
                                data-trial
                                data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                    aria-label="{{ __('document_ui.home.trial_action_aria') }}">
                                <span>{{ __('messages.trial_button') }}</span>
                                <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
