@extends('layouts.default')
@section('title', __('messages.checkout_complete_title_page', [], app()->getLocale()) ?? 'Complete Your Order')

@section('content')
    <style>
        .checkout-badges {
            font-weight: 600;
            color: #059669
        }

        .checkout-badges i {
            margin-right: .35rem
        }

        .hero-title {
            font-weight: 700;
            letter-spacing: .2px
        }

        .hero-sub {
            color: #6b7280
        }

        .notice {
            border: 1px solid #e5e7eb;
            border-left: 4px solid #60a5fa;
            background: #f8fafc;
            color: #334155
        }

        .card-soft {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .02)
        }

        .required:after {
            content: " *";
            color: #ef4444
        }

        .order-box {
            border: 1.5px solid #dbeafe;
            border-radius: .75rem;
            background: #f8fbff
        }

        .order-line {
            display: flex;
            justify-content: space-between;
            color: #374151
        }

        .order-meta {
            font-size: .85rem;
            color: #6b7280;
            margin-top: .35rem
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            border-top: 1px dashed #e5e7eb;
            margin-top: .5rem;
            padding-top: .5rem;
            font-weight: 700;
            font-size: 1.25rem
        }

        .pay-option {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            padding: .5rem .75rem;
            display: flex
        }

        .pay-option.active {
            border-color: #bfdbfe;
            background: #eff6ff
        }

        .place-order {
            background: #2563eb;
            border: 0;
            font-weight: 600;
            padding: .85rem 1rem;
            width: 100%
        }

        .place-order:hover {
            background: #1d4ed8
        }

        .small-note {
            font-size: .85rem;
            color: #6b7280
        }
    </style>

    <div class="container pt-3">
        <h1 class="hero-title mb-2">{{ __('messages.checkout_complete_title_page', [], app()->getLocale()) ?? 'Complete Your Order' }}</h1>
        <p class="hero-sub mb-4">{{ __('messages.checkout_complete_subtitle') ?? __('messages.checkout_step_subtitle') }}</p>
    </div>

    @php
        // ------------------------------
        // Gather selections from request
        // ------------------------------
        $selectedDevice = $device ?? request('device');
        $selectedDeviceId = $device_id ?? request('device_id');
        $selectedPackageId = $package_id ?? request('package_id');
        $selectedVendor = $iptv_vendor ?? request('iptv_vendor');
        $selectedPlanName = $plan_name ?? request('plan_name');

        // Optional component prices coming from configure page
        $connectionPriceParam = request('connection_price');
        $pkgPriceParam = request('pkg_price');

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
        $ppNum = $cleanNumber($pkgPriceParam);

        // ------------------------------
        // Special rule:
        // 2 connections -> Connection & Subscription both 69.99
        // 4 connections -> Connection & Subscription both 139.99
        // (detect via connection_price)
        // ------------------------------
        $match = function ($val, $target) {
            if (is_null($val)) {
                return false;
            }
            return abs($val - $target) < 0.01;
        };

        if ($match($cpNum, 69.99)) {
            $cpNum = 69.99;
            $ppNum = 69.99;
        } elseif ($match($cpNum, 139.99)) {
            $cpNum = 139.99;
            $ppNum = 139.99;
        }

        // ------------------------------
        // DB package price only as fallback
        // ------------------------------
        $planPriceDb = null;
        try {
            if (!empty($selectedPackageId)) {
                $pkg = \App\Models\Package::find($selectedPackageId);
                if ($pkg && isset($pkg->price_amount)) {
                    $planPriceDb = (float) $pkg->price_amount;
                    if (empty($selectedPlanName)) {
                        $selectedPlanName = $pkg->title ?? $selectedPlanName;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // ------------------------------
        // Billing logic:
        // Total / Subtotal = sirf SUBSCRIPTION price
        // Connection price sirf show hoga
        // ------------------------------
        if (!is_null($ppNum)) {
            $planPrice = $ppNum; // charge only subscription
        } elseif (!is_null(request('plan_price'))) {
            $planPrice = $cleanNumber(request('plan_price')) ?? 0.0;
        } elseif (!is_null($planPriceDb)) {
            $planPrice = $planPriceDb;
        } else {
            $planPrice = 0.0;
        }

        // Package type normalization
        $selectedTypeRaw = $package_type ?? request('package_type'); // 'iptv'/'package' or 'reseller'
        $selectedType = old('package_type', $selectedTypeRaw);
        if ($selectedType === 'iptv') {
            $selectedType = 'package'; // DB enum: package | reseller
        }
        $typeLabel =
            $selectedType === 'reseller' ? __('messages.checkout_type_reseller') : __('messages.checkout_type_iptv');

        // Totals (based only on planPrice)
        $qty = 1;
        $subtotal = $planPrice * $qty;
        $total = $subtotal;

        // Carry values forward to step2 (safe defaults)
        $carryConn = number_format((float) ($cpNum ?? 0), 2, '.', '');
        $carryPkg = number_format((float) ($ppNum ?? 0), 2, '.', '');
    @endphp

    <div class="container text-center mt-3">
        <div class="checkout-badges d-inline-flex flex-wrap align-items-center">
            <div class="mr-3 d-flex align-items-center">
                <i class="fa fa-shield"></i> {{ __('messages.checkout_badge_secure') }}
            </div>
            <span class="text-secondary mr-3">•</span>
            <div class="mr-3 d-flex align-items-center">
                <i class="fa fa-check-circle"></i> {{ __('messages.checkout_badge_safe_info') }}
            </div>
            <span class="text-secondary mr-3">•</span>
            <div class="d-flex align-items-center">
                <i class="fa fa-lock"></i> {{ __('messages.checkout_badge_encryption') }}
            </div>
        </div>
    </div>

    <div class="container text-center mt-3">
        <h2 class="hero-title">{{ __('messages.checkout_complete_title') }}</h2>
        <div class="hero-sub">{{ __('messages.checkout_complete_sub') }}</div>
    </div>

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
                        <input type="hidden" name="pkg_price" value="{{ $carryPkg }}">
                        <input type="hidden" name="quantity" value="{{ $qty }}">

                        <div class="form-group">
                            <label class="required">{{ __('messages.checkout_package_type') }}</label>
                            <select name="package_type" class="form-control">
                                <option value="package" {{ $selectedType === 'package' ? 'selected' : '' }}>
                                    {{ __('messages.checkout_package_type_iptv') }}
                                </option>
                                <option value="reseller" {{ $selectedType === 'reseller' ? 'selected' : '' }}>
                                    {{ __('messages.checkout_package_type_reseller') }}
                                </option>
                            </select>
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

                        <div class="d-block d-lg-none mt-3">
                            <button type="submit" class="btn btn-primary place-order">
                                {{ __('messages.checkout_place_order_btn') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-5">
                <div class="card-soft p-4 mb-3">
                    <h5 class="mb-3">{{ __('messages.checkout_your_order') }}</h5>
                    <div class="order-box p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="font-weight-bold">
                                    {{ $selectedPlanName ?: __('messages.checkout_selected_package_fallback') }} ×
                                    {{ $qty }}
                                </div>

                                <div class="order-meta">
                                    @if ($selectedVendor)
                                        <div>
                                            {{ __('messages.checkout_provider') }}:
                                            <strong>{{ ucfirst($selectedVendor) }}</strong>
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
                                </div>

                                <a class="small mt-1 d-inline-block" href="{{ route('configure', request()->query()) }}">
                                    {{ __('messages.checkout_edit_options') }}
                                </a>
                            </div>
                            <div class="font-weight-bold">
                                ${{ number_format($planPrice, 2) }}
                            </div>
                        </div>

                        @if (!is_null($ppNum) && $ppNum > 0)
                            <div class="order-line">
                                <span>{{ __('messages.checkout_subscription_label') }}</span>
                                <span>${{ number_format($ppNum, 2) }}</span>
                            </div>
                        @endif

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

                    <button type="submit" form="checkoutForm" class="btn btn-primary place-order">
                        {{ __('messages.checkout_place_order_btn') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        Array.prototype.slice.call(document.querySelectorAll('input[name="paymethod"]'))
            .forEach(function(r) {
                r.addEventListener('change', function() {
                    document.querySelectorAll('.pay-option').forEach(function(c) {
                        c.classList.remove('active');
                    });
                    r.closest('.pay-option').classList.add('active');
                });
            });
    </script>
@endsection
