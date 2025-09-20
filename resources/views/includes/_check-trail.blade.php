<section class="appointment-section style-two"
         style="background-image:url('{{ asset('images/background/pattern-21.webp') }}');"
         dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
         aria-label="Start Your IPTV Free Trial with Opplex" role="region">
    <div class="auto-container">
        <div class="row clearfix {{ $isRtl ? 'rtl-row' : '' }}">
            <div class="title-column col-lg-6 col-md-12 col-sm-12" role="heading" aria-level="2"
                 style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="inner-column">
                    <h3>{{ __('messages.trial_title') }}</h3>
                </div>
            </div>
            <div class="form-column col-lg-6 col-md-12 col-sm-12" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="inner-column">
                    <div class="appointment-form" role="form" aria-label="Start IPTV Free Trial Form">
                        <div class="form-group">
                            <a href="https://wa.me/16393903194" rel="noopener" aria-label="Start your IPTV free trial now">
                                <button class="theme-btn btn-style-five" type="button">
                                    <span class="txt">
                                        {{ __('messages.trial_button') }}
                                        <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                                    </span>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>