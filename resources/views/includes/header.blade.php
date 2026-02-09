<header class="main-header">

    @if (!$agent->isMobile())
        <div class="header-top">
            <div class="auto-container clearfix custom-max">

                <div class="pull-left">
                    <ul class="info d-flex justify-content-start align-items-center">
                        <li>
                            <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_contact')) }}"
                                target="_blank" rel="noopener noreferrer">
                                <i class="fa fa-phone"></i> {{ __('messages.header_whatsapp') }}
                            </a>
                        </li>
                        <li>
                            <marquee behavior="scroll" direction="{{ $isRtl ? 'right' : 'left' }}" scrollamount="6">
                                <a href="https://whatsapp.com/channel/0029VbAP0di0lwgiHJIUOz34" target="_blank"
                                    rel="noopener noreferrer"
                                    style="color: #25D366; font-weight: bold; font-size: 16px; text-decoration: underline;">
                                    {{ __('messages.whatsapp_channel') }}
                                </a>
                            </marquee>
                        </li>
                    </ul>
                </div>

                <div class="pull-right clearfix">
                    <ul class="social-box">
                        <li><a href="https://www.facebook.com/profile.php?id=61565476366548" class="fa fa-facebook-f"
                                target="_blank"></a></li>
                        <li><a href="https://www.linkedin.com/company/digitalize-store/" class="fa fa-linkedin"
                                target="_blank"></a></li>
                        <li><a href="https://www.instagram.com/oplextv/" class="fa fa-instagram" target="_blank"
                                rel="noopener noreferrer"></a></li>
                    </ul>
                </div>

            </div>
        </div>
    @else
        <div class="header-top">
            <div class="auto-container clearfix">
                <div class="text-center py-2">
                    <marquee behavior="scroll" direction="left" scrollamount="6">
                        <a href="https://whatsapp.com/channel/0029VbAP0di0lwgiHJIUOz34" target="_blank"
                            rel="noopener noreferrer"
                            style="color: #25D366; font-weight: bold; font-size: 16px; text-decoration: underline;">
                            {{ __('messages.whatsapp_channel') }}
                        </a>
                    </marquee>
                </div>
            </div>
        </div>
    @endif


    <div class="header-lower">

        <div class="auto-container clearfix">
            <div
                class="inner-container clearfix mobile-header-container d-flex justify-content-between align-items-center 
        {{ $isRtl ? 'flex-row-reverse' : '' }}">

                <div class="pull-left logo-box">
                    <div class="logo transparent-logo"><a href="/">
                            <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Logo" title=""
                                width="250" height="65" /> </a>
                    </div>
                </div>
                <div class="nav-outer clearfix">

                    <div class="mobile-nav-toggler"><span class="icon flaticon-menu"></span></div>
                    <nav class="main-menu show navbar-expand-md">
                        <div class="navbar-header">
                            <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>

                        <div class="navbar-collapse collapse clearfix" id="navbarSupportedContent">
                            <ul class="navigation clearfix">
                                <li class="current dropdown">
                                    <a class="nav-link home-cls"
                                        href="{{ route('home') }}">{{ __('messages.nav_home') }}</a>
                                </li>

                                <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                        href="{{ route('packages') }}">{{ __('messages.nav_packages') }}</a>
                                </li>

                                <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                        href="{{ route('iptv-applications') }}">{{ __('messages.nav_iptv_apps') }}</a>
                                </li>

                                <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                        href="{{ route('faqs') }}">{{ __('messages.nav_faqs') }}</a></li>

                                <li class="dropdown"><a href="#">{{ __('more') }}</a>
                                    <ul class="sub-menu">

                                        <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                                href="{{ route('about') }}">{{ __('messages.nav_about_us') }}</a></li>

                                        <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                                href="contact">{{ __('messages.nav_contact') }}</a></li>


                                        <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                                href="{{ route('blogs.index') }}">{{ __('messages.blogs') }}</a></li>

                                        <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                                href="{{ route('reseller-panel') }}">{{ __('messages.nav_reseller') }}</a>
                                        </li>


                                        <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                                href="{{ route('pricing') }}">{{ __('messages.nav_pricing') }}</a>
                                        </li>

                                        <li><a class="{{ $isRtl ? 'text-right' : '' }}"
                                                href="{{ route('movies') }}">{{ __('messages.nav_movies_series') }}</a>
                                        </li>
                                    </ul>
                                </li>
                                @if (!$agent->isMobile())
                                    <li
                                        class="dropdown language-switcher nav-item dropdown {{ $isRtl ? 'mr-4' : '' }}">
                                        <a href="#"
                                            class="nav-link dropdown-toggle {{ $isRtl ? 'text-right' : '' }}"
                                            data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                            <i class="fa fa-globe"></i>
                                            {{ LaravelLocalization::getCurrentLocaleNative() }}
                                        </a>
                                        <ul class="dropdown-menu">
                                            @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                                <li>
                                                    <a class="dropdown-item {{ $isRtl ? 'text-right' : '' }}"
                                                        rel="alternate" hreflang="{{ $localeCode }}"
                                                        href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                                        {{ $properties['native'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>

                                    </li>
                                @endif
                            </ul>
                        </div>

                    </nav>

                </div>
            </div>

        </div>
    </div>

    <div class="mobile-menu">
        <div class="menu-backdrop"></div>
        <div class="close-btn"><span class="icon flaticon-multiply"></span></div>

        <nav class="menu-box">
            <div class="nav-logo"><a href="/">
                    <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Logo" title=""
                        width="250" height="65" />
                </a>
                </a>
            </div>
            <div class="menu-outer">
                <div class="d-flex justify-content-center mt-4">
                    <div class="d-flex align-items-center px-3 py-2 rounded border"
                        style="background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <i class="fa fa-globe mr-2" style="font-size: 18px;"></i>
                        <select onchange="location = this.value;" class="custom-select custom-select-sm border-0"
                            style="box-shadow: none; padding-right: 24px;">
                            @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                <option
                                    value="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                    {{ app()->getLocale() == $localeCode ? 'selected' : '' }}>
                                    {{ $properties['native'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </nav>
    </div>

</header>
