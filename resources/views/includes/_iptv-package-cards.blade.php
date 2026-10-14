@php
    $displayPackages = collect($displayPackages ?? $packages ?? [])->values()->all();
    $documentPricing = $documentPricing ?? [];
    $isDocumentEnglishPricing = $isDocumentEnglishPricing ?? false;
    $hasCatalogPlans = $hasCatalogPlans ?? collect($displayPackages)
        ->contains(static fn ($package) => !data_get($package, 'is_duration_plan', false));
    $catalogServiceName = $catalogServiceName ?? static function ($package): string {
        $stableService = trim((string) data_get($package, 'service', ''));
        if ($stableService !== '') {
            return $stableService;
        }

        if (data_get($package, 'is_duration_plan', false)) {
            return strtolower((string) data_get($package, 'vendor')) === 'starshare'
                ? 'Filex'
                : 'Opplex';
        }

        $title = trim((string) data_get($package, 'title', ''));

        return trim((string) preg_replace(
            '/\s*-\s*(?:3\s*Months?|Half\s*Yearly|Yearly|Monthly|1\s*Month)\s*$/iu',
            '',
            $title
        ));
    };
    $initialIptvService = $initialIptvService ?? collect($displayPackages)
        ->map($catalogServiceName)
        ->filter()
        ->first();
    $monthlyPricesByService = $monthlyPricesByService ?? collect($displayPackages)
        ->filter(static fn ($package) => (int) data_get($package, 'duration_months', 1) === 1)
        ->mapWithKeys(static fn ($package) => [
            $catalogServiceName($package) => (float) data_get($package, 'price_amount', 0),
        ]);
    $activeEventPromotion = $activeEventPromotion
        ?? app(\App\Services\EventPromotionService::class)->activeForSession();
@endphp

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
        $packageId = (int) data_get($package, 'id', 0);
        $purchaseUrl = $packageId > 0
            ? URL::temporarySignedRoute('packages.purchase', now()->addMinutes(15), ['package' => $packageId])
            : route('configure', [
                'price' => $buyPrice,
                'ptype' => 'iptv',
                'plan' => $fullPlanTitle,
                'vendor' => $vendorKey,
            ]);
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
                            href="{{ $purchaseUrl }}"
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
