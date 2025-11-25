@extends('layouts.default')
@section('title', __('messages.checkout_step_title'))

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* --- page look --- */
.config-wrap{background:#eef2f7}
.config-card{background:#fff;border:1px solid #e6eaf0;border-radius:14px;box-shadow:0 8px 30px rgba(16,24,40,.04)}
.head-num{width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#1d4ed8;color:#fff;font-weight:700;margin-right:10px}
.section-title{font-weight:700;color:#0f172a}
.section-label{font-weight:700;color:#64748b;margin:8px 0 14px}

/* items */
.item-flex{display:flex;flex-wrap:wrap;gap:18px}
.pick{flex:0 0 auto;width:160px;padding:16px;border:1.5px solid #e7eef5;border-radius:14px;background:#fff;text-align:center;cursor:pointer;transition:.2s ease}
.pick:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(16,24,40,.06)}
.pick.active{background:#eff6ff;border-color:#93c5fd;box-shadow:0 10px 28px rgba(37,99,235,.08)}
.pick .ico{font-size:34px;margin-bottom:6px}
.pick small{display:block;color:#475569}

/* lock state for connection section */
.locked .pick{opacity:.55;cursor:not-allowed}
.lock-hint{font-size:13px;color:#64748b}

/* packages grid */
.pkg-grid{display:grid;gap:22px;grid-template-columns:repeat(3,minmax(0,1fr))}
@media (max-width: 992px){ .pkg-grid{grid-template-columns:repeat(2,minmax(0,1fr))} }
@media (max-width: 576px){ .pkg-grid{grid-template-columns:1fr} }

/* package card */
.pick.pkg-card{width:100%}
.pkg-card{position:relative;padding:26px 22px 20px;border-radius:16px;border:1.5px solid #e7eef5;background:#fff}
.pkg-card:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(16,24,40,.06)}
.pkg-card.active{background:#eff6ff;border-color:#93c5fd;box-shadow:0 10px 28px rgba(37,99,235,.08)}
.pkg-badge{position:absolute;top:-20px;left:22px;width:56px;height:56px;border-radius:99px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;box-shadow:0 12px 26px rgba(2,6,23,.10)}
.pkg-title{font-weight:800;font-size:20px;color:#0f172a;margin:6px 0 6px;line-height:1.1}
.pkg-old{color:#94a3b8;text-decoration:line-through;font-weight:700;margin-bottom:2px}
.pkg-new{color:#dc2626;font-weight:800;font-size:20px}
.pkg-unit{color:#111827;font-weight:600}

/* CTA */
.cta{display:inline-flex;align-items:center;gap:10px;border:0;border-radius:10px;padding:12px 18px;font-weight:700;background:#2563eb;color:#fff;text-decoration:none}
.cta[disabled]{opacity:.6;pointer-events:none}

/* toggle */
.toggle-wrap{display:inline-flex;gap:6px;background:#eef2ff;border-radius:12px;padding:6px}
.tg-btn{border:0;background:transparent;padding:8px 14px;border-radius:10px;font-weight:700;color:#254;cursor:pointer}
.tg-btn.active{background:#2563eb;color:#fff;box-shadow:0 6px 14px rgba(37,99,235,.25)}
.hidden{display:none !important}
</style>

<div class="config-wrap py-5">
  <div class="container">

    <div class="text-center mb-4">
      <h2 class="fw-bold">
          {{ __('messages.checkout_step_title') }}
      </h2>
      <p class="text-muted mb-0">
          {{ __('messages.checkout_step_subtitle') }}
      </p>
    </div>

    <form action="{{ route('checkout') }}" method="get" id="configForm">
      {{-- Hidden values sent to checkout --}}
      <input type="hidden" name="device"       id="deviceInput">
      <input type="hidden" name="device_id"    id="deviceIdInput">
      <input type="hidden" name="package_id"   id="packageIdInput">

      {{-- Store canonical vendor for logic + keep label for display in plan_name --}}
      <input type="hidden" name="iptv_vendor"  id="iptvVendorInput" data-label="">

      {{-- Combined summary (what checkout expects) --}}
      <input type="hidden" name="plan_name"    id="planNameInput">
      <input type="hidden" name="plan_price"   id="planPriceInput">

      {{-- Separate picks (with names so they reach checkout step) --}}
      <input type="hidden" name="connection_price" id="connectionPriceInput">
      <input type="hidden" name="pkg_price"        id="pkgPriceInput">
      <input type="hidden" name="package_type"     id="packageTypeInput"> {{-- iptv | reseller --}}

      {{-- 1) Device --}}
      <div class="config-card p-4 mb-4" id="deviceSection">
        <div class="d-flex align-items-center mb-3">
          <div class="head-num">1</div>
          <div class="section-title">{{ __('messages.checkout_device_title') }}</div>
        </div>
        <div class="item-flex">
          @foreach ($devices as $d)
            <div class="pick"
                 data-device="{{ $d->name }}"
                 data-device-id="{{ $d->id }}">
              <div class="ico {{ $d->icon }}"></div>
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
          @php $vendors = $iptvVendors ?? ['Opplex','Starshare']; @endphp
          @foreach ($vendors as $v)
            <div class="pick" data-vendor="{{ $v }}">
              <div class="ico bi bi-broadcast-pin"></div>
              <div>{{ $v }}</div>
              <small>{{ __('messages.checkout_iptv_small') }}</small>
            </div>
          @endforeach
        </div>
        <div class="lock-hint mt-2">
          {{ __('messages.checkout_iptv_lock_hint') }}
        </div>
      </div>

      @php
        // 1 connection plan price/name same as existing data
        $onePlan = null;
        if(isset($plans)){
            foreach($plans as $p){
                if((int)$p->max_devices === 1){
                    $onePlan = $p;
                    break;
                }
            }
            if(!$onePlan){
                $onePlan = $plans[0] ?? null;
            }
        }
        $onePlanName  = $onePlan->name  ?? __('messages.checkout_one_connection_label');
        $onePlanPrice = isset($onePlan) ? number_format($onePlan->price, 2, '.', '') : '0.00';
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
          {{-- 1 connection – always available, price from DB/old config --}}
          <div class="pick"
               data-kind="connection"
               data-max="1"
               data-yearly="0"
               data-plan="{{ $onePlanName }}"
               data-price="{{ $onePlanPrice }}">
            <div class="ico bi bi-hdd-network"></div>
            <div>{{ $onePlanName }}</div>
            <small>{{ __('messages.checkout_one_connection_hint') }}</small>
          </div>

          {{-- 2 connections – yearly only, $69.99 --}}
          <div class="pick"
               data-kind="connection"
               data-max="2"
               data-yearly="1"
               data-plan="{{ __('messages.checkout_two_connection_label') }}"
               data-price="69.99">
            <div class="ico bi bi-hdd-network"></div>
            <div>{{ __('messages.checkout_two_connection_label') }}</div>
            <small>{{ __('messages.checkout_two_connection_hint') }}</small>
          </div>

          {{-- 4 connections – yearly only, $139.99 --}}
          <div class="pick"
               data-kind="connection"
               data-max="4"
               data-yearly="1"
               data-plan="{{ __('messages.checkout_four_connection_label') }}"
               data-price="139.99">
            <div class="ico bi bi-hdd-network"></div>
            <div>{{ __('messages.checkout_four_connection_label') }}</div>
            <small>{{ __('messages.checkout_four_connection_hint') }}</small>
          </div>
        </div>
      </div>

      {{-- 4) Packages + Toggle --}}
      <div class="config-card p-4 mb-5">
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
                   data-unit="{{ strtolower($p['unit']) }}"
                   data-price="{{ number_format($p['price'], 2, '.', '') }}">
                <div class="pkg-badge {{ $p['icon'] }}"></div>
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
                <div class="pkg-badge {{ $p['icon'] }}"></div>
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

      <button type="submit" class="cta" id="continueBtn" disabled>
        <span class="bi bi-lock-fill"></span>
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
  const pkgPriceInp       = document.getElementById('pkgPriceInput');
  const packageType       = document.getElementById('packageTypeInput');
  const btn               = document.getElementById('continueBtn');

  const vendorSection     = document.getElementById('vendorSection');
  const connectionSection = document.getElementById('connectionSection');

  const iptvWrap          = document.getElementById('iptvWrap');
  const resellerWrap      = document.getElementById('resellerWrap');
  const pkgToggle         = document.getElementById('pkgToggle');

  let isYearlyPackage = false;

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

    const parts = [];
    if (vendorLabel && pkgPriceInp.value) parts.push(`${vendorLabel} - ${findActivePlanText()}`);
    else if (pkgPriceInp.value) parts.push(findActivePlanText());

    const total = connPrice + pkgPrice;
    planNameInput.value  = parts.join(' + ');
    planPriceInput.value = total.toFixed(2);

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
      ok = hasDevice && hasPackage;
    } else {
      ok = hasDevice && hasVendor && hasConn && hasPackage;
    }
    btn.disabled = !ok;
  }

  // set yearly flag based on selected package card
  function setYearlyFlagFromCard(card){
    const unit = (card.getAttribute('data-unit') || '').toLowerCase();
    isYearlyPackage =
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
      updateSummary();
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
      return;
    }
  }

  // ---------- DEFAULT SELECTION HELPERS ----------
  function defaultSelectDevice(){
    if (deviceInput.value) return;
    const all = Array.from(document.querySelectorAll('[data-device]'));
    if (!all.length) return;

    let target = all.find(c => canon(c.getAttribute('data-device')).includes('android'));
    if (!target) target = all[0];

    clearGroup('[data-device]');
    target.classList.add('active');
    deviceInput.value = target.getAttribute('data-device') || '';
    if (deviceIdInput) deviceIdInput.value = target.getAttribute('data-device-id') || '';
    enableIfReady();
  }

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
    });
  });

  // vendor select (save canonical + label)
  document.querySelectorAll('#vendorSection [data-vendor]').forEach(c=>{
    c.addEventListener('click', ()=>{
      clearGroup('#vendorSection [data-vendor]');
      c.classList.add('active');

      const label = nice(c.getAttribute('data-vendor'));
      const canonVal = canon(label);
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

      updateSummary();
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
      iptvWrap.classList.remove('hidden');
      resellerWrap.classList.add('hidden');
      filterIptvByVendor(vendorInput.value || '');
    }else{
      resellerWrap.classList.remove('hidden');
      iptvWrap.classList.add('hidden');
      filterResellerByVendor(vendorInput.value || '');
    }
    enableIfReady();
  }
  pkgToggle.querySelectorAll('.tg-btn').forEach(btn=>{
    btn.addEventListener('click', ()=> setPackageTab(btn.getAttribute('data-tab')));
  });
  setPackageTab('iptv');

  // ---------- Deep-link support (pricing → configure) ----------
  (function () {
    const url     = new URL(window.location.href);
    const ptype   = url.searchParams.get('ptype');
    const priceQ  = url.searchParams.get('price');
    const planQ   = url.searchParams.get('plan');
    const vendorQ = url.searchParams.get('vendor');

    if (ptype === 'reseller') {
      setPackageTab('reseller');
    } else {
      setPackageTab('iptv');
    }

    let vendorCan = vendorQ ? canon(vendorQ) : '';

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
    if (priceQ) {
      target = cards.find(
        x => toNumber(x.getAttribute('data-price')) === toNumber(priceQ)
      );
    }
    if (!target && planQ) {
      target = cards.find(
        x => canon(x.getAttribute('data-plan')) === canon(planQ)
      );
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
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  })();

  // DEFAULTS: Android device + 1 connection
  updateConnectionVisibility();
  defaultSelectDevice();
  defaultSelectConnection();

  lockConnection(true);
})();
</script>
@endsection
