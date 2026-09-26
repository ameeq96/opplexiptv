<?php

namespace App\Http\Controllers;

use App\Mail\CheckoutOrderMail;
use App\Http\Requests\Site\{BuyNowRequest, ContactRequest, SubscribeRequest};
use App\Models\Admin;
use App\Models\CheckoutDraft;
use App\Models\Device;
use App\Models\Digital\DigitalProduct;
use App\Models\Order;
use App\Models\MarketingDelivery;
use App\Models\Package;
use App\Models\Referral;
use App\Models\ShopProduct;
use App\Services\{TmdbService, ImageService, LocaleService, ContactService, CaptchaService, EventPromotionService};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use Illuminate\Support\Str;
use App\Notifications\NewOrderNotification;
use App\Services\UnifiedProductService;
use App\Services\Clients\CustomerIdentityService;

class HomeController extends Controller
{
    public function __construct(
        private TmdbService $tmdb,
        private ImageService $images,
        private LocaleService $locale,
        private ContactService $contact,
        private CaptchaService $captcha,
        private UnifiedProductService $unifiedProducts,
        private CustomerIdentityService $customerIdentity,
        private EventPromotionService $eventPromotions,
    ) {}

    public function home()
    {
        $allProducts = $this->unifiedProducts->frontendProducts();

        $homeProducts = $allProducts
            ->where('type', 'digital')
            ->values();
        $homeAffiliateProducts = $allProducts
            ->where('type', 'affiliate')
            ->values();

        return view('pages.home', [
            'homeProducts' => $homeProducts,
            'homeAffiliateProducts' => $homeAffiliateProducts,
            'isRtl'        => $this->locale->isRtl(),
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function pricing()
    {   
        return view('pages.pricing');
    }

    public function movies()
    {
        return view('pages.movies');
    }

    public function movieTrailer(string $mediaType, int $id)
    {
        abort_unless(in_array($mediaType, ['movie', 'tv'], true), 404);

        $url = $this->tmdb->trailerUrl($id, $mediaType);
        $response = response()->json(['url' => $url], $url ? 200 : 404);

        return $response->header('Cache-Control', 'public, max-age=86400');
    }

    public function getTrending(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $payload = $this->tmdb->trending('all', 'day', $page);

        $movies = collect($payload['results'] ?? [])
            ->filter(static fn (array $movie) => !empty($movie['backdrop_path']))
            ->take(10)
            ->map(function (array $movie) {
                $imageUrl = $this->images->tmdbImage($movie['backdrop_path'], 'w780');

                return [
                    'id' => $movie['id'] ?? null,
                    'media_type' => $movie['media_type'] ?? null,
                    'safe_title' => $movie['title'] ?? $movie['name'] ?? __('interface.movies.featured_title'),
                    'safe_overview' => isset($movie['overview'])
                        ? Str::limit((string) $movie['overview'], 150)
                        : __('interface.movies.no_overview'),
                    'webp_image_url' => $this->images->toWebp($imageUrl, 960, 540, 70),
                ];
            })
            ->values();

        return response()->json([
            'results' => $movies,
            'page' => (int) ($payload['page'] ?? $page),
            'total_pages' => (int) ($payload['total_pages'] ?? 1),
        ]);
    }

    public function packages()
    {
        return view('pages.packages');
    }

    public function iptvSubscriptionService()
    {
        return view('pages.iptv-subscription-service', ['activeIndex' => 0]);
    }

    public function resellerPanel()
    {
        return view('pages.resellerpanel');
    }

    public function buynow()
    {
        return view('pages.buynow');
    }

    public function buynowpanel()
    {
        return view('pages.buynowpanel');
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function shop()
    {
        $usesDocumentLayout = in_array(app()->getLocale(), config('app.locales', ['en']), true);
        $type = $usesDocumentLayout ? 'all' : 'affiliate';
        $allProducts = $this->unifiedProducts->frontendProducts();

        if ($usesDocumentLayout) {
            $documentProductPriority = [
                'affiliate:B08CRV62C4' => 0,
                'affiliate:B0BP9SNVH9' => 1,
                'affiliate:B0DXXYS4BJ' => 2,
                'affiliate:B00SFSU53G' => 3,
                'digital:netflix' => 4,
                'digital:prime-video' => 5,
                'digital:hbo-max-premium' => 6,
                'digital:nordvpn' => 7,
            ];
            $documentProductKey = static function (array $product): string {
                $type = strtolower((string) data_get($product, 'type', 'affiliate'));
                $identifier = (string) data_get(
                    $product,
                    'identifier',
                    $type === 'digital'
                        ? data_get($product, 'slug', '')
                        : data_get($product, 'asin', '')
                );

                return $type . ':' . $identifier;
            };

            $documentProducts = $allProducts
                ->filter(static fn (array $product) => isset($documentProductPriority[$documentProductKey($product)]))
                ->sortBy(static fn (array $product) => $documentProductPriority[$documentProductKey($product)])
                ->values();
            $remainingProducts = $allProducts
                ->reject(static fn (array $product) => isset($documentProductPriority[$documentProductKey($product)]))
                ->values();
            $products = $documentProducts->concat($remainingProducts)->values();
        } else {
            $products = $allProducts->where('type', 'affiliate')->values();
        }

        $isRtl = $this->locale->isRtl();

        // --- PAGINATION (array-based) ---
        $page = Paginator::resolveCurrentPage('page');
        $perPage = 8;

        $collection = collect($products);
        $items = $collection->forPage($page, $perPage)->values();

        $paginatedProducts = new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('pages.shop', [
            'products' => $paginatedProducts,
            'selectedType' => $type,
            'isRtl'    => $isRtl,
        ]);
    }

    public function sharedProduct(string $type, int $id)
    {
        $type = strtolower($type);

        if (!in_array($type, ['digital', 'affiliate'], true)) {
            abort(404);
        }

        $shareLandingUrl = route('products.share', ['type' => $type, 'id' => $id]);

        if ($type === 'digital') {
            $product = DigitalProduct::query()
                ->where('id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            $name = $product->title;
            $image = $product->image ? asset('images/digital-products/' . $product->image) : asset('images/placeholder.webp');
            $price = (string) $product->currency . ' ' . number_format((float) $product->price, 2);
            $actionUrl = 'https://wa.me/' . config('services.whatsapp.number') . '?text=' . rawurlencode(
                __('interface.product.digital_purchase_message', [
                    'name' => $name,
                    'price' => $price,
                    'url' => $shareLandingUrl,
                ])
            );
            $actionLabel = __('interface.product.buy');
            $description = __('interface.product.digital_description', [
                'name' => $name,
                'price' => $price,
            ]);
            $badge = __('interface.product.digital_badge');
        } else {
            $product = ShopProduct::query()
                ->with('translations')
                ->where('id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            $name = $product->translation()?->name ?: $product->name;
            $image = $product->image ? asset('images/shop/' . $product->image) : asset('images/placeholder.webp');
            $price = null;
            $actionUrl = 'https://wa.me/' . config('services.whatsapp.number') . '?text=' . rawurlencode(
                __('interface.product.affiliate_purchase_message', [
                    'name' => $name,
                    'url' => $shareLandingUrl,
                ])
            );
            $actionLabel = __('interface.product.buy');
            $description = __('interface.product.affiliate_description', ['name' => $name]);
            $badge = __('interface.product.affiliate_badge');
        }

        return view('pages.products.share', [
            'productName' => $name,
            'productImage' => $image,
            'productPrice' => $price,
            'productType' => $type,
            'productTypeLabel' => $badge,
            'productDescription' => $description,
            'productActionUrl' => $actionUrl,
            'productActionLabel' => $actionLabel,
            'isRtl' => $this->locale->isRtl(),
            'pageMetaTitle' => $name . ' | Opplex IPTV',
            'pageMetaDescription' => $description,
            'pageMetaImage' => $image,
            'pageCanonical' => $shareLandingUrl,
            'pageOgTitle' => $name . ' | Opplex IPTV',
            'pageOgDescription' => $description,
        ]);
    }

    public function activate()
    {
        return view('pages.activate');
    }

    public function activateInfo()
    {
        return view('pages.activate-info');
    }

    public function iptvApplications()
    {
        return view('pages.iptvapplications');
    }

    public function redirect(Request $request)
    {
        $target = (string) $request->query('target', '');

        if (empty($target)) {
            abort(404, 'Download link missing.');
        }

        return view('pages.redirect', [
            'isRtl'  => $this->locale->isRtl(),
            'target' => $target,
            'adUrl'  => 'https://handhighlight.com/sgtebuerf8?key=6085cca57bba1090342bc3bcbd3ee779',
        ]);
    }

    public function send(ContactRequest $request)
    {
        if (!$this->captcha->check($request->captcha)) {
            return back()->with('error', __('document_ui.contact.captcha_error'));
        }
        $this->contact->contact($request->only('username', 'email', 'phone', 'message'));
        return back()->with('success', __('document_ui.contact.message_sent'));
    }

    public function sendBuynow(BuyNowRequest $request)
    {
        if (!$this->captcha->check($request->captcha)) {
            return back()->with('error', __('document_ui.contact.captcha_error'));
        }
        $this->contact->buyNow($request->only('username', 'email', 'package', 'phone', 'message'));
        return back()->with('success', __('document_ui.contact.message_sent'));
    }

    public function postBuyNowPanel(BuyNowRequest $request)
    {
        if (!$this->captcha->check($request->captcha)) {
            return back()->with('error', __('document_ui.contact.captcha_error'));
        }
        $this->contact->buyNow($request->only('username', 'email', 'package', 'phone', 'message'));
        return back()->with('success', __('document_ui.contact.message_sent'));
    }

    public function subscribe(SubscribeRequest $request)
    {
        $this->contact->subscribe($request->email);
        return back()->with('success', __('document_ui.contact.subscription_success'));
    }

    public function configure(Request $request)
    {
        // Devices
        $devices = Device::query()
            ->orderBy('name')
            ->get(['id', 'name', 'icon']);

        // IPTV packages (type = iptv, vendor = opplex|starshare)
        $iptvRows = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->where('type', 'iptv')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->orderByRaw("CASE vendor WHEN 'opplex' THEN 0 WHEN 'starshare' THEN 1 ELSE 2 END")
            ->orderByRaw("COALESCE(sort_order, duration_months, id)")
            ->with('translations')
            ->get(['id', 'type', 'vendor', 'title', 'price_amount', 'duration_months', 'icon']);

        $iptvPackages = [];
        foreach ($iptvRows as $r) {
            $dur = (int) ($r->duration_months ?: 1);

            $iptvPackages[] = [
                'id'     => $r->id,                           // <--- ID SEND HO RAHA
                'vendor' => strtolower($r->vendor),           // opplex | starshare
                'service' => $this->packageServiceName($r),
                'title'  => $this->packageDisplayTitle($r),
                'old'    => 0.00,
                'price'  => (float) $r->price_amount,
                'duration_months' => $dur,
                'unit'   => $dur === 1
                    ? __('interface.checkout.month_one')
                    : __('interface.checkout.months', ['count' => $dur]),
                'icon'   => $r->icon ?: 'bi-router',
            ];
        }

        // Reseller packages (type = reseller, vendor = opplex|starshare)
        $resellerRows = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->where('type', 'reseller')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->orderByRaw("CASE vendor WHEN 'opplex' THEN 0 WHEN 'starshare' THEN 1 ELSE 2 END")
            ->orderByRaw('COALESCE(sort_order, credits, id)')
            ->with('translations')
            ->get(['id', 'type', 'vendor', 'title', 'price_amount', 'credits', 'icon']);

        $resellerPackages = [];
        foreach ($resellerRows as $r) {
            $credits = (int) ($r->credits ?: 0);

            $resellerPackages[] = [
                'id'     => $r->id,                           // <--- ID SEND HO RAHA
                'vendor' => strtolower($r->vendor),
                'title'  => $this->packageDisplayTitle($r),
                'old'    => 0.00,
                'price'  => (float) $r->price_amount,
                'unit'   => $credits > 0
                    ? __('interface.checkout.credits', ['count' => $credits])
                    : __('interface.checkout.credits_label'),
                'icon'   => $r->icon ?: 'bi-router',
            ];
        }

        $prePrice    = $request->query('price');
        $iptvVendors = ['Opplex', 'starshare'];
        $eventPromotion = $this->eventPromotions->activeForSession();

        return view('pages.checkout.configure', compact(
            'devices',
            'iptvPackages',
            'resellerPackages',
            'prePrice',
            'iptvVendors',
            'eventPromotion'
        ));
    }

    public function checkoutStep2(Request $request)
    {
        $data = $request->validate(
            [
                'device'       => 'nullable|string',
                'device_id'    => 'nullable|integer|exists:devices,id',
                'iptv_vendor'  => 'nullable|string',
                'plan_name'    => 'required|string',
                'plan_price'   => 'required|numeric|min:0',
                // ENUM: package | reseller
                'package_type' => 'required|in:package,reseller',

                'package_id'   => 'required|integer|exists:packages,id',
                'quantity'     => 'required|integer|in:1',

                'email'        => 'required|email',
                'first_name'   => 'required|string|max:255',
                'last_name'    => 'required|string|max:255',
                'phone'        => 'required|string|max:50',
                'notes'        => 'nullable|string',
                'captcha'      => 'required',
                'coupon'       => 'nullable|string',
                'paymethod'    => 'required|in:card,crypto',
                'policy_accepted' => 'required|accepted',
                'checkout_draft_token' => 'required|uuid',
                'marketing_email' => 'nullable|boolean',
                'marketing_whatsapp' => 'nullable|boolean',
                'marketing_ads' => 'nullable|boolean',
            ],
            [
                '*.required' => __('document_ui.validation.required'),
                'email.email' => __('document_ui.validation.email'),
                '*.string' => __('document_ui.validation.string'),
                '*.max' => __('document_ui.validation.max'),
                '*.numeric' => __('document_ui.validation.numeric'),
                '*.integer' => __('document_ui.validation.integer'),
                '*.min' => __('document_ui.validation.min_numeric'),
                '*.in' => __('document_ui.validation.invalid_selection'),
                '*.exists' => __('document_ui.validation.invalid_selection'),
                'policy_accepted.accepted' => __('messages.final_sale_confirmed'),
            ],
            [
                'device' => __('messages.checkout_device'),
                'device_id' => __('messages.checkout_device'),
                'iptv_vendor' => __('messages.checkout_provider'),
                'plan_name' => __('messages.checkout_selected_package_fallback'),
                'plan_price' => __('messages.checkout_subscription_label'),
                'package_type' => __('messages.checkout_package_type'),
                'package_id' => __('messages.checkout_selected_package_fallback'),
                'quantity' => __('interface.fields.quantity'),
                'email' => __('messages.checkout_email'),
                'first_name' => __('messages.checkout_first_name'),
                'last_name' => __('messages.checkout_last_name'),
                'phone' => __('messages.checkout_phone'),
                'notes' => __('messages.checkout_notes_label'),
                'captcha' => 'CAPTCHA',
                'coupon' => __('interface.fields.coupon'),
                'paymethod' => __('interface.fields.payment_method'),
                'policy_accepted' => __('document_ui.footer.refund'),
            ],
        );

        $checkoutKey = $data['checkout_draft_token'];
        $submittedEmail = User::normalizeEmail($data['email']);
        $existingDraft = CheckoutDraft::query()
            ->with(['user', 'completedOrder.user'])
            ->where('token', $checkoutKey)
            ->first();
        $existingOrder = Order::query()
            ->with('user')
            ->where('checkout_key', $checkoutKey)
            ->first();
        $existingOrder ??= $existingDraft?->completedOrder;

        if ($existingOrder) {
            $this->assertCheckoutReplayOwnership($existingOrder, $submittedEmail, $existingDraft);

            return $this->checkoutSuccessRedirect($request, $existingOrder);
        }

        if (!$this->captcha->check($data['captcha'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'captcha' => __('document_ui.contact.captcha_error'),
            ]);
        }

        // 1) Package / pricing calculations. Never trust client-supplied package pricing.
        $package = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->whereIn('type', ['iptv', 'reseller'])
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->findOrFail($data['package_id']);

        $packageType = $package->type === 'reseller' ? 'reseller' : 'package';
        $device = null;
        if ($packageType === 'package') {
            $device = isset($data['device_id']) ? Device::findOrFail($data['device_id']) : null;
            if (!$device) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'device_id' => __('document_ui.validation.required', [
                        'attribute' => __('messages.checkout_device'),
                    ]),
                ]);
            }
        }

        // 2) User create / get only after the selected product and device are verified.
        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
        $eventPromotion = $this->eventPromotions->activeForSession();
        if ($eventPromotion
            && (!$submittedEmail
                || !hash_equals($eventPromotion['recipient_email_normalized'], $submittedEmail))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Use the same email address that received this personal event offer.',
            ]);
        }

        $user = $this->customerIdentity->resolve($data['email'], null)
            ?? User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $fullName,
                    'phone'    => $data['phone'],
                    'country'  => null,
                    'notes'    => $data['notes'] ?? null,
                    'password' => bcrypt(Str::random(16)),
                ]
            );

        $consentUpdates = [];
        $consentIpHash = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
        $submittedPhone = preg_replace('/\D+/', '', (string) $data['phone']);
        $storedPhone = preg_replace('/\D+/', '', (string) $user->phone);
        $contactMatches = $user->wasRecentlyCreated
            || ($submittedPhone !== '' && $storedPhone !== '' && hash_equals($storedPhone, $submittedPhone));

        if ($contactMatches) {
            foreach (['email', 'whatsapp', 'ads'] as $channel) {
                if ($request->boolean('marketing_' . $channel)) {
                    $consentedAt = 'marketing_' . $channel . '_consented_at';
                    $optedOutAt = 'marketing_' . $channel . '_opted_out_at';
                    $consentUpdates[$consentedAt] = $user->{$consentedAt} && !$user->{$optedOutAt}
                        ? $user->{$consentedAt}
                        : now();
                    $consentUpdates[$optedOutAt] = null;
                }
            }
        }
        if ($consentUpdates !== []) {
            $user->forceFill($consentUpdates + [
                'marketing_consent_version' => $user->marketing_consent_version
                    ?: config('services.marketing.consent_version'),
                'marketing_consent_source' => $user->marketing_consent_source ?: 'checkout',
                'marketing_consent_locale' => $user->marketing_consent_locale ?: app()->getLocale(),
                'marketing_consent_ip_hash' => $user->marketing_consent_ip_hash ?: $consentIpHash,
            ])->save();
        }

        $this->customerIdentity->linkUser($user);

        if ($eventPromotion && (int) $eventPromotion['recipient_user_id'] !== (int) $user->id) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Use the same email address that received this personal event offer.',
            ]);
        }

        $vendor = strtolower((string) $package->vendor);
        $vendorLabel = $vendor === 'starshare' ? 'Filex' : 'Opplex';
        $serviceName = $this->packageServiceName($package);
        $packageTitle = $this->packageDisplayTitle($package);
        $planName = $package->isDurationPlan()
            ? $vendorLabel . ' - ' . $packageTitle
            : $packageTitle;

        $qty = 1;
        $basePrice = (float) $package->price_amount;
        $sellPriceSingle = $basePrice;
        $subtotal = $sellPriceSingle * $qty;
        $promotionQuote = $eventPromotion
            ? $this->eventPromotions->quote($subtotal, $eventPromotion)
            : ['subtotal' => round($subtotal, 2), 'discount' => 0.0, 'total' => round($subtotal, 2)];
        $sellPrice = $promotionQuote['total'];

        $costPriceSingle = isset($package->cost_price)
            ? (float) $package->cost_price
            : 0.0;

        $costPrice = $costPriceSingle * $qty;
        $profit    = $sellPrice - $costPrice;

        $credits  = $package->credits ? (int) $package->credits : 0;
        $duration = $package->duration_months ? (int) $package->duration_months : 0;

        $currency = 'USD';
        $now      = now();
        $expiry   = $duration > 0 ? $now->copy()->addMonths($duration) : null;
        $analyticsConsented = $this->hasAnalyticsConsent($request);

        $orderAttributes = [
            'checkout_key'  => $checkoutKey,
            'user_id'        => $user->id,
            'package'        => $planName,
            'price'          => $sellPrice,
            'cost_price'     => $costPriceSingle,
            'sell_price'     => $sellPrice,
            'subtotal'       => $promotionQuote['subtotal'],
            'discount'       => $promotionQuote['discount'],
            'promotion_campaign_id' => $eventPromotion['id'] ?? null,
            'promotion_discount_percent' => $eventPromotion['discount_percent'] ?? null,
            'profit'         => $profit,
            'credits'        => $credits,
            'duration'       => $duration,
            'status'         => 'pending',
            'payment_method' => $data['paymethod'],
            'payment_status' => 'unpaid',

            'custom_payment_method' => null,
            'custom_package'        => null,
            'buying_date'    => $now,
            'expiry_date'    => $expiry,
            'currency'       => $currency,
            'note'           => $data['notes'] ?? null,
            'messaged_by'    => false,
            'messaged_at'    => null,
            'iptv_username'  => null,

            // ENUM matches DB: package | reseller
            'type'           => $packageType,

            'device_id'      => $device?->id,
            'package_id'     => $package->id,
            'locale'         => app()->getLocale(),
            'ga_client_id'   => $analyticsConsented ? $this->gaClientId($request) : null,
            'analytics_consented_at' => $analyticsConsented ? $now : null,
        ];

        $draftRetentionExpiresAt = $now->copy()->addDays(
            max(1, (int) config('services.marketing.draft_retention_days', 30))
        );
        $checkoutDraft = CheckoutDraft::firstOrCreate(
            ['token' => $checkoutKey],
            [
                'locale' => app()->getLocale(),
                'last_activity_at' => $now,
                'retention_expires_at' => $draftRetentionExpiresAt,
            ]
        );

        // Serialize submissions for this checkout key. The unique orders.checkout_key
        // constraint remains the final database-level safeguard against duplicates.
        [$order, $orderCreated] = DB::transaction(function () use (
            $checkoutDraft,
            $checkoutKey,
            $device,
            $draftRetentionExpiresAt,
            $now,
            $orderAttributes,
            $package,
            $submittedEmail,
            $user,
            $vendor
        ) {
            $lockedDraft = CheckoutDraft::query()
                ->whereKey($checkoutDraft->id)
                ->lockForUpdate()
                ->firstOrFail();

            $order = $lockedDraft->completed_order_id
                ? Order::find($lockedDraft->completed_order_id)
                : null;
            $order ??= Order::query()
                ->where('checkout_key', $checkoutKey)
                ->lockForUpdate()
                ->first();
            $orderCreated = false;

            if ($order) {
                $this->assertCheckoutReplayOwnership($order, $submittedEmail, $lockedDraft);
            } else {
                $order = Order::create($orderAttributes);
                $orderCreated = true;
            }

            $lockedDraft->forceFill([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'device_id' => $device?->id,
                'vendor' => $vendor,
                'connection_name' => null,
                'connection_price' => null,
                'name' => null,
                'email' => null,
                'phone' => null,
                'email_consented_at' => null,
                'whatsapp_consented_at' => null,
                'ads_consented_at' => null,
                'consent_version' => null,
                'consent_ip_hash' => null,
                'locale' => app()->getLocale(),
                'last_activity_at' => $now,
                'retention_expires_at' => $draftRetentionExpiresAt,
                'completed_order_id' => $order->id,
                'completed_at' => $lockedDraft->completed_at ?: $now,
            ])->save();

            MarketingDelivery::query()
                ->where('checkout_draft_id', $lockedDraft->id)
                ->where('workflow', 'abandoned')
                ->whereNull('sent_at')
                ->update([
                    'failed_at' => $now,
                    'last_error' => 'Checkout completed',
                    'processing_at' => null,
                    'processing_token' => null,
                ]);

            return [$order, $orderCreated];
        }, 3);

        if (!$orderCreated) {
            return $this->checkoutSuccessRedirect($request, $order);
        }

        if ($analyticsConsented) {
            $analyticsOrderIds = (array) session('analytics_order_ids', []);
            $analyticsOrderIds[] = $order->id;
            session(['analytics_order_ids' => array_slice(array_unique($analyticsOrderIds), -20)]);
        }

        $referralCode = session()->pull('referral_code');
        if ($referralCode) {
            Referral::query()
                ->where('code', $referralCode)
                ->where('referrer_user_id', '<>', $user->id)
                ->whereIn('status', ['available', 'captured'])
                ->whereNull('referred_order_id')
                ->update([
                    'referred_user_id' => $user->id,
                    'referred_order_id' => $order->id,
                    'status' => 'captured',
                    'captured_at' => $now,
                ]);
        }

        $emailData = [
            'order_id'          => $order->id,
            'customer_name'     => $fullName,
            'customer_email'    => $data['email'],
            'phone'             => $data['phone'],
            'package'           => $planName,
            'package_type'      => $packageType,
            'vendor'            => $serviceName,
            'device'            => $device?->name,
            'quantity'          => $qty,
            'payment_method'    => $data['paymethod'],
            'subscription_price'=> $sellPriceSingle,
            'unit_price'        => $sellPriceSingle,
            'subtotal'          => $promotionQuote['subtotal'],
            'discount_amount'   => $promotionQuote['discount'],
            'promotion_name'    => $eventPromotion['name'] ?? null,
            'total_price'       => $sellPrice,
            'expiry'            => $expiry ? $expiry->toDateString() : null,
            'notes'             => $data['notes'] ?? null,
        ];

        try {
            Mail::to($user->email)->queue(
                (new CheckoutOrderMail($emailData, false))->locale(app()->getLocale())
            );

            $adminEmail = config('mail.from.address', 'info@opplexiptv.com');
            Mail::to($adminEmail)->queue(new CheckoutOrderMail($emailData, true));

            $admins = Admin::query()
                ->whereIn('role', [Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT])
                ->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new NewOrderNotification([
                    'title'   => 'New order received',
                    'body'    => "{$fullName} placed an order ({$packageType}).",
                    'order_id'=> $order->id,
                    'package' => $planName,
                    'type'    => $packageType,
                    'client'  => $fullName,
                    'phone'   => $data['phone'],
                    'payment' => $data['paymethod'],
                    'price'   => $sellPrice,
                    'created' => $now->toDateTimeString(),
                ]));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send checkout emails', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }

        return $this->checkoutSuccessRedirect($request, $order, [
            'package_id' => $package->id,
            'package' => $planName,
            'package_type' => $packageType,
            'vendor' => $serviceName,
            'device' => $device?->name,
            'subtotal' => $promotionQuote['subtotal'],
            'discount' => $promotionQuote['discount'],
            'promotion_name' => $eventPromotion['name'] ?? null,
            'total' => $sellPrice,
            'currency' => $currency,
            'payment_method' => $data['paymethod'],
        ]);
    }

    public function thankYou()
    {
        return view('pages.checkout.thank-you');
    }

    public function checkoutStep1(Request $request)
    {
        $packageId = $request->input('package_id');
        if (!is_scalar($packageId) || filter_var($packageId, FILTER_VALIDATE_INT) === false) {
            return redirect()->route('configure', $request->except('package_id'));
        }
        $packageId = (int) $packageId;

        $package = Package::query()
            ->where('active', true)
            ->where('is_available', true)
            ->whereIn('type', ['iptv', 'reseller'])
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->where('price_amount', '>', 0)
            ->whereKey($packageId)
            ->first(['id', 'type', 'vendor', 'title', 'duration_months']);

        if (!$package) {
            return redirect()->route('configure', $request->except('package_id'));
        }

        if ($package->type === 'iptv') {
            $deviceId = $request->input('device_id');
            $deviceExists = is_scalar($deviceId)
                && filter_var($deviceId, FILTER_VALIDATE_INT) !== false
                && Device::query()->whereKey((int) $deviceId)->exists();

            if (!$deviceExists) {
                return redirect()->route('configure', $request->query());
            }
        }

        $planName  = $request->input('plan_name', __('interface.checkout.default_plan'));
        $planPrice = (float) $request->input('plan_price', 15);
        $device    = $request->input('device', null);
        $eventPromotion = $this->eventPromotions->activeForSession();

        return view('pages.checkout.step1', compact('planName', 'planPrice', 'device', 'eventPromotion'));
    }

    private function checkoutSuccessRedirect(Request $request, Order $order, array $summary = [])
    {
        $whatsappPaymentOrders = (array) $request->session()->get('whatsapp_payment_orders', []);
        $whatsappPaymentOrders[(string) $order->id] = now()->timestamp;
        $request->session()->put(
            'whatsapp_payment_orders',
            array_slice($whatsappPaymentOrders, -5, null, true)
        );

        return redirect()
            ->route('thankyou')
            ->with('success', __('interface.checkout.order_received', ['id' => $order->id]))
            ->with('order_summary', array_merge([
                'id' => $order->id,
                'package_id' => $order->package_id,
                'package' => $order->package,
                'package_type' => $order->type,
                'vendor' => null,
                'device' => null,
                'subtotal' => $order->subtotal ?? $order->sell_price ?? $order->price,
                'discount' => $order->discount ?? 0,
                'promotion_name' => null,
                'total' => $order->sell_price ?? $order->price,
                'currency' => $order->currency,
                'payment_method' => $order->payment_method,
            ], $summary));
    }

    private function assertCheckoutReplayOwnership(
        Order $order,
        ?string $submittedEmail,
        ?CheckoutDraft $draft = null
    ): void {
        $order->loadMissing('user');
        $orderEmail = $order->user?->email_normalized
            ?: User::normalizeEmail($order->user?->email);
        $matchesOrder = $submittedEmail !== null
            && $orderEmail !== null
            && hash_equals($orderEmail, $submittedEmail);

        $matchesDraft = true;
        if ($draft?->completed_order_id && (int) $draft->completed_order_id !== (int) $order->id) {
            $matchesDraft = false;
        } elseif ($draft?->user_id) {
            $draft->loadMissing('user');
            $draftEmail = $draft->user?->email_normalized
                ?: User::normalizeEmail($draft->user?->email);
            $matchesDraft = $draftEmail !== null
                && $submittedEmail !== null
                && hash_equals($draftEmail, $submittedEmail);
        }

        if (!$matchesOrder || !$matchesDraft) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Please use the same email address used for this checkout.',
            ]);
        }
    }

    private function packageServiceName(Package $package): string
    {
        if ($package->type !== 'iptv' || $package->isDurationPlan()) {
            return strtolower((string) $package->vendor) === 'starshare' ? 'Filex' : 'Opplex';
        }

        $title = trim((string) $package->title);
        $service = preg_replace(
            '/\s*-\s*(?:3\s*Months?|Half\s*Yearly|Yearly|Monthly|1\s*Month)\s*$/iu',
            '',
            $title
        );

        return trim((string) $service) ?: $title;
    }

    private function packageDisplayTitle(Package $package): string
    {
        if ($package->type === 'iptv' && $package->isDurationPlan()) {
            $planKey = match ((int) $package->duration_months) {
                1 => 'monthly',
                3 => 'three_months',
                6 => 'half_yearly',
                12 => 'yearly',
                default => null,
            };

            if ($planKey !== null) {
                $translationKey = 'document_commerce.packages.pricing.plans.' . $planKey . '.title';
                $localizedTitle = __($translationKey);
                if ($localizedTitle !== $translationKey) {
                    return $localizedTitle;
                }
            }
        } elseif ($package->type === 'reseller') {
            $translationKey = match ($package->title) {
                'Starter Reseller Package' => 'messages.starter_reseller',
                'Essential Reseller Bundle' => 'messages.essential_reseller',
                'Pro Reseller Suite' => 'messages.pro_reseller',
                'Advanced Reseller Toolkit' => 'messages.advanced_reseller',
                default => null,
            };

            if ($translationKey !== null && __($translationKey) !== $translationKey) {
                return __($translationKey);
            }
        }

        $title = (string) ($package->translation()?->title ?: $package->title);

        if ($package->type === 'iptv') {
            return Str::startsWith($title, 'messages.') || trim($title) === ''
                ? (string) $package->title
                : trim($title);
        }

        $title = (string) preg_replace('/\s*\([^)]*\)/u', '', $title);
        $title = trim((string) preg_replace('/\s*-\s*\$?\d+(?:[.,]\d+)?/u', '', $title, 1));

        return Str::startsWith($title, 'messages.') || $title === ''
            ? (string) $package->title
            : $title;
    }

    private function hasAnalyticsConsent(Request $request): bool
    {
        $raw = $this->rawCookie($request, 'opplex_consent');
        if ($raw === null) {
            return false;
        }

        $preference = json_decode($raw, true);

        return is_array($preference)
            && (($preference['analytics'] ?? false) === true || ($preference['analytics'] ?? null) === 1)
            && ($preference['version'] ?? null) === config('services.marketing.tracking_consent_version');
    }

    private function gaClientId(Request $request): ?string
    {
        $cookie = $this->rawCookie($request, '_ga');
        if ($cookie && preg_match('/^GA\d+\.\d+\.(\d+\.\d+)$/', $cookie, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function rawCookie(Request $request, string $name): ?string
    {
        foreach (explode(';', (string) $request->headers->get('cookie')) as $cookie) {
            [$key, $value] = array_pad(explode('=', trim($cookie), 2), 2, null);
            if ($key === $name && $value !== null) {
                return urldecode($value);
            }
        }

        return null;
    }
}
