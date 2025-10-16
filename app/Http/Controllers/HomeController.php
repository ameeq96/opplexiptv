<?php

namespace App\Http\Controllers;

use App\Http\Requests\Site\{BuyNowRequest, ContactRequest, SubscribeRequest};
use App\Services\{TmdbService, ImageService, LocaleService, ContactService, CaptchaService};
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private TmdbService $tmdb,
        private ImageService $images,
        private LocaleService $locale,
        private ContactService $contact,
        private CaptchaService $captcha,
    ) {}

   public function home()
    {
        return view('pages.home');
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
        if (!app(CaptchaService::class)->check($request->captcha)) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }
        $this->contact->contact($request->only('username', 'email', 'phone', 'message'));
        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function sendBuynow(BuyNowRequest $request)
    {
        if (!app(CaptchaService::class)->check($request->captcha)) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }
        $this->contact->buyNow($request->only('username', 'email', 'package', 'phone', 'message'));
        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function postBuyNowPanel(BuyNowRequest $request)
    {
        if (!app(CaptchaService::class)->check($request->captcha)) {
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
