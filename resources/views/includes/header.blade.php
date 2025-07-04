<header class="main-header">

    @if (!$agent->isMobile())
        <div class="header-top">
            <div class="auto-container clearfix">

                <div class="pull-left">
                    <ul class="info">
                        <li><a href="https://wa.me/923121108582" target="_blank" rel="noopener noreferrer"><span
                                    class="icon flaticon-maps-and-flags"></span> {{ __('messages.header_whatsapp') }}</a>
                        </li>
                        <li><a href="mailto:info@opplexiptv.com"><span
                                    class="icon flaticon-email-2"></span>{{ __('messages.header_email') }}</a></li>
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
    @endif

    <div class="header-lower">

        <div class="auto-container clearfix">
            <div class="inner-container clearfix mobile-header-container">

                <div class="pull-left logo-box">
                    <div class="logo transparent-logo"><a href="/">
                            <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Logo" title=""
                                width="300" height="78" /> </a>
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
                                <li class="dropdown"><a href="{{ route('about') }}">{{ __('messages.nav_about') }}</a>
                                    <ul class="sub-menu">
                                        <li><a href="{{ route('about') }}">{{ __('messages.nav_about_us') }}</a></li>
                                        <li><a href="{{ route('pricing') }}">{{ __('messages.nav_pricing') }}</a></li>
                                        <li><a href="{{ route('movies') }}">{{ __('messages.nav_movies_series') }}</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown"><a
                                        href="{{ route('packages') }}">{{ __('messages.nav_services') }}</a>
                                    <ul class="sub-menu">
                                        <li><a
                                                href="{{ route('reseller-panel') }}">{{ __('messages.nav_reseller') }}</a>
                                        </li>
                                        <li><a href="{{ route('packages') }}">{{ __('messages.nav_packages') }}</a>
                                        </li>
                                    </ul>
                                </li>
                                <li><a href="{{ route('iptv-applications') }}">{{ __('messages.nav_iptv_apps') }}</a>
                                <li><a href="{{ route('faqs') }}">{{ __('messages.nav_faqs') }}</a>
                                </li>
                                <li><a href="contact">{{ __('messages.nav_contact') }}</a></li>
                                <li class="dropdown language-switcher nav-item dropdown">
                                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                                        role="button" aria-expanded="false">
                                        {{ LaravelLocalization::getCurrentLocaleNative() }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                            <li>
                                                <a class="dropdown-item" rel="alternate" hreflang="{{ $localeCode }}"
                                                    href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                                    {{ $properties['native'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>

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
                    <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Logo" title="" width="300"
                        height="78" />
                </a>
                </a>
            </div>
            <div class="menu-outer">
                <div class="language-switcher-mobile" style="margin: 15px;">
                    <select onchange="location = this.value;" class="form-select">
                        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            <option value="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                {{ app()->getLocale() == $localeCode ? 'selected' : '' }}>
                                {{ $properties['native'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </nav>
    </div>

</header>
