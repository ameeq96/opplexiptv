@extends('layouts.default')
@section('title', __('messages.checkout_complete_title_page'))

@section('content')
<div class="checkout-step1-page">
@php
        // ------------------------------
        // Gather selections from request
        // ------------------------------
        $selectedDevice = old('device', $device ?? request('device'));
        $selectedDeviceId = old('device_id', $device_id ?? request('device_id'));
        $selectedPackageId = old('package_id', $package_id ?? request('package_id'));
        $selectedVendor = old('iptv_vendor', $iptv_vendor ?? request('iptv_vendor'));
        $selectedPlanName = old('plan_name', $plan_name ?? request('plan_name'));
        $selectedConnectionName = old('connection_name', request('connection_name'));

        // Optional component prices coming from configure page
        $connectionPriceParam = old('connection_price', request('connection_price'));

        // Clean numeric helpers
        $cleanNumber = function ($v) {
            if (is_null($v)) {
                return null;
            }
            if (is_numeric($v)) {
                return (float) $v;
            }
            $s = preg_replace('/[^0-9.]/', '', (string) $v);
            return $s === '' ? null : (float) $s;
        };

        $cpNum = $cleanNumber($connectionPriceParam);

        $match = function ($val, $target) {
            if (is_null($val)) {
                return false;
            }
            return abs($val - $target) < 0.01;
        };

        // Load the selected package so the visible checkout price matches the database.
        $planPriceDb = null;
        $selectedPackage = null;
        try {
            if (!empty($selectedPackageId)) {
                $selectedPackage = \App\Models\Package::query()
                    ->where('active', true)
                    ->whereIn('type', ['iptv', 'reseller'])
                    ->whereIn('vendor', ['opplex', 'starshare'])
                    ->with('translations')
                    ->find($selectedPackageId);
                if ($selectedPackage && isset($selectedPackage->price_amount)) {
                    $planPriceDb = (float) $selectedPackage->price_amount;
                    $selectedVendor = strtolower((string) $selectedPackage->vendor);

                    if ($selectedPackage->type === 'iptv') {
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
                    } else {
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
                    }

                    $selectedPlanName = ($selectedVendor === 'starshare' ? 'Filex' : 'Opplex') . ' - ' . $packageTitle;
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
        $typeLabel =
            $selectedType === 'reseller' ? __('messages.checkout_type_reseller') : __('messages.checkout_type_iptv');

        $filexYearlyConnectionPrices = \App\Models\Package::FILEX_YEARLY_CONNECTION_PRICES;
        $allowsMultiConnection = $selectedPackage
            && $selectedType === 'package'
            && $selectedVendor === 'starshare'
            && (int) $selectedPackage->duration_months === 12;
        $isTwoConnections = $allowsMultiConnection && $match($cpNum, $filexYearlyConnectionPrices[2]);
        $isFourConnections = $allowsMultiConnection && $match($cpNum, $filexYearlyConnectionPrices[4]);

        if ($isTwoConnections) {
            $planPrice = $filexYearlyConnectionPrices[2];
            $selectedConnectionName = __('messages.checkout_two_connection_label');
        } elseif ($isFourConnections) {
            $planPrice = $filexYearlyConnectionPrices[4];
            $selectedConnectionName = __('messages.checkout_four_connection_label');
        } else {
            $cpNum = 0.0;
            $planPrice = $planPriceDb ?? 0.0;
            $selectedConnectionName = __('messages.checkout_one_connection_label');
        }
        // Totals (based only on planPrice)
        $qty = 1;
        $subtotal = $planPrice * $qty;
        $total = $subtotal;

        // Carry values forward to step2 (safe defaults)
        $carryConn = number_format((float) ($cpNum ?? 0), 2, '.', '');
        $carryPkg = number_format((float) $planPrice, 2, '.', '');
        $providerLabel = strtolower((string) $selectedVendor) === 'starshare'
            ? 'Filex'
            : ucfirst((string) $selectedVendor);
        $editOptions = [
            'package_id' => $selectedPackageId,
            'vendor' => $selectedVendor,
            'ptype' => $selectedType === 'reseller' ? 'reseller' : 'iptv',
            'price' => $planPriceDb,
            'plan' => $selectedPlanName,
            'device' => $selectedDevice,
            'device_id' => $selectedDeviceId,
            'connection_price' => $carryConn,
            'connection_name' => $selectedConnectionName,
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

    <div class="container text-center mt-3">
        <div class="checkout-badges d-inline-flex flex-wrap align-items-center">
            <div class="mr-3 d-flex align-items-center">
                <i class="fa fa-shield"></i> {{ __('messages.checkout_badge_secure') }}
            </div>
            <span class="text-secondary mr-3">&bull;</span>
            <div class="mr-3 d-flex align-items-center">
                <i class="fa fa-check-circle"></i> {{ __('messages.checkout_badge_safe_info') }}
            </div>
            <span class="text-secondary mr-3">&bull;</span>
            <div class="d-flex align-items-center">
                <i class="fa fa-lock"></i> {{ __('messages.checkout_badge_encryption') }}
            </div>
        </div>
    </div>

    <div class="container text-center mt-3">
        <h1 class="hero-title change-font">{{ __('messages.checkout_complete_title') }}</h1>
        <h2 class="hero-sub change-font">{{ __('messages.checkout_complete_sub') }}</h2>
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

    <div class="container my-4">
        <div class="row">
            {{-- Billing --}}
            <div class="col-lg-7 mb-4">
                <div class="card-soft p-4">
                    <h5 class="mb-3">{{ __('messages.checkout_billing_details') }}</h5>

                    <form action="{{ route('step2') }}" method="post" id="checkoutForm">
                        @csrf

                        {{-- carry over selected config values --}}
                        <input type="hidden" name="device" value="{{ $selectedDevice }}">
                        <input type="hidden" name="device_id" value="{{ $selectedDeviceId }}">
                        <input type="hidden" name="package_id" value="{{ $selectedPackageId }}">
                        <input type="hidden" name="iptv_vendor" value="{{ $selectedVendor }}">
                        <input type="hidden" name="plan_name" value="{{ $selectedPlanName }}">
                        <input type="hidden" name="plan_price" value="{{ number_format($planPrice, 2, '.', '') }}">
                        <input type="hidden" name="connection_price" value="{{ $carryConn }}">
                        <input type="hidden" name="connection_name" value="{{ $selectedConnectionName }}">
                        <input type="hidden" name="pkg_price" value="{{ $carryPkg }}">
                        <input type="hidden" name="quantity" value="{{ $qty }}">
                        <input type="hidden" name="package_type" value="{{ $selectedType }}">
                        <input type="hidden" name="checkout_draft_token" id="checkoutDraftToken"
                            value="{{ old('checkout_draft_token', (string) \Illuminate\Support\Str::uuid()) }}">

                        <div class="form-group">
                            <label class="required">{{ __('messages.checkout_package_type') }}</label>
                            <div class="form-control bg-light" aria-readonly="true">{{ $typeLabel }}</div>
                        </div>

                        <div class="form-group">
                            <label class="required">{{ __('messages.checkout_email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="required">{{ __('messages.checkout_first_name') }}</label>
                                <input type="text" name="first_name" class="form-control"
                                    value="{{ old('first_name') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="required">{{ __('messages.checkout_last_name') }}</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required">{{ __('messages.checkout_phone') }}</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                value="{{ old('phone') }}">
                            <small id="phone-client-error" class="text-danger d-none"></small>
                            <small class="text-muted d-block mt-1">{{ __('messages.checkout_phone_hint') }}</small>
                        </div>

                        <div class="form-group">
                            <label>{{ __('messages.checkout_notes_label') }}</label>
                            <textarea rows="4" name="notes" class="form-control"
                                placeholder="{{ __('messages.checkout_notes_placeholder') }}">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-group">
                            <input type="text" name="captcha" class="form-control"
                                placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                required aria-invalid="@error('captcha') true @else false @enderror"
                                aria-describedby="@error('captcha') checkout-captcha-error @enderror">
                            @error('captcha')
                                <small id="checkout-captcha-error" class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-5 checkout-order-column">
                <div class="card-soft p-4 mb-3">
                    <h5 class="mb-3">{{ __('messages.checkout_your_order') }}</h5>
                    <div class="order-box p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="font-weight-bold">
                                    {{ $selectedPlanName ?: __('messages.checkout_selected_package_fallback') }} &times;
                                    {{ $qty }}
                                </div>

                                <div class="order-meta">
                                    @if ($selectedVendor)
                                        <div>
                                            {{ __('messages.checkout_provider') }}:
                                            <strong>{{ $providerLabel }}</strong>
                                        </div>
                                    @endif
                                    @if ($selectedDevice)
                                        <div>
                                            {{ __('messages.checkout_device') }}:
                                            <strong>{{ $selectedDevice }}</strong>
                                        </div>
                                    @endif
                                    @if ($selectedType)
                                        <div>
                                            {{ __('messages.checkout_type') }}:
                                            <strong>{{ $typeLabel }}</strong>
                                        </div>
                                    @endif
                                    @if ($selectedType === 'package' && $selectedConnectionName)
                                        <div>
                                            {{ __('messages.checkout_connection_title') }}:
                                            <strong>{{ $selectedConnectionName }}</strong>
                                        </div>
                                    @endif
                                </div>

                                <a class="small mt-1 d-inline-block" href="{{ route('configure', $editOptions) }}">
                                    {{ __('messages.checkout_edit_options') }}
                                </a>
                            </div>
                            <div class="font-weight-bold">
                                ${{ number_format($planPrice, 2) }}
                            </div>
                        </div>

                        <div class="order-line mt-2">
                            <span>{{ __('messages.checkout_subtotal_label') }}</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="order-total">
                            <span>{{ __('messages.checkout_total_label') }}</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-soft p-4">
                    <div class="checkout-assurances">
                        <span><i class="fa fa-refresh" aria-hidden="true"></i>{{ __('messages.page_faq.packages.a5') }}</span>
                        <span><i class="fa fa-clock-o" aria-hidden="true"></i>{{ __('messages.thankyou_page.delivery_text') }}</span>
                        <span><i class="fa fa-life-ring" aria-hidden="true"></i>{{ __('messages.thankyou_page.support_text') }}</span>
                    </div>

                    <div class="pay-option active mb-3">
                        <input class="mr-2 mt-1" type="radio" name="paymethod" id="pm1" value="card"
                            form="checkoutForm" checked>
                        <label class="w-100" for="pm1">
                            <div class="font-weight-bold">
                                {{ __('messages.checkout_pay_card_title') }}
                            </div>
                            <div class="small-note">
                                {{ __('messages.checkout_pay_card_desc') }}
                            </div>
                        </label>
                    </div>

                    <div class="pay-option mb-3">
                        <input class="mr-2 mt-1" type="radio" name="paymethod" id="pm2" value="crypto"
                            form="checkoutForm">
                        <label class="w-100" for="pm2">
                            <div class="font-weight-bold">
                                {{ __('messages.checkout_pay_crypto_title') }}
                            </div>
                            <div class="small-note">
                                {{ __('messages.checkout_pay_crypto_desc') }}
                            </div>
                        </label>
                    </div>

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

                    <button type="submit" form="checkoutForm" class="btn btn-primary place-order">
                        {{ __('messages.checkout_place_order_btn') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const checkoutValue = @json((float) $total);
        const checkoutCurrency = @json(config('services.app.default_currency', 'USD'));
        const checkoutItem = @json($checkoutTrackingItem);
        const checkoutDraftUrl = @json(route('checkout.draft'));

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

        document.getElementById('checkoutForm').addEventListener('submit', function() {
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
                payload.append('connection_name', value('connection_name'));
                payload.append('connection_price', value('connection_price'));
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
