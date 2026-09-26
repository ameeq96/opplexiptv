@extends('layouts.default')
@section('title', __('messages.checkout_step_title'))

@push('styles')
<style>
  .configure-page .config-wrap {
    min-height: 100vh;
    background:
      radial-gradient(circle at top left, rgba(37, 99, 235, .09), transparent 34%),
      linear-gradient(180deg, #f8fbff 0%, #eef3f9 100%);
  }
  .configure-page .config-shell { max-width: 1320px; }
  .configure-page .config-hero { max-width: 760px; margin: 0 auto 28px; }
  .configure-page .config-hero h1 {
    margin-bottom: 10px;
    color: #0b1739;
    font-size: clamp(2rem, 4vw, 3rem);
    line-height: 1.08;
    letter-spacing: -.035em;
  }
  .configure-page .config-hero h2 { max-width: 620px; margin: 0 auto; line-height: 1.6; }
  .configure-page .config-mode-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
    padding: 18px 20px;
    border: 1px solid #dce5f2;
    border-radius: 22px;
    background: rgba(255, 255, 255, .92);
    box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
  }
  .configure-page .config-mode-card__label {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    color: #0f172a;
    font-weight: 800;
  }
  .configure-page .config-mode-card__icon {
    display: inline-flex;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: #eaf1ff;
    color: #1d4ed8;
    font-size: 18px;
  }
  .configure-page .config-mode-card .toggle-wrap {
    display: grid;
    grid-template-columns: repeat(2, minmax(150px, 1fr));
    flex: 0 0 auto;
    gap: 6px;
    padding: 6px;
    border: 1px solid #dbe5f3;
    border-radius: 16px;
    background: #f1f5f9;
  }
  .configure-page .config-mode-card .tg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 46px;
    padding: 10px 18px;
    border-radius: 12px;
    color: #475569;
  }
  .configure-page .config-mode-card .tg-btn.active {
    background: linear-gradient(135deg, #102052 0%, #1d4ed8 100%);
    color: #fff;
  }
  .configure-page .config-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(310px, 360px);
    gap: 26px;
    align-items: start;
  }
  .configure-page .config-layout.is-summary-only {
    grid-template-columns: minmax(0, 520px);
    justify-content: center;
  }
  .configure-page .config-layout.is-summary-only .config-builder { display: none; }
  .configure-page .config-layout.is-summary-only .config-sidebar { width: 100%; }
  .configure-page .config-builder { min-width: 0; }
  .configure-page .config-card {
    margin-bottom: 22px !important;
    padding: 26px !important;
    overflow: hidden;
    border-color: #dfe7f2 !important;
    border-radius: 22px !important;
    box-shadow: 0 16px 40px rgba(15, 23, 42, .06) !important;
  }
  .configure-page .config-card__head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }
  .configure-page .config-card .head-num {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    margin: 0;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    box-shadow: 0 10px 20px rgba(37, 99, 235, .2);
  }
  .configure-page .config-card .section-title { font-size: 1.08rem; line-height: 1.3; }
  .configure-page .config-card .section-label { margin: 0 0 14px; font-size: .82rem; text-transform: uppercase; letter-spacing: .08em; }
  .configure-page .config-empty-state {
    grid-column: 1 / -1;
    margin: 0;
    padding: 24px;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    background: #f8fafc;
    color: #64748b;
    text-align: center;
  }
  .configure-page .config-card .item-flex {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: 14px;
  }
  .configure-page .config-duration-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .configure-page .config-provider-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .configure-page .config-card .item-flex .pick {
    position: relative;
    width: 100%;
    min-height: 126px;
    padding: 18px 14px;
    border-color: #dfe7f2;
    border-radius: 18px;
    background: #fbfdff;
  }
  .configure-page .config-card .pick {
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease, background .2s ease;
  }
  .configure-page .config-card .pick:hover {
    border-color: #a8c3ee;
    box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
  }
  .configure-page .config-card .pick.active {
    border-color: #2563eb;
    background: linear-gradient(180deg, #f0f6ff 0%, #fff 100%);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .1), 0 14px 30px rgba(37, 99, 235, .1);
  }
  .configure-page .config-card .pick:disabled,
  .configure-page .config-card .pick.is-unavailable {
    cursor: not-allowed;
    opacity: .42;
    transform: none;
    box-shadow: none;
  }
  .configure-page .provider-logo {
    display: block;
    width: 54px;
    height: 54px;
    margin: 0 auto 10px;
    border-radius: 14px;
    object-fit: contain;
  }
  .configure-page .provider-name { font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
  .configure-page .pick-check {
    position: absolute;
    top: 12px;
    right: 12px;
    display: inline-flex;
    width: 24px;
    height: 24px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    font-size: 11px;
    opacity: 0;
    transform: scale(.75);
    transition: opacity .18s ease, transform .18s ease;
  }
  [dir="rtl"] .configure-page .pick-check { right: auto; left: 12px; }
  .configure-page .pick.active .pick-check { opacity: 1; transform: scale(1); }
  .configure-page .config-card .pkg-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 26px !important;
    margin-bottom: 0 !important;
  }
  .configure-page .config-card .pkg-card {
    display: flex;
    width: 100%;
    min-height: 210px;
    height: 100%;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    padding: 22px !important;
    text-align: left;
  }
  [dir="rtl"] .configure-page .config-card .pkg-card { align-items: flex-end; text-align: right; }
  .configure-page .config-card .pkg-badge {
    position: static;
    width: 46px;
    height: 46px;
    margin-bottom: 16px;
    border: 1px solid #dbeafe;
    background: #eff6ff;
    color: #1d4ed8;
    box-shadow: none;
    font-size: 20px;
  }
  .configure-page .pkg-provider {
    display: inline-flex;
    margin-bottom: 8px;
    padding: 5px 9px;
    border-radius: 999px;
    background: #edf2f7;
    color: #475569;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .configure-page .config-card .pkg-title { margin: 0 0 12px; font-size: 1.08rem; line-height: 1.35; }
  .configure-page .pkg-price-row { margin-top: auto; }
  .configure-page .config-card .pkg-new { font-size: 1.35rem; }
  .configure-page .config-sidebar { position: sticky; top: 96px; }
  .configure-page .config-order-summary {
    position: static;
    bottom: auto;
    display: block;
    padding: 24px;
    border-color: #dbe5f3;
    border-radius: 22px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, .09);
  }
  .configure-page .config-order-summary__details > small {
    margin-bottom: 6px;
    color: #2563eb;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
  }
  .configure-page .config-order-summary__details > strong { font-size: 1.12rem; line-height: 1.35; }
  .configure-page .config-order-summary__meta { grid-template-columns: 1fr; }
  .configure-page .config-order-summary__meta > div { padding: 10px 12px; border-radius: 12px; }
  .configure-page .config-order-summary__pricing {
    margin-top: 18px;
    padding: 16px 0 0;
    border-top: 1px solid #e2e8f0;
    border-left: 0;
  }
  [dir="rtl"] .configure-page .config-order-summary__pricing { padding-right: 0; border-right: 0; }
  .configure-page .config-order-summary__total { margin-top: 4px; padding-top: 12px; }
  .configure-page .config-order-summary__delivery { display: flex; align-items: center; gap: 7px; }
  .configure-page .config-continue {
    display: flex;
    width: 100%;
    min-height: 54px;
    justify-content: center;
    margin-top: 14px;
    border-radius: 15px;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    box-shadow: 0 14px 28px rgba(220, 38, 38, .2);
  }
  .configure-page .config-continue:not([disabled]):hover { transform: translateY(-2px); color: #fff; }

  @media (max-width: 991px) {
    .configure-page .config-layout { grid-template-columns: 1fr; }
    .configure-page .config-sidebar { position: static; }
  }
  @media (max-width: 767px) {
    .configure-page .config-wrap { padding-top: 32px !important; }
    .configure-page .config-hero { margin-bottom: 22px; }
    .configure-page .config-mode-card { display: block; padding: 14px; border-radius: 18px; }
    .configure-page .config-mode-card__label { margin-bottom: 12px; }
    .configure-page .config-mode-card .toggle-wrap { width: 100%; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .configure-page .config-mode-card .tg-btn { min-width: 0; padding: 9px 10px; font-size: .88rem; }
    .configure-page .config-card { padding: 20px !important; border-radius: 18px !important; }
    .configure-page .config-card .item-flex { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .configure-page .config-duration-grid,
    .configure-page .config-provider-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .configure-page .config-card .item-flex .pick { min-height: 116px; padding: 16px 10px; }
    .configure-page .config-card .pkg-grid {
      display: grid;
      grid-auto-flow: column;
      grid-auto-columns: minmax(250px, 82vw);
      grid-template-columns: none;
      gap: 12px;
      overflow-x: auto;
      padding: 8px 3px 14px;
      scroll-snap-type: x mandatory;
      scrollbar-width: thin;
    }
    .configure-page .config-card .pkg-card { min-height: 205px; scroll-snap-align: start; }
    .configure-page .config-order-summary { padding: 20px; border-radius: 18px; }
  }
  @media (max-width: 420px) {
    .configure-page .config-mode-card__label { font-size: .92rem; }
    .configure-page .config-mode-card__icon { flex-basis: 40px; width: 40px; height: 40px; }
    .configure-page .config-card { padding: 17px !important; }
    .configure-page .config-card .head-num { flex-basis: 38px; width: 38px; height: 38px; }
    .configure-page .config-card .section-title { font-size: .98rem; }
    .configure-page .config-card .item-flex .pick { font-size: .88rem; }
  }
</style>
@endpush

@section('content')
@php
  $requestedVendor = strtolower((string) request('iptv_vendor', request('vendor', '')));
  $requestedVendor = in_array($requestedVendor, ['filex', 'starshare'], true) ? 'starshare' : $requestedVendor;
  $requestedType = request('package_type', request('ptype', 'iptv'));
  $requestedType = $requestedType === 'reseller' ? 'reseller' : 'iptv';
  $requestedPrice = request('pkg_price', request('plan_price', request('price', '')));

  $durationOptions = [
    1 => [
      'label' => __('document_commerce.packages.pricing.plans.monthly.title'),
      'icon' => 'fa fa-calendar-o',
    ],
    3 => [
      'label' => __('document_commerce.packages.pricing.plans.three_months.title'),
      'icon' => 'fa fa-calendar',
    ],
    6 => [
      'label' => __('document_commerce.packages.pricing.plans.half_yearly.title'),
      'icon' => 'fa fa-star',
    ],
    12 => [
      'label' => __('document_commerce.packages.pricing.plans.yearly.title'),
      'icon' => 'fa fa-trophy',
    ],
  ];

  $providerKey = static function (string $service): string {
    $key = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $service));
    return (string) preg_replace('/iptv$/', '', $key);
  };

  $providerLabel = static fn (string $value): string => trim(
    (string) preg_replace('/^[^\p{L}\p{N}]+/u', '', $value)
  );

  $iptvProviderOptions = collect($iptvPackages)
    ->groupBy(fn (array $package) => $providerKey((string) ($package['service'] ?? '')))
    ->map(function ($packages, $key) use ($providerLabel) {
      $preferred = $packages->sortByDesc(
        fn (array $package) => str_contains((string) ($package['icon'] ?? ''), '/') ? 1 : 0
      )->first();

      return [
        'key' => $key,
        'label' => $providerLabel((string) ($preferred['service'] ?? '')),
        'vendor' => strtolower((string) ($preferred['vendor'] ?? '')),
        'icon' => $preferred['icon'] ?? '',
      ];
    })
    ->filter(fn (array $provider) => $provider['key'] !== '' && $provider['label'] !== '')
    ->values();
@endphp
<div class="config-wrap py-5">
  <div class="container config-shell">

    <header class="config-hero text-center">
      <h1 class="fw-bold change-font">
          {{ __('messages.checkout_step_title') }}
      </h1>
      <h2 class="text-muted mb-0" style="font-size:1rem; font-weight:600;">
          {{ __('messages.checkout_step_subtitle') }}
      </h2>
    </header>

    <form action="{{ route('checkout') }}" method="get" id="configForm" autocomplete="off">
      {{-- Hidden values sent to checkout --}}
      <input type="hidden" name="device"       id="deviceInput" value="{{ request('device') }}">
      <input type="hidden" name="device_id"    id="deviceIdInput" value="{{ request('device_id') }}">
      <input type="hidden" name="package_id"   id="packageIdInput" value="{{ request('package_id') }}">

      {{-- Store canonical vendor for logic + keep label for display in plan_name --}}
      <input type="hidden" name="iptv_vendor"  id="iptvVendorInput" value="{{ $requestedVendor }}" data-label="">

      {{-- Combined summary (what checkout expects) --}}
      <input type="hidden" name="plan_name"    id="planNameInput" value="{{ request('plan_name', request('plan')) }}">
      <input type="hidden" name="plan_price"   id="planPriceInput" value="{{ request('plan_price', request('price')) }}">

      {{-- Separate picks (with names so they reach checkout step) --}}
      <input type="hidden" name="pkg_price"        id="pkgPriceInput" value="{{ $requestedPrice }}">
      <input type="hidden" name="package_type"     id="packageTypeInput" value="{{ request()->filled('package_id') ? $requestedType : '' }}"> {{-- iptv | reseller --}}

      <section class="config-mode-card" id="modeSection">
        <div class="config-mode-card__label">
          <span class="config-mode-card__icon fa fa-th-large" aria-hidden="true"></span>
          <span>{{ __('messages.checkout_package_type') }}</span>
        </div>
        <div class="toggle-wrap" id="pkgToggle">
          <button type="button" class="tg-btn active" data-tab="iptv" aria-pressed="true">
            <span class="fa fa-television" aria-hidden="true"></span>
            {{ __('messages.checkout_iptv_packages_label') }}
          </button>
          <button type="button" class="tg-btn" data-tab="reseller" aria-pressed="false">
            <span class="fa fa-line-chart" aria-hidden="true"></span>
            {{ __('messages.checkout_reseller_packages_label') }}
          </button>
        </div>
      </section>

      <div class="config-layout" id="configLayout">
        <div class="config-builder" id="configBuilder">

      {{-- IPTV 1) Subscription duration --}}
      <div class="config-card p-4 mb-4" id="durationSection">
        <div class="config-card__head">
          <div class="head-num">1</div>
          <div class="section-title">{{ __('messages.checkout_subscription_title') }}</div>
        </div>
        <div class="item-flex config-duration-grid">
          @foreach ($durationOptions as $months => $option)
            <button type="button" class="pick duration-pick" aria-pressed="false"
                 data-duration-choice="{{ $months }}" data-label="{{ $option['label'] }}">
              <span class="pick-check fa fa-check" aria-hidden="true"></span>
              <span class="ico {{ $option['icon'] }} d-block" aria-hidden="true"></span>
              <span class="d-block">{{ $option['label'] }}</span>
            </button>
          @endforeach
        </div>
      </div>

      {{-- IPTV 2) Provider / Reseller 1) Umbrella vendor --}}
      <div class="config-card p-4 mb-4" id="vendorSection">
        <div class="config-card__head">
          <div class="head-num" id="vendorStepNumber">2</div>
          <div class="section-title" id="vendorSectionTitle">{{ __('messages.checkout_iptv_title') }}</div>
        </div>

        <div class="item-flex config-provider-grid" id="iptvProviderChoices">
          @forelse ($iptvProviderOptions as $provider)
            @php $providerHasImage = str_contains((string) $provider['icon'], '/'); @endphp
            <button type="button" class="pick provider-pick" aria-pressed="false"
                 data-iptv-provider data-provider-key="{{ $provider['key'] }}"
                 data-vendor="{{ $provider['vendor'] }}" data-label="{{ $provider['label'] }}">
              <span class="pick-check fa fa-check" aria-hidden="true"></span>
              @if ($providerHasImage)
                <img class="provider-logo" src="{{ asset(ltrim((string) $provider['icon'], '/')) }}"
                     width="54" height="54" alt="" loading="lazy">
              @else
                <span class="ico fa fa-signal d-block" aria-hidden="true"></span>
              @endif
              <span class="provider-name d-block">{{ $provider['label'] }}</span>
              <small class="provider-availability">{{ __('messages.checkout_iptv_small') }}</small>
            </button>
          @empty
            <p class="config-empty-state">{{ __('messages.no_results') }}</p>
          @endforelse
        </div>

        <div class="item-flex" id="resellerProviderChoices" hidden>
          @php $vendors = $iptvVendors ?? ['Opplex','starshare']; @endphp
          @foreach ($vendors as $v)
            @php $vendorLabel = strtolower($v) === 'starshare' ? 'Filex' : $v; @endphp
            <button type="button" class="pick" aria-pressed="false"
                 data-reseller-vendor data-vendor="{{ $v }}" data-label="{{ $vendorLabel }}">
              <span class="pick-check fa fa-check" aria-hidden="true"></span>
              <span class="ico fa fa-signal d-block" aria-hidden="true"></span>
              <span class="d-block">{{ $vendorLabel }}</span>
            </button>
          @endforeach
        </div>
      </div>

      {{-- IPTV 3) Device --}}
      <div class="config-card p-4 mb-4" id="deviceSection">
        <div class="config-card__head">
          <div class="head-num" id="deviceStepNumber">3</div>
          <div class="section-title">{{ __('messages.checkout_device_title') }}</div>
        </div>
        <div class="item-flex">
          @forelse ($devices as $d)
            @php
              $deviceIcon = match (strtolower($d->name)) {
                'smart tv' => 'fa fa-desktop',
                'firestick' => 'fa fa-fire',
                'android' => 'fa fa-android',
                'ios' => 'fa fa-apple',
                'mag box' => 'fa fa-cube',
                default => 'fa fa-laptop',
              };
            @endphp
            <button type="button" class="pick" aria-pressed="false"
                 data-device="{{ $d->name }}" data-device-id="{{ $d->id }}">
              <span class="pick-check fa fa-check" aria-hidden="true"></span>
              <span class="ico {{ $deviceIcon }} d-block" aria-hidden="true"></span>
              <span class="d-block">{{ $d->name }}</span>
            </button>
          @empty
            <p class="config-empty-state">{{ __('messages.no_results') }}</p>
          @endforelse
        </div>
      </div>

      {{-- Reseller 2) Credit package --}}
      <div class="config-card p-4 mb-5" id="packageSection" hidden>
        <div class="config-card__head">
          <div class="head-num" id="packageStepNumber">2</div>
          <div class="section-title">{{ __('messages.checkout_subscription_title') }}</div>
        </div>
        <div id="resellerWrap">
          <div class="section-label">{{ __('messages.checkout_reseller_packages_label') }}</div>
          <div class="pkg-grid mb-3 mt-4">
            @forelse ($resellerPackages as $p)
              @php $resellerProvider = strtolower($p['vendor']) === 'starshare' ? 'Filex' : ucfirst($p['vendor']); @endphp
              <button type="button" class="pick pkg-card mb-2 mt-2" aria-pressed="false"
                   data-kind="reseller"
                   data-package-id="{{ $p['id'] ?? '' }}"
                   data-vendor="{{ strtolower($p['vendor']) }}"
                   data-service="{{ $resellerProvider }}"
                   data-plan="{{ $p['title'] }}"
                   data-unit="{{ $p['unit'] }}"
                   data-price="{{ number_format($p['price'], 2, '.', '') }}">
                <span class="pick-check fa fa-check" aria-hidden="true"></span>
                <span class="pkg-badge fa fa-line-chart" aria-hidden="true"></span>
                <span class="pkg-provider">{{ $resellerProvider }}</span>
                <span class="pkg-title d-block">{{ $p['title'] }}</span>
                @if (($p['old'] ?? 0) > 0)
                  <span class="pkg-old d-block">${{ number_format($p['old'], 2) }}</span>
                @endif
                <span class="pkg-price-row d-block">
                  <span class="pkg-new">${{ number_format($p['price'], 2) }}</span>
                  <span class="pkg-unit">{{ $p['unit'] }}</span>
                </span>
              </button>
            @empty
              <p class="config-empty-state">{{ __('messages.no_results') }}</p>
            @endforelse
          </div>
        </div>
      </div>

      {{-- Exact package rows used to resolve provider + duration without recalculating prices. --}}
      <div id="iptvPackageCatalog" hidden aria-hidden="true">
        @foreach ($iptvPackages as $p)
          <button type="button" class="pkg-card" aria-pressed="false" tabindex="-1"
               data-kind="iptv"
               data-package-id="{{ $p['id'] ?? '' }}"
               data-provider-key="{{ $providerKey((string) ($p['service'] ?? '')) }}"
               data-provider-priority="{{ str_contains((string) ($p['icon'] ?? ''), '/') ? 1 : 0 }}"
               data-vendor="{{ strtolower($p['vendor']) }}"
               data-service="{{ $providerLabel((string) ($p['service'] ?? '')) }}"
               data-plan="{{ $providerLabel((string) $p['title']) }}"
               data-duration="{{ $p['duration_months'] }}"
               data-unit="{{ $p['unit'] }}"
               data-price="{{ number_format($p['price'], 2, '.', '') }}"></button>
        @endforeach
      </div>

        </div>

        <div class="config-sidebar">

      <aside class="config-order-summary mb-3" id="configOrderSummary" aria-live="polite">
        <div class="config-order-summary__details">
          <small>{{ __('messages.checkout_your_order') }}</small>
          <strong id="configSummaryPlan">{{ __('messages.checkout_selected_package_fallback') }}</strong>
          <button type="button" class="config-order-summary__edit" id="configChangePlan" hidden>
            {{ __('messages.checkout_edit_options') }}
          </button>

          <div class="config-order-summary__meta">
            <div>
              <span>{{ __('messages.checkout_provider') }}</span>
              <strong id="configSummaryProvider">&mdash;</strong>
            </div>
            <div>
              <span>{{ __('messages.checkout_subscription_label') }}</span>
              <strong id="configSummaryDuration">&mdash;</strong>
            </div>
            <div id="configSummaryDeviceItem">
              <span>{{ __('messages.checkout_device') }}</span>
              <strong id="configSummaryDevice">&mdash;</strong>
            </div>
          </div>

          <span class="config-order-summary__delivery"><i class="fa fa-clock-o" aria-hidden="true"></i> {{ __('messages.thankyou_page.delivery_text') }}</span>
        </div>
        <div class="config-order-summary__pricing">
          <div>
            <span>{{ __('messages.checkout_subtotal_label') }}</span>
            <strong id="configSummarySubtotal">$0.00</strong>
          </div>
          <div class="config-order-summary__discount" id="configSummaryDiscountRow" hidden>
            <span>{{ $eventPromotion['name'] ?? '' }} ({{ number_format((float) ($eventPromotion['discount_percent'] ?? 0), 0) }}% OFF)</span>
            <strong id="configSummaryDiscount">-$0.00</strong>
          </div>
          <div class="config-order-summary__total">
            <span>{{ __('messages.checkout_total_label') }}</span>
            <strong id="configSummaryTotal">$0.00</strong>
          </div>
        </div>
      </aside>

      <button type="submit" class="cta config-continue" id="continueBtn" disabled>
        <span class="fa fa-lock" aria-hidden="true"></span>
        {{ __('messages.checkout_continue_button') }}
      </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  document.body.classList.add('configure-page');

  // ---------- Helpers ----------
  const canon = (s) => String(s||'').toLowerCase().trim().replace(/\s+/g,'').replace(/[^a-z0-9]/g,'');
  const nice  = (s) => String(s||'').trim();

  const deviceInput       = document.getElementById('deviceInput');
  const deviceIdInput     = document.getElementById('deviceIdInput');
  const packageIdInput    = document.getElementById('packageIdInput');
  const vendorInput       = document.getElementById('iptvVendorInput');

  const planNameInput     = document.getElementById('planNameInput');
  const planPriceInput    = document.getElementById('planPriceInput');

  const pkgPriceInp       = document.getElementById('pkgPriceInput');
  const packageType       = document.getElementById('packageTypeInput');
  const btn               = document.getElementById('continueBtn');

  const durationSection   = document.getElementById('durationSection');
  const vendorSection     = document.getElementById('vendorSection');
  const deviceSection     = document.getElementById('deviceSection');
  const packageSection    = document.getElementById('packageSection');
  const modeSection       = document.getElementById('modeSection');
  const configLayout      = document.getElementById('configLayout');
  const vendorStepNumber  = document.getElementById('vendorStepNumber');
  const vendorSectionTitle= document.getElementById('vendorSectionTitle');

  const iptvProviderChoices = document.getElementById('iptvProviderChoices');
  const resellerProviderChoices = document.getElementById('resellerProviderChoices');
  const pkgToggle         = document.getElementById('pkgToggle');
  const summaryPlan       = document.getElementById('configSummaryPlan');
  const summaryProvider   = document.getElementById('configSummaryProvider');
  const summaryDuration   = document.getElementById('configSummaryDuration');
  const summaryDevice     = document.getElementById('configSummaryDevice');
  const summaryDeviceItem = document.getElementById('configSummaryDeviceItem');
  const summarySubtotal   = document.getElementById('configSummarySubtotal');
  const summaryDiscount   = document.getElementById('configSummaryDiscount');
  const summaryDiscountRow= document.getElementById('configSummaryDiscountRow');
  const summaryTotal      = document.getElementById('configSummaryTotal');
  const changePlanBtn     = document.getElementById('configChangePlan');
  const currency          = @json(config('services.app.default_currency', 'USD'));
  const discountPercent   = @json((float) ($eventPromotion['discount_percent'] ?? 0));
  const iptvVendorTitle   = @json(__('messages.checkout_iptv_title'));
  const resellerVendorTitle = @json(__('messages.checkout_provider'));
  let activePackageTab    = @json($requestedType);
  let selectedDuration    = '';
  let selectedProviderKey = '';
  let preselectedMode     = false;

  function setPickActive(card, active){
    card.classList.toggle('active', active);
    card.setAttribute('aria-pressed', active ? 'true' : 'false');
  }
  function toNumber(v){ const n = parseFloat(v); return isNaN(n)?0:n; }
  function formatMoney(value){
    return `${currency === 'USD' ? '$' : currency + ' '}${Number(value).toFixed(2)}`;
  }
  function clearGroup(sel){ document.querySelectorAll(sel).forEach(x=>setPickActive(x, false)); }
  function flash(el){ el.style.boxShadow='0 0 0 4px rgba(37,99,235,.35)'; setTimeout(()=> el.style.boxShadow='',800); }

  function iptvCards(){
    return Array.from(document.querySelectorAll('[data-kind="iptv"]'));
  }

  function clearCheckoutPackageFields(){
    pkgPriceInp.value = '';
    packageType.value = '';
    packageIdInput.value = '';
  }

  function clearPackageSelection(kind){
    clearGroup(`[data-kind="${kind}"]`);
    if (packageType.value === kind) clearCheckoutPackageFields();
  }

  function findIptvPackage(providerKey, duration){
    return iptvCards()
      .filter(card => card.getAttribute('data-provider-key') === providerKey
        && Number(card.getAttribute('data-duration')) === Number(duration))
      .sort((a, b) => Number(b.getAttribute('data-provider-priority') || 0)
        - Number(a.getAttribute('data-provider-priority') || 0))[0] || null;
  }

  function syncProviderAvailability(){
    document.querySelectorAll('[data-iptv-provider]').forEach(provider => {
      const providerKey = provider.getAttribute('data-provider-key') || '';
      const unavailable = !selectedDuration || !findIptvPackage(providerKey, selectedDuration);
      provider.disabled = unavailable;
      provider.classList.toggle('is-unavailable', unavailable);
      provider.setAttribute('aria-disabled', unavailable ? 'true' : 'false');

      if (unavailable && provider.classList.contains('active')) {
        setPickActive(provider, false);
        selectedProviderKey = '';
        vendorInput.value = '';
        vendorInput.dataset.label = '';
        clearPackageSelection('iptv');
      }
    });
  }

  function filterResellerByVendor(vendorCanon){
    const target = canon(vendorCanon);
    document.querySelectorAll('[data-kind="reseller"]').forEach(card=>{
      const v = canon(card.getAttribute('data-vendor'));
      const show = !target || v === target;
      card.style.display = show ? '' : 'none';
      if(!show && card.classList.contains('active')){
        setPickActive(card, false);
        if (packageType.value === 'reseller') clearCheckoutPackageFields();
      }
    });
  }

  // ---------- Summary & readiness ----------
  function currentActivePackage(){
    return document.querySelector(`[data-kind="${activePackageTab}"].pkg-card.active`);
  }

  function updateSummary(){
    const vendorLabel = vendorInput.dataset.label || vendorInput.value || '';
    const pkgPrice    = toNumber(pkgPriceInp.value);
    const isReseller = activePackageTab === 'reseller';
    const activePackage = currentActivePackage();
    const serviceLabel = activePackage ? (activePackage.getAttribute('data-service') || '') : '';
    const durationChoice = document.querySelector('[data-duration-choice].active');

    const subtotal = pkgPrice;
    const discount = Math.round((subtotal * discountPercent / 100 + Number.EPSILON) * 100) / 100;
    const total = Math.max(0, subtotal - discount);
    planNameInput.value  = pkgPriceInp.value ? findActivePlanText() : '';
    planPriceInput.value = subtotal.toFixed(2);

    summaryPlan.textContent = findActivePlanText() || @json(__('messages.checkout_selected_package_fallback'));
    summaryProvider.textContent = vendorLabel || serviceLabel || '\u2014';
    summaryDuration.textContent = activePackage
      ? (activePackage.getAttribute('data-unit') || '\u2014').replace(/^\/\s*/, '')
      : (!isReseller && durationChoice ? (durationChoice.getAttribute('data-label') || '\u2014') : '\u2014');
    summaryDevice.textContent = !isReseller && deviceInput.value ? deviceInput.value : '\u2014';
    summaryDeviceItem.hidden = isReseller;
    summarySubtotal.textContent = formatMoney(subtotal);
    summaryDiscount.textContent = `-${formatMoney(discount)}`;
    summaryDiscountRow.hidden = discount <= 0;
    summaryTotal.textContent = formatMoney(total);

    enableIfReady();
  }

  function findActivePlanText(){
    const active = currentActivePackage();
    return active ? (active.getAttribute('data-plan') || '') : '';
  }

  function enableIfReady(){
    const hasDevice   = !!deviceInput.value;
    const hasVendor   = !!vendorInput.value;
    const hasPackage  = !!pkgPriceInp.value;
    const kind        = packageType.value;
    const ok = activePackageTab === 'reseller'
      ? kind === 'reseller' && hasPackage
      : kind === 'iptv' && !!selectedDuration && !!selectedProviderKey
        && hasDevice && hasVendor && hasPackage;
    btn.disabled = !ok;
  }

  // ---------- Selection ----------
  function selectMonetary(card){
    const kind  = card.getAttribute('data-kind');
    const price = card.getAttribute('data-price') || '0';

    if(kind==='iptv'){
      if(!selectedDuration || !selectedProviderKey || !vendorInput.value){
        flash(vendorSection);
        vendorSection.scrollIntoView({behavior:'smooth', block:'center'});
        return;
      }
      clearGroup('[data-kind="iptv"]');
      setPickActive(card, true);
      pkgPriceInp.value  = price;
      packageType.value  = 'iptv';
      if (packageIdInput) packageIdInput.value = card.getAttribute('data-package-id') || '';
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_item', {
          item_list_id: 'configure',
          items: [{
            item_id: card.getAttribute('data-package-id') || card.getAttribute('data-plan'),
            item_name: card.getAttribute('data-plan') || '',
            item_brand: card.getAttribute('data-service') || vendorInput.dataset.label || vendorInput.value,
            item_category: 'iptv',
            price: toNumber(price),
            quantity: 1
          }]
        });
      }
      return;
    }

    if(kind==='reseller'){
      const cardVendor = canon(card.getAttribute('data-vendor'));
      if (cardVendor) {
        const vendorCard = Array.from(document.querySelectorAll('[data-reseller-vendor]'))
          .find(item => canon(item.getAttribute('data-vendor')) === cardVendor);
        clearGroup('[data-reseller-vendor]');
        if (vendorCard) setPickActive(vendorCard, true);
        vendorInput.value = cardVendor;
        vendorInput.dataset.label = card.getAttribute('data-service')
          || (vendorCard ? vendorCard.getAttribute('data-label') : cardVendor);
        filterResellerByVendor(cardVendor);
      }
      clearGroup('[data-kind="reseller"]');
      setPickActive(card, true);
      pkgPriceInp.value  = price;
      packageType.value  = 'reseller';
      if (packageIdInput) packageIdInput.value = card.getAttribute('data-package-id') || '';
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_item', {
          item_list_id: 'configure',
          items: [{
            item_id: card.getAttribute('data-package-id') || card.getAttribute('data-plan'),
            item_name: card.getAttribute('data-plan') || '',
            item_brand: card.getAttribute('data-service') || vendorInput.dataset.label || vendorInput.value,
            item_category: 'reseller',
            price: toNumber(price),
            quantity: 1
          }]
        });
      }
      return;
    }
  }

  function resolveIptvPackage(){
    clearPackageSelection('iptv');

    if (!selectedDuration || !selectedProviderKey) {
      updateSummary();
      return;
    }

    const target = findIptvPackage(selectedProviderKey, selectedDuration);
    const provider = Array.from(document.querySelectorAll('[data-iptv-provider]'))
      .find(choice => choice.getAttribute('data-provider-key') === selectedProviderKey);

    if (!target || !provider) {
      updateSummary();
      return;
    }

    vendorInput.value = canon(target.getAttribute('data-vendor'));
    vendorInput.dataset.label = provider.getAttribute('data-label')
      || target.getAttribute('data-service') || vendorInput.value;
    selectMonetary(target);
  }

  // device select
  document.querySelectorAll('[data-device]').forEach(c=>{
    c.addEventListener('click', ()=>{
      clearGroup('[data-device]');
      setPickActive(c, true);
      deviceInput.value = c.getAttribute('data-device') || '';
      if (deviceIdInput) deviceIdInput.value = c.getAttribute('data-device-id') || '';
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'device',
          item_id: deviceInput.value
        });
      }
    });
  });

  // subscription duration select
  document.querySelectorAll('[data-duration-choice]').forEach(c=>{
    c.addEventListener('click', ()=>{
      clearGroup('[data-duration-choice]');
      setPickActive(c, true);
      selectedDuration = c.getAttribute('data-duration-choice') || '';
      syncProviderAvailability();
      if (selectedProviderKey) resolveIptvPackage();
      else updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'subscription_duration',
          item_id: selectedDuration
        });
      }
    });
  });

  // IPTV provider select
  document.querySelectorAll('[data-iptv-provider]').forEach(c=>{
    c.addEventListener('click', ()=>{
      if (c.disabled) return;
      clearGroup('[data-iptv-provider]');
      setPickActive(c, true);
      selectedProviderKey = c.getAttribute('data-provider-key') || '';
      vendorInput.value = canon(c.getAttribute('data-vendor'));
      vendorInput.dataset.label = nice(c.getAttribute('data-label'));
      resolveIptvPackage();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'iptv_provider',
          item_id: selectedProviderKey
        });
      }
    });
  });

  // reseller provider select
  document.querySelectorAll('[data-reseller-vendor]').forEach(c=>{
    c.addEventListener('click', ()=>{
      clearGroup('[data-reseller-vendor]');
      setPickActive(c, true);
      const vendorValue = canon(c.getAttribute('data-vendor'));
      vendorInput.value = vendorValue;
      vendorInput.dataset.label = nice(c.getAttribute('data-label') || vendorValue);
      filterResellerByVendor(vendorValue);
      clearPackageSelection('reseller');
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'reseller_provider',
          item_id: vendorValue === 'starshare' ? 'filex' : vendorValue
        });
      }
    });
  });

  // reseller package select; IPTV packages resolve from provider + duration.
  document.querySelectorAll('[data-kind="reseller"]').forEach(c=>{
    c.addEventListener('click', ()=> selectMonetary(c));
  });

  function renderModeSections(){
    const isResellerTab = activePackageTab === 'reseller';
    modeSection.hidden = preselectedMode;
    changePlanBtn.hidden = !preselectedMode;
    configLayout.classList.toggle('is-summary-only', preselectedMode && isResellerTab);
    vendorStepNumber.textContent = isResellerTab ? '1' : '2';
    vendorSectionTitle.textContent = isResellerTab ? resellerVendorTitle : iptvVendorTitle;

    if (preselectedMode) {
      durationSection.hidden = true;
      vendorSection.hidden = true;
      packageSection.hidden = true;
      deviceSection.hidden = isResellerTab;
      return;
    }

    durationSection.hidden = isResellerTab;
    vendorSection.hidden = false;
    deviceSection.hidden = isResellerTab;
    packageSection.hidden = !isResellerTab;
    iptvProviderChoices.hidden = isResellerTab;
    resellerProviderChoices.hidden = !isResellerTab;
  }

  function setPreselectedMode(enabled){
    preselectedMode = enabled;
    renderModeSections();
  }

  changePlanBtn.addEventListener('click', function(){
    setPreselectedMode(false);
    const target = activePackageTab === 'reseller' ? vendorSection : durationSection;
    target.scrollIntoView({behavior:'smooth', block:'start'});
  });

  function setPackageTab(tab, resetSelections){
    activePackageTab = tab === 'reseller' ? 'reseller' : 'iptv';
    const buttons = pkgToggle.querySelectorAll('.tg-btn');
    buttons.forEach(b => {
      const active = b.getAttribute('data-tab') === activePackageTab;
      b.classList.toggle('active', active);
      b.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    if (resetSelections) {
      clearGroup('[data-duration-choice]');
      clearGroup('[data-iptv-provider]');
      clearGroup('[data-reseller-vendor]');
      clearGroup('[data-device]');
      clearGroup('[data-kind]');
      selectedDuration = '';
      selectedProviderKey = '';
      deviceInput.value = '';
      deviceIdInput.value = '';
      vendorInput.value = '';
      vendorInput.dataset.label = '';
      clearCheckoutPackageFields();
      filterResellerByVendor('');
    }

    renderModeSections();
    if (activePackageTab === 'iptv') syncProviderAvailability();
    updateSummary();
  }
  pkgToggle.querySelectorAll('.tg-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      setPreselectedMode(false);
      setPackageTab(btn.getAttribute('data-tab'), true);
    });
  });
  setPackageTab(@json($requestedType), true);

  // ---------- Deep-link support (pricing to configure) ----------
  function applyDeepLinkSelection() {
    const url     = new URL(window.location.href);
    const rawType = url.searchParams.get('ptype') || url.searchParams.get('package_type') || 'iptv';
    const ptype   = rawType === 'reseller' ? 'reseller' : 'iptv';
    const priceQ  = url.searchParams.get('price') || url.searchParams.get('pkg_price');
    const planQ   = url.searchParams.get('plan') || url.searchParams.get('plan_name');
    const vendorQ = url.searchParams.get('vendor') || url.searchParams.get('iptv_vendor');
    const packageIdQ = url.searchParams.get('package_id');
    const deviceIdQ = url.searchParams.get('device_id');
    const deviceQ = url.searchParams.get('device');
    setPackageTab(ptype, true);

    let vendorCan = vendorQ ? canon(vendorQ) : '';
    if (vendorCan === 'filex' || vendorCan === 'starshare') {
      vendorCan = 'starshare';
    }

    const allCards = Array.from(document.querySelectorAll(`[data-kind="${ptype}"]`));

    const cards = vendorCan
      ? allCards.filter(x => canon(x.getAttribute('data-vendor')) === vendorCan)
      : allCards;

    let target = null;
    if (packageIdQ) {
      target = allCards.find(
        x => String(x.getAttribute('data-package-id') || '') === String(packageIdQ)
      );
    }
    if (!target && planQ) {
      const expectedPlan = canon(planQ);
      if (expectedPlan !== '') {
        target = cards.find(
          x => canon(x.getAttribute('data-plan')) === expectedPlan
        );
      }
    }
    if (!target && priceQ) {
      target = cards.find(
        x => toNumber(x.getAttribute('data-price')) === toNumber(priceQ)
      );
    }

    if (target) {
      if (ptype === 'iptv') {
        selectedDuration = target.getAttribute('data-duration') || '';
        const durationChoice = Array.from(document.querySelectorAll('[data-duration-choice]'))
          .find(choice => choice.getAttribute('data-duration-choice') === selectedDuration);
        if (durationChoice) {
          clearGroup('[data-duration-choice]');
          setPickActive(durationChoice, true);
        }

        syncProviderAvailability();
        selectedProviderKey = target.getAttribute('data-provider-key') || '';
        const provider = Array.from(document.querySelectorAll('[data-iptv-provider]'))
          .find(choice => choice.getAttribute('data-provider-key') === selectedProviderKey);
        if (provider) {
          clearGroup('[data-iptv-provider]');
          setPickActive(provider, true);
          vendorInput.value = canon(target.getAttribute('data-vendor'));
          vendorInput.dataset.label = provider.getAttribute('data-label')
            || target.getAttribute('data-service') || vendorInput.value;
          selectMonetary(target);
        }
      } else {
        const targetVendor = canon(target.getAttribute('data-vendor'));
        const provider = Array.from(document.querySelectorAll('[data-reseller-vendor]'))
          .find(choice => canon(choice.getAttribute('data-vendor')) === targetVendor);
        if (provider) {
          clearGroup('[data-reseller-vendor]');
          setPickActive(provider, true);
          vendorInput.value = targetVendor;
          vendorInput.dataset.label = provider.getAttribute('data-label') || targetVendor;
          filterResellerByVendor(targetVendor);
        }
        selectMonetary(target);
      }
    } else if (ptype === 'reseller' && vendorCan) {
      const provider = Array.from(document.querySelectorAll('[data-reseller-vendor]'))
        .find(choice => canon(choice.getAttribute('data-vendor')) === vendorCan);
      if (provider) provider.click();
    }

    const deviceTarget = Array.from(document.querySelectorAll('[data-device]')).find(card => {
      if (deviceIdQ && String(card.getAttribute('data-device-id')) === String(deviceIdQ)) return true;
      return deviceQ && canon(card.getAttribute('data-device')) === canon(deviceQ);
    });
    if (deviceTarget && ptype !== 'reseller') deviceTarget.click();

    if (target && packageIdQ) setPreselectedMode(true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(applyDeepLinkSelection, 0), { once: true });
  } else {
    setTimeout(applyDeepLinkSelection, 0);
  }

  document.getElementById('configForm').addEventListener('submit', function() {
    if (typeof window.trackMarketingEvent === 'function') {
      window.trackMarketingEvent('begin_checkout', {
        currency: currency,
        value: toNumber(planPriceInput.value),
        items: [{
          item_id: packageIdInput.value || planNameInput.value,
          item_name: findActivePlanText(),
          item_brand: currentActivePackage()?.getAttribute('data-service')
            || vendorInput.dataset.label || vendorInput.value,
          item_category: packageType.value || 'iptv',
          price: toNumber(planPriceInput.value),
          quantity: 1
        }]
      }, 'InitiateCheckout');
    }
  });
})();
</script>
@endsection
