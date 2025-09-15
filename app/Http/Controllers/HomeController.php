<?php

namespace App\Http\Controllers;

use App\Http\Requests\Site\{ContactRequest, BuyNowRequest, SubscribeRequest};
use App\Services\{TmdbService, ImageService, LocaleService, ContactService, CaptchaService};
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

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
        $isMobile = (new Agent())->isMobile();
        $isRtl    = $this->locale->isRtl();

        $payload = $this->tmdb->trending('movie', 'day', 1);
        $movies  = $payload['results'] ?? [];

        foreach ($movies as &$m) {
            if (!empty($m['backdrop_path'])) {
                $src = $this->images->tmdbImage($m['backdrop_path'], $isMobile ? 'w500' : 'w1280');
                $m['webp_image_url'] = $this->images->toWebp($src, $isMobile ? 428 : 1280, $isMobile ? 220 : 720);
            }
            if (!empty($m['poster_path'])) {
                $src = $this->images->tmdbImage($m['poster_path'], 'w500');
                $m['webp_poster_url'] = $this->images->toWebp($src, 308, 462);
            }
        }
        unset($m);

        return view('pages.home', [
            'movies'   => collect($movies)->take(10),
            'logos'    => $this->images->logos(),
            'isMobile' => $isMobile,
            'isRtl'    => $isRtl,
        ]);
    }

    public function about()
    {
        return view('pages.about', [
            'logos'    => $this->images->logos(),
            'isRtl'    => $this->locale->isRtl(),
            'isMobile' => (new Agent())->isMobile(),
        ]);
    }

    public function contact()
    {
        ['num1' => $num1, 'num2' => $num2] = $this->captcha->generate();
        return view('pages.contact', [
            'num1' => $num1,
            'num2' => $num2,
            'isRtl' => $this->locale->isRtl(),
        ]);
    }

    public function pricing()
    {
        return view('pages.pricing', ['isRtl' => $this->locale->isRtl()]);
    }

    public function redirect(Request $request)
    {
        $target = $request->query('target');
        abort_unless($target && filter_var($target, FILTER_VALIDATE_URL), 404);
        return view('pages.redirect', [
            'isRtl' => $this->locale->isRtl(),
            'target' => $target
        ]);
    }

    public function movies(Request $request)
    {
        $page   = max(1, (int) $request->input('page', 1));
        $query  = (string) $request->input('search', '');
        $isRtl  = $this->locale->isRtl();

        if ($query !== '') {
            $payload    = $this->tmdb->searchMulti($query, $page);
        } else {
            $payload    = $this->tmdb->trending('all', 'day', $page);
        }

        $results    = $payload['results']      ?? [];
        $totalPages = (int) ($payload['total_pages'] ?? 1);
        $totalPages = max(1, $totalPages);

        foreach ($results as &$movie) {
            $movie['trailer_url'] = $this->tmdb->trailerUrl(
                $movie['id'],
                $movie['media_type'] ?? 'movie'
            );
        }
        unset($movie);

        $collection = collect($results);
        $filteredMovies = [
            'movies'   => $collection->where('media_type', 'movie'),
            'series'   => $collection->where('media_type', 'tv'),
            'cartoons' => $collection->filter(fn($m) => in_array(16, $m['genre_ids'] ?? [])),
        ];

        return view('pages.movies', compact('filteredMovies', 'page', 'totalPages', 'query', 'isRtl'));
    }


    public function packages()
    {
        return view('pages.packages',     ['isRtl' => $this->locale->isRtl()]);
    }

    public function resellerPanel()
    {
        return view('pages.resellerpanel', ['isRtl' => $this->locale->isRtl(), 'logos' => $this->images->logos()]);
    }

    public function buynow()
    {
        ['num1' => $a, 'num2' => $b] = $this->captcha->generate();
        return view('pages.buynow',      ['num1' => $a, 'num2' => $b, 'isRtl' => $this->locale->isRtl()]);
    }

    public function buynowpanel()
    {
        ['num1' => $a, 'num2' => $b] = $this->captcha->generate();
        return view('pages.buynowpanel', ['num1' => $a, 'num2' => $b, 'isRtl' => $this->locale->isRtl()]);
    }

    public function iptvApplications()
    {
        return view('pages.iptvapplications', ['isRtl' => $this->locale->isRtl()]);
    }

    public function faq()
    {
        return view('pages.faq', ['isRtl' => $this->locale->isRtl()]);
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

    public function getTrending()
    {
        $data = $this->tmdb->trending('all', 'day');
        return !empty($data) ? response()->json(['results' => $data]) : response()->json(['error' => 'Unable to fetch trending data'], 500);
    }
}
