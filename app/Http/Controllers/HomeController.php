<?php

namespace App\Http\Controllers;

use App\Mail\CheckoutOrderMail;
use App\Http\Requests\Site\{BuyNowRequest, ContactRequest, SubscribeRequest};
use App\Models\Admin;
use App\Models\Device;
use App\Models\Order;
use App\Models\Package;
use App\Models\Plan;
use App\Services\{TmdbService, ImageService, LocaleService, ContactService, CaptchaService, ProductCatalogService};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use Illuminate\Support\Str;
use App\Notifications\NewOrderNotification;

class HomeController extends Controller
{
    public function __construct(
        private TmdbService $tmdb,
        private ImageService $images,
        private LocaleService $locale,
        private ContactService $contact,
        private CaptchaService $captcha,
        private ProductCatalogService $catalog,
    ) {}

    public function home()
    {
        $products = $this->catalog->getAll();
        $homeProducts = array_slice($products, 0, 8);

        return view('pages.home', [
            'shopProducts' => $homeProducts,
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

    public function packages()
    {
        return view('pages.packages', ['activeIndex' => 1]);
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
        $products = [
            [
                'name' => 'Android TV Box 10.0 4GB RAM 32GB ROM Allwinner H616 Quad-core Android Box, Support 2.4G/5.0G Dual WiFi 6K Utral HD / 3D / H.265 with Bluetooth 5.0',
                'asin' => 'B08CRV62C4',
                'link' => 'https://amzn.to/4ndDMIh',
                'image' => 'B08CRV62C4.webp',
            ],
            [
                'name' => 'Amazon Fire TV Stick 4K Max streaming device, with AI-powered Fire TV Search, supports Wi-Fi 6E, free & live TV without cable or satellite',
                'asin' => 'B0BP9SNVH9',
                'link' => 'https://amzn.to/4hm6GoE',
                'image' => 'B0BP9SNVH9.webp',
            ],
            [
                'name' => 'Roku Streaming Stick HD 2025 — HD Streaming Device for TV with Roku Voice Remote, Free & Live TV',
                'asin' => 'B0DXXYS4BJ',
                'link' => 'https://amzn.to/47trln4',
                'image' => 'B0DXXYS4BJ.webp',
            ],
            [
                'name' => 'Mounting Dream TV Wall Mount for 32-65 Inch Television, Swivel & Tilt, Full Motion Dual Arms, VESA 400x400, 99lbs (MD2380)',
                'asin' => 'B00SFSU53G',
                'link' => 'https://amzn.to/3KNGbfD',
                'image' => 'B00SFSU53G.webp',
            ],
            [
                'name' => '【Pack of 2】 New Universal Remote for All Samsung TV Remote (Smart/LED/LCD/HDTV/3D)',
                'asin' => 'B0B7B6KLH3',
                'link' => 'https://amzn.to/3WbBxdB',
                'image' => 'B0B7B6KLH3.webp',
            ],
            [
                'name' => 'Universal for VIZIO Smart TV Remote Control Replacement XRT136',
                'asin' => 'B08PK7TBFD',
                'link' => 'https://amzn.to/4hkhKT4',
                'image' => 'B08PK7TBFD.webp',
            ],
            [
                'name' => 'Roku Streaming Stick 4K – HDR & Dolby Vision, Voice Remote & Long-Range Wi-Fi',
                'asin' => 'B09BKCDXZC',
                'link' => 'https://amzn.to/4hkhZNY',
                'image' => 'B09BKCDXZC.webp',
            ],
            [
                'name' => '【Pack of 2】 for Samsung Smart TV Remote Control Replacement, Universal for All Samsung TVs',
                'asin' => 'B0BDRSY88T',
                'link' => 'https://amzn.to/4n9DL8e',
                'image' => 'B0BDRSY88T.webp',
            ],
            [
                'name' => 'Roku Ultra – Ultimate 4K Streaming Player (HDR10+, Dolby Vision & Atmos, Wi-Fi 6, Voice Remote Pro)',
                'asin' => 'B0DF44RTTP',
                'link' => 'https://amzn.to/4nS6zmV',
                'image' => 'B0DF44RTTP.webp',
            ],
            [
                'name' => 'INSIGNIA 40" Class F40 Series LED Full HD Smart Fire TV (NS-40F401NA26)',
                'asin' => 'B0F7RXTN1Y',
                'link' => 'https://amzn.to/3WdzXYL',
                'image' => 'B0F7RXTN1Y.webp',
            ],
        ];

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
            'isRtl'    => $isRtl,
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
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }
        $this->contact->contact($request->only('username', 'email', 'phone', 'message'));
        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function sendBuynow(BuyNowRequest $request)
    {
        if (!$this->captcha->check($request->captcha)) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }
        $this->contact->buyNow($request->only('username', 'email', 'package', 'phone', 'message'));
        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function postBuyNowPanel(BuyNowRequest $request)
    {
        if (!$this->captcha->check($request->captcha)) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }
        $this->contact->buyNow($request->only('username', 'email', 'package', 'phone', 'message'));
        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function subscribe(SubscribeRequest $request)
    {
        $this->contact->subscribe($request->email);
        return back()->with('success', 'Thank you for subscribing!');
    }

    public function configure(Request $request)
    {
        // Devices
        $devices = Device::query()
            ->orderBy('name')
            ->get(['id', 'name', 'icon']);

        // Connection plans
        $plans = Plan::query()
            ->where('active', true)
            ->orderBy('max_devices')
            ->get(['id', 'name', 'price', 'max_devices']);

        // IPTV packages (type = iptv, vendor = opplex|starshare)
        $iptvRows = Package::query()
            ->where('active', true)
            ->where('type', 'iptv')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->orderByRaw("FIELD(vendor,'opplex','starshare')")
            ->orderByRaw("COALESCE(sort_order, duration_months, id)")
            ->get(['id', 'vendor', 'title', 'price_amount', 'duration_months', 'icon']);

        $iptvPackages = [];
        foreach ($iptvRows as $r) {
            $dur = (int) ($r->duration_months ?: 1);

            $iptvPackages[] = [
                'id'     => $r->id,                           // <--- ID SEND HO RAHA
                'vendor' => strtolower($r->vendor),           // opplex | starshare
                'title'  => $r->title,
                'old'    => 0.00,
                'price'  => (float) $r->price_amount,
                'unit'   => '/ ' . $dur . ' month' . ($dur > 1 ? 's' : ''),
                'icon'   => $r->icon ?: 'bi-router',
            ];
        }

        // Reseller packages (type = reseller, vendor = opplex|starshare)
        $resellerRows = Package::query()
            ->where('active', true)
            ->where('type', 'reseller')
            ->whereIn('vendor', ['opplex', 'starshare'])
            ->orderByRaw("FIELD(vendor,'opplex','starshare'), COALESCE(sort_order, credits, id)")
            ->get(['id', 'vendor', 'title', 'price_amount', 'credits', 'icon']);

        $resellerPackages = [];
        foreach ($resellerRows as $r) {
            $credits = (int) ($r->credits ?: 0);

            $resellerPackages[] = [
                'id'     => $r->id,                           // <--- ID SEND HO RAHA
                'vendor' => strtolower($r->vendor),
                'title'  => $r->title,
                'old'    => 0.00,
                'price'  => (float) $r->price_amount,
                'unit'   => $credits > 0 ? '/ ' . $credits . ' Credits' : '/ Credits',
                'icon'   => $r->icon ?: 'bi-router',
            ];
        }

        $prePrice    = $request->query('price');
        $iptvVendors = ['Opplex', 'Starshare'];

        return view('pages.checkout.configure', compact(
            'devices',
            'plans',
            'iptvPackages',
            'resellerPackages',
            'prePrice',
            'iptvVendors'
        ));
    }

    public function checkoutStep2(Request $request)
    {
        $data = $request->validate([
            'device'       => 'nullable|string',
            'device_id'    => 'nullable|integer|exists:devices,id',
            'iptv_vendor'  => 'nullable|string',
            'plan_name'    => 'required|string',
            'plan_price'   => 'required|numeric|min:0',

            // ENUM: package | reseller
            'package_type' => 'required|in:package,reseller',

            'package_id'   => 'nullable|integer|exists:packages,id',
            'quantity'     => 'required|integer|min:1',

            'email'        => 'required|email',
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'phone'        => 'required|string|max:50',
            'notes'        => 'nullable|string',
            'coupon'       => 'nullable|string',
            'paymethod'    => 'required|in:card,crypto',
        ]);

        // 1) User create / get
        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $fullName,
                'phone'    => $data['phone'],
                'country'  => null,
                'notes'    => $data['notes'] ?? null,
                'password' => bcrypt(Str::random(16)),
            ]
        );

        // 2) Package / pricing calculations
        $package  = $data['package_id']
            ? Package::find($data['package_id'])
            : null;

        $qty             = $data['quantity'];
        $sellPriceSingle = (float) $data['plan_price'];
        $sellPrice       = $sellPriceSingle * $qty;

        $costPriceSingle = $package && isset($package->cost_price)
            ? (float) $package->cost_price
            : 0.0;

        $costPrice = $costPriceSingle * $qty;
        $profit    = $sellPrice - $costPrice;

        $credits  = $package && $package->credits ? (int) $package->credits : 0;
        $duration = $package && $package->duration_months ? (int) $package->duration_months : 0;

        $currency = 'USD';
        $now      = now();
        $expiry   = $duration > 0 ? $now->copy()->addMonths($duration) : null;

        $cleanMoney = static function ($v) {
            if (is_null($v)) return null;
            if (is_numeric($v)) return (float) $v;
            $s = preg_replace('/[^0-9.]/', '', (string) $v);
            return $s === '' ? null : (float) $s;
        };

        // 3) Order create
        $order = Order::create([
            'user_id'        => $user->id,
            'package'        => $data['plan_name'],
            'price'          => $sellPriceSingle,
            'cost_price'     => $costPriceSingle,
            'sell_price'     => $sellPrice,
            'profit'         => $profit,
            'credits'        => $credits,
            'duration'       => $duration,
            'status'         => 'pending',
            'payment_method' => $data['paymethod'],

            'custom_payment_method' => null,
            'custom_package'        => null,
            'buying_date'    => $now,
            'expiry_date'    => $expiry,
            'currency'       => $currency,
            'note'           => $data['notes'] ?? null,
            'messaged_by'    => null,
            'messaged_at'    => null,
            'iptv_username'  => null,

            // ENUM matches DB: package | reseller
            'type'           => $data['package_type'],

            'device_id'      => $data['device_id']  ?? null,
            'package_id'     => $data['package_id'] ?? null,
        ]);

        $emailData = [
            'order_id'          => $order->id,
            'customer_name'     => $fullName,
            'customer_email'    => $data['email'],
            'phone'             => $data['phone'],
            'package'           => $data['plan_name'],
            'package_type'      => $data['package_type'],
            'vendor'            => $data['iptv_vendor'] ?? null,
            'device'            => $data['device'] ?? null,
            'quantity'          => $qty,
            'payment_method'    => $data['paymethod'],
            'subscription_price'=> $cleanMoney($request->input('pkg_price')),
            'connection_price'  => $cleanMoney($request->input('connection_price')),
            'unit_price'        => $sellPriceSingle,
            'total_price'       => $sellPrice,
            'expiry'            => $expiry ? $expiry->toDateString() : null,
            'notes'             => $data['notes'] ?? null,
        ];

        try {
            Mail::to($user->email)->send(new CheckoutOrderMail($emailData, false));

            $adminEmail = config('mail.from.address', 'info@opplexiptv.com');
            Mail::to($adminEmail)->send(new CheckoutOrderMail($emailData, true));

            $admins = Admin::all();
            if ($admins->count() > 0) {
                Notification::send($admins, new NewOrderNotification([
                    'title'   => 'New order received',
                    'body'    => "{$fullName} placed an order ({$data['package_type']}).",
                    'order_id'=> $order->id,
                    'package' => $data['plan_name'],
                    'type'    => $data['package_type'],
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

        return redirect()
            ->route('thankyou')
            ->with('success', 'Order created with ID #' . $order->id);
    }

    public function thankYou() 
    {
        return view('pages.checkout.thank-you');
    }

    public function checkoutStep1(Request $request)
    {
        $planName  = $request->input('plan_name', 'Premium subscription 1 Month × 1');
        $planPrice = (float) $request->input('plan_price', 15);
        $device    = $request->input('device', null);

        return view('pages.checkout.step1', compact('planName', 'planPrice', 'device'));
    }
}
