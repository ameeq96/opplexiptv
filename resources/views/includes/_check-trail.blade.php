

<section class="trial-cta {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--cta' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    aria-label="Start Your IPTV Free Trial with Opplex"
    role="region">
    @if (!empty($useSectionSkeletons))
        <div class="section-skeleton__overlay" aria-hidden="true">
            <div class="section-skeleton__content">
                <div class="section-skeleton__meta">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                </div>
                <span class="section-skeleton__button"></span>
            </div>
        </div>
    @endif

    <div class="trial-cta__shell">
        <div class="trial-cta__panel">
            <div class="trial-cta__grid {{ $isRtl ? 'rtl-row' : '' }}">
                <div class="trial-cta__content" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <span class="trial-cta__eyebrow">{{ __('messages.home_trial_eyebrow') }}</span>
                    <h3 class="trial-cta__title">{{ __('messages.trial_title') }}</h3>
                    <p class="trial-cta__text">{{ __('messages.home_trial_text') }}</p>
                    <div class="trial-cta__meta">
                        <span class="trial-cta__chip">{{ __('messages.home_trial_chip_streaming') }}</span>
                        <span class="trial-cta__chip">{{ __('messages.home_trial_chip_channels') }}</span>
                        <span class="trial-cta__chip">{{ __('messages.home_trial_chip_activation') }}</span>
                    </div>
                </div>

                <div class="trial-cta__actions" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="trial-cta__action-card">
                        <span class="trial-cta__action-label">{{ __('messages.home_trial_action_label') }}</span>
                        <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                            target="_blank"
                            rel="noopener"
                            class="trial-cta__button"
                            data-trial
                            data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                            aria-label="Start your IPTV free trial now">
                            <span>{{ __('messages.trial_button') }}</span>
                            <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

