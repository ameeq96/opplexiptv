@extends('layouts.default')
@section('title', __('messages.checkout_complete_title_page'))

@push('styles')
<style>
    .checkout-step1-page {
        min-height: 100vh;
        padding: 38px 0 76px;
        background:
            radial-gradient(circle at 8% 0%, rgba(37, 99, 235, .10), transparent 30%),
            radial-gradient(circle at 94% 18%, rgba(220, 38, 38, .07), transparent 28%),
            linear-gradient(180deg, #f8fbff 0%, #eef3f9 100%);
    }
    .checkout-step1-page .checkout-hero { max-width: 760px; margin: 0 auto 30px; }
    .checkout-step1-page .checkout-badges {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 18px;
        padding: 7px;
        border: 1px solid #dce5f2;
        border-radius: 999px;
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    }
    .checkout-step1-page .checkout-trust-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 999px;
        color: #35516f;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .checkout-step1-page .checkout-trust-item i { color: #059669; }
    .checkout-step1-page .hero-title {
        margin-bottom: 10px;
        color: #0b1739;
        font-size: clamp(2rem, 4vw, 3rem);
        line-height: 1.1;
        letter-spacing: -.035em;
    }
    .checkout-step1-page .hero-subtitle {
        max-width: 620px;
        margin: 0 auto;
        color: #64748b;
        font-size: 1rem;
        line-height: 1.65;
    }
    .checkout-step1-page .checkout-shell { max-width: 1200px; }
    .checkout-step1-page .checkout-layout { margin-right: -12px; margin-left: -12px; }
    .checkout-step1-page .checkout-column { padding-right: 12px; padding-left: 12px; }
    .checkout-step1-page .checkout-panel {
        overflow: hidden;
        border: 1px solid #dfe7f2;
        border-radius: 22px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
    }
    .checkout-step1-page .checkout-panel__heading {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 24px;
    }
    .checkout-step1-page .checkout-panel__icon {
        display: inline-flex;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: linear-gradient(135deg, #2563eb, #1e40af);
        color: #fff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, .2);
    }
    .checkout-step1-page .checkout-panel__heading h5 { margin: 0; color: #0f172a; font-size: 1.08rem; }
    .checkout-step1-page .checkout-panel__kicker {
        display: block;
        margin-bottom: 3px;
        color: #2563eb;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .checkout-step1-page .form-group { margin-bottom: 20px; }
    .checkout-step1-page .checkout-details-panel .form-group > label { margin-bottom: 7px; color: #24324a; }
    .checkout-step1-page .form-control {
        min-height: 50px;
        border: 1px solid #d9e2ef;
        border-radius: 12px;
        background: #fbfdff;
        color: #0f172a;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .checkout-step1-page textarea.form-control { min-height: 118px; resize: vertical; }
    .checkout-step1-page .form-control:focus {
        border-color: #60a5fa;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
    }
    .checkout-step1-page .iti { width: 100%; }
    .checkout-step1-page .order-box {
        padding: 0 !important;
        overflow: hidden;
        border: 0;
        border-radius: 16px;
        background: #f8fbff;
    }
    .checkout-step1-page .order-summary__heading { padding: 18px; border-bottom: 1px solid #dbeafe; }
    .checkout-step1-page .order-summary__heading > div { min-width: 0; }
    .checkout-step1-page .order-summary__heading strong { display: block; color: #0f172a; line-height: 1.4; }
    .checkout-step1-page .order-summary__type {
        display: inline-flex;
        margin-bottom: 7px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #eaf1ff;
        color: #1d4ed8;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .checkout-step1-page .order-summary__heading a {
        padding-top: 4px;
        color: #2563eb;
        font-weight: 700;
        white-space: nowrap;
    }
    .checkout-step1-page .order-details {
        grid-template-columns: 1fr;
        gap: 9px;
        margin: 0;
        padding: 16px 18px;
    }
    .checkout-step1-page .order-detail {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 10px 12px;
        border-color: #e2e8f0;
        border-radius: 11px;
    }
    .checkout-step1-page .order-detail strong { margin-top: 0; text-align: right; }
    [dir="rtl"] .checkout-step1-page .order-detail strong { text-align: left; }
    .checkout-step1-page .order-pricing { margin: 0; padding: 16px 18px 18px; background: #fff; }
    .checkout-step1-page .order-total { margin-top: 9px; padding-top: 12px; color: #0f172a; }
    .checkout-step1-page .order-total span:last-child { color: #dc2626; }
    .checkout-step1-page .checkout-payment-panel { padding: 24px !important; }
    .checkout-step1-page .checkout-assurances {
        margin-bottom: 18px;
        padding-bottom: 18px;
        border-bottom: 1px solid #e2e8f0;
    }
    .checkout-step1-page .pay-option {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-height: 76px;
        padding: 13px 14px;
        border-color: #dbe3ee;
        border-radius: 13px;
        background: #fff;
        cursor: pointer;
    }
    .checkout-step1-page .pay-option.active {
        border-color: #60a5fa;
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }
    .checkout-step1-page .pay-option input { flex: 0 0 auto; margin: 4px 2px 0 0 !important; }
    .checkout-step1-page .pay-option label { min-width: 0; margin: 0; cursor: pointer; }
    .checkout-step1-page .pay-option__content { flex: 1 1 auto; min-width: 0; }
    .checkout-step1-page .payment-method-group + .payment-method-group { margin-top: 20px; }
    .checkout-step1-page .payment-method-group__heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
        color: #0f172a;
        font-size: .88rem;
        font-weight: 800;
    }
    .checkout-step1-page .payment-method-group__heading span:first-child {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .checkout-step1-page .payment-method-group__heading i { color: #059669; }
    .checkout-step1-page .payment-method-group__fee {
        flex: 0 0 auto;
        padding: 3px 8px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: .68rem;
        font-weight: 800;
    }
    .checkout-step1-page .pay-option__details {
        display: grid;
        gap: 3px;
        margin-top: 5px;
        color: #64748b;
        font-size: .78rem;
        line-height: 1.45;
    }
    .checkout-step1-page .pay-option__detail {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }
    .checkout-step1-page .pay-option__detail-label {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }
    .checkout-step1-page .pay-option__detail strong { color: #334155; }
    .checkout-step1-page .pay-option__detail span { overflow-wrap: anywhere; }
    .checkout-step1-page .payment-copy-button {
        display: inline-flex;
        width: 28px;
        height: 28px;
        align-items: center;
        justify-content: center;
        margin-left: 3px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        cursor: pointer;
    }
    .checkout-step1-page .payment-copy-button:hover,
    .checkout-step1-page .payment-copy-button:focus-visible {
        border-color: #059669;
        color: #047857;
    }
    .checkout-step1-page .payment-proof-notice {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 20px 0;
        padding: 12px 13px;
        border: 1px solid #f3d27a;
        border-radius: 13px;
        background: #fff8e1;
        color: #713f12;
        font-size: .82rem;
        line-height: 1.5;
    }
    .checkout-step1-page .payment-proof-notice i { margin-top: 3px; color: #d97706; }
    .checkout-step1-page .checkout-policy,
    .checkout-step1-page .checkout-marketing { border-radius: 13px; }
    .checkout-step1-page .checkout-marketing { padding: 14px; }
    .checkout-step1-page .place-order {
        display: inline-flex;
        width: 100%;
        min-height: 54px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 14px;
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        box-shadow: 0 14px 28px rgba(220, 38, 38, .20);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .checkout-step1-page .place-order:hover {
        background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
        box-shadow: 0 16px 32px rgba(220, 38, 38, .25);
        transform: translateY(-1px);
    }
    .checkout-step1-page .checkout-order-column { position: static; }
    [dir="rtl"] .checkout-step1-page .checkout-assurances i { margin-right: 0; margin-left: 6px; }
    [dir="rtl"] .checkout-step1-page .pay-option input { margin-right: 0 !important; margin-left: 2px !important; }

    @media (min-width: 992px) and (min-height: 900px) {
        .checkout-step1-page .checkout-order-column { position: sticky; top: 92px; align-self: flex-start; }
    }
    @media (max-width: 767px) {
        .checkout-step1-page { padding: 28px 0 104px; }
        .checkout-step1-page .checkout-hero { margin-bottom: 22px; }
        .checkout-step1-page .checkout-badges { max-width: 100%; flex-wrap: wrap; border-radius: 18px; }
        .checkout-step1-page .checkout-trust-item { padding: 5px 7px; font-size: .7rem; }
        .checkout-step1-page .hero-title { font-size: 2rem; }
        .checkout-step1-page .hero-subtitle { font-size: .92rem; }
        .checkout-step1-page .checkout-panel { border-radius: 18px; }
        .checkout-step1-page .checkout-details-panel,
        .checkout-step1-page .checkout-payment-panel { padding: 20px !important; }
        .checkout-step1-page .checkout-mobile-action { width: auto; }
        .checkout-step1-page .form-row { display: block; }
        .checkout-step1-page .form-row > [class*="col-"] { max-width: 100%; }
        body.checkout-review-page .whatsapp-icon,
        body.checkout-review-page #voice-assistant { display: none !important; }
    }
    @media (max-width: 420px) {
        .checkout-step1-page .checkout-trust-item { width: 100%; justify-content: center; }
        .checkout-step1-page .order-summary__heading { display: block; }
        .checkout-step1-page .order-summary__heading a { display: inline-block; margin-top: 10px; }
    }
</style>
@endpush

@section('content')
<div class="checkout-step1-page">
    <script>document.body.classList.add('checkout-review-page');</script>
@php
        $paymentMethodGroups = [
            [
                'title' => 'Local Payment Methods for Pakistanis',
                'fee' => '0% Fees',
                'icon' => 'fa-university',
                'methods' => [
                    [
                        'value' => 'easypaisa',
                        'title' => 'Easypaisa',
                        'details' => [
                            'Account' => '+92300-4446130',
                            'Name' => 'Muhammad Ateeq',
                        ],
                    ],
                    [
                        'value' => 'nayapay / sadapay',
                        'title' => 'Nayapay / Sadapay',
                        'details' => [
                            'Account' => '+92307-9021909',
                        ],
                    ],
                    [
                        'value' => 'meezan bank',
                        'title' => 'Meezan Bank',
                        'details' => [
                            'Account' => '0175-010564698-1',
                            'Name' => 'Muhammad Emmad Khan',
                        ],
                    ],
                    [
                        'value' => 'raast',
                        'title' => 'RAAST',
                        'details' => [
                            'RAAST ID' => '03079021909',
                            'Name' => 'Muhammad Emmad Khan',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'International Payment Methods',
                'fee' => 'Fees Apply',
                'icon' => 'fa-globe',
                'methods' => [
                    [
                        'value' => 'remitly',
                        'title' => 'Remitly (Light Fees)',
                        'details' => [
                            'Name' => 'Muhammad Emmad Khan',
                            'Account' => '01750105646981',
                            'IBAN' => 'PK04MEZN0001750105646981',
                            'Bank' => 'Meezan Bank, Darakhshan Soc - Karachi',
                        ],
                    ],
                    [
                        'value' => 'skrill',
                        'title' => 'Skrill to Skrill (High Fees)',
                        'details' => [
                            'Customer ID' => '264987278',
                            'Email' => 'khanemaad92@gmail.com',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Crypto Payment Methods',
                'fee' => 'Fast & 0% Fees',
                'icon' => 'fa-btc',
                'methods' => [
                    [
                        'value' => 'binance',
                        'title' => 'Binance Exchange',
                        'details' => [
                            'Binance ID' => '437295954',
                            'Name' => 'Khanemmad',
                        ],
                    ],
                    [
                        'value' => 'mexc',
                        'title' => 'MEXC Exchange',
                        'details' => [
                            'MEXC ID' => '22385490',
                            'Name' => 'Khanemmad',
                        ],
                    ],
                    [
                        'value' => 'on-chain usdt (bep20)',
                        'title' => 'On-Chain USDT (BEP20)',
                        'details' => [
                            'Wallet' => '0xe2f4fe351603296ef3f6362a59a2440e4b2d376e',
                        ],
                    ],
                ],
            ],
        ];
        $selectedPaymentMethod = old('paymethod', 'easypaisa');

        // ------------------------------
        // Gather selections from request
        // ------------------------------
        $selectedDevice = old('device', $device ?? request('device'));
        $selectedDeviceId = old('device_id', $device_id ?? request('device_id'));
        $selectedPackageId = old('package_id', $package_id ?? request('package_id'));
        $selectedVendor = old('iptv_vendor', $iptv_vendor ?? request('iptv_vendor'));
        $selectedPlanName = old('plan_name', $plan_name ?? request('plan_name'));

        // Load the selected package so the visible checkout price matches the database.
        $planPriceDb = null;
        $selectedPackage = null;
        $packageTitle = null;
        $packageServiceName = null;
        try {
            if (!empty($selectedPackageId)) {
                $selectedPackage = \App\Models\Package::query()
                    ->where('active', true)
                    ->where('is_available', true)
                    ->whereIn('type', ['iptv', 'reseller'])
                    ->whereIn('vendor', ['opplex', 'starshare'])
                    ->with('translations')
                    ->find($selectedPackageId);
                if ($selectedPackage && isset($selectedPackage->price_amount)) {
                    $planPriceDb = (float) $selectedPackage->price_amount;
                    $selectedVendor = strtolower((string) $selectedPackage->vendor);

                    if ($selectedPackage->type === 'iptv' && $selectedPackage->isDurationPlan()) {
                        $planKey = match ((int) $selectedPackage->duration_months) {
                            1 => 'monthly',
                            3 => 'three_months',
                            6 => 'half_yearly',
                            12 => 'yearly',
                            default => null,
                        };
                        $titleKey = $planKey ? 'document_commerce.packages.pricing.plans.' . $planKey . '.title' : null;
                        $packageTitle = $titleKey && __($titleKey) !== $titleKey
                            ? __($titleKey)
                            : ($selectedPackage->translation()?->title ?: $selectedPackage->title);
                    } elseif ($selectedPackage->type === 'reseller') {
                        $resellerTitleKey = match ($selectedPackage->title) {
                            'Starter Reseller Package' => 'messages.starter_reseller',
                            'Essential Reseller Bundle' => 'messages.essential_reseller',
                            'Pro Reseller Suite' => 'messages.pro_reseller',
                            'Advanced Reseller Toolkit' => 'messages.advanced_reseller',
                            default => null,
                        };
                        $packageTitle = $resellerTitleKey && __($resellerTitleKey) !== $resellerTitleKey
                            ? __($resellerTitleKey)
                            : ($selectedPackage->translation()?->title ?: $selectedPackage->title);
                    } else {
                        $packageTitle = $selectedPackage->translation()?->title ?: $selectedPackage->title;
                    }

                    $legacyProviderLabel = $selectedVendor === 'starshare' ? 'Filex' : 'Opplex';
                    if ($selectedPackage->type === 'iptv' && !$selectedPackage->isDurationPlan()) {
                        $packageServiceName = trim((string) preg_replace(
                            '/\s*-\s*(?:3\s*Months?|Half\s*Yearly|Yearly|Monthly|1\s*Month)\s*$/iu',
                            '',
                            (string) $selectedPackage->title
                        ));
                        $selectedPlanName = $packageTitle;
                    } else {
                        $packageServiceName = $legacyProviderLabel;
                        $selectedPlanName = $legacyProviderLabel . ' - ' . $packageTitle;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Package type normalization
        $selectedTypeRaw = $selectedPackage
            ? ($selectedPackage->type === 'reseller' ? 'reseller' : 'package')
            : old('package_type', $package_type ?? request('package_type'));
        $selectedType = $selectedTypeRaw;
        if ($selectedType === 'iptv') {
            $selectedType = 'package'; // DB enum: package | reseller
        }
        if ($selectedType === 'package' && !empty($selectedDeviceId)) {
            try {
                $selectedDeviceModel = \App\Models\Device::query()->find($selectedDeviceId);
                if ($selectedDeviceModel) {
                    $selectedDeviceId = $selectedDeviceModel->id;
                    $selectedDevice = $selectedDeviceModel->name;
                }
            } catch (\Throwable $e) {
                // The controller already validates the device before rendering checkout.
            }
        } elseif ($selectedType === 'reseller') {
            $selectedDeviceId = null;
            $selectedDevice = null;
        }
        $typeLabel =
            $selectedType === 'reseller' ? __('messages.checkout_type_reseller') : __('messages.checkout_type_iptv');
        $durationMonths = $selectedPackage && $selectedType === 'package'
            ? (int) $selectedPackage->duration_months
            : 0;
        $durationLabel = $durationMonths === 1
            ? __('interface.checkout.month_one')
            : ($durationMonths > 1 ? __('interface.checkout.months', ['count' => $durationMonths]) : null);
        $durationLabel = $durationLabel ? preg_replace('/^\/\s*/', '', $durationLabel) : null;

        $planPrice = $planPriceDb ?? 0.0;
        // Totals (based only on planPrice)
        $qty = 1;
        $subtotal = $planPrice * $qty;
        $promotionDiscount = !empty($eventPromotion)
            ? round($subtotal * (((float) ($eventPromotion['discount_percent'] ?? 0)) / 100), 2)
            : 0.0;
        $promotionPercent = (float) ($eventPromotion['discount_percent'] ?? 0);
        $total = max(0, $subtotal - $promotionDiscount);

        // Carry values forward to step2 (safe defaults)
        $carryPkg = number_format((float) $planPrice, 2, '.', '');
        $providerLabel = $packageServiceName ?: (strtolower((string) $selectedVendor) === 'starshare'
            ? 'Filex'
            : ucfirst((string) $selectedVendor));
        $editOptions = [
            'package_id' => $selectedPackageId,
            'vendor' => $selectedVendor,
            'ptype' => $selectedType === 'reseller' ? 'reseller' : 'iptv',
            'price' => $planPriceDb,
            'plan' => $selectedPlanName,
            'device' => $selectedDevice,
            'device_id' => $selectedDeviceId,
        ];
        $checkoutTrackingItem = [
            'item_id' => (string) $selectedPackageId,
            'item_name' => $selectedPlanName,
            'item_brand' => $providerLabel,
            'item_category' => $selectedType,
            'price' => (float) $total,
            'quantity' => 1,
        ];
    @endphp

    <div class="container checkout-hero text-center">
        <div class="checkout-badges badge-row">
            <span class="checkout-trust-item">
                <i class="fa fa-shield"></i> {{ __('messages.checkout_badge_secure') }}
            </span>
            <span class="checkout-trust-item">
                <i class="fa fa-check-circle"></i> {{ __('messages.checkout_badge_safe_info') }}
            </span>
            <span class="checkout-trust-item">
                <i class="fa fa-lock"></i> {{ __('messages.checkout_badge_encryption') }}
            </span>
        </div>
        <h1 class="hero-title change-font">{{ __('messages.checkout_complete_title') }}</h1>
        <h2 class="hero-subtitle change-font">{{ __('messages.checkout_complete_sub') }}</h2>
    </div>

    @if ($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="container checkout-shell">
        <div class="row checkout-layout">
            {{-- Billing --}}
            <div class="col-lg-7 checkout-column mb-4">
                <section class="card-soft checkout-panel checkout-details-panel p-4">
                    <div class="checkout-panel__heading">
                        <span class="checkout-panel__icon" aria-hidden="true"><i class="fa fa-user"></i></span>
                        <div>
                            <span class="checkout-panel__kicker">{{ $typeLabel }}</span>
                            <h5>{{ __('messages.checkout_billing_details') }}</h5>
                        </div>
                    </div>

                    <form action="{{ route('step2') }}" method="post" id="checkoutForm">
                        @csrf

                        {{-- carry over selected config values --}}
                        <input type="hidden" name="device" value="{{ $selectedDevice }}">
                        <input type="hidden" name="device_id" value="{{ $selectedDeviceId }}">
                        <input type="hidden" name="package_id" value="{{ $selectedPackageId }}">
                        <input type="hidden" name="iptv_vendor" value="{{ $selectedVendor }}">
                        <input type="hidden" name="plan_name" value="{{ $selectedPlanName }}">
                        <input type="hidden" name="plan_price" value="{{ number_format($planPrice, 2, '.', '') }}">
                        <input type="hidden" name="pkg_price" value="{{ $carryPkg }}">
                        <input type="hidden" name="quantity" value="{{ $qty }}">
                        <input type="hidden" name="package_type" value="{{ $selectedType }}">
                        <input type="hidden" name="checkout_draft_token" id="checkoutDraftToken"
                            value="{{ old('checkout_draft_token', (string) \Illuminate\Support\Str::uuid()) }}">

                        <div class="form-group">
                            <label class="required" for="checkoutEmail">{{ __('messages.checkout_email') }}</label>
                            <input type="email" id="checkoutEmail" name="email" class="form-control"
                                value="{{ old('email') }}" autocomplete="email" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="required" for="checkoutFirstName">{{ __('messages.checkout_first_name') }}</label>
                                <input type="text" id="checkoutFirstName" name="first_name" class="form-control"
                                    value="{{ old('first_name') }}" autocomplete="given-name" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="required" for="checkoutLastName">{{ __('messages.checkout_last_name') }}</label>
                                <input type="text" id="checkoutLastName" name="last_name" class="form-control"
                                    value="{{ old('last_name') }}" autocomplete="family-name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required" for="phone">{{ __('messages.checkout_phone') }}</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                value="{{ old('phone') }}" autocomplete="tel" required>
                            <small id="phone-client-error" class="text-danger d-none"></small>
                            <small class="text-muted d-block mt-1">{{ __('messages.checkout_phone_hint') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="checkoutNotes">{{ __('messages.checkout_notes_label') }}</label>
                            <textarea rows="4" id="checkoutNotes" name="notes" class="form-control"
                                placeholder="{{ __('messages.checkout_notes_placeholder') }}">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="required" for="checkoutCaptcha">
                                {{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}
                            </label>
                            <input type="text" id="checkoutCaptcha" name="captcha" class="form-control"
                                placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                required aria-invalid="@error('captcha') true @else false @enderror"
                                aria-describedby="@error('captcha') checkout-captcha-error @enderror">
                            @error('captcha')
                                <small id="checkout-captcha-error" class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                    </form>
                </section>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-5 checkout-column checkout-order-column">
                <section class="card-soft checkout-panel p-4 mb-3">
                    <div class="checkout-panel__heading">
                        <span class="checkout-panel__icon" aria-hidden="true"><i class="fa fa-shopping-cart"></i></span>
                        <div>
                            <span class="checkout-panel__kicker">{{ $typeLabel }}</span>
                            <h5>{{ __('messages.checkout_your_order') }}</h5>
                        </div>
                    </div>
                    <div class="order-box p-3">
                        <div class="order-summary__heading">
                            <div>
                                <span class="order-summary__type">{{ $typeLabel }}</span>
                                <strong>{{ $packageTitle ?: ($selectedPlanName ?: __('messages.checkout_selected_package_fallback')) }}</strong>
                            </div>
                            <a class="small" href="{{ route('configure', $editOptions) }}">
                                {{ __('messages.checkout_edit_options') }}
                            </a>
                        </div>

                        <div class="order-details">
                            @if ($selectedType === 'package' && $durationLabel)
                                <div class="order-detail">
                                    <span>{{ __('messages.checkout_subscription_label') }}</span>
                                    <strong>{{ $durationLabel }}</strong>
                                </div>
                            @endif
                            @if ($selectedVendor)
                                <div class="order-detail">
                                    <span>{{ __('messages.checkout_provider') }}</span>
                                    <strong>{{ $providerLabel }}</strong>
                                </div>
                            @endif
                            @if ($selectedType === 'package' && $selectedDevice)
                                <div class="order-detail">
                                    <span>{{ __('messages.checkout_device') }}</span>
                                    <strong>{{ $selectedDevice }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="order-pricing">
                        <div class="order-line">
                            <span>{{ __('messages.checkout_subtotal_label') }}</span>
                            <strong>${{ number_format($subtotal, 2) }}</strong>
                        </div>

                        @if ($promotionDiscount > 0)
                            <div class="order-line order-line--discount">
                                <span>{{ $eventPromotion['name'] }} ({{ number_format($promotionPercent, 0) }}% OFF)</span>
                                <strong>-${{ number_format($promotionDiscount, 2) }}</strong>
                            </div>
                        @endif

                        <div class="order-total">
                            <span>{{ __('messages.checkout_total_label') }}</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        </div>
                    </div>
                </section>

                <section class="card-soft checkout-panel checkout-payment-panel p-4">
                    <div class="checkout-assurances">
                        <span><i class="fa fa-refresh" aria-hidden="true"></i>{{ __('messages.page_faq.packages.a5') }}</span>
                        <span><i class="fa fa-clock-o" aria-hidden="true"></i>{{ __('messages.thankyou_page.delivery_text') }}</span>
                        <span><i class="fa fa-life-ring" aria-hidden="true"></i>{{ __('messages.thankyou_page.support_text') }}</span>
                    </div>

                    @foreach ($paymentMethodGroups as $groupIndex => $paymentGroup)
                        <div class="payment-method-group">
                            <div class="payment-method-group__heading">
                                <span>
                                    <i class="fa {{ $paymentGroup['icon'] }}" aria-hidden="true"></i>
                                    {{ $paymentGroup['title'] }}
                                </span>
                                <span class="payment-method-group__fee">{{ $paymentGroup['fee'] }}</span>
                            </div>

                            @foreach ($paymentGroup['methods'] as $methodIndex => $paymentMethod)
                                @php($paymentMethodId = 'paymentMethod' . $groupIndex . $methodIndex)
                                <div class="pay-option {{ $selectedPaymentMethod === $paymentMethod['value'] ? 'active' : '' }} mb-2">
                                    <input class="mr-2 mt-1" type="radio" name="paymethod"
                                        id="{{ $paymentMethodId }}" value="{{ $paymentMethod['value'] }}"
                                        form="checkoutForm" @checked($selectedPaymentMethod === $paymentMethod['value'])>
                                    <div class="pay-option__content">
                                        <label class="w-100" for="{{ $paymentMethodId }}">
                                            <span class="font-weight-bold">{{ $paymentMethod['title'] }}</span>
                                        </label>
                                        <div class="pay-option__details">
                                            @foreach ($paymentMethod['details'] as $detailLabel => $detailValue)
                                                <div class="pay-option__detail">
                                                    <label class="pay-option__detail-label" for="{{ $paymentMethodId }}">
                                                        <strong>{{ $detailLabel }}:</strong>
                                                        <span dir="auto">{{ $detailValue }}</span>
                                                    </label>
                                                    @if (strtolower($detailLabel) !== 'name')
                                                        <button type="button" class="payment-copy-button"
                                                            data-copy-payment-value="{{ $detailValue }}"
                                                            aria-label="{{ __('messages.checkout_copy_value', ['label' => $detailLabel]) }}"
                                                            title="{{ __('messages.checkout_copy_value', ['label' => $detailLabel]) }}">
                                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="payment-proof-notice">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        <strong>{{ __('messages.checkout_payment_proof_notice') }}</strong>
                    </div>
                    <span class="sr-only" id="paymentCopyStatus" aria-live="polite"></span>

                    <div class="checkout-policy mb-3">
                        <input type="checkbox" name="policy_accepted" id="policyAccepted" value="1"
                            form="checkoutForm" required @checked(old('policy_accepted'))>
                        <label for="policyAccepted">
                            <strong>{{ __('messages.final_sale_no_refunds') }}</strong>
                            {{ __('messages.final_sale_confirmed') }}
                            <a href="{{ route('refund-policy') }}" target="_blank" rel="noopener">
                                {{ __('document_ui.footer.refund') }}
                            </a>
                        </label>
                    </div>

                    <fieldset class="checkout-marketing mb-3">
                        <legend class="h6 mb-2">{{ __('marketing.checkout.title') }}</legend>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="marketing_email" id="marketingEmail"
                                value="1" form="checkoutForm" @checked(old('marketing_email'))>
                            <label class="form-check-label" for="marketingEmail">
                                {{ __('marketing.checkout.email') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="marketing_whatsapp" id="marketingWhatsapp"
                                value="1" form="checkoutForm" @checked(old('marketing_whatsapp'))>
                            <label class="form-check-label" for="marketingWhatsapp">
                                {{ __('marketing.checkout.whatsapp') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="marketing_ads" id="marketingAds"
                                value="1" form="checkoutForm" @checked(old('marketing_ads'))>
                            <label class="form-check-label" for="marketingAds">
                                {{ __('marketing.checkout.ads') }}
                            </label>
                        </div>
                        <small class="d-block mt-2 text-muted">
                            {{ __('marketing.checkout.optional') }}
                            <a href="{{ route('privacy-policy') }}" target="_blank" rel="noopener">
                                {{ __('marketing.checkout.privacy') }}
                            </a>
                        </small>
                    </fieldset>

                    <button type="submit" form="checkoutForm" class="btn btn-primary place-order checkout-mobile-action">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        {{ __('messages.checkout_place_order_btn') }}
                    </button>
                </section>
            </div>
        </div>
    </div>

    <script>
        const checkoutValue = @json((float) $total);
        const checkoutCurrency = @json(config('services.app.default_currency', 'USD'));
        const checkoutItem = @json($checkoutTrackingItem);
        const checkoutDraftUrl = @json(route('checkout.draft'));

        async function copyPaymentValue(value) {
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(value);
                    return;
                } catch (error) {}
            }

            const helper = document.createElement('textarea');
            helper.value = value;
            helper.setAttribute('readonly', '');
            helper.style.position = 'fixed';
            helper.style.opacity = '0';
            document.body.appendChild(helper);
            try {
                helper.select();
                const copied = document.execCommand('copy');
                if (!copied) throw new Error('Copy failed');
            } finally {
                helper.remove();
            }
        }

        document.querySelectorAll('[data-copy-payment-value]').forEach(function(button) {
            button.addEventListener('click', async function(event) {
                event.preventDefault();
                event.stopPropagation();

                try {
                    await copyPaymentValue(button.getAttribute('data-copy-payment-value') || '');
                    const icon = button.querySelector('i');
                    const status = document.getElementById('paymentCopyStatus');
                    if (icon) icon.className = 'fa fa-check';
                    if (status) status.textContent = @json(__('messages.checkout_copied'));
                    window.setTimeout(function() {
                        if (icon) icon.className = 'fa fa-copy';
                        if (status) status.textContent = '';
                    }, 1600);
                } catch (error) {}
            });
        });

        Array.prototype.slice.call(document.querySelectorAll('input[name="paymethod"]'))
            .forEach(function(r) {
                r.addEventListener('change', function() {
                    document.querySelectorAll('.pay-option').forEach(function(c) {
                        c.classList.remove('active');
                    });
                    r.closest('.pay-option').classList.add('active');
                    if (typeof window.trackMarketingEvent === 'function') {
                        window.trackMarketingEvent('select_content', {
                            content_type: 'payment_method',
                            item_id: r.value
                        });
                    }
                });
            });

        let checkoutSubmitting = false;
        document.getElementById('checkoutForm').addEventListener('submit', function(event) {
            if (checkoutSubmitting) {
                event.preventDefault();
                return;
            }

            checkoutSubmitting = true;
            const submitButton = document.querySelector('[type="submit"][form="checkoutForm"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.setAttribute('aria-disabled', 'true');
            }

            window.setTimeout(function() {
                if (!event.defaultPrevented) return;

                checkoutSubmitting = false;
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.removeAttribute('aria-disabled');
                }
            }, 0);

            const payment = document.querySelector('input[name="paymethod"]:checked');
            if (typeof window.trackMarketingEvent === 'function') {
                window.trackMarketingEvent('add_payment_info', {
                    currency: checkoutCurrency,
                    value: checkoutValue,
                    payment_type: payment ? payment.value : '',
                    items: [checkoutItem]
                }, 'AddPaymentInfo');
            }
        });

        window.addEventListener('pageshow', function() {
            checkoutSubmitting = false;
            const submitButton = document.querySelector('[type="submit"][form="checkoutForm"]');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-disabled');
            }
        });

        (function() {
            const form = document.getElementById('checkoutForm');
            const watchedNames = [
                'email', 'first_name', 'last_name', 'phone',
                'marketing_email', 'marketing_whatsapp', 'marketing_ads'
            ];
            let saveTimer = null;
            let saveInFlight = false;
            let saveQueued = false;
            let retryCount = 0;

            function saveDraft() {
                if (saveInFlight) {
                    saveQueued = true;
                    return;
                }

                const payload = new FormData();
                const value = function(name) {
                    const input = form.elements.namedItem(name);
                    return input ? input.value : '';
                };
                const checked = function(name) {
                    const input = form.elements.namedItem(name);
                    return input && input.checked ? '1' : '0';
                };

                payload.append('_token', value('_token'));
                payload.append('token', value('checkout_draft_token'));
                payload.append('package_id', value('package_id'));
                payload.append('device_id', value('device_id'));
                payload.append('vendor', value('iptv_vendor'));
                payload.append('first_name', value('first_name'));
                payload.append('last_name', value('last_name'));
                payload.append('email', value('email'));
                payload.append('phone', value('phone'));
                payload.append('marketing_email', checked('marketing_email'));
                payload.append('marketing_whatsapp', checked('marketing_whatsapp'));
                payload.append('marketing_ads', checked('marketing_ads'));

                saveInFlight = true;
                fetch(checkoutDraftUrl, {
                    method: 'POST',
                    body: payload,
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: { 'Accept': 'application/json' }
                }).then(function(response) {
                    if (!response.ok) {
                        const error = new Error('Draft preference was not saved.');
                        error.retryAfter = Number(response.headers.get('Retry-After') || 0) * 1000;
                        throw error;
                    }
                    retryCount = 0;
                }).catch(function(error) {
                    if (retryCount < 3) {
                        retryCount += 1;
                        const delay = error.retryAfter || (retryCount * 1500);
                        window.setTimeout(saveDraft, delay);
                    }
                }).finally(function() {
                    saveInFlight = false;
                    if (saveQueued) {
                        saveQueued = false;
                        saveDraft();
                    }
                });
            }

            function scheduleSave() {
                window.clearTimeout(saveTimer);
                saveTimer = window.setTimeout(saveDraft, 800);
            }

            watchedNames.forEach(function(name) {
                const input = form.elements.namedItem(name);
                if (!input) return;
                input.addEventListener(input.type === 'checkbox' ? 'change' : 'input', function() {
                    retryCount = 0;
                    if (input.type === 'checkbox') {
                        window.clearTimeout(saveTimer);
                        saveDraft();
                    } else {
                        scheduleSave();
                    }
                });
            });
        })();
    </script>
</div>
@endsection
