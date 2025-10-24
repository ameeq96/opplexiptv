<?php

namespace App\Http\Controllers;

use App\Http\Requests\Site\{BuyNowRequest, ContactRequest, SubscribeRequest};
use App\Services\{TmdbService, ImageService, LocaleService, ContactService, CaptchaService, ProductCatalogService};
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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

    private function getShopProducts(): array
    {
        return [
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
                'image' => 'B0BP9SNVH9.jpg',
            ],
            [
                'name' => 'Roku Streaming Stick HD 2025 �?" HD Streaming Device for TV with Roku Voice Remote, Free & Live TV',
                'asin' => 'B0DXXYS4BJ',
                'link' => 'https://amzn.to/47trln4',
                'image' => 'B0DXXYS4BJ.jpg',
            ],
            [
                'name' => 'Mounting Dream TV Wall Mount for 32-65 Inch Television, Swivel & Tilt, Full Motion Dual Arms, VESA 400x400, 99lbs (MD2380)',
                'asin' => 'B00SFSU53G',
                'link' => 'https://amzn.to/3KNGbfD',
                'image' => 'B00SFSU53G.jpg',
            ],
            [
                'name' => 'a??Pack of 2a?` New Universal Remote for All Samsung TV Remote (Smart/LED/LCD/HDTV/3D)',
                'asin' => 'B0B7B6KLH3',
                'link' => 'https://amzn.to/3WbBxdB',
                'image' => 'B0B7B6KLH3.jpg',
            ],
            [
                'name' => 'Universal for VIZIO Smart TV Remote Control Replacement XRT136',
                'asin' => 'B08PK7TBFD',
                'link' => 'https://amzn.to/4hkhKT4',
                'image' => 'B08PK7TBFD.jpg',
            ],
            [
                'name' => 'Roku Streaming Stick 4K �?" HDR & Dolby Vision, Voice Remote & Long-Range Wi-Fi',
                'asin' => 'B09BKCDXZC',
                'link' => 'https://amzn.to/4hkhZNY',
                'image' => 'B09BKCDXZC.jpg',
            ],
            [
                'name' => 'a??Pack of 2a?` for Samsung Smart TV Remote Control Replacement, Universal for All Samsung TVs',
                'asin' => 'B0BDRSY88T',
                'link' => 'https://amzn.to/4n9DL8e',
                'image' => 'B0BDRSY88T.jpg',
            ],
            [
                'name' => 'Roku Ultra �?" Ultimate 4K Streaming Player (HDR10+, Dolby Vision & Atmos, Wi-Fi 6, Voice Remote Pro)',
                'asin' => 'B0DF44RTTP',
                'link' => 'https://amzn.to/4nS6zmV',
                'image' => 'B0DF44RTTP.jpg',
            ],
            [
                'name' => 'INSIGNIA 40" Class F40 Series LED Full HD Smart Fire TV (NS-40F401NA26)',
                'asin' => 'B0F7RXTN1Y',
                'link' => 'https://amzn.to/3WdzXYL',
                'image' => 'B0F7RXTN1Y.jpg',
            ],
        ];
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
}
