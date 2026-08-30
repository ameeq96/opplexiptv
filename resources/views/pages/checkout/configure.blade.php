@extends('layouts.default')
@section('title', __('messages.checkout_step_title'))

@section('content')
@php
  $requestedVendor = strtolower((string) request('iptv_vendor', request('vendor', '')));
  $requestedVendor = in_array($requestedVendor, ['filex', 'starshare'], true) ? 'starshare' : $requestedVendor;
  $requestedType = request('package_type', request('ptype', 'iptv'));
  $requestedType = $requestedType === 'reseller' ? 'reseller' : 'iptv';
  $requestedPrice = request('pkg_price', request('plan_price', request('price', '')));
@endphp
<div class="config-wrap py-5">
  <div class="container">

    <div class="text-center mb-4">
      <h1 class="fw-bold change-font">
          {{ __('messages.checkout_step_title') }}
      </h1>
      <h2 class="text-muted mb-0" style="font-size:1rem; font-weight:600;">
          {{ __('messages.checkout_step_subtitle') }}
      </h2>
    </div>

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
      <input type="hidden" name="connection_price" id="connectionPriceInput" value="{{ request('connection_price', $requestedType === 'iptv' && $requestedVendor !== '' ? '0.00' : '') }}">
      <input type="hidden" name="connection_name"  id="connectionNameInput" value="{{ request('connection_name', $requestedType === 'iptv' && $requestedVendor !== '' ? __('messages.checkout_one_connection_label') : '') }}">
      <input type="hidden" name="pkg_price"        id="pkgPriceInput" value="{{ $requestedPrice }}">
      <input type="hidden" name="package_type"     id="packageTypeInput" value="{{ request()->filled('package_id') ? $requestedType : '' }}"> {{-- iptv | reseller --}}

      {{-- 1) Device --}}
      <div class="config-card p-4 mb-4" id="deviceSection">
        <div class="d-flex align-items-center mb-3">
          <div class="head-num">1</div>
          <div class="section-title">{{ __('messages.checkout_device_title') }}</div>
        </div>
        <div class="item-flex">
          @foreach ($devices as $d)
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
            <div class="pick"
                 data-device="{{ $d->name }}"
                 data-device-id="{{ $d->id }}">
              <div class="ico {{ $deviceIcon }}" aria-hidden="true"></div>
              <div>{{ $d->name }}</div>
            </div>
          @endforeach
        </div>
      </div>

      {{-- 2) IPTV Vendor --}}
      <div class="config-card p-4 mb-4" id="vendorSection">
        <div class="d-flex align-items-center mb-3">
          <div class="head-num">2</div>
          <div class="section-title">{{ __('messages.checkout_iptv_title') }}</div>
        </div>
        <div class="item-flex">
          @php $vendors = $iptvVendors ?? ['Opplex','starshare']; @endphp
          @foreach ($vendors as $v)
            @php $vendorLabel = strtolower($v) === 'starshare' ? 'Filex' : $v; @endphp
            <div class="pick" data-vendor="{{ $v }}" data-label="{{ $vendorLabel }}">
              <div class="ico fa fa-signal" aria-hidden="true"></div>
              <div>{{ $vendorLabel }}</div>
              <small>{{ __('messages.checkout_iptv_small') }}</small>
            </div>
          @endforeach
        </div>
        <div class="lock-hint mt-2">
          {{ __('messages.checkout_iptv_lock_hint') }}
        </div>
      </div>

      @php
        $onePlanName  = __('messages.checkout_one_connection_label');
        $onePlanPrice = '0.00';
        $filexYearlyConnectionPrices = \App\Models\Package::FILEX_YEARLY_CONNECTION_PRICES;
      @endphp

      {{-- 3) Connection Plan --}}
      <div class="config-card p-4 mb-4 locked" id="connectionSection">
        <div class="d-flex align-items-center mb-1">
          <div class="head-num">3</div>
          <div class="section-title">{{ __('messages.checkout_connection_title') }}</div>
        </div>
        <div class="lock-hint mb-3" id="lockMsg">
            {{ __('messages.checkout_connection_lock_msg') }}
        </div>

        <div class="item-flex">
          {{-- One connection is included in the selected subscription price. --}}
          <div class="pick"
               data-kind="connection"
               data-max="1"
               data-yearly="0"
               data-plan="{{ $onePlanName }}"
               data-price="{{ $onePlanPrice }}">
            <div class="ico fa fa-wifi" aria-hidden="true"></div>
            <div>{{ $onePlanName }}</div>
            <small>{{ __('messages.checkout_one_connection_hint') }}</small>
          </div>

          {{-- Two connections, yearly only. --}}
          <div class="pick"
               data-kind="connection"
               data-max="2"
               data-yearly="1"
               data-plan="{{ __('messages.checkout_two_connection_label') }}"
               data-price="{{ number_format($filexYearlyConnectionPrices[2], 2, '.', '') }}">
            <div class="ico fa fa-wifi" aria-hidden="true"></div>
            <div>{{ __('messages.checkout_two_connection_label') }}</div>
            <small>{{ __('messages.checkout_two_connection_hint') }}</small>
          </div>

          {{-- Four connections, yearly only. --}}
          <div class="pick"
               data-kind="connection"
               data-max="4"
               data-yearly="1"
               data-plan="{{ __('messages.checkout_four_connection_label') }}"
               data-price="{{ number_format($filexYearlyConnectionPrices[4], 2, '.', '') }}">
            <div class="ico fa fa-wifi" aria-hidden="true"></div>
            <div>{{ __('messages.checkout_four_connection_label') }}</div>
            <small>{{ __('messages.checkout_four_connection_hint') }}</small>
          </div>
        </div>
      </div>

      {{-- 4) Packages + Toggle --}}
      <div class="config-card p-4 mb-5" id="packageSection">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center">
            <div class="head-num">4</div>
            <div class="section-title">{{ __('messages.checkout_subscription_title') }}</div>
          </div>

          <div class="toggle-wrap" id="pkgToggle">
            <button type="button" class="tg-btn active" data-tab="iptv">
                {{ __('messages.checkout_iptv_packages_label') }}
            </button>
            <button type="button" class="tg-btn" data-tab="reseller">
                {{ __('messages.checkout_reseller_packages_label') }}
            </button>
          </div>
        </div>

        {{-- IPTV GRID --}}
        <div id="iptvWrap">
          <div class="section-label">{{ __('messages.checkout_iptv_packages_label') }}</div>
          <div class="pkg-grid mb-5 mt-4">
            @foreach ($iptvPackages as $p)
              <div class="pick pkg-card"
                   data-kind="iptv"
                   data-package-id="{{ $p['id'] ?? '' }}"
                   data-vendor="{{ strtolower($p['vendor']) }}"
                   data-plan="{{ $p['title'] }}"
                   data-duration="{{ $p['duration_months'] }}"
                   data-unit="{{ strtolower($p['unit']) }}"
                   data-price="{{ number_format($p['price'], 2, '.', '') }}">
                <div class="pkg-badge fa fa-film" aria-hidden="true"></div>
                <div class="pkg-title">{{ $p['title'] }}</div>
                @if (($p['old'] ?? 0) > 0)
                  <div class="pkg-old">${{ number_format($p['old'], 2) }}</div>
                @endif
                <div>
                  <span class="pkg-new">${{ number_format($p['price'], 2) }}</span>
                  <span class="pkg-unit">{{ $p['unit'] }}</span>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Reseller GRID --}}
        <div id="resellerWrap" class="hidden">
          <div class="section-label">{{ __('messages.checkout_reseller_packages_label') }}</div>
          <div class="pkg-grid mb-3 mt-4">
            @foreach ($resellerPackages as $p)
              <div class="pick pkg-card mb-2 mt-2"
                   data-kind="reseller"
                   data-package-id="{{ $p['id'] ?? '' }}"
                   data-vendor="{{ strtolower($p['vendor']) }}"
                   data-plan="{{ $p['title'] }}"
                   data-unit="{{ strtolower($p['unit']) }}"
                   data-price="{{ number_format($p['price'], 2, '.', '') }}">
                <div class="pkg-badge fa fa-line-chart" aria-hidden="true"></div>
                <div class="pkg-title">{{ $p['title'] }}</div>
                <div class="pkg-old">${{ number_format($p['old'], 2) }}</div>
                <div>
                  <span class="pkg-new">${{ number_format($p['price'], 2) }}</span>
                  <span class="pkg-unit">{{ $p['unit'] }}</span>
                </div>
              </div>
            @endforeach
          </div>
        </div>

      </div>

      <aside class="config-order-summary mb-3" id="configOrderSummary" aria-live="polite">
        <div>
          <small>{{ __('messages.checkout_your_order') }}</small>
          <strong id="configSummaryPlan">{{ __('messages.checkout_selected_package_fallback') }}</strong>
          <span id="configSummaryMeta"></span>
          <span><i class="fa fa-clock-o" aria-hidden="true"></i> {{ __('messages.thankyou_page.delivery_text') }}</span>
          <button type="button" class="config-order-summary__edit" id="configChangePlan" hidden>
            {{ __('messages.checkout_edit_options') }}
          </button>
        </div>
        <div class="config-order-summary__total">
          <small>{{ __('messages.checkout_total_label') }}</small>
          <strong id="configSummaryTotal">$0.00</strong>
        </div>
      </aside>

      <button type="submit" class="cta" id="continueBtn" disabled>
        <span class="fa fa-lock" aria-hidden="true"></span>
        {{ __('messages.checkout_continue_button') }}
      </button>
    </form>
  </div>
</div>

<script>
(function(){
  // ---------- Helpers ----------
  const canon = (s) => String(s||'').toLowerCase().trim().replace(/\s+/g,'').replace(/[^a-z0-9]/g,'');
  const nice  = (s) => String(s||'').trim();

  const deviceInput       = document.getElementById('deviceInput');
  const deviceIdInput     = document.getElementById('deviceIdInput');
  const packageIdInput    = document.getElementById('packageIdInput');
  const vendorInput       = document.getElementById('iptvVendorInput');

  const planNameInput     = document.getElementById('planNameInput');
  const planPriceInput    = document.getElementById('planPriceInput');

  const connectionPriceInp= document.getElementById('connectionPriceInput');
  const connectionNameInp = document.getElementById('connectionNameInput');
  const pkgPriceInp       = document.getElementById('pkgPriceInput');
  const packageType       = document.getElementById('packageTypeInput');
  const btn               = document.getElementById('continueBtn');

  const vendorSection     = document.getElementById('vendorSection');
  const connectionSection = document.getElementById('connectionSection');
  const deviceSection     = document.getElementById('deviceSection');
  const packageSection    = document.getElementById('packageSection');
  const connectionStepNum = connectionSection.querySelector('.head-num');
  const connectionLockMsg = document.getElementById('lockMsg');

  const iptvWrap          = document.getElementById('iptvWrap');
  const resellerWrap      = document.getElementById('resellerWrap');
  const pkgToggle         = document.getElementById('pkgToggle');
  const summaryPlan       = document.getElementById('configSummaryPlan');
  const summaryMeta       = document.getElementById('configSummaryMeta');
  const summaryTotal      = document.getElementById('configSummaryTotal');
  const changePlanBtn     = document.getElementById('configChangePlan');
  const currency          = @json(config('services.app.default_currency', 'USD'));

  let isYearlyPackage = false;

  function setPreselectedMode(enabled){
    vendorSection.hidden = enabled;
    packageSection.hidden = enabled;
    changePlanBtn.hidden = !enabled;
    connectionStepNum.textContent = enabled ? '2' : '3';
    connectionLockMsg.hidden = enabled;
  }

  changePlanBtn.addEventListener('click', function(){
    setPreselectedMode(false);
    packageSection.scrollIntoView({behavior:'smooth', block:'start'});
  });

  function lockConnection(lock=true){ connectionSection.classList.toggle('locked', lock); }
  function toNumber(v){ const n = parseFloat(v); return isNaN(n)?0:n; }
  function clearGroup(sel){ document.querySelectorAll(sel).forEach(x=>x.classList.remove('active')); }
  function flash(el){ el.style.boxShadow='0 0 0 4px rgba(37,99,235,.35)'; setTimeout(()=> el.style.boxShadow='',800); }

  // ---------- Connection visibility (vendor + yearly logic) ----------
  function updateConnectionVisibility(){
    const isOpplex = canon(vendorInput.value) === 'opplex';

    document.querySelectorAll('[data-kind="connection"]').forEach(card=>{
      const max = Number(card.getAttribute('data-max')||'0');
      const yearlyOnly = card.getAttribute('data-yearly') === '1';

      let show = true;

      if (yearlyOnly && !isYearlyPackage) show = false;
      if (isOpplex && max !== 1) show = false;

      card.style.display = show ? '' : 'none';

      if (!show && card.classList.contains('active')) {
        card.classList.remove('active');
        connectionPriceInp.value = '';
        connectionNameInp.value = '';
        updateSummary();
      }
    });
  }

  function filterIptvByVendor(vendorCanon){
    const target = canon(vendorCanon);
    document.querySelectorAll('[data-kind="iptv"]').forEach(card=>{
      const v = canon(card.getAttribute('data-vendor'));
      const show = !target || v === target;
      card.style.display = show ? '' : 'none';
      if(!show && card.classList.contains('active')){
        card.classList.remove('active');
        pkgPriceInp.value='';
        packageType.value='';
        if(packageIdInput) packageIdInput.value='';
        updateSummary();
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
        card.classList.remove('active');
        pkgPriceInp.value='';
        packageType.value='';
        if(packageIdInput) packageIdInput.value='';
        updateSummary();
      }
    });
  }

  function filterConnectionsByVendor(vendorCanon){
    updateConnectionVisibility();
  }

  // ---------- Summary & readiness ----------
  function updateSummary(){
    const vendorLabel = vendorInput.dataset.label || vendorInput.value || '';
    const connPrice   = toNumber(connectionPriceInp.value);
    const pkgPrice    = toNumber(pkgPriceInp.value);
    const isReseller = packageType.value === 'reseller';
    const activeConnection = isReseller
      ? null
      : document.querySelector('[data-kind="connection"].active');
    const connectionCount = activeConnection
      ? Number(activeConnection.getAttribute('data-max') || '1')
      : 1;

    const parts = [];
    if (vendorLabel && pkgPriceInp.value) parts.push(`${vendorLabel} - ${findActivePlanText()}`);
    else if (pkgPriceInp.value) parts.push(findActivePlanText());

    const total = connectionCount > 1 ? connPrice : pkgPrice;
    planNameInput.value  = parts.join(' + ');
    planPriceInput.value = total.toFixed(2);

    summaryPlan.textContent = parts.join(' + ') || @json(__('messages.checkout_selected_package_fallback'));
    summaryMeta.textContent = !isReseller && connectionNameInp.value
      ? `${vendorLabel ? vendorLabel + ' | ' : ''}${connectionNameInp.value}`
      : vendorLabel;
    summaryTotal.textContent = `${currency === 'USD' ? '$' : currency + ' '}${total.toFixed(2)}`;

    enableIfReady();
  }

  function findActivePlanText(){
    const active = document.querySelector('.pkg-card.active');
    return active ? (active.getAttribute('data-plan') || '') : '';
  }

  function enableIfReady(){
    const hasDevice   = !!deviceInput.value;
    const hasVendor   = !!vendorInput.value;
    const hasConn     = !!connectionPriceInp.value;
    const hasPackage  = !!pkgPriceInp.value;
    const kind        = packageType.value;
    let ok;
    if (kind === 'reseller') {
      ok = hasPackage;
    } else {
      ok = hasDevice && hasVendor && hasConn && hasPackage;
    }
    btn.disabled = !ok;
  }

  // set yearly flag based on selected package card
  function setYearlyFlagFromCard(card){
    const duration = Number(card.getAttribute('data-duration') || '0');
    const unit = (card.getAttribute('data-unit') || '').toLowerCase();
    isYearlyPackage =
      duration === 12 ||
      unit.includes('year') ||
      unit.includes('12month') ||
      unit.includes('12-month') ||
      unit.includes('12 m');
    updateConnectionVisibility();
  }

  // ---------- Selection ----------
  function selectMonetary(card){
    const kind  = card.getAttribute('data-kind');
    const price = card.getAttribute('data-price') || '0';

    if(kind==='connection'){
      if(!vendorInput.value){
        flash(vendorSection);
        vendorSection.scrollIntoView({behavior:'smooth', block:'center'});
        return;
      }
      document.querySelectorAll('[data-kind="connection"]').forEach(x=>x.classList.remove('active'));
      card.classList.add('active');
      connectionPriceInp.value = price;
      connectionNameInp.value = nice(card.getAttribute('data-plan'));
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'connection',
          item_id: card.getAttribute('data-max') || '1'
        });
      }
      return;
    }

    if(kind==='iptv'){
      if(!vendorInput.value){
        flash(vendorSection);
        vendorSection.scrollIntoView({behavior:'smooth', block:'center'});
        return;
      }
      document.querySelectorAll('[data-kind="iptv"]').forEach(x=>x.classList.remove('active'));
      card.classList.add('active');
      pkgPriceInp.value  = price;
      packageType.value  = 'iptv';
      if (packageIdInput) packageIdInput.value = card.getAttribute('data-package-id') || '';
      setYearlyFlagFromCard(card);
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_item', {
          item_list_id: 'configure',
          items: [{
            item_id: card.getAttribute('data-package-id') || card.getAttribute('data-plan'),
            item_name: card.getAttribute('data-plan') || '',
            item_brand: vendorInput.dataset.label || vendorInput.value,
            item_category: 'iptv',
            price: toNumber(price),
            quantity: 1
          }]
        });
      }
      return;
    }

    if(kind==='reseller'){
      document.querySelectorAll('[data-kind="reseller"]').forEach(x=>x.classList.remove('active'));
      card.classList.add('active');
      pkgPriceInp.value  = price;
      packageType.value  = 'reseller';
      if (packageIdInput) packageIdInput.value = card.getAttribute('data-package-id') || '';
      setYearlyFlagFromCard(card);
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_item', {
          item_list_id: 'configure',
          items: [{
            item_id: card.getAttribute('data-package-id') || card.getAttribute('data-plan'),
            item_name: card.getAttribute('data-plan') || '',
            item_brand: vendorInput.dataset.label || vendorInput.value,
            item_category: 'reseller',
            price: toNumber(price),
            quantity: 1
          }]
        });
      }
      return;
    }
  }

  // ---------- DEFAULT SELECTION HELPER ----------
  function defaultSelectConnection(){
    if (connectionPriceInp.value) return;
    const cards = Array.from(document.querySelectorAll('[data-kind="connection"]'));
    if (!cards.length) return;

    let target = cards.find(card => {
      const planCanon = canon(card.getAttribute('data-plan') || card.textContent || '');
      return planCanon.includes('1device') || planCanon.includes('1connection') || planCanon.includes('1tv');
    });

    if (!target) target = cards[0];

    document.querySelectorAll('[data-kind="connection"]').forEach(x=>x.classList.remove('active'));
    target.classList.add('active');
    connectionPriceInp.value = target.getAttribute('data-price') || '0';
    connectionNameInp.value = nice(target.getAttribute('data-plan'));
    updateSummary();
  }

  // device select
  document.querySelectorAll('[data-device]').forEach(c=>{
    c.addEventListener('click', ()=>{
      clearGroup('[data-device]');
      c.classList.add('active');
      deviceInput.value = c.getAttribute('data-device') || '';
      if (deviceIdInput) deviceIdInput.value = c.getAttribute('data-device-id') || '';
      enableIfReady();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'device',
          item_id: deviceInput.value
        });
      }
    });
  });

  // vendor select (save canonical + label)
  document.querySelectorAll('#vendorSection [data-vendor]').forEach(c=>{
    c.addEventListener('click', ()=>{
      clearGroup('#vendorSection [data-vendor]');
      c.classList.add('active');

      const vendorValue = nice(c.getAttribute('data-vendor'));
      const label = nice(c.getAttribute('data-label') || vendorValue);
      const canonVal = canon(vendorValue);
      vendorInput.value = canonVal;
      vendorInput.dataset.label = label;

      lockConnection(false);
      filterConnectionsByVendor(canonVal);
      filterIptvByVendor(canonVal);
      filterResellerByVendor(canonVal);

      clearGroup('[data-kind="iptv"]');
      clearGroup('[data-kind="reseller"]');
      pkgPriceInp.value  = '';
      packageType.value  = '';
      if (packageIdInput) packageIdInput.value = '';

      if (resellerWrap.classList.contains('hidden')) defaultSelectConnection();
      updateSummary();
      if (typeof window.trackMarketingEvent === 'function') {
        window.trackMarketingEvent('select_content', {
          content_type: 'iptv_provider',
          item_id: canonVal === 'starshare' ? 'filex' : canonVal
        });
      }
    });
  });

  // plan/package select
  document.querySelectorAll('[data-kind]').forEach(c=>{
    c.addEventListener('click', ()=> selectMonetary(c));
  });

  function setPackageTab(tab){
    const buttons = pkgToggle.querySelectorAll('.tg-btn');
    buttons.forEach(b => b.classList.toggle('active', b.getAttribute('data-tab') === tab));

    if(tab === 'iptv'){
      if (packageType.value === 'reseller') {
        clearGroup('[data-kind="reseller"]');
        pkgPriceInp.value = '';
        packageType.value = '';
        packageIdInput.value = '';
      }
      iptvWrap.classList.remove('hidden');
      resellerWrap.classList.add('hidden');
      deviceSection.hidden = false;
      connectionSection.hidden = false;
      filterIptvByVendor(vendorInput.value || '');
      if (vendorInput.value) defaultSelectConnection();
    }else{
      if (packageType.value === 'iptv') {
        clearGroup('[data-kind="iptv"]');
        pkgPriceInp.value = '';
        packageType.value = '';
        packageIdInput.value = '';
      }
      resellerWrap.classList.remove('hidden');
      iptvWrap.classList.add('hidden');
      deviceSection.hidden = true;
      connectionSection.hidden = true;
      clearGroup('[data-device]');
      deviceInput.value = '';
      deviceIdInput.value = '';
      clearGroup('[data-kind="connection"]');
      connectionPriceInp.value = '';
      connectionNameInp.value = '';
      filterResellerByVendor(vendorInput.value || '');
    }
    updateSummary();
    enableIfReady();
  }
  pkgToggle.querySelectorAll('.tg-btn').forEach(btn=>{
    btn.addEventListener('click', ()=> setPackageTab(btn.getAttribute('data-tab')));
  });
  setPackageTab('iptv');

  // ---------- Deep-link support (pricing to configure) ----------
  function applyDeepLinkSelection() {
    const url     = new URL(window.location.href);
    const rawType = url.searchParams.get('ptype') || url.searchParams.get('package_type') || 'iptv';
    const ptype   = rawType === 'package' ? 'iptv' : rawType;
    const priceQ  = url.searchParams.get('price') || url.searchParams.get('pkg_price');
    const planQ   = url.searchParams.get('plan') || url.searchParams.get('plan_name');
    const vendorQ = url.searchParams.get('vendor') || url.searchParams.get('iptv_vendor');
    const packageIdQ = url.searchParams.get('package_id');
    const deviceIdQ = url.searchParams.get('device_id');
    const deviceQ = url.searchParams.get('device');
    const connectionPriceQ = url.searchParams.get('connection_price');
    const connectionNameQ = url.searchParams.get('connection_name');

    if (ptype === 'reseller') {
      setPackageTab('reseller');
    } else {
      setPackageTab('iptv');
    }

    let vendorCan = vendorQ ? canon(vendorQ) : '';
    if (vendorCan === 'filex' || vendorCan === 'starshare') {
      vendorCan = 'starshare';
    }

    if (vendorCan) {
      const vBtn = Array.from(document.querySelectorAll('#vendorSection [data-vendor]'))
        .find(x => canon(x.getAttribute('data-vendor')) === vendorCan);

      if (vBtn) {
        vBtn.click();
      } else {
        vendorInput.value         = vendorCan;
        vendorInput.dataset.label = vendorQ || vendorCan;
        lockConnection(false);
        filterConnectionsByVendor(vendorCan);
        filterIptvByVendor(vendorCan);
        filterResellerByVendor(vendorCan);
      }
    } else {
      filterIptvByVendor('');
      filterResellerByVendor('');
    }

    const allCards = Array.from(
      document.querySelectorAll(ptype ? `[data-kind="${ptype}"]` : '[data-kind]')
    );

    const cards = vendorCan
      ? allCards.filter(x => canon(x.getAttribute('data-vendor')) === vendorCan)
      : allCards;

    let target = null;
    if (packageIdQ) {
      target = cards.find(
        x => String(x.getAttribute('data-package-id') || '') === String(packageIdQ)
      );
    }
    if (!target && priceQ) {
      target = cards.find(
        x => toNumber(x.getAttribute('data-price')) === toNumber(priceQ)
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

    if (!vendorCan && target) {
      vendorCan = canon(target.getAttribute('data-vendor') || '');
      if (vendorCan) {
        const vBtn2 = Array.from(document.querySelectorAll('#vendorSection [data-vendor]'))
          .find(x => canon(x.getAttribute('data-vendor')) === vendorCan);
        if (vBtn2) {
          vBtn2.click();
        } else {
          vendorInput.value         = vendorCan;
          vendorInput.dataset.label = vendorCan;
          lockConnection(false);
          filterConnectionsByVendor(vendorCan);
          filterIptvByVendor(vendorCan);
          filterResellerByVendor(vendorCan);
        }
      }
    }

    if (target) {
      selectMonetary(target);
      if (packageIdQ) setPreselectedMode(true);
    }

    const deviceTarget = Array.from(document.querySelectorAll('[data-device]')).find(card => {
      if (deviceIdQ && String(card.getAttribute('data-device-id')) === String(deviceIdQ)) return true;
      return deviceQ && canon(card.getAttribute('data-device')) === canon(deviceQ);
    });
    if (deviceTarget && ptype !== 'reseller') deviceTarget.click();

    if (connectionPriceQ || connectionNameQ) {
      const connectionTarget = Array.from(document.querySelectorAll('[data-kind="connection"]')).find(card => {
        if (connectionPriceQ && toNumber(card.getAttribute('data-price')) === toNumber(connectionPriceQ)) return true;
        const expectedName = canon(connectionNameQ);
        return expectedName !== '' && canon(card.getAttribute('data-plan')) === expectedName;
      });
      if (connectionTarget && connectionTarget.style.display !== 'none') {
        selectMonetary(connectionTarget);
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(applyDeepLinkSelection, 0), { once: true });
  } else {
    setTimeout(applyDeepLinkSelection, 0);
  }

  // Keep the connection section locked only until a provider is selected.
  updateConnectionVisibility();
  if (vendorInput.value) defaultSelectConnection();

  lockConnection(!vendorInput.value);

  document.getElementById('configForm').addEventListener('submit', function() {
    if (typeof window.trackMarketingEvent === 'function') {
      window.trackMarketingEvent('begin_checkout', {
        currency: currency,
        value: toNumber(planPriceInput.value),
        items: [{
          item_id: packageIdInput.value || planNameInput.value,
          item_name: findActivePlanText(),
          item_brand: vendorInput.dataset.label || vendorInput.value,
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
