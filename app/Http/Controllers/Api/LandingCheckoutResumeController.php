<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LandingCheckoutResumeMail;
use App\Services\Api\PublicSiteDataService;
use App\Services\Clients\LandingInteractionService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LandingCheckoutResumeController extends Controller
{
    public function store(Request $request, PublicSiteDataService $site, LandingInteractionService $interactions)
    {
        abort_unless($request->isJson(), 415, 'Send a JSON request.');
        abort_if(strlen($request->getContent()) > 4096, 413, 'Request is too large.');
        $data = $request->validate([
            'brand' => ['required', Rule::in(array_keys(LandingInteractionService::BRANDS))],
            'package_id' => ['required', 'integer', 'min:1'],
            'locale' => ['required', Rule::in(config('app.locales', ['en']))],
            'email' => ['nullable', 'string', 'email:rfc', 'max:254'],
            'one_send_consent' => [Rule::requiredIf($request->filled('email')), 'boolean', Rule::when($request->filled('email'), 'accepted')],
            'website' => ['nullable', 'string', 'max:0'],
        ]);
        $interactions->assertOrigin($data['brand'], (string) $request->header('Origin'));
        $plan = $this->currentPlan(['brand' => $data['brand'], 'package_id' => (int) $data['package_id']], $site);
        abort_unless($plan, 422, 'This plan is not available for the selected brand.');
        $expires = now()->addDay();
        $selection = [
            'purpose' => 'landing-plan-resume-v1', 'brand' => $data['brand'],
            'package_id' => (int) $plan['id'], 'locale' => $data['locale'],
            'amount_minor' => (int) round((float) $plan['price_amount'] * 100),
            'currency' => $plan['currency'], 'exp' => $expires->timestamp, 'nonce' => Str::random(32),
        ];
        $token = rtrim(strtr(Crypt::encryptString(json_encode($selection, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $url = route('landing.resume.show', ['token' => $token]);
        $sent = false;
        if (!empty($data['email'])) {
            $recipient = strtolower(trim($data['email']));
            $key = 'landing-resume-mail:'.hash_hmac('sha256', $recipient, (string) config('app.key'));
            if (RateLimiter::tooManyAttempts($key, 1)) {
                return $this->privateResponse(response()->json(['message' => 'Please wait before requesting another email.'], 429));
            }
            RateLimiter::hit($key, 120);
            try {
                Mail::to($recipient)->send(new LandingCheckoutResumeMail($url, $this->ui($data['locale'])));
                $sent = true;
            } catch (\Throwable) {
                return $this->privateResponse(response()->json(['message' => 'Email is temporarily unavailable. Save a link without email or try again later.'], 503));
            }
        }

        return $this->privateResponse(response()->json(['ok' => true, 'data' => [
            'url' => $url, 'expires_at' => $expires->toIso8601String(), 'email_sent' => $sent,
        ]], 201));
    }

    public function show(Request $request, string $token, PublicSiteDataService $site)
    {
        $selection = $this->decode($token);
        $locale = in_array($selection['locale'] ?? null, config('app.locales', ['en']), true) ? $selection['locale'] : 'en';
        $plan = $this->currentPlan($selection, $site);
        $brand = $selection && isset(LandingInteractionService::BRANDS[$selection['brand'] ?? ''])
            ? LandingInteractionService::BRANDS[$selection['brand']] : null;

        return $this->privateResponse(response()->view('pages.checkout.resume', [
            'ui' => $this->ui($locale), 'locale' => $locale, 'plan' => $plan, 'brand' => $brand,
            'priceChanged' => $plan && ((int) round((float) $plan['price_amount'] * 100) !== $selection['amount_minor'] || $plan['currency'] !== $selection['currency']),
            'confirmUrl' => $plan ? route('api.browser.landing.resume.confirm', ['token' => $token]) : null,
        ], $plan ? 200 : 410));
    }

    public function confirm(Request $request, string $token, PublicSiteDataService $site)
    {
        $selection = $this->decode($token);
        $plan = $this->currentPlan($selection, $site);
        if (!$plan) {
            return $this->privateResponse(response()->json(['message' => 'This saved-plan link is invalid, expired or unavailable.'], 410));
        }
        $request->validate([
            'confirm_selection' => ['required', 'accepted'],
            'amount_minor' => ['required', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
        ]);
        if ((int) $request->input('amount_minor') !== (int) round((float) $plan['price_amount'] * 100)
            || $request->input('currency') !== $plan['currency']) {
            return $this->privateResponse(response()->json(['message' => 'The plan price changed. Reload this page and review the current price.'], 409));
        }
        $source = ['trex' => 'trexiptv8k', 'opplex' => 'opplexiptv-store', 'geo' => 'geoiptv', '5g' => '5gliveiptv',
            'iptvsubscription' => 'iptvsubscription', 'getiptv' => 'getiptv'][$selection['brand']];
        $url = route('checkout', ['package_id' => $plan['id'], 'utm_source' => $source, 'utm_medium' => 'landing']);

        return $this->privateResponse(response()->json(['redirect_url' => $url]));
    }

    private function decode(string $token): ?array
    {
        if (!preg_match('/^[A-Za-z0-9_-]{80,4096}$/D', $token)) return null;
        try {
            $data = json_decode(Crypt::decryptString(strtr($token, '-_', '+/')), true, 16, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return null;
        }
        if (!is_array($data) || ($data['purpose'] ?? null) !== 'landing-plan-resume-v1'
            || !isset(LandingInteractionService::BRANDS[$data['brand'] ?? ''])
            || !is_int($data['package_id'] ?? null) || $data['package_id'] <= 0
            || !in_array($data['locale'] ?? null, config('app.locales', ['en']), true)
            || !is_int($data['exp'] ?? null) || $data['exp'] <= now()->timestamp || $data['exp'] > now()->addDay()->timestamp
            || !is_int($data['amount_minor'] ?? null) || $data['amount_minor'] <= 0
            || !is_string($data['currency'] ?? null) || !preg_match('/^[A-Z]{3}$/D', $data['currency'])
            || !is_string($data['nonce'] ?? null) || !preg_match('/^[A-Za-z0-9]{32}$/D', $data['nonce'])) return null;

        return $data;
    }

    private function currentPlan(?array $selection, PublicSiteDataService $site): ?array
    {
        if (!$selection) return null;
        $plan = collect($site->landingPlans($selection['brand'])['packages'])->firstWhere('id', $selection['package_id']);
        if (!$plan) return null;
        $currency = strtoupper((string) config('services.app.default_currency', 'USD'));
        $plan['currency'] = preg_match('/^[A-Z]{3}$/D', $currency) ? $currency : 'USD';
        return $plan;
    }

    private function ui(string $locale): array
    {
        $document = json_decode(file_get_contents(resource_path('content/landing-pages/'.$locale.'.json')), true, 64, JSON_THROW_ON_ERROR);
        return $document['experience']['ui'] ?? [];
    }

    private function privateResponse($response)
    {
        return $response->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
