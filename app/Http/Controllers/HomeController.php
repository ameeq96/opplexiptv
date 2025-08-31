<?php

namespace App\Http\Controllers;

use App\Mail\{BuyNowAutoReply, BuyNowEmail, ContactAutoReply, ContactEmail, SubscribeEmail};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Http, Log, Mail};
use Jenssegers\Agent\Agent;

class HomeController extends Controller
{

    public function home()
    {
        $apiKey = env('TMDB_API_KEY');
        $baseUrl = env('TMDB_BASE_URL');
        $imageBaseUrl = "https://image.tmdb.org/t/p/w1280";
        $posterBaseUrl = "https://image.tmdb.org/t/p/w500";


        $locale = app()->getLocale();

        $languageCode = match ($locale) {
            'fr' => 'fr-FR',
            'it' => 'it-IT',
            'ur' => 'ur-PK',
            'ru' => 'ru-RU',
            'es' => 'es-ES',
            'pt' => 'pt-BR',
            default => 'en-US',
        };


        $agent = new Agent();
        $isMobile = $agent->isMobile();

        $logos = [
            'images/resource/5.webp',
            'images/resource/4.webp',
            'images/resource/3.webp',
            'images/resource/6.webp',
            'images/resource/7.webp',
            'images/resource/8.webp',
            'images/resource/9.webp',
        ];

        $optimizedLogos = array_map(function ($logo) {
            $localPath = public_path($logo);
            return $this->convertToWebp($localPath, 100);
        }, $logos);

        $cacheKey = 'trending_movies_' . $languageCode;




        $moviesUrl = $baseUrl . '/trending/movie/day?api_key=504f8cb78e140a66dc170c28614f2e50&language=' . $languageCode;
        $movies = Cache::remember($cacheKey, now()->addHour(), function () use ($moviesUrl) {
            $response = Http::withoutVerifying()->get($moviesUrl);
            return $response->json()['results'] ?? [];
        });

        foreach ($movies as &$movie) {
            if (!empty($movie['backdrop_path'])) {
                if ($isMobile) {
                    $movie['webp_image_url'] = $this->convertToWebp($imageBaseUrl . $movie['backdrop_path'], 428, 220);
                } else {
                    $movie['webp_image_url'] = $this->convertToWebp($imageBaseUrl . $movie['backdrop_path'], 1280, 720);
                }
            }

            if (!empty($movie['poster_path'])) {
                $movie['webp_poster_url'] = $this->convertToWebp($posterBaseUrl . $movie['poster_path'], 308, 462);
            }

            $movieId = $movie['id'];
            $mediaType = $movie['media_type'];
        }

        $movies = collect($movies)->take(10);

        return view('pages.home', compact('movies', 'logos', 'isMobile'));
    }

    private function convertToWebp($imageUrl, $width = 308, $height = 462)
    {
        $webpDir = public_path('webp_images');
        $webpPath = 'webp_images/' . md5($imageUrl . $width . $height) . '.webp';
        $fullPath = public_path($webpPath);

        if (file_exists($fullPath)) {
            return asset($webpPath);
        }

        try {
            $imageData = Http::timeout(10)->withoutVerifying()->get($imageUrl)->body();
            $image = @imagecreatefromstring($imageData);

            if (!$image) {
                return $imageUrl;
            }

            $resizedImage = imagecreatetruecolor($width, $height);
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
            imagefill($resizedImage, 0, 0, $transparent);

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

            if (!file_exists($webpDir)) {
                mkdir($webpDir, 0755, true);
            }

            imagewebp($resizedImage, $fullPath, 75);

            imagedestroy($image);
            imagedestroy($resizedImage);

            return asset($webpPath);
        } catch (\Exception $e) {
            return $imageUrl;
        }
    }

    public function about()
    {
        $logos = [
            'images/resource/5.webp',
            'images/resource/4.webp',
            'images/resource/3.webp',
            'images/resource/6.webp',
            'images/resource/7.webp',
            'images/resource/8.webp',
            'images/resource/9.webp',
        ];

        return view("pages.about", compact('logos'));
    }

    public function contact()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);

        session(['captcha_sum' => $num1 + $num2]);

        return view("pages.contact", compact('num1', 'num2'));
    }

    public function pricing()
    {
        return view("pages.pricing");
    }

    public function movies(Request $request)
    {
        $apiKey = env('TMDB_API_KEY');
        $baseUrl = env('TMDB_BASE_URL');
        $page = $request->input('page', 1);
        $query = $request->input('search');

        // Get language code based on locale
        $locale = app()->getLocale();
        $languageCode = match ($locale) {
            'fr' => 'fr-FR',
            'it' => 'it-IT',
            default => 'en-US',
        };

        $cacheKey = $query
            ? "search_{$query}_page_{$page}_{$locale}"
            : "trending_page_{$page}_{$locale}";

        // Build the URL like in the home() method
        $moviesUrl = $query
            ? $baseUrl . "/search/multi?api_key={$apiKey}&query=" . urlencode($query) . "&page={$page}&language={$languageCode}"
            : $baseUrl . "/trending/all/day?api_key={$apiKey}&page={$page}&language={$languageCode}";

        // Fetch and cache movies
        $movies = Cache::remember($cacheKey, now()->addHour(), function () use ($moviesUrl) {
            $response = Http::withoutVerifying()->get($moviesUrl);
            return $response->json()['results'] ?? [];
        });

        // Add trailer URLs
        foreach ($movies as &$movie) {
            $movieId = $movie['id'];
            $mediaType = $movie['media_type'] ?? 'movie';

            $movie['trailer_url'] = Cache::remember("movie_{$movieId}_trailer_{$locale}", now()->addDay(), function () use ($baseUrl, $mediaType, $movieId, $apiKey, $languageCode) {
                $trailerUrl = "$baseUrl/$mediaType/$movieId/videos?api_key={$apiKey}&language={$languageCode}";
                $trailerResponse = Http::withoutVerifying()->get($trailerUrl);

                $trailers = $trailerResponse->json()['results'] ?? [];
                $youtubeTrailer = collect($trailers)->firstWhere('site', 'YouTube');

                return $youtubeTrailer ? "https://www.youtube.com/watch?v={$youtubeTrailer['key']}" : null;
            });
        }

        // Group filtered results
        $filteredMovies = [
            'movies' => collect($movies)->where('media_type', 'movie'),
            'series' => collect($movies)->where('media_type', 'tv'),
            'cartoons' => collect($movies)->filter(function ($movie) {
                return in_array(16, $movie['genre_ids'] ?? []);
            }),
        ];

        // Cache total pages
        $totalPagesCacheKey = $query
            ? "search_{$query}_total_pages_{$locale}"
            : "trending_total_pages_{$locale}";

        $totalPages = Cache::remember($totalPagesCacheKey, now()->addHour(), function () use ($baseUrl, $apiKey, $query, $page, $languageCode) {
            $url = $query
                ? "$baseUrl/search/multi?api_key={$apiKey}&query=" . urlencode($query) . "&page={$page}&language={$languageCode}"
                : "$baseUrl/trending/all/day?api_key={$apiKey}&page={$page}&language={$languageCode}";

            $response = Http::withoutVerifying()->get($url);
            return $response->json()['total_pages'] ?? 1;
        });

        return view('pages.movies', compact('filteredMovies', 'page', 'totalPages', 'query'));
    }



    public function packages()
    {
        return view("pages.packages");
    }

    public function resellerPanel()
    {
        return view("pages.resellerpanel");
    }

    public function buynow()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);

        session(['captcha_sum' => $num1 + $num2]);

        return view("pages.buynow", compact('num1', 'num2'));
    }

    public function buynowpanel()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);

        session(['captcha_sum' => $num1 + $num2]);

        return view("pages.buynowpanel", compact('num1', 'num2'));
    }

    public function iptvApplications()
    {
        return view("pages.iptvapplications");
    }

    public function faq()
    {
        return view("pages.faq");
    }

    public function send(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($request->captcha != session('captcha_sum')) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }

        try {

            $details = [
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
            ];

            Mail::to('info@opplexiptv.com')->send(new ContactEmail($details));

            Mail::to($request->email)->send(new ContactAutoReply($details));

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'There was an error sending your message. Please try again later.');
        }
    }

    public function sendBuynow(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'package' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($request->captcha != session('captcha_sum')) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }

        try {
            $details = [
                'username' => $request->username,
                'email' => $request->email,
                'package' => $request->package,
                'phone' => $request->phone,
                'message' => $request->message,
            ];

            Mail::to('info@opplexiptv.com')->send(new BuyNowEmail($details));

            Mail::to($request->email)->send(new BuyNowAutoReply($details));

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'There was an error sending your message. Please try again later.');
        }
    }

    public function postBuyNowPanel(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'package' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($request->captcha != session('captcha_sum')) {
            return back()->with('error', 'Invalid Captcha. Please try again.');
        }

        try {
            $details = [
                'username' => $request->username,
                'email' => $request->email,
                'package' => $request->package,
                'phone' => $request->phone,
                'message' => $request->message,
            ];

            Mail::to('info@opplexiptv.com')->send(new BuyNowEmail($details));

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'There was an error sending your message. Please try again later.');
        }
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $details = [
            'email' => $request->email,
        ];

        Mail::to('info@opplexiptv.com')->send(new SubscribeEmail($details));

        return redirect()->back()->with('success', 'Thank you for subscribing!');
    }

    public function getTrending()
    {
        $apiKey = env('TMDB_API_KEY');
        $baseUrl = env('TMDB_BASE_URL');

        $response = Http::withoutVerifying()->get("$baseUrl/trending/all/day", [
            'api_key' => $apiKey,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Unable to fetch trending data'], 500);
    }
}
