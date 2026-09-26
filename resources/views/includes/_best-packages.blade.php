<section class="pricing-section style-two" id="pricing-section" aria-label="{{ __('document_ui.home.pricing_aria') }}">
    @once
        <style>
            .va-container { transition: opacity .2s ease, visibility .2s ease, transform .2s ease; }
            body.pricing-in-view .whatsapp-icon,
            body.pricing-in-view:not(.va-panel-open) .va-container {
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
                transform: translateY(10px);
            }
            #pricing-section .pricing-buy-cta {
                display: inline-flex;
                min-height: 52px;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #e32430 0%, #c70e1a 100%) !important;
                border: 1px solid #c70e1a !important;
                box-shadow: 0 12px 24px rgba(199, 14, 26, .2) !important;
                transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            }
            #pricing-section .pricing-buy-cta .txt { color: #fff !important; }
            #pricing-section .pricing-buy-cta:hover {
                transform: translateY(-1px);
                background: linear-gradient(135deg, #cf1521 0%, #ad0914 100%) !important;
                box-shadow: 0 15px 28px rgba(173, 9, 20, .24) !important;
            }
            #pricing-section .pricing-buy-cta:focus-visible,
            #pricing-section .pricing-share-link:focus-visible,
            #pricing-section .price-block .button-box > a:not(.pricing-buy-cta):focus-visible,
            #pricing-section .iptv-service-picker select:focus-visible,
            #pricing-section .vendor-toggle .tg:focus-visible,
            #pricing-section .vendor-toggle-reseller .tg:focus-visible {
                outline: 3px solid rgba(201, 19, 29, .42) !important;
                outline-offset: 3px;
            }
            .pricing-trial-cta { box-shadow: 0 12px 28px rgba(223, 3, 3, .25); }
            #pricing-section .pricing-controls {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: center !important;
                gap: 18px;
                margin: 30px 0 26px !important;
                padding: 12px 14px;
                border: 1px solid #e2e8f1;
                border-radius: 18px;
                background: linear-gradient(135deg, #fff 0%, #f7f9fd 100%);
                box-shadow: 0 12px 32px rgba(15, 23, 42, .07);
            }
            #pricing-section #real-toggle {
                display: flex;
                width: fit-content;
                min-width: 0;
                min-height: 60px;
                align-items: center;
                padding: 8px 16px;
                border: 1px solid #e0e6ef;
                border-radius: 15px;
                background: #fff;
                box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
            }
            #pricing-section .form-switch {
                position: relative;
                width: 100%;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                min-height: 44px;
                flex-wrap: nowrap;
                color: #17213a;
                font-size: 15px;
                font-weight: 700;
                line-height: 1.4;
            }
            #pricing-section .form-switch > span {
                min-width: 0;
                flex: 1 1 auto;
            }
            #pricing-section .form-switch input[type="checkbox"] {
                position: absolute;
                display: block !important;
                width: 1px;
                height: 1px;
                margin: 0;
                opacity: 0;
                clip-path: inset(50%);
            }
            #pricing-section .form-switch i {
                flex: 0 0 48px;
                width: 48px;
                height: 28px;
                border: 1px solid #cbd3df;
                background: #d7dce5;
                box-shadow: inset 0 1px 2px rgba(15, 23, 42, .08);
            }
            #pricing-section .form-switch i::before {
                width: 22px;
                height: 22px;
                top: 2px;
                left: 2px;
                box-shadow: 0 2px 6px rgba(15, 23, 42, .2);
            }
            #pricing-section .form-switch input[type="checkbox"]:checked + i {
                border-color: #c9131d;
                background: #d71923;
            }
            #pricing-section .form-switch input[type="checkbox"]:checked + i::before { left: 22px; }
            #pricing-section .form-switch input[type="checkbox"]:focus-visible + i {
                outline: 3px solid rgba(201, 19, 29, .35);
                outline-offset: 3px;
            }
            #pricing-section .vendor-toggle,
            #pricing-section .vendor-toggle-reseller {
                justify-self: end;
                min-height: 52px;
                margin-inline-start: auto;
                gap: 4px;
                padding: 4px;
                border: 1px solid #dce3ee;
                border-radius: 15px;
                background: #eef2f8;
                box-shadow: inset 0 1px 2px rgba(15, 23, 42, .05);
            }
            #pricing-section .vendor-toggle .tg,
            #pricing-section .vendor-toggle-reseller .tg {
                min-width: 88px;
                min-height: 42px;
                padding: 9px 16px;
                border-radius: 11px;
                color: #47546a;
                font-size: 14px;
                font-weight: 800;
                transition: color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
            }
            #pricing-section .vendor-toggle .tg:hover,
            #pricing-section .vendor-toggle-reseller .tg:hover { color: #111a2e; }
            #pricing-section .vendor-toggle .tg.active,
            #pricing-section .vendor-toggle-reseller .tg.active {
                background: linear-gradient(135deg, #111d4a 0%, #061039 100%);
                color: #fff;
                box-shadow: 0 8px 18px rgba(6, 16, 57, .2);
            }
            #pricing-section .pricing-control-actions {
                display: flex;
                min-width: 0;
                align-items: flex-end;
                justify-content: flex-end;
                justify-self: end;
                gap: 12px;
            }
            #pricing-section .pricing-control-actions .vendor-toggle,
            #pricing-section .pricing-control-actions .vendor-toggle-reseller {
                margin-inline-start: 0;
            }
            #pricing-section .iptv-service-picker {
                display: grid;
                width: clamp(280px, 24vw, 360px);
                min-width: 0;
                gap: 4px;
                margin: 0;
                color: #4c586d;
                font-size: 11px;
                font-weight: 900;
                letter-spacing: .07em;
                line-height: 1.2;
                text-transform: uppercase;
            }
            #pricing-section .iptv-service-picker select {
                width: 100%;
                min-height: 54px;
                padding: 10px 42px 10px 14px;
                border: 1px solid #dce3ee;
                border-radius: 13px;
                background: #fff;
                color: #111a2e;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .07);
                font-size: 14px;
                font-weight: 800;
                letter-spacing: 0;
                text-transform: none;
            }
            [dir="rtl"] #pricing-section .iptv-service-picker select {
                padding: 10px 14px 10px 42px;
            }
            #pricing-section .iptv-service-picker > .select2-container {
                width: 100% !important;
                min-width: 0;
                letter-spacing: 0;
                text-align: start;
                text-transform: none;
            }
            #pricing-section .iptv-service-picker > .select2-container .select2-selection--single {
                height: 54px;
                border: 1px solid #dce3ee;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .07);
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            #pricing-section .iptv-service-picker > .select2-container .select2-selection--single .select2-selection__rendered {
                padding: 0 46px 0 17px;
                color: #111a2e;
                font-size: 14px;
                font-weight: 800;
                line-height: 52px;
            }
            [dir="rtl"] #pricing-section .iptv-service-picker > .select2-container .select2-selection--single .select2-selection__rendered {
                padding: 0 17px 0 46px;
            }
            #pricing-section .iptv-service-picker > .select2-container .select2-selection--single .select2-selection__arrow {
                width: 42px;
                height: 52px;
                inset-inline-end: 2px;
                top: 0;
            }
            #pricing-section .iptv-service-picker > .select2-container .select2-selection--single .select2-selection__arrow b {
                margin-top: -3px;
                border-top-color: #344158;
            }
            #pricing-section .iptv-service-picker > .select2-container--open .select2-selection--single,
            #pricing-section .iptv-service-picker > .select2-container--focus .select2-selection--single {
                border-color: #c9131d;
                box-shadow: 0 0 0 4px rgba(201, 19, 29, .1), 0 10px 24px rgba(15, 23, 42, .08);
            }
            #pricing-section .select2-container--open { z-index: 1080; }
            #pricing-section .pricing-provider-dropdown {
                margin-top: 6px;
                overflow: hidden;
                border: 1px solid #dce3ee;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
            }
            #pricing-section .pricing-provider-dropdown .select2-search--dropdown { padding: 10px; }
            #pricing-section .pricing-provider-dropdown .select2-search__field {
                min-height: 42px;
                padding: 8px 12px;
                border: 1px solid #d9e0ea;
                border-radius: 10px;
                color: #111a2e;
                font-size: 14px;
                outline: none;
            }
            #pricing-section .pricing-provider-dropdown .select2-search__field:focus {
                border-color: #c9131d;
                box-shadow: 0 0 0 3px rgba(201, 19, 29, .09);
            }
            #pricing-section .pricing-provider-dropdown .select2-results__options {
                max-height: 280px;
                padding: 5px;
            }
            #pricing-section .pricing-provider-dropdown .select2-results__option {
                margin: 2px 0;
                padding: 10px 12px;
                border-radius: 9px;
                color: #344158;
                font-size: 14px;
                font-weight: 700;
            }
            #pricing-section .pricing-provider-dropdown .select2-results__option--selected {
                background: #f1f4f9;
                color: #111a2e;
            }
            #pricing-section .pricing-provider-dropdown .select2-results__option--highlighted[aria-selected] {
                background: #111d4a;
                color: #fff;
            }
            #pricing-section .pricing-share-link {
                display: inline-flex;
                min-height: 54px;
                flex: 0 1 auto;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 9px 18px;
                border: 1px solid #bfe7cb;
                border-radius: 14px;
                background: #f5fff8;
                color: #167c3c;
                box-shadow: 0 8px 18px rgba(22, 163, 74, .1);
                font-size: 13px;
                font-weight: 700;
                line-height: 1.3;
                white-space: nowrap;
                transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
            }
            #pricing-section .pricing-share-link:hover {
                transform: translateY(-1px);
                border-color: #86cf9b;
                color: #116a32;
                box-shadow: 0 11px 22px rgba(22, 163, 74, .16);
            }
            #pricing-section .pricing-share-link img {
                width: 25px;
                height: 25px;
            }
            #pricing-section .pricing-share-link--share {
                width: 54px;
                flex: 0 0 54px;
                padding: 8px;
            }
            #pricing-section .pricing-share-link[hidden] {
                display: none;
            }
            #pricing-section .pricing-compare-button {
                display: inline-flex;
                min-height: 54px;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 10px 16px;
                border: 1px solid #cfd8e7;
                border-radius: 14px;
                background: #fff;
                color: #111d4a;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .07);
                font-size: 13px;
                font-weight: 800;
                white-space: nowrap;
                transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
            }
            #pricing-section .pricing-compare-button:hover {
                transform: translateY(-1px);
                border-color: #aebbd0;
                box-shadow: 0 11px 22px rgba(15, 23, 42, .11);
            }
            #pricing-section .pricing-compare-button:focus-visible,
            #pricing-section .pricing-compare-modal__close:focus-visible {
                outline: 3px solid rgba(201, 19, 29, .35);
                outline-offset: 3px;
            }
            #pricing-section .pricing-promotion-banner {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 9px;
                margin: -10px 0 22px;
                padding: 12px 16px;
                border: 1px solid #f1c88b;
                border-radius: 14px;
                background: linear-gradient(135deg, #fff9e9 0%, #fff 100%);
                color: #81500a;
                font-size: 14px;
                font-weight: 800;
                text-align: center;
            }
            #pricing-section #creditInfo {
                margin: 0 0 24px !important;
                padding: 13px 18px;
                border: 1px solid #e1e7f0;
                border-radius: 14px;
                background: #f8faff;
            }
            #pricing-section #creditInfo p {
                margin: 0;
                color: #445169;
                line-height: 1.65;
            }
            #pricing-section .package-catalog-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                margin: 0 0 22px;
                padding: 16px 18px;
                border: 1px solid #e1e7f0;
                border-radius: 18px;
                background: linear-gradient(135deg, #f8faff 0%, #fff 62%);
                box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
            }
            #pricing-section .package-catalog-toolbar[hidden] { display: none !important; }
            #pricing-section .package-catalog-meta {
                display: flex;
                min-width: 0;
                align-items: center;
                gap: 12px;
                color: #4e5b70;
                font-size: 14px;
                font-weight: 700;
            }
            #pricing-section .package-catalog-meta__icon {
                display: inline-flex;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                align-items: center;
                justify-content: center;
                border-radius: 13px;
                background: linear-gradient(135deg, #111d4a 0%, #071137 100%);
                color: #fff;
                box-shadow: 0 9px 20px rgba(7, 17, 55, .2);
            }
            #pricing-section .package-catalog-meta strong {
                display: block;
                color: #111a2e;
                font-size: 17px;
                font-weight: 900;
                line-height: 1.2;
            }
            #pricing-section .package-catalog-meta > span:last-child > span {
                display: block;
                margin-top: 2px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            #pricing-section .package-catalog-search {
                position: relative;
                width: min(100%, 360px);
                margin: 0;
            }
            #pricing-section .package-catalog-search i {
                position: absolute;
                z-index: 1;
                inset-inline-start: 16px;
                top: 50%;
                color: #738097;
                font-size: 14px;
                transform: translateY(-50%);
                pointer-events: none;
            }
            #pricing-section .package-catalog-search input {
                width: 100%;
                min-height: 46px;
                padding: 10px 16px 10px 43px;
                border: 1px solid #dce3ed;
                border-radius: 13px;
                background: #fff;
                color: #111a2e;
                box-shadow: inset 0 1px 2px rgba(15, 23, 42, .03);
                font-size: 14px;
                font-weight: 600;
                outline: none;
                transition: border-color .18s ease, box-shadow .18s ease;
            }
            [dir="rtl"] #pricing-section .package-catalog-search input {
                padding: 10px 43px 10px 16px;
            }
            #pricing-section .package-catalog-search input:focus {
                border-color: #c9131d;
                box-shadow: 0 0 0 4px rgba(201, 19, 29, .1);
            }
            #pricing-section .package-catalog-search input::placeholder { color: #8a95a8; }
            #pricing-section .package-empty-state {
                margin: 0;
                padding: 28px 18px;
                border: 1px dashed #ccd5e2;
                border-radius: 18px;
                background: #f8faff;
                color: #5e6a7e;
                font-size: 15px;
                font-weight: 700;
                text-align: center;
            }
            #pricing-section .package-empty-state[hidden] { display: none !important; }
            #pricing-section .package-pagination {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                margin: 28px auto 4px;
            }
            #pricing-section .package-pagination[hidden] { display: none !important; }
            #pricing-section .package-pagination__button {
                display: inline-flex;
                min-width: 118px;
                min-height: 46px;
                align-items: center;
                justify-content: center;
                gap: 9px;
                padding: 10px 18px;
                border: 1px solid #d9e0eb;
                border-radius: 13px;
                background: #fff;
                color: #18233d;
                box-shadow: 0 8px 20px rgba(15, 23, 42, .07);
                font-size: 14px;
                font-weight: 800;
                transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
            }
            #pricing-section .package-pagination__button:hover:not(:disabled) {
                transform: translateY(-1px);
                border-color: #c9131d;
                box-shadow: 0 11px 24px rgba(15, 23, 42, .11);
            }
            #pricing-section .package-pagination__button:focus-visible {
                outline: 3px solid rgba(201, 19, 29, .35);
                outline-offset: 3px;
            }
            #pricing-section .package-pagination__button:disabled {
                cursor: not-allowed;
                opacity: .45;
                box-shadow: none;
            }
            #pricing-section .package-pagination__status {
                display: inline-flex;
                min-width: 74px;
                min-height: 40px;
                align-items: center;
                justify-content: center;
                padding: 8px 14px;
                border-radius: 999px;
                background: #111d4a;
                color: #fff;
                font-size: 14px;
                font-weight: 800;
                letter-spacing: .04em;
            }
            #pricing-section #normalPackages.scroll-wrapper.normal-wrapper,
            #pricing-section #resellerPackages .reseller-wrapper {
                align-items: stretch;
                gap: 20px !important;
            }
            #pricing-section #normalPackages .pkg-item,
            #pricing-section #resellerPackages .reseller-wrapper .pkg-item {
                height: 100%;
                padding-bottom: 0;
            }
            #pricing-section .price-block .inner-box.custom-color {
                position: relative;
                display: flex !important;
                height: 100% !important;
                padding-bottom: 0 !important;
                flex-direction: column;
                overflow: hidden;
                border: 1px solid #e1e7f0;
                border-radius: 22px;
                background: linear-gradient(180deg, #fff 0%, #fbfcff 100%) !important;
                box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
                transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
            }
            #pricing-section .price-block .inner-box.custom-color::before {
                position: absolute;
                z-index: 2;
                top: 0;
                right: 0;
                left: 0;
                height: 4px;
                border-radius: 22px 22px 0 0;
                background: linear-gradient(90deg, #ed3944 0%, #c80f1b 100%);
                content: "";
            }
            #pricing-section .price-block .upper-box {
                flex: 0 0 auto;
                gap: 0;
                padding: 30px 22px 24px;
                border-bottom: 1px solid #edf0f5;
                background: radial-gradient(circle at 50% 0%, rgba(227, 36, 48, .13) 0%, rgba(227, 36, 48, 0) 62%), linear-gradient(180deg, #fffafb 0%, #fff 100%) !important;
            }
            #pricing-section .price-block .icon-list {
                margin: 0 0 18px !important;
                padding: 0;
            }
            #pricing-section .price-block .icon-list:empty { display: none; }
            #pricing-section .price-block .icon-list li {
                display: inline-flex;
                width: 68px;
                height: 68px;
                align-items: center;
                justify-content: center;
                padding: 0;
                border: 1px solid #e6eaf1;
                border-radius: 20px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, .09);
                transform: none !important;
            }
            #pricing-section .price-block .icon-list img {
                width: 56px;
                height: 56px;
                object-fit: contain;
            }
            #pricing-section .price-block .icon-list .package-provider-logo {
                border-radius: 12px;
            }
            #pricing-section .price-block .inner-box:hover .icon-list li { transform: none !important; }
            #pricing-section .package-brand-mark {
                display: inline-flex;
                width: 100%;
                height: 100%;
                align-items: center;
                justify-content: center;
                border-radius: inherit;
                background: linear-gradient(145deg, #111d4a 0%, #071137 100%);
                color: #fff;
                font-size: 21px;
                font-weight: 900;
                letter-spacing: -.02em;
                line-height: 1;
            }
            #pricing-section .package-brand-mark--basic {
                background: linear-gradient(145deg, #178449 0%, #0e6536 100%);
            }
            #pricing-section .package-brand-mark--standard {
                background: linear-gradient(145deg, #286fc3 0%, #174c91 100%);
            }
            #pricing-section .package-brand-mark--advanced {
                background: linear-gradient(145deg, #7357c8 0%, #50369d 100%);
            }
            #pricing-section .package-brand-mark--premium {
                background: linear-gradient(145deg, #b97b16 0%, #86520a 100%);
            }
            #pricing-section .pkg-item--basic .inner-box.custom-color::before {
                background: linear-gradient(90deg, #1b9a56 0%, #0f6e3a 100%);
            }
            #pricing-section .pkg-item--standard .inner-box.custom-color::before {
                background: linear-gradient(90deg, #347fd1 0%, #19569c 100%);
            }
            #pricing-section .pkg-item--advanced .inner-box.custom-color::before {
                background: linear-gradient(90deg, #8064d5 0%, #5439a4 100%);
            }
            #pricing-section .pkg-item--premium .inner-box.custom-color::before {
                background: linear-gradient(90deg, #d49a31 0%, #99600d 100%);
            }
            #pricing-section .pkg-item--basic .upper-box {
                background: radial-gradient(circle at 50% 0%, rgba(27, 154, 86, .14) 0%, rgba(27, 154, 86, 0) 62%), linear-gradient(180deg, #f8fffb 0%, #fff 100%) !important;
            }
            #pricing-section .pkg-item--standard .upper-box {
                background: radial-gradient(circle at 50% 0%, rgba(52, 127, 209, .14) 0%, rgba(52, 127, 209, 0) 62%), linear-gradient(180deg, #f8fbff 0%, #fff 100%) !important;
            }
            #pricing-section .pkg-item--advanced .upper-box {
                background: radial-gradient(circle at 50% 0%, rgba(115, 87, 200, .15) 0%, rgba(115, 87, 200, 0) 62%), linear-gradient(180deg, #fbf9ff 0%, #fff 100%) !important;
            }
            #pricing-section .pkg-item--premium .upper-box {
                background: radial-gradient(circle at 50% 0%, rgba(212, 154, 49, .16) 0%, rgba(212, 154, 49, 0) 62%), linear-gradient(180deg, #fffdf7 0%, #fff 100%) !important;
            }
            #pricing-section .package-tier-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin: 0 0 12px;
                padding: 6px 11px;
                border: 1px solid #dbe5f2;
                border-radius: 999px;
                background: #f3f7fc;
                color: #41516b;
                font-size: 11px;
                font-weight: 900;
                letter-spacing: .08em;
                line-height: 1;
                text-transform: uppercase;
            }
            #pricing-section .package-tier-badge--basic {
                border-color: #c9ead6;
                background: #effcf4;
                color: #14733a;
            }
            #pricing-section .package-tier-badge--standard {
                border-color: #cadcf7;
                background: #eff6ff;
                color: #245da8;
            }
            #pricing-section .package-tier-badge--advanced {
                border-color: #d9cff7;
                background: #f4f0ff;
                color: #6044ad;
            }
            #pricing-section .package-tier-badge--premium {
                border-color: #f1d7a9;
                background: #fff8e8;
                color: #986211;
            }
            #pricing-section .package-service-name {
                margin: 0 0 8px;
                color: #657187;
                font-size: 13px;
                font-weight: 800;
                line-height: 1.35;
            }
            #pricing-section .price-block .upper-box .package-plan-title {
                margin: 0;
                color: #111a2e;
                font-size: 22px;
                font-weight: 800;
                line-height: 1.22;
                letter-spacing: -.02em;
                text-wrap: balance;
            }
            #pricing-section .price-block .upper-box .package-plan-title span {
                display: block;
                margin-top: 8px;
                color: #d71722;
                font-size: 17px;
                font-weight: 800;
                line-height: 1.35;
                letter-spacing: 0;
            }
            #pricing-section .price-block:not(.pkg-item--duration) .upper-box .package-plan-title span {
                margin-top: 10px;
                font-size: 20px;
            }
            #pricing-section .package-card-flags,
            #pricing-section .package-benefits {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-wrap: wrap;
                gap: 7px;
            }
            #pricing-section .package-card-flags {
                min-height: 25px;
                margin: 0 0 11px;
            }
            #pricing-section .package-benefits {
                justify-content: flex-start;
                margin: 0 0 15px;
            }
            #pricing-section .package-card-flag,
            #pricing-section .package-benefit {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 5px 9px;
                border-radius: 999px;
                font-size: 10px;
                font-weight: 900;
                letter-spacing: .045em;
                line-height: 1.2;
                text-transform: uppercase;
            }
            #pricing-section .package-card-flag--popular {
                background: #111d4a;
                color: #fff;
            }
            #pricing-section .package-card-flag--value {
                background: #8a5a08;
                color: #fff;
            }
            #pricing-section .package-card-flag--saving {
                background: #eaf9ef;
                color: #14733a;
            }
            #pricing-section .package-card-flag--event {
                background: #fff0f1;
                color: #bd101a;
            }
            #pricing-section .package-benefit {
                border: 1px solid #dce5f1;
                background: #f7f9fc;
                color: #41516b;
                letter-spacing: 0;
                text-transform: none;
            }
            #pricing-section .package-benefit--trial {
                border-color: #cce9d5;
                background: #f1fbf4;
                color: #14733a;
            }
            #pricing-section .package-benefit--instant {
                border-color: #cbdcf8;
                background: #f2f7ff;
                color: #245da8;
            }
            #pricing-section .pricing-unavailable-cta {
                display: inline-flex;
                min-height: 52px;
                align-items: center;
                justify-content: center;
                padding: 14px 18px;
                flex: 1 1 auto;
                border: 1px solid #d9dfe8;
                border-radius: 14px;
                background: #eef1f5;
                color: #697386;
                font-size: 15px;
                font-weight: 800;
                cursor: not-allowed;
            }
            #pricing-section .price-block .lower-box {
                display: flex;
                min-height: 0;
                padding: 22px 22px 24px !important;
                flex: 1 1 auto;
                flex-direction: column;
            }
            #pricing-section .price-block .price-list {
                margin: 0;
                padding: 0;
                flex: 1 1 auto;
            }
            #pricing-section .price-block .price-list li {
                margin-bottom: 14px;
                padding: 0 0 0 30px;
                color: #3f4b61;
                font-size: 14px;
                font-weight: 500;
                line-height: 1.55;
                text-align: start;
            }
            #pricing-section .price-block .price-list li::before {
                display: inline-flex;
                width: 19px;
                height: 19px;
                align-items: center;
                justify-content: center;
                inset-inline-start: 0;
                top: 1px;
                border-radius: 50%;
                background: #fff0f1;
                color: #d71722;
                content: "\2713";
                font-family: inherit;
                font-size: 11px;
                font-weight: 900;
                line-height: 1;
            }
            #pricing-section .price-block .button-box {
                gap: 12px;
                margin-top: 22px;
            }
            #pricing-section .price-block .button-box .theme-btn {
                min-width: 0;
                padding: 14px 18px;
                flex: 1 1 auto;
                border-radius: 14px;
            }
            #pricing-section .price-block .button-box .theme-btn .txt {
                color: #fff;
                font-size: 16px;
                font-weight: 800;
            }
            #pricing-section .price-block .button-box > a:not(.pricing-buy-cta) {
                display: inline-flex;
                width: 52px;
                height: 52px;
                flex: 0 0 52px;
                align-items: center;
                justify-content: center;
                border: 1px solid #d9ebdf;
                border-radius: 14px;
                background: #f5fff8;
                box-shadow: 0 8px 20px rgba(22, 163, 74, .1);
                transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
            }
            #pricing-section .price-block .button-box > a:not(.pricing-buy-cta):hover {
                transform: translateY(-1px);
                border-color: #9dd5ad;
                box-shadow: 0 12px 24px rgba(22, 163, 74, .15);
            }
            #pricing-section .price-block .button-box .whatsapp {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                box-shadow: none;
            }
            [dir="rtl"] #pricing-section .price-block .price-list li { padding: 0 30px 0 0; }
            [dir="rtl"] #pricing-section #normalPackages .pkg-item,
            [dir="rtl"] #pricing-section #resellerPackages .pkg-item {
                direction: rtl;
                text-align: right;
            }
            #pricing-section .pricing-compare-modal[hidden] { display: none !important; }
            #pricing-section .pricing-compare-modal {
                position: fixed;
                z-index: 1100;
                inset: 0;
                display: grid;
                place-items: center;
                padding: 20px;
                background: rgba(6, 16, 57, .66);
                backdrop-filter: blur(5px);
            }
            #pricing-section .pricing-compare-modal__dialog {
                width: min(980px, 100%);
                max-height: min(720px, calc(100vh - 40px));
                overflow: auto;
                border-radius: 20px;
                background: #fff;
                box-shadow: 0 30px 80px rgba(3, 9, 30, .35);
            }
            #pricing-section .pricing-compare-modal__header {
                position: sticky;
                z-index: 2;
                top: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 20px 22px;
                border-bottom: 1px solid #e6eaf1;
                background: #fff;
            }
            #pricing-section .pricing-compare-modal__header h3 {
                margin: 0;
                color: #111a2e;
                font-size: 22px;
                font-weight: 850;
            }
            #pricing-section .pricing-compare-modal__close {
                display: inline-flex;
                width: 40px;
                height: 40px;
                align-items: center;
                justify-content: center;
                border: 1px solid #dce3ed;
                border-radius: 11px;
                background: #f7f9fc;
                color: #17213a;
                font-size: 24px;
                line-height: 1;
            }
            #pricing-section .pricing-compare-table-wrap { overflow-x: auto; }
            #pricing-section .pricing-compare-table {
                width: 100%;
                min-width: 700px;
                border-collapse: collapse;
                color: #344158;
            }
            #pricing-section .pricing-compare-table th,
            #pricing-section .pricing-compare-table td {
                padding: 15px 18px;
                border-bottom: 1px solid #edf0f5;
                text-align: start;
                vertical-align: middle;
            }
            #pricing-section .pricing-compare-table th {
                background: #f8faff;
                color: #111a2e;
                font-size: 12px;
                font-weight: 900;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            #pricing-section .pricing-compare-table td strong { color: #111a2e; }
            #pricing-section .pricing-compare-table .pricing-buy-cta {
                min-height: 42px;
                padding: 10px 15px;
                border-radius: 11px;
                color: #fff;
                font-size: 13px;
                font-weight: 800;
            }
            #pricing-section .mobile-pricing-cta[hidden] { display: none !important; }
            #pricing-section .mobile-pricing-cta {
                position: fixed;
                z-index: 1070;
                right: 12px;
                bottom: 12px;
                left: 12px;
                display: none;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 10px 10px 14px;
                border: 1px solid rgba(255, 255, 255, .14);
                border-radius: 16px;
                background: rgba(10, 20, 58, .96);
                box-shadow: 0 18px 42px rgba(3, 9, 30, .32);
                color: #fff;
                backdrop-filter: blur(10px);
            }
            #pricing-section .mobile-pricing-cta small,
            #pricing-section .mobile-pricing-cta strong { display: block; }
            #pricing-section .mobile-pricing-cta small { color: #cfd7ea; font-size: 11px; }
            #pricing-section .mobile-pricing-cta strong { margin-top: 2px; font-size: 14px; }
            #pricing-section .mobile-pricing-cta__buy {
                display: inline-flex;
                min-height: 44px;
                align-items: center;
                justify-content: center;
                padding: 10px 17px;
                border-radius: 12px;
                background: #df1722;
                color: #fff;
                font-size: 13px;
                font-weight: 900;
                white-space: nowrap;
            }
            @media (hover: hover) and (pointer: fine) {
                #pricing-section .price-block .inner-box.custom-color:hover {
                    transform: translateY(-6px);
                    border-color: #d7aeb2;
                    box-shadow: 0 22px 46px rgba(15, 23, 42, .13);
                }
            }
            @media (min-width: 1101px) {
                #pricing-section #real-toggle {
                    min-height: 54px;
                    padding: 4px 16px;
                    align-self: end;
                }
            }
            @media (max-width: 1100px) {
                #pricing-section .pricing-controls {
                    grid-template-columns: minmax(0, 1fr);
                }
                #pricing-section #real-toggle { width: 100%; }
                #pricing-section .pricing-control-actions {
                    width: 100%;
                    justify-content: stretch;
                    justify-self: stretch;
                }
                #pricing-section .pricing-share-link:not(.pricing-share-link--share) {
                    flex: 1 1 auto;
                }
                #pricing-section .iptv-service-picker {
                    width: auto;
                    flex: 1 1 320px;
                }
            }
            @media (max-width: 768px) {
                #pricing-section #normalPackages.scroll-wrapper.normal-wrapper {
                    display: flex !important;
                    flex-wrap: nowrap;
                    align-items: stretch;
                    overflow-x: auto !important;
                    overflow-y: hidden;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: none;
                }
                #pricing-section #normalPackages.scroll-wrapper.normal-wrapper::-webkit-scrollbar { display: none; }
                #pricing-section #normalPackages .scroll-item {
                    display: block !important;
                    width: min(84vw, 360px) !important;
                    min-width: min(84vw, 360px) !important;
                    max-width: min(84vw, 360px) !important;
                    flex: 0 0 min(84vw, 360px) !important;
                    margin: 0;
                    scroll-snap-align: start;
                    align-self: stretch;
                }
                #pricing-section #normalPackages.scroll-wrapper.normal-wrapper,
                #pricing-section #resellerPackages .reseller-wrapper {
                    gap: 14px !important;
                    padding: 4px 4px 16px !important;
                    scroll-padding-inline: 4px;
                }
                #pricing-section .price-block .inner-box.custom-color { border-radius: 20px; }
                #pricing-section .price-block .upper-box { padding: 26px 18px 21px; }
                #pricing-section .price-block .lower-box { padding: 20px 18px 21px !important; }
                body.pricing-in-view #pricing-section .mobile-pricing-cta:not([hidden]) {
                    display: flex;
                }
            }
            @media (max-width: 640px) {
                #pricing-section .pricing-controls {
                    grid-template-columns: minmax(0, 1fr);
                    gap: 10px;
                    margin-top: 24px !important;
                    padding: 10px;
                    border-radius: 16px;
                }
                #pricing-section #real-toggle { padding: 10px 12px; }
                #pricing-section .vendor-toggle,
                #pricing-section .vendor-toggle-reseller {
                    width: 100%;
                    margin-inline-start: 0;
                    justify-self: stretch;
                }
                #pricing-section .vendor-toggle .tg,
                #pricing-section .vendor-toggle-reseller .tg {
                    min-width: 0;
                    flex: 1 1 50%;
                }
                #pricing-section .pricing-control-actions {
                    width: 100%;
                    flex-wrap: wrap;
                    justify-content: flex-start;
                    justify-self: stretch;
                }
                #pricing-section .pricing-share-link:not(.pricing-share-link--share) {
                    width: 100%;
                    flex: 1 0 100%;
                    white-space: normal;
                }
                #pricing-section .pricing-control-actions .vendor-toggle,
                #pricing-section .pricing-control-actions .vendor-toggle-reseller {
                    width: auto;
                    flex: 1 0 100%;
                }
                #pricing-section .pricing-control-actions .iptv-service-picker {
                    width: 100%;
                    flex: 1 0 100%;
                }
                #pricing-section .pricing-compare-button {
                    min-width: 0;
                    flex: 1 1 calc(100% - 66px);
                }
                #pricing-section .package-catalog-toolbar {
                    align-items: stretch;
                    gap: 14px;
                    padding: 14px;
                    flex-direction: column;
                }
                #pricing-section .package-catalog-search { width: 100%; }
                #pricing-section .package-pagination {
                    gap: 8px;
                    margin-top: 20px;
                }
                #pricing-section .package-pagination__button {
                    min-width: 0;
                    padding: 10px 14px;
                    flex: 1 1 0;
                }
                #pricing-section .package-pagination__status {
                    min-width: 66px;
                    padding-inline: 10px;
                }
            }
            @media (prefers-reduced-motion: reduce) {
                #pricing-section .price-block .inner-box.custom-color,
                #pricing-section .pricing-buy-cta,
                #pricing-section .pricing-share-link,
                #pricing-section .price-block .button-box > a:not(.pricing-buy-cta),
                #pricing-section .vendor-toggle .tg,
                #pricing-section .vendor-toggle-reseller .tg {
                    transition: none;
                }
                #pricing-section .price-block .inner-box.custom-color:hover,
                #pricing-section .pricing-buy-cta:hover,
                #pricing-section .pricing-share-link:hover,
                #pricing-section .price-block .button-box > a:not(.pricing-buy-cta):hover {
                    transform: none;
                }
            }
            .pricing-intro-shell {
                margin: 0 auto;
                text-align: center;
            }
            .pricing-intro__accent {
                width: 64px;
                height: 4px;
                margin: 0 auto 22px;
                border-radius: 999px;
                background: linear-gradient(90deg, #f04444 0%, #dc111b 100%);
                box-shadow: 0 7px 18px rgba(220, 17, 27, .18);
            }
            .pricing-section.style-two .pricing-intro__title {
                max-width: 980px;
                margin: 0 auto;
                color: #111a2e;
                font-size: clamp(30px, 3.15vw, 44px);
                font-weight: 800;
                line-height: 1.14;
                letter-spacing: -.035em;
                text-wrap: balance;
            }
            .pricing-section.style-two .pricing-intro__subtitle {
                max-width: 720px;
                margin: 10px auto 0;
                color: #253047;
                font-size: clamp(18px, 1.8vw, 22px);
                font-weight: 500;
                line-height: 1.45;
                text-wrap: balance;
            }
            .pricing-section.style-two .pricing-intro__description {
                max-width: 790px;
                margin: 18px auto 0;
                color: #5b6881;
                font-size: 16px;
                line-height: 1.75;
                text-wrap: balance;
            }
            .pricing-policy-note {
                display: grid;
                grid-template-columns: 44px minmax(0, 1fr);
                align-items: center;
                gap: 16px;
                margin: 24px auto 0;
                padding: 18px 20px;
                border: 1px solid #f0d9dc;
                border-inline-start: 4px solid #df202a;
                border-radius: 16px;
                background: linear-gradient(135deg, #fffafa 0%, #fff 72%);
                box-shadow: 0 14px 34px rgba(32, 42, 65, .07);
                color: #273249;
                text-align: start;
            }
            .pricing-policy-note__icon {
                display: inline-flex;
                width: 44px;
                height: 44px;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(223, 32, 42, .16);
                border-radius: 13px;
                background: rgba(223, 32, 42, .08);
                color: #d71923;
            }
            .pricing-policy-note__icon svg {
                width: 22px;
                height: 22px;
            }
            .pricing-policy-note__content { min-width: 0; }
            .pricing-policy-note__title {
                display: block;
                margin: 0 0 4px;
                color: #111a2e;
                font-size: 15px;
                font-weight: 800;
                line-height: 1.45;
            }
            .pricing-policy-note__text {
                margin: 0;
                color: #5b6579;
                font-size: 14px;
                line-height: 1.65;
                overflow-wrap: anywhere;
            }
            [dir="rtl"] .pricing-section.style-two .pricing-intro__title { letter-spacing: 0; }
            @media (max-width: 991px) {
                .pricing-policy-note { grid-template-columns: 44px minmax(0, 1fr); }
            }
            @media (max-width: 580px) {
                .pricing-intro__accent {
                    width: 52px;
                    margin-bottom: 18px;
                }
                .pricing-section.style-two .pricing-intro__title {
                    font-size: 28px;
                    line-height: 1.18;
                }
                .pricing-section.style-two .pricing-intro__subtitle { font-size: 18px; }
                .pricing-section.style-two .pricing-intro__description {
                    margin-top: 14px;
                    font-size: 15px;
                    line-height: 1.65;
                }
                .pricing-policy-note {
                    grid-template-columns: 38px minmax(0, 1fr);
                    gap: 12px;
                    margin-top: 20px;
                    padding: 15px 14px;
                    border-radius: 14px;
                }
                .pricing-policy-note__icon {
                    width: 38px;
                    height: 38px;
                    border-radius: 11px;
                }
                .pricing-policy-note__icon svg {
                    width: 19px;
                    height: 19px;
                }
                .pricing-policy-note__title { font-size: 14px; }
                .pricing-policy-note__text { font-size: 14px; }
            }
        </style>
    @endonce

    @php
        $providedPricingCopy = $pricingCopy ?? ($documentPricing ?? null);
        $isDocumentEnglishPricing = request()->routeIs('home') || is_array($providedPricingCopy);
        $documentPricing = is_array($providedPricingCopy)
            ? $providedPricingCopy
            : ($isDocumentEnglishPricing ? __('document_home.pricing') : []);
        $initialMode = $initialMode ?? 'iptv';
        $showResellerInitially = $initialMode === 'reseller';
        $displayPackages = collect($packages ?? [])->values()->all();
        if ($isDocumentEnglishPricing && empty($displayPackages)) {
            $documentPlanPrices = [
                'monthly' => $documentPricing['plans']['monthly']['price']
                    ?? ('$2.99 / ' . $documentPricing['plans']['monthly']['title']),
                'three_months' => $documentPricing['plans']['three_months']['price']
                    ?? ('$7.99 / ' . $documentPricing['plans']['three_months']['title']),
                'half_yearly' => $documentPricing['plans']['half_yearly']['price']
                    ?? ('$14.99 / ' . $documentPricing['plans']['half_yearly']['title']),
                'yearly' => $documentPricing['plans']['yearly']['price']
                    ?? ('$23.99 / ' . $documentPricing['plans']['yearly']['title']),
            ];

            foreach ($documentPlanPrices as $planKey => $price) {
                $displayPackages[] = [
                    'vendor' => 'opplex',
                    'title' => $documentPricing['plans'][$planKey]['title'],
                    'price' => $price,
                    'is_duration_plan' => true,
                    'duration_months' => match ($planKey) {
                        'three_months' => 3,
                        'half_yearly' => 6,
                        'yearly' => 12,
                        default => 1,
                    },
                    'features' => $documentPricing['plans'][$planKey]['features'],
                ];
            }
        }
        $hasCatalogPlans = collect($displayPackages)
            ->contains(static fn ($package) => !data_get($package, 'is_duration_plan', false));
        $catalogServiceName = static function ($package): string {
            if (data_get($package, 'is_duration_plan', false)) {
                return strtolower((string) data_get($package, 'vendor')) === 'starshare'
                    ? 'Filex'
                    : 'Opplex';
            }

            $title = trim((string) data_get($package, 'title', ''));

            return trim((string) preg_replace(
                '/\s+-\s+(?:3 Months|Half Yearly|Yearly)$/iu',
                '',
                $title
            ));
        };

        if ($hasCatalogPlans) {
            $displayPackages = collect($displayPackages)
                ->reject(static fn ($package) => data_get($package, 'is_duration_plan', false))
                ->values()
                ->all();
        }

        $allIptvServices = collect($displayPackages)
            ->map($catalogServiceName)
            ->filter()
            ->unique()
            ->values();
        $servicePackageGroups = collect($displayPackages)->groupBy($catalogServiceName);
        $featuredIptvServices = $allIptvServices
            ->filter(static fn ($service) => $servicePackageGroups->get($service, collect())
                ->contains(static fn ($package) => (bool) data_get($package, 'is_featured', false)))
            ->values();
        $iptvServices = $featuredIptvServices
            ->concat($allIptvServices->reject(static fn ($service) => $featuredIptvServices->contains($service)))
            ->values()
            ->all();
        $monthlyPricesByService = collect($displayPackages)
            ->filter(static fn ($package) => (int) data_get($package, 'duration_months', 1) === 1)
            ->mapWithKeys(static fn ($package) => [
                $catalogServiceName($package) => (float) data_get($package, 'price_amount', 0),
            ]);
        $activeEventPromotion = app(\App\Services\EventPromotionService::class)->activeForSession();
        $initialIptvService = $iptvServices[0] ?? 'Opplex';
        $initialIptvPackageCount = collect($displayPackages)
            ->filter(static fn ($package) => $catalogServiceName($package) === $initialIptvService)
            ->count();
    @endphp

    <div class="auto-container">

        <div class="{{ $containerClass ?? 'container' }} pricing-intro-shell">
            @unless ($isMobile ?? false)
                <div class="pricing-intro__accent" aria-hidden="true"></div>
            @endunless

            <h2 class="pricing-intro__title">{{ $isDocumentEnglishPricing ? $documentPricing['heading'] : ($pricingSection['heading'] ?? __('messages.pricing_heading')) }}</h2>

            @unless (request()->is('packages') || request()->is('pricing') || request()->is('reseller-panel'))
                <p class="pricing-intro__subtitle">{{ $isDocumentEnglishPricing ? ($documentPricing['subheading'] ?? ($pricingSection['subheading'] ?? __('messages.pricing_subheading'))) : ($pricingSection['subheading'] ?? __('messages.pricing_subheading')) }}</p>
            @endunless

            @if ($isDocumentEnglishPricing)
                <p class="home-document-pricing__intro pricing-intro__description">
                    {{ $hasCatalogPlans ? __('messages.iptv_packages_desc') : $documentPricing['intro'] }}
                </p>
            @endif

            <aside class="pricing-policy-note" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}"
                aria-label="{{ __('messages.final_sale_no_refunds') }}">
                <span class="pricing-policy-note__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M12 3 4.5 6v5.2c0 4.7 3.1 7.8 7.5 9.8 4.4-2 7.5-5.1 7.5-9.8V6L12 3Z" />
                        <path d="M12 8v5" />
                        <circle cx="12" cy="16.5" r=".8" fill="currentColor" stroke="none" />
                    </svg>
                </span>
                <div class="pricing-policy-note__content">
                    <strong class="pricing-policy-note__title">{{ __('messages.final_sale_no_refunds') }}</strong>
                    <p class="pricing-policy-note__text">
                        {{ __('messages.final_sale_confirmed') }}
                        {{ __('messages.final_sale_verify_before_payment') }}
                    </p>
                </div>
            </aside>
        </div>

        

        <div class="pricing-controls d-flex align-items-center justify-content-between mb-3 mt-3">
            <div id="real-toggle">
                <label class="form-switch m-0">
                    <input type="checkbox" id="resellerToggle" @checked($showResellerInitially)>
                    <i></i>
                    <span>{{ $pricingSection['show_reseller_label'] ?? __('messages.show_reseller_packages') }}</span>
                </label>
            </div>

            <div class="pricing-control-actions">
                <a id="packageDetailsWhatsApp" class="pricing-share-link"
                    href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('messages.whatsapp_pricing')) }}"
                    target="_blank" rel="noopener noreferrer"
                    aria-label="Get package details on WhatsApp"
                    title="Get package details on WhatsApp"
                    data-whatsapp-click data-whatsapp-placement="pricing_package_details"
                    data-whatsapp-intent="package_details" data-whatsapp-package="all_packages"
                    data-whatsapp-lead-reference>
                    <img src="{{ asset('images/whatsapp.webp') }}" width="25" height="25" alt="" aria-hidden="true">
                    <span>Get package details on WhatsApp</span>
                </a>

                <a id="shareAllPackages" class="pricing-share-link pricing-share-link--share"
                    href="https://api.whatsapp.com/send?text={{ rawurlencode(__('document_ui.home.pricing_aria') . "\n" . route('packages', ['direct' => 1])) }}"
                    target="_blank" rel="noopener noreferrer"
                    aria-label="Share all package details on WhatsApp"
                    title="Share all package details on WhatsApp"
                    data-whatsapp-click data-whatsapp-placement="pricing_share_all"
                    data-whatsapp-intent="package_share" data-whatsapp-package="all_packages">
                    <i class="fa fa-share-alt" aria-hidden="true"></i>
                </a>

                <button type="button" id="comparePlansButton" class="pricing-compare-button"
                    aria-haspopup="dialog" aria-controls="pricingCompareModal">
                    <i class="fa fa-columns" aria-hidden="true"></i>
                    <span>Compare Plans</span>
                </button>

                <label id="iptvServicePicker" class="iptv-service-picker"
                    @if ($showResellerInitially) style="display:none" @endif>
                    <span>{{ __('messages.checkout_provider') }}</span>
                    <select id="iptvServiceSelect" aria-label="{{ __('document_ui.home.choose_iptv_vendor') }}">
                        @foreach ($iptvServices as $service)
                            <option value="{{ $service }}" @selected($service === $initialIptvService)
                                data-featured="{{ $featuredIptvServices->contains($service) ? '1' : '0' }}">
                                {{ $featuredIptvServices->contains($service) ? '★ ' : '' }}{{ $service }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div id="vendorToggleReseller" class="vendor-toggle-reseller" role="group"
                    aria-label="{{ __('document_ui.home.choose_reseller_vendor') }}" style="display:{{ $showResellerInitially ? 'inline-flex' : 'none' }}">
                    <button type="button" class="tg active" data-vendor="opplex" aria-pressed="true">Opplex</button>
                    <button type="button" class="tg" data-vendor="starshare" aria-pressed="false">Filex</button>
                </div>
            </div>
        </div>

        @if ($activeEventPromotion)
            <div class="pricing-promotion-banner" role="status">
                <i class="fa fa-tag" aria-hidden="true"></i>
                <span>{{ $activeEventPromotion['name'] }}: {{ number_format((float) $activeEventPromotion['discount_percent'], 0) }}% OFF is active and will be applied at checkout.</span>
            </div>
        @endif

        <div id="creditInfo" class="sec-title centered mb-4" style="display:{{ $showResellerInitially ? 'block' : 'none' }}">
            <p><strong>
                {!! $pricingSection['credit_info'] ?? (
                    '<span style="color:red;">1 '.__('messages.credit').'</span> = '.__('messages.1_month').
                    ' &nbsp;<i class="fa fa-plus"></i>&nbsp; '.
                    '<span style="color:red;">5 '.__('messages.credit').'</span> = '.__('messages.6_months').
                    ' &nbsp;<i class="fa fa-plus"></i>&nbsp; '.
                    '<span style="color:red;">10 '.__('messages.credit').'</span> = '.__('messages.12_months')
                ) !!}
            </strong></p>
        </div>

        <p id="iptvPackagesEmpty" class="package-empty-state" hidden>{{ __('messages.no_results') }}</p>

        <div class="scroll-wrapper normal-wrapper" id="normalPackages"
            @if ($showResellerInitially) style="display:none!important" @endif>
            @foreach ($displayPackages as $package)
                @php
                    $vendorRaw = strtolower(data_get($package, 'vendor', 'opplex'));
                    $vendorRaw = in_array($vendorRaw, ['opplex', 'starshare']) ? $vendorRaw : 'opplex';
                    $vendorKey = $vendorRaw;

                    $buyPrice = data_get($package, 'price_amount');
                    if ($buyPrice === null) {
                        $plainPrice = trim(strip_tags(data_get($package, 'price', '')));
                        preg_match_all('/(?:USD\s*)?\$\s*(\d+(?:\.\d+)?)/i', $plainPrice, $priceMatches);
                        $buyPrice = $priceMatches[1] ? end($priceMatches[1]) : null;
                    }
                    $buyPrice = $buyPrice !== null ? number_format((float) $buyPrice, 2, '.', '') : null;

                    $rawTitle = (string) data_get($package, 'title', '');
                    $serviceName = $catalogServiceName($package);
                    $durationMonths = (int) data_get($package, 'duration_months', 1);
                    $durationPlanKey = match ($durationMonths) {
                        3 => 'three_months',
                        6 => 'half_yearly',
                        12 => 'yearly',
                        default => 'monthly',
                    };
                    if (data_get($package, 'is_duration_plan', false)) {
                        $titleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $rawTitle);
                        $titleBase = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $titleNoParen, 1));
                    } else {
                        $titleBase = $serviceName;
                    }
                    $displayTitle = $hasCatalogPlans
                        ? ($documentPricing['plans'][$durationPlanKey]['title']
                            ?? __('document_commerce.packages.pricing.plans.' . $durationPlanKey . '.title'))
                        : $titleBase;
                    $fullPlanTitle = $hasCatalogPlans
                        ? $serviceName . ' - ' . $displayTitle
                        : $displayTitle;
                    $displayPrice = $package['price'] ?? '';
                    $displayFeatures = $package['features'] ?? [];
                    $tierLabel = null;
                    $tierClass = null;

                    if ($isDocumentEnglishPricing && $vendorKey === 'opplex'
                        && data_get($package, 'is_duration_plan', false)) {
                         $documentPlanKey = match ($durationMonths) {
                            3 => 'three_months',
                            6 => 'half_yearly',
                            12 => 'yearly',
                            default => match (true) {
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), '3 month') => 'three_months',
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), 'half'),
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), '6 month') => 'half_yearly',
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), 'year'),
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), '12 month') => 'yearly',
                                default => 'monthly',
                            },
                        };
                        $documentPlan = $documentPricing['plans'][$documentPlanKey] ?? null;

                        if ($documentPlan) {
                            $displayTitle = $documentPlan['title'];
                            $displayPrice = $documentPlan['price'] ?? ($package['price'] ?? '');
                            $displayFeatures = $documentPlan['features'];
                        }
                    }

                    if ($hasCatalogPlans && !data_get($package, 'is_duration_plan', false) && $buyPrice !== null) {
                        $displayPrice = '$' . $buyPrice . ' / ' . ($durationMonths === 1
                            ? '1 month'
                            : $durationMonths . ' months');
                    }

                    if (!data_get($package, 'is_duration_plan', false) && !empty($displayFeatures)) {
                        $firstFeature = trim((string) reset($displayFeatures));
                        $normalizedTier = strtolower($firstFeature);

                        if (in_array($normalizedTier, ['basic plan', 'standard plan', 'premium plan', 'most premium plan'], true)) {
                            $tierLabel = $firstFeature;
                            $tierClass = str_contains($normalizedTier, 'premium')
                                ? 'premium'
                                : str_replace(' plan', '', $normalizedTier);
                            $displayFeatures = array_values(array_slice($displayFeatures, 1));
                        }
                    }

                    if ($hasCatalogPlans && !data_get($package, 'is_duration_plan', false)) {
                        [$tierLabel, $tierClass] = match ($durationMonths) {
                            3 => ['Standard Plan', 'standard'],
                            6 => ['Advanced Plan', 'advanced'],
                            12 => ['Premium Plan', 'premium'],
                            default => ['Basic Plan', 'basic'],
                        };
                    }

                    $brandMonogram = null;
                    if (!data_get($package, 'is_duration_plan', false)) {
                        $monogramTitle = preg_replace('/\([^)]*\)|\b(?:IPTV|OTT|LIVE|TV)\b/iu', ' ', $serviceName);
                        $monogramWords = array_values(array_filter(array_map(
                            static fn ($word) => preg_replace('/[^\p{L}\p{N}]+/u', '', $word),
                            preg_split('/\s+/u', trim((string) $monogramTitle)) ?: []
                        )));

                        if (count($monogramWords) >= 2) {
                            $brandMonogram = mb_substr($monogramWords[0], 0, 1)
                                . mb_substr($monogramWords[1], 0, 1);
                        } elseif (!empty($monogramWords[0])) {
                            $brandMonogram = mb_substr($monogramWords[0], 0, 2);
                        }

                        $brandMonogram = mb_strtoupper($brandMonogram ?: 'TV');
                    }

                    $packageIcon = trim((string) data_get($package, 'icon', ''));
                    $providerLogo = null;
                    if ($packageIcon !== '' && preg_match('/\.(?:avif|gif|jpe?g|png|svg|webp)(?:\?.*)?$/i', $packageIcon)) {
                        $providerLogo = preg_match('#^https?://#i', $packageIcon)
                            ? $packageIcon
                            : asset(ltrim($packageIcon, '/'));
                    }

                    $badgeKey = (string) data_get($package, 'badge_key', '');
                    $badgeLabel = match ($badgeKey) {
                        'most_popular' => 'Most Popular',
                        'best_value' => 'Best Value',
                        default => null,
                    };
                    $badgeClass = $badgeKey === 'best_value' ? 'value' : 'popular';
                    $isAvailable = (bool) data_get($package, 'is_available', true);
                    $freeTrialHours = (int) data_get($package, 'free_trial_hours', 0);
                    $instantActivation = (bool) data_get($package, 'instant_activation', false);
                    $basePriceAmount = (float) ($buyPrice ?? 0);
                    $monthlyPriceAmount = (float) $monthlyPricesByService->get($serviceName, 0);
                    $regularDurationPrice = $monthlyPriceAmount * max(1, $durationMonths);
                    $savingPercent = $durationMonths > 1
                        && $regularDurationPrice > 0
                        && $basePriceAmount < ($regularDurationPrice - 0.005)
                            ? (int) round((($regularDurationPrice - $basePriceAmount) / $regularDurationPrice) * 100)
                            : 0;
                    $promotionPercent = (int) ($activeEventPromotion['discount_percent'] ?? 0);
                @endphp

                <div class="price-block scroll-item pkg-item {{ data_get($package, 'is_duration_plan', false) ? 'pkg-item--duration' : 'pkg-item--'.($tierClass ?: 'standard') }}"
                    data-type="iptv" data-vendor="{{ $vendorKey }}"
                    @if ($serviceName !== $initialIptvService) style="display:none!important" @endif
                    data-service="{{ $serviceName }}"
                    data-duration="{{ $durationMonths }}"
                    data-badge="{{ $badgeLabel }}" data-saving="{{ $savingPercent }}"
                    data-base-saving="{{ $savingPercent }}"
                    data-trial-hours="{{ $freeTrialHours }}" data-instant="{{ $instantActivation ? '1' : '0' }}"
                    data-available="{{ $isAvailable ? '1' : '0' }}"
                    data-package-id="{{ data_get($package, 'id') }}"
                    data-plan="{{ $fullPlanTitle }}" data-price="{{ $buyPrice }}">
                    <div class="inner-box custom-color">
                        <div class="upper-box"
                            @unless ($isMobile ?? false) style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');" @endunless>
                            <div class="package-card-flags">
                                @if ($badgeLabel)
                                    <span class="package-card-flag package-card-flag--{{ $badgeClass }}">{{ $badgeLabel }}</span>
                                @endif
                                @if ($savingPercent > 0)
                                    <span class="package-card-flag package-card-flag--saving" data-package-saving>Save {{ $savingPercent }}%</span>
                                @endif
                                @if ($promotionPercent > 0)
                                    <span class="package-card-flag package-card-flag--event">{{ $promotionPercent }}% OFF</span>
                                @endif
                            </div>
                            <ul class="icon-list">
                                @if ($providerLogo)
                                    <li>
                                        <img class="package-provider-logo" src="{{ $providerLogo }}"
                                            alt="{{ $serviceName }} logo" width="56" height="56" loading="lazy" decoding="async">
                                    </li>
                                @elseif ($brandMonogram)
                                    <li aria-hidden="true">
                                        <span class="package-brand-mark package-brand-mark--{{ $tierClass ?: 'standard' }}">{{ $brandMonogram }}</span>
                                    </li>
                                @else
                                    <li><span class="icon"><img src="{{ asset('images/icons/service-1.svg') }}"
                                                alt="IPTV" width="48" height="48" loading="lazy" decoding="async"></span></li>
                                @endif
                            </ul>
                            @if ($tierLabel)
                                <span class="package-tier-badge package-tier-badge--{{ $tierClass }}">{{ $tierLabel }}</span>
                            @endif
                            @if ($hasCatalogPlans)
                                <p class="package-service-name">{{ $serviceName }}</p>
                            @endif
                            <h3 class="package-plan-title">{{ $displayTitle }} <span data-package-price-label>{{ $displayPrice }}</span></h3>
                        </div>

                        <div class="lower-box">
                            @if ($freeTrialHours > 0 || $instantActivation)
                                <div class="package-benefits">
                                    @if ($freeTrialHours > 0)
                                        <span class="package-benefit package-benefit--trial">
                                            <i class="fa fa-gift" aria-hidden="true"></i>
                                            {{ $freeTrialHours }}-hour Free Trial
                                        </span>
                                    @endif
                                    @if ($instantActivation)
                                        <span class="package-benefit package-benefit--instant">
                                            <i class="fa fa-bolt" aria-hidden="true"></i>
                                            Instant Activation
                                        </span>
                                    @endif
                                </div>
                            @endif
                            @if (!empty($displayFeatures))
                                <ul class="price-list">
                                    @foreach ($displayFeatures as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="button-box package-price-button d-flex align-items-center">
                                @if ($isAvailable)
                                    <a rel="noopener"
                                        href="{{ route('configure', [
                                            'price' => $buyPrice,
                                            'ptype' => 'iptv',
                                            'plan' => $fullPlanTitle,
                                            'vendor' => $vendorKey,
                                            'package_id' => data_get($package, 'id'),
                                        ]) }}"
                                        class="theme-btn btn-style-four pricing-buy-cta" data-package-buy>
                                        <span class="txt">{{ __('messages.buy_now') }}</span>
                                    </a>
                                @else
                                    <span class="pricing-unavailable-cta" aria-disabled="true">Out of Stock</span>
                                @endif

                                @if ($buyPrice && $isAvailable)
                                    <a rel="noopener" data-whatsapp-click data-whatsapp-placement="pricing_card"
                                        data-whatsapp-intent="package" data-whatsapp-package="{{ $fullPlanTitle }}"
                                        data-whatsapp-value="{{ $buyPrice }}" data-whatsapp-currency="{{ config('services.app.default_currency', 'USD') }}"
                                        data-whatsapp-vendor="{{ $vendorKey }}"
                                        data-whatsapp-lead-reference
                                        href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $fullPlanTitle, 'price' => $buyPrice])) }}">
                                        <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}" width="32"
                                            height="32" alt="WhatsApp" loading="lazy" decoding="async" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <nav id="iptvPackagesPagination" class="package-pagination"
            aria-label="{{ __('document_ui.movies.pagination_aria') }}" hidden>
            <button type="button" class="package-pagination__button" data-package-page="previous"
                aria-label="{{ __('document_ui.movies.previous_page_aria') }}">
                <span aria-hidden="true">&larr;</span>
                <span>{{ __('document_ui.movies.previous_label') }}</span>
            </button>
            <span class="package-pagination__status" data-package-page-status aria-live="polite"></span>
            <button type="button" class="package-pagination__button" data-package-page="next"
                aria-label="{{ __('document_ui.movies.next_page_aria') }}">
                <span>{{ __('document_ui.movies.next_label') }}</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </nav>

        <div id="resellerPackages" style="display:{{ $showResellerInitially ? 'block' : 'none' }}"
            aria-label="{{ __('document_ui.home.reseller_packages_aria') }}">
            <div class="reseller-wrapper">
                @foreach ($resellerPlans as $plan)
                    @php
                        $vendorResRaw = strtolower(data_get($plan, 'vendor', 'opplex'));
                        $vendorResKey = in_array($vendorResRaw, ['opplex', 'starshare']) ? $vendorResRaw : 'opplex';

                        $buyPrice = data_get($plan, 'price_amount');
                        if ($buyPrice === null) {
                            $plainPrice = trim(strip_tags($plan['price'] ?? ''));
                            preg_match_all('/(?:USD\s*)?\$\s*(\d+(?:\.\d+)?)/i', $plainPrice, $priceMatches);
                            $buyPrice = $priceMatches[1] ? end($priceMatches[1]) : null;
                        }
                        $buyPrice = $buyPrice !== null ? number_format((float) $buyPrice, 2, '.', '') : null;
                        $resellerRawTitle = (string) data_get($plan, 'title', '');
                        $resellerTitleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $resellerRawTitle);
                        $resellerDisplayTitle = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $resellerTitleNoParen, 1));
                        $resellerAvailable = (bool) data_get($plan, 'is_available', true);
                    @endphp

                    <div class="price-block reseller-price-block pkg-item d-flex flex-column justify-content-between"
                        data-type="reseller" data-vendor="{{ $vendorResKey }}"
                        data-available="{{ $resellerAvailable ? '1' : '0' }}"
                        data-package-id="{{ data_get($plan, 'id') }}" data-plan="{{ $resellerDisplayTitle }}" data-price="{{ $buyPrice }}">
                        <div class="inner-box custom-color">
                            <div class="upper-box"
                                @unless ($isMobile ?? false)
                                    style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');"
                                @endunless>
                                <ul class="icon-list">
                                    @php
                                        $primaryResellerIcon = collect($plan['icons'] ?? [])->first();
                                    @endphp
                                    @if ($primaryResellerIcon)
                                        <li><span class="icon"><img src="{{ asset($primaryResellerIcon) }}" alt="{{ __('document_ui.home.reseller_icon_alt') }}"
                                                    width="48" height="48" loading="lazy" decoding="async"></span></li>
                                    @endif
                                </ul>
                                <h3 class="package-plan-title">{{ $resellerDisplayTitle }}<span>{!! $plan['price'] !!}</span></h3>
                            </div>

                            <div class="lower-box">
                                <ul class="price-list">
                                    @foreach ($plan['features'] ?? [] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>

                                <div class="button-box button-box-2 d-flex align-items-center">
                                    @if ($resellerAvailable)
                                        <a rel="noopener"
                                            href="{{ route('configure', [
                                                'price' => $buyPrice,
                                                'ptype' => 'reseller',
                                                'plan' => $resellerDisplayTitle,
                                                'vendor' => $vendorResKey,
                                                'package_id' => data_get($plan, 'id'),
                                            ]) }}"
                                            class="theme-btn btn-style-four pricing-buy-cta" data-package-buy>
                                            <span class="txt">{{ __('messages.buy_now') }}</span>
                                        </a>
                                    @else
                                        <span class="pricing-unavailable-cta" aria-disabled="true">Out of Stock</span>
                                    @endif

                                    @if ($resellerAvailable)
                                        <a rel="noopener" data-whatsapp-click data-whatsapp-placement="pricing_card"
                                            data-whatsapp-intent="reseller" data-whatsapp-package="{{ $resellerDisplayTitle }}"
                                            data-whatsapp-value="{{ $buyPrice }}" data-whatsapp-currency="{{ config('services.app.default_currency', 'USD') }}"
                                            data-whatsapp-vendor="{{ $vendorResKey }}"
                                            data-whatsapp-lead-reference
                                            href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $resellerDisplayTitle, 'price' => $buyPrice])) }}">
                                            <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}"
                                                width="32" height="32" alt="WhatsApp" loading="lazy" decoding="async" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div id="pricingCompareModal" class="pricing-compare-modal" role="dialog" aria-modal="true"
            aria-labelledby="pricingCompareTitle" hidden>
            <div class="pricing-compare-modal__dialog">
                <div class="pricing-compare-modal__header">
                    <h3 id="pricingCompareTitle">Compare <span data-compare-provider>{{ $initialIptvService }}</span> Plans</h3>
                    <button type="button" class="pricing-compare-modal__close" data-compare-close
                        aria-label="Close comparison">&times;</button>
                </div>
                <div class="pricing-compare-table-wrap">
                    <table class="pricing-compare-table">
                        <thead>
                            <tr>
                                <th scope="col">Duration</th>
                                <th scope="col">Price</th>
                                <th scope="col">Saving</th>
                                <th scope="col">Benefits</th>
                                <th scope="col">Availability</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody data-compare-rows></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="mobilePricingCta" class="mobile-pricing-cta" hidden>
            <div>
                <small data-sticky-provider>{{ $initialIptvService }}</small>
                <strong data-sticky-plan></strong>
            </div>
            <a class="mobile-pricing-cta__buy" href="#" data-sticky-buy>{{ __('messages.buy_now') }}</a>
        </div>

    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resellerToggle = document.getElementById('resellerToggle');
        const iptvServicePicker = document.getElementById('iptvServicePicker');
        const iptvServiceSelect = document.getElementById('iptvServiceSelect');
        const resellerVendorToggle = document.getElementById('vendorToggleReseller');

        const normalPackagesWrap = document.getElementById('normalPackages');
        const resellerWrap = document.getElementById('resellerPackages');
        const creditInfo = document.getElementById('creditInfo');
        const shareAllPackages = document.getElementById('shareAllPackages');
        const iptvPagination = document.getElementById('iptvPackagesPagination');
        const iptvPageStatus = iptvPagination?.querySelector('[data-package-page-status]');
        const iptvPreviousPage = iptvPagination?.querySelector('[data-package-page="previous"]');
        const iptvNextPage = iptvPagination?.querySelector('[data-package-page="next"]');
        const iptvCatalogToolbar = document.getElementById('iptvCatalogToolbar');
        const iptvPackageSearch = document.getElementById('iptvPackageSearch');
        const iptvResultCount = iptvCatalogToolbar?.querySelector('[data-package-result-count]');
        const iptvEmptyState = document.getElementById('iptvPackagesEmpty');
        const comparePlansButton = document.getElementById('comparePlansButton');
        const compareModal = document.getElementById('pricingCompareModal');
        const compareRows = compareModal?.querySelector('[data-compare-rows]');
        const compareProvider = compareModal?.querySelector('[data-compare-provider]');
        const mobilePricingCta = document.getElementById('mobilePricingCta');
        const stickyProvider = mobilePricingCta?.querySelector('[data-sticky-provider]');
        const stickyPlan = mobilePricingCta?.querySelector('[data-sticky-plan]');
        const stickyBuy = mobilePricingCta?.querySelector('[data-sticky-buy]');

        const iptvCards = document.querySelectorAll('.pkg-item[data-type="iptv"]');
        const resellerCards = document.querySelectorAll('.pkg-item[data-type="reseller"]');
        const hasShareablePackages = Array.from([...iptvCards, ...resellerCards])
            .some(card => card.dataset.available !== '0');
        let iptvPage = 1;
        let pricingSelect2Jquery = null;
        let pricingSelect2Promise = null;
        let compareReturnFocus = null;

        const norm = s => (s || '').toString().trim().toLowerCase();
        const isMobilePricing = () => window.matchMedia('(max-width: 768px)').matches;
        const getNormalPackagesDisplay = () => isMobilePricing() ? 'flex' : 'grid';
        const getIptvPageSize = () => (isMobilePricing() ? 4 : 8);
        const shareLabels = {
            title: @json(__('document_ui.home.pricing_aria')),
            iptv: @json(__('messages.checkout_iptv_packages_label')),
            reseller: @json(__('messages.checkout_reseller_packages_label')),
            packagesLink: @json(__('messages.nav_packages'))
        };
        const packagesUrl = @json(route('packages', ['direct' => 1]));
        const track = (name, params, metaEvent) => {
            if (typeof window.trackMarketingEvent === 'function') {
                window.trackMarketingEvent(name, params, metaEvent);
            }
        };

        const cleanText = element => (element && element.textContent ? element.textContent : '')
            .replace(/\s+/g, ' ')
            .trim();

        const isCardShown = card => card && card.style.display !== 'none';
        const formatPackageMoney = value => '$' + Number(value || 0).toFixed(2);

        function syncStickyFromCard(preferredCard = null) {
            if (!mobilePricingCta) return;

            const pool = resellerToggle?.checked ? resellerCards : iptvCards;
            const card = preferredCard && isCardShown(preferredCard)
                ? preferredCard
                : Array.from(pool).find(item => isCardShown(item)
                    && item.dataset.available !== '0'
                    && item.querySelector('[data-package-buy]'));
            const buyLink = card?.querySelector('[data-package-buy]');

            if (!card || !buyLink || !isMobilePricing()) {
                mobilePricingCta.hidden = true;
                return;
            }

            mobilePricingCta.hidden = false;
            stickyProvider.textContent = card.dataset.service
                || (norm(card.dataset.vendor) === 'starshare' ? 'Filex' : 'Opplex');
            stickyPlan.textContent = `${cleanText(card.querySelector('.package-plan-title'))}`;
            stickyBuy.href = buyLink.href;
        }

        function addComparisonCell(row, value, className = '') {
            const cell = document.createElement('td');
            if (className) cell.className = className;
            if (value instanceof Node) cell.appendChild(value);
            else cell.textContent = value;
            row.appendChild(cell);
            return cell;
        }

        function renderComparison() {
            if (!compareRows) return;

            const service = norm(iptvServiceSelect?.value);
            const cards = Array.from(iptvCards)
                .filter(card => !service || norm(card.dataset.service) === service)
                .sort((left, right) => Number(left.dataset.duration || 0) - Number(right.dataset.duration || 0));

            compareRows.textContent = '';
            if (compareProvider) {
                compareProvider.textContent = iptvServiceSelect?.selectedOptions?.[0]?.textContent.replace(/^\s*★\s*/, '').trim()
                    || cards[0]?.dataset.service
                    || '';
            }

            cards.forEach(card => {
                const row = document.createElement('tr');
                const title = document.createElement('strong');
                title.textContent = cleanText(card.querySelector('.package-plan-title'))
                    .replace(cleanText(card.querySelector('[data-package-price-label]')), '')
                    .trim();
                addComparisonCell(row, title);
                addComparisonCell(row, cleanText(card.querySelector('[data-package-price-label]')));
                addComparisonCell(row, Number(card.dataset.saving || 0) > 0
                    ? `Save ${card.dataset.saving}%`
                    : '—');

                const benefits = [];
                if (card.dataset.badge) benefits.push(card.dataset.badge);
                if (Number(card.dataset.trialHours || 0) > 0) benefits.push(`${card.dataset.trialHours}-hour free trial`);
                if (card.dataset.instant === '1') benefits.push('Instant activation');
                addComparisonCell(row, benefits.join(' · ') || '—');
                addComparisonCell(row, card.dataset.available === '0' ? 'Out of Stock' : 'Available');

                const action = document.createElement(card.dataset.available === '0' ? 'span' : 'a');
                if (card.dataset.available === '0') {
                    action.textContent = 'Unavailable';
                } else {
                    const cardBuy = card.querySelector('[data-package-buy]');
                    action.href = cardBuy?.href || '#';
                    action.className = 'pricing-buy-cta';
                    action.textContent = @json(__('messages.buy_now'));
                }
                addComparisonCell(row, action);
                compareRows.appendChild(row);
            });
        }

        function openComparison() {
            if (!compareModal) return;
            compareReturnFocus = document.activeElement;
            renderComparison();
            compareModal.hidden = false;
            document.body.style.overflow = 'hidden';
            compareModal.querySelector('[data-compare-close]')?.focus();
        }

        function closeComparison() {
            if (!compareModal || compareModal.hidden) return;
            compareModal.hidden = true;
            document.body.style.overflow = '';
            compareReturnFocus?.focus?.();
        }

        function loadPricingStylesheet(href) {
            const absoluteHref = new URL(href, document.baseURI).href;
            const existing = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .find(link => link.href === absoluteHref);
            if (existing) return Promise.resolve();

            return new Promise(function(resolve, reject) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = absoluteHref;
                link.addEventListener('load', resolve, { once: true });
                link.addEventListener('error', reject, { once: true });
                document.head.appendChild(link);
            });
        }

        function loadPricingScript(id, src, isReady) {
            const existing = document.getElementById(id);
            if (typeof isReady === 'function' && isReady()) return Promise.resolve();

            if (existing) {
                if (existing.dataset.loaded === 'true') return Promise.resolve();
                return new Promise(function(resolve, reject) {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                });
            }

            return new Promise(function(resolve, reject) {
                const script = document.createElement('script');
                script.id = id;
                script.src = src;
                script.async = true;
                script.addEventListener('load', function() {
                    script.dataset.loaded = 'true';
                    resolve();
                }, { once: true });
                script.addEventListener('error', reject, { once: true });
                document.head.appendChild(script);
            });
        }

        function initializePricingSelect2(jquery) {
            if (!iptvServiceSelect || !jquery || !jquery.fn || !jquery.fn.select2) return;

            const serviceSelect = jquery(iptvServiceSelect);
            if (serviceSelect.data('select2')) return;

            serviceSelect.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dir: document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr',
                dropdownParent: jquery('#pricing-section'),
                dropdownCssClass: 'pricing-provider-dropdown',
                language: {
                    noResults: () => @json(__('messages.no_results')),
                    searching: () => @json(__('messages.search')) + '...'
                }
            });

            const selection = serviceSelect.next('.select2').find('.select2-selection');
            selection.removeAttr('aria-labelledby');
            selection.attr('aria-label', iptvServiceSelect.getAttribute('aria-label'));

            iptvServiceSelect.removeEventListener('change', handleIptvServiceChange);
            serviceSelect.off('change.pricingPackages')
                .on('change.pricingPackages', handleIptvServiceChange);
        }

        function ensurePricingSelect2() {
            if (!iptvServiceSelect) return Promise.resolve();
            if (pricingSelect2Jquery?.fn?.select2) {
                initializePricingSelect2(pricingSelect2Jquery);
                return Promise.resolve();
            }
            if (pricingSelect2Promise) return pricingSelect2Promise;

            const stylesheet = loadPricingStylesheet(
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
            );
            const jqueryReady = window.jQuery
                ? Promise.resolve()
                : loadPricingScript(
                    'whatsapp-lead-jquery',
                    @json(asset('js/jquery.js')),
                    () => Boolean(window.jQuery)
                );

            pricingSelect2Promise = Promise.all([stylesheet, jqueryReady])
                .then(function() {
                    if (!window.jQuery) throw new Error('jQuery is unavailable.');
                    pricingSelect2Jquery = window.jQuery;
                    if (pricingSelect2Jquery.fn.select2) return;

                    return loadPricingScript(
                        'whatsapp-lead-select2',
                        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                        () => Boolean(pricingSelect2Jquery?.fn?.select2)
                    );
                })
                .then(function() {
                    initializePricingSelect2(pricingSelect2Jquery);
                })
                .catch(function() {
                    pricingSelect2Jquery = null;
                    return null;
                });

            return pricingSelect2Promise;
        }

        function appendPackageGroup(lines, cards, heading, note = '') {
            const groupedCards = Array.from(cards).filter(card => card.dataset.available !== '0');
            if (!groupedCards.length) return;

            lines.push(`*${heading}*`);
            if (note) lines.push(note);

            ['opplex', 'starshare'].forEach(vendor => {
                const vendorCards = groupedCards.filter(card => norm(card.dataset.vendor) === vendor);
                if (!vendorCards.length) return;

                lines.push('', `*${vendor === 'starshare' ? 'Filex' : 'Opplex'}*`);
                vendorCards.forEach(card => {
                    const plan = (card.dataset.plan || '').trim();
                    const price = cleanText(card.querySelector('.package-plan-title span'));
                    lines.push(`• *${plan}*${price ? ` — ${price}` : ''}`);

                    const tier = cleanText(card.querySelector('.package-tier-badge'));
                    if (tier) lines.push(`  ✓ ${tier}`);

                    card.querySelectorAll('.price-list li').forEach(item => {
                        const feature = cleanText(item);
                        if (feature) lines.push(`  ✓ ${feature}`);
                    });
                });
            });

            lines.push('');
        }

        if (shareAllPackages) {
            if (hasShareablePackages) {
                const lines = [`*${shareLabels.title}*`, ''];
                appendPackageGroup(lines, iptvCards, shareLabels.iptv);
                appendPackageGroup(lines, resellerCards, shareLabels.reseller, cleanText(creditInfo));
                lines.push(`${shareLabels.packagesLink}: ${packagesUrl}`);
                shareAllPackages.href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(lines.join('\n').trim());
            } else {
                shareAllPackages.hidden = true;
            }
        }

        function getActiveVendor(toggleEl, fallback = 'opplex') {
            if (!toggleEl) return fallback;
            const activeBtn = toggleEl.querySelector('.tg.active');
            return activeBtn ? norm(activeBtn.dataset.vendor) : fallback;
        }

        function renderIptv() {
            const showReseller = resellerToggle && resellerToggle.checked;
            const service = norm(iptvServiceSelect?.value);
            const searchQuery = norm(iptvPackageSearch?.value);
            const serviceCards = Array.from(iptvCards)
                .filter(card => !service || norm(card.dataset.service) === service)
                .filter(card => !searchQuery || norm(card.textContent).includes(searchQuery));
            const pageSize = getIptvPageSize();
            const totalPages = Math.max(1, Math.ceil(serviceCards.length / pageSize));
            iptvPage = Math.min(Math.max(iptvPage, 1), totalPages);
            const pageStart = (iptvPage - 1) * pageSize;
            const pageEnd = pageStart + pageSize;

            iptvCards.forEach(card => {
                const cardService = norm(card.dataset.service);
                const serviceIndex = serviceCards.indexOf(card);
                const show = !showReseller
                    && (!service || cardService === service)
                    && serviceIndex >= pageStart
                    && serviceIndex < pageEnd;
                card.style.setProperty('display', show ? 'block' : 'none', 'important');
            });

            if (iptvPagination) {
                iptvPagination.hidden = showReseller || serviceCards.length === 0 || totalPages <= 1;
            }
            if (iptvCatalogToolbar) {
                iptvCatalogToolbar.hidden = showReseller;
            }
            if (iptvResultCount) {
                iptvResultCount.textContent = serviceCards.length;
            }
            if (iptvEmptyState) {
                iptvEmptyState.hidden = showReseller || serviceCards.length > 0;
            }
            if (iptvPageStatus) {
                iptvPageStatus.textContent = `${iptvPage} / ${totalPages}`;
            }
            if (iptvPreviousPage) {
                iptvPreviousPage.disabled = iptvPage <= 1;
            }
            if (iptvNextPage) {
                iptvNextPage.disabled = iptvPage >= totalPages;
            }
            if (!showReseller) syncStickyFromCard();
        }

        function renderReseller() {
            const showReseller = resellerToggle && resellerToggle.checked;
            const vendor = getActiveVendor(resellerVendorToggle);
            let visibleCount = 0;

            if (normalPackagesWrap) {
                normalPackagesWrap.style.setProperty('display', showReseller ? 'none' : getNormalPackagesDisplay(), 'important');
            }
            if (resellerWrap) {
                resellerWrap.style.setProperty('display', showReseller ? 'block' : 'none', 'important');
            }
            if (creditInfo) {
                creditInfo.style.setProperty('display', showReseller ? 'block' : 'none', 'important');
            }

            if (iptvServicePicker) {
                iptvServicePicker.style.setProperty('display', showReseller ? 'none' : 'grid', 'important');
            }
            if (resellerVendorToggle) {
                resellerVendorToggle.style.setProperty('display', showReseller ? 'inline-flex' : 'none', 'important');
            }
            if (shareAllPackages) {
                shareAllPackages.hidden = !hasShareablePackages;
            }
            if (comparePlansButton) {
                comparePlansButton.hidden = showReseller;
            }

            resellerCards.forEach(card => {
                const cardVendor = norm(card.dataset.vendor);
                const showCard = showReseller && cardVendor === vendor;
                if (showCard) visibleCount += 1;
                card.style.setProperty('display', showCard ? 'block' : 'none', 'important');
            });

            if (showReseller && visibleCount === 0) {
                resellerCards.forEach(card => {
                    card.style.setProperty('display', 'block', 'important');
                });
            }

            if (showReseller) {
                iptvCards.forEach(card => {
                    card.style.setProperty('display', 'none', 'important');
                });
            }

            syncStickyFromCard();
        }

        function trackVisiblePackages() {
            const type = resellerToggle && resellerToggle.checked ? 'reseller' : 'iptv';
            const visibleCards = Array.from(type === 'reseller' ? resellerCards : iptvCards)
                .filter(card => card.style.display !== 'none');
            const items = visibleCards.map(card => ({
                item_id: card.dataset.packageId || [card.dataset.vendor, card.dataset.plan].join('-'),
                item_name: card.dataset.plan || '',
                item_brand: card.dataset.service || (norm(card.dataset.vendor) === 'starshare' ? 'Filex' : 'Opplex'),
                item_category: type,
                price: Number(card.dataset.price || 0),
                quantity: 1
            }));

            if (items.length) {
                track('view_item_list', {
                    item_list_id: 'pricing',
                    currency: @json(config('services.app.default_currency', 'USD')),
                    items: items
                });
            }
        }

        function handleIptvServiceChange() {
            iptvPage = 1;
            if (iptvPackageSearch) iptvPackageSearch.value = '';
            renderIptv();
            if (compareModal && !compareModal.hidden) renderComparison();
            trackVisiblePackages();
            track('select_content', {
                content_type: 'iptv_provider',
                item_id: norm(iptvServiceSelect?.value)
            });
        }

        if (iptvServiceSelect) {
            iptvServiceSelect.addEventListener('change', handleIptvServiceChange);
            ensurePricingSelect2();
        }

        if (comparePlansButton) {
            comparePlansButton.addEventListener('click', openComparison);
        }
        if (compareModal) {
            compareModal.addEventListener('click', function(event) {
                if (event.target === compareModal || event.target.closest('[data-compare-close]')) {
                    closeComparison();
                }
            });
        }
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeComparison();
        });

        if (resellerVendorToggle) {
            resellerVendorToggle.addEventListener('click', function(e) {
                const btn = e.target.closest('.tg');
                if (!btn) return;

                resellerVendorToggle.querySelectorAll('.tg').forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');

                renderReseller();
                trackVisiblePackages();
                track('select_content', {
                    content_type: 'reseller_provider',
                    item_id: norm(btn.dataset.vendor)
                });
            });
        }

        if (resellerToggle) {
            resellerToggle.addEventListener('change', function() {
                iptvPage = 1;
                if (iptvPackageSearch) iptvPackageSearch.value = '';
                renderIptv();
                renderReseller();
                trackVisiblePackages();
                track('select_content', {
                    content_type: 'package_type',
                    item_id: resellerToggle.checked ? 'reseller' : 'iptv'
                });
            });
        }

        if (iptvPackageSearch) {
            iptvPackageSearch.addEventListener('input', function() {
                iptvPage = 1;
                renderIptv();
            });
        }

        if (iptvPagination) {
            iptvPagination.addEventListener('click', function(e) {
                const button = e.target.closest('[data-package-page]');
                if (!button || button.disabled) return;

                iptvPage += button.dataset.packagePage === 'next' ? 1 : -1;
                renderIptv();
                trackVisiblePackages();

                if (normalPackagesWrap) {
                    normalPackagesWrap.scrollIntoView({
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                        block: 'start'
                    });
                    normalPackagesWrap.scrollTo({ left: 0, behavior: 'auto' });
                }
            });
        }

        document.querySelectorAll('[data-package-buy]').forEach(link => {
            link.addEventListener('click', function() {
                const card = link.closest('.pkg-item');
                if (!card) return;
                const item = {
                    item_id: card.dataset.packageId || [card.dataset.vendor, card.dataset.plan].join('-'),
                    item_name: card.dataset.plan || '',
                    item_brand: card.dataset.service || (card.dataset.vendor === 'starshare' ? 'Filex' : 'Opplex'),
                    item_category: card.dataset.type || 'iptv',
                    price: Number(card.dataset.price || 0),
                    quantity: 1
                };
                track('select_item', {
                    item_list_id: 'pricing',
                    currency: @json(config('services.app.default_currency', 'USD')),
                    value: item.price,
                    items: [item]
                });
            });
        });

        window.addEventListener('resize', function() {
            renderIptv();
            renderReseller();
            syncStickyFromCard();
        });

        renderIptv();
        renderReseller();
        trackVisiblePackages();

        const pricingSection = document.getElementById('pricing-section');
        if (pricingSection && 'IntersectionObserver' in window) {
            const pricingObserver = new IntersectionObserver(entries => {
                const isVisible = entries.some(entry => entry.isIntersecting);
                document.body.classList.toggle('pricing-in-view', isVisible);
                if (isVisible) {
                    try { sessionStorage.setItem('marketing.pricingViewed', '1'); } catch (e) {}
                    window.dispatchEvent(new CustomEvent('pricing:viewed'));
                }
            });
            pricingObserver.observe(pricingSection);
        }

    });
</script>
