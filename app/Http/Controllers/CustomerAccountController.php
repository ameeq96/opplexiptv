<?php

namespace App\Http\Controllers;

use App\Mail\CustomerAccountAccessMail;
use App\Models\CustomerExternalLink;
use App\Models\MarketingDelivery;
use App\Models\Order;
use App\Models\Package;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAccountController extends Controller
{
    public function login()
    {
        return view('pages.account.login');
    }

    public function session(Request $request)
    {
        $customer = $request->user('web');
        $verified = $customer && $customer->email_verified_at
            && hash_equals(hash('sha256', User::normalizeEmail($customer->email)), (string) $request->session()->get('customer_verified_email', ''));
        return response()->json(['data' => [
            'authenticated' => (bool) $verified,
            'customer' => $verified ? ['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email] : null,
            'csrf_token' => csrf_token(),
        ]]);
    }

    public function requestAccess(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:191']]);
        $email = User::normalizeEmail($data['email']);
        $key = 'customer-access:'.hash('sha256', $email);
        $status = 'If this email can receive account access, a private sign-in link has been sent. Check your inbox and spam folder.';

        if (! RateLimiter::tooManyAttempts($key, 1)) {
            RateLimiter::hit($key, 60);
            $users = User::query()->where('email_normalized', $email)
                ->orWhereRaw('LOWER(TRIM(email)) = ?', [$email])->limit(2)->get();
            if ($users->count() < 2) {
                $user = $users->first() ?: User::firstOrCreate(['email' => $email], [
                    'name' => 'Customer', 'password' => Str::random(64),
                ]);
                try {
                    $token = Password::broker('users')->createToken($user);
                    $accessUrl = URL::temporarySignedRoute('customer.access', now()->addMinutes(15), [
                        'email' => $email, 'token' => $token,
                    ]);
                    $mailer = app()->environment('local') ? 'log' : config('mail.default');
                    Mail::mailer($mailer)->to($email)->send(new CustomerAccountAccessMail($accessUrl));
                } catch (\Throwable $exception) {
                    report($exception);
                    if ($request->routeIs('api.browser.*')) {
                        return response()->json(['message' => 'Account email is temporarily unavailable. Please try again shortly.'], 503);
                    }
                    return back()->withErrors(['email' => 'Account email is temporarily unavailable. Please try again shortly.'])->withInput();
                }
            }
        }

        if ($request->routeIs('api.browser.*')) return response()->json(['message' => $status]);
        return redirect()->route('customer.login')->with('status', $status);
    }

    public function access(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:191'], 'token' => ['required', 'string', 'size:64']]);
        $action = URL::temporarySignedRoute('api.browser.customer.access.consume', \Illuminate\Support\Carbon::createFromTimestamp((int) $request->query('expires')), [
            'email' => $request->query('email'), 'token' => $request->query('token'),
        ]);
        return view('pages.account.access', compact('action'));
    }

    public function consumeAccess(Request $request)
    {
        $data = validator($request->query(), ['email' => ['required', 'email', 'max:191'], 'token' => ['required', 'string', 'size:64']])->validate();
        $email = User::normalizeEmail($data['email']);
        $user = DB::transaction(function () use ($email, $data) {
            $matches = User::query()->where('email_normalized', $email)
                ->orWhereRaw('LOWER(TRIM(email)) = ?', [$email])->lockForUpdate()->limit(2)->get();
            $user = $matches->count() === 1 ? $matches->first() : null;
            if (! $user || ! Password::broker('users')->tokenExists($user, $data['token'])) {
                throw ValidationException::withMessages(['access' => 'This sign-in link has expired or has already been used. Request a new link.'])->redirectTo(route('customer.login'));
            }
            Password::broker('users')->deleteToken($user);
            $user->forceFill(['email_verified_at' => now()])->save();
            return $user;
        }, 3);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put('customer_verified_email', hash('sha256', $email));
        $intended = $request->session()->pull('customer_intended_path');
        if (is_string($intended) && preg_match('~^/account(?:/|$)~', $intended)
            && ! str_contains($intended, '\\') && ! preg_match('/[\r\n]/', $intended)) {
            if ($request->routeIs('api.browser.*')) return response()->json(['redirect_url' => $intended]);
            return redirect()->to($intended);
        }
        if ($request->routeIs('api.browser.*')) return response()->json(['redirect_url' => route('customer.account')]);
        return redirect()->route('customer.account');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['customer_verified_email', 'customer_intended_path']);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();
        if ($request->routeIs('api.browser.*')) return response()->json(['message' => 'You have signed out of your customer account.', 'redirect_url' => route('customer.login')]);
        return redirect()->route('customer.login')->with('status', 'You have signed out of your customer account.');
    }

    public function dashboard(Request $request)
    {
        return view('pages.account.dashboard');
    }

    public function overview(Request $request)
    {
        $customer = $request->user('web');
        $activeCount = $customer->orders()->where('status', 'active')->where('payment_status', 'paid')
            ->where(function ($query) { $query->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay()); })->count();
        $pendingCount = $customer->orders()->where('status', 'pending')->where('payment_status', 'paid')
            ->where(function ($query) { $query->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay()); })->count();
        $openTickets = $customer->customerConversations()->where('channel', 'portal')->where('property_id', 'customer-account')
            ->whereIn('status', ['open', 'in_progress'])->count();
        return response()->json(['data' => [
            'customer' => ['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email, 'renewal_email_enabled' => $customer->wantsRenewalEmail()],
            'active_count' => $activeCount, 'pending_count' => $pendingCount, 'open_tickets' => $openTickets,
        ]]);
    }

    public function orders(Request $request)
    {
        $request->validate(['page' => ['nullable', 'integer', 'min:1', 'max:1000000']]);
        $orders = $request->user('web')->orders()->latest('id')->paginate(12);
        return response()->json(['data' => $orders->getCollection()->map(fn (Order $order) => $this->orderData($order)),
            'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()]]);
    }

    public function trials(Request $request)
    {
        $customer = $request->user('web');
        $trials = $customer->trialClicks()->whereNotNull('trial_owner_verified_at')->where('intent', 'trial')->latest('id')->limit(10)->get();
        return response()->json(['data' => $trials->map(fn (TrialClick $trial) => [
            'id' => $trial->id, 'package_name' => $trial->package_name, 'reference' => $trial->lead_code,
            'status' => $trial->trial_status, 'started_at' => $trial->trial_started_at?->toIso8601String(),
            'expires_at' => $trial->trial_expires_at?->toIso8601String(),
            'remaining_minutes' => $trial->trial_status === 'active' ? max(0, (int) ceil(now()->diffInMinutes($trial->trial_expires_at, false))) : null,
            'plan_url' => $trial->trial_package_id ? route('api.browser.customer.trials.plan', $trial->id) : null,
        ])]);
    }

    public function trialPackages()
    {
        $trialPackages = Package::query()->where('active', true)->where('is_available', true)->where('type', 'iptv')
            ->where('free_trial_hours', '>', 0)->sorted()->get()
            ->filter(fn (Package $package) => $package->isSelectableIptvProvider())->values();
        return response()->json(['data' => $trialPackages->map(fn (Package $package) => [
            'id' => $package->id, 'title' => $package->title, 'vendor' => $package->vendor, 'free_trial_hours' => $package->free_trial_hours,
        ])]);
    }

    public function order(Request $request, int $order)
    {
        if (! $request->routeIs('api.browser.*')) {
            abort_unless($request->user('web')->orders()->whereKey($order)->exists(), 404);
            return view('pages.account.order', ['orderId' => $order]);
        }
        $order = $request->user('web')->orders()->findOrFail($order);
        $package = $order->package_id ? Package::find($order->package_id) : null;
        $renewable = $package && $package->active && $package->is_available && $package->price_amount > 0;
        $invoiceUrl = $this->invoiceUrl($order);
        return response()->json(['data' => $this->orderData($order) + [
            'renewable' => (bool) $renewable, 'invoice_url' => $invoiceUrl,
            'renew_url' => route('api.browser.customer.orders.renew', $order->id),
        ]]);
    }

    public function renew(Request $request, int $order)
    {
        $order = $request->user('web')->orders()->findOrFail($order);
        $package = $order->package_id ? Package::find($order->package_id) : null;
        if (! $package || ! $package->active || ! $package->is_available || $package->price_amount <= 0) {
            if ($request->routeIs('api.browser.*')) throw ValidationException::withMessages(['renewal' => 'This plan is not currently available for renewal. Please choose a plan or contact support.']);
            return redirect()->route('customer.orders.show', $order->id)->withErrors(['renewal' => 'This plan is not currently available for renewal. Please choose a plan or contact support.']);
        }
        if (! $this->checkoutOrigin()) {
            if ($request->routeIs('api.browser.*')) return response()->json(['message' => 'Checkout is unavailable in this environment. No payment has been started.'], 503);
            return redirect()->route('customer.orders.show', $order->id)->withErrors(['renewal' => 'Checkout is unavailable in this environment. No payment has been started.']);
        }
        $url = URL::temporarySignedRoute('packages.purchase', now()->addMinutes(10), ['package' => $package->id]);
        if ($request->routeIs('api.browser.*')) return response()->json(['redirect_url' => $url]);
        return redirect()->to($url);
    }

    public function preferences(Request $request)
    {
        $data = $request->validate(['renewal_email_enabled' => ['required', 'boolean']]);
        $customer = $request->user('web');
        $customer->forceFill(['renewal_email_enabled' => (bool) $data['renewal_email_enabled'], 'renewal_email_updated_at' => now()])->save();
        if (! $customer->renewal_email_enabled) {
            $this->cancelRenewalEmails($customer);
        }
        if ($request->routeIs('api.browser.*')) return response()->json(['message' => 'Expiry reminder preference saved. Your other marketing preferences have not changed.', 'data' => ['renewal_email_enabled' => $customer->renewal_email_enabled]]);
        return redirect()->route('customer.account')->with('status', 'Expiry reminder preference saved. Your other marketing preferences have not changed.');
    }

    public function unsubscribe(Request $request)
    {
        $data = validator($request->query(), ['user' => ['required', 'integer', 'min:1']])->validate();
        $confirmed = false;
        if ($request->isMethod('post')) {
            $customer = User::findOrFail($data['user']);
            $customer->forceFill(['renewal_email_enabled' => false, 'renewal_email_updated_at' => now()])->save();
            $this->cancelRenewalEmails($customer);
            $confirmed = true;
        }
        if ($request->routeIs('api.browser.*')) return response()->json(['message' => 'Expiry reminder emails are now turned off. Your service and other notification preferences are unchanged.', 'data' => ['confirmed' => $confirmed]]);
        $action = URL::signedRoute('api.browser.customer.renewal.unsubscribe', ['user' => $data['user']],
            $request->query('expires') ? \Illuminate\Support\Carbon::createFromTimestamp((int) $request->query('expires')) : null);
        return view('pages.account.unsubscribe', ['confirmed' => $confirmed, 'action' => $action]);
    }

    public function requestTrial(Request $request)
    {
        $data = $request->validate(['package_id' => ['required', 'integer'], 'submission_id' => ['required', 'uuid']]);
        $package = Package::query()->whereKey($data['package_id'])->where('type', 'iptv')->where('active', true)
            ->where('is_available', true)->where('free_trial_hours', '>', 0)->firstOrFail();
        abort_unless($package->isSelectableIptvProvider(), 404);
        DB::transaction(function () use ($request, $data, $package) {
            $customer = User::query()->whereKey($request->user('web')->id)->lockForUpdate()->firstOrFail();
            $exists = $customer->trialClicks()->whereNotNull('trial_owner_verified_at')->where('intent', 'trial')
                ->where(function ($query) use ($data, $package) {
                    $query->where('event_id', $data['submission_id'])->orWhere(function ($pending) use ($package) {
                        $pending->where('trial_package_id', $package->id)->where(function ($dates) {
                            $dates->whereNull('trial_expires_at')->orWhere('trial_expires_at', '>', now());
                        });
                    });
                })->exists();
            if (! $exists) {
                TrialClick::create([
                    'event_id' => $data['submission_id'], 'user_id' => $customer->id,
                    'contact_name' => $customer->name, 'contact_email' => $customer->email,
                    'intent' => 'trial', 'placement' => 'customer_portal', 'status' => 'new',
                    'package_name' => $package->title, 'vendor' => $package->vendor,
                    'trial_package_id' => $package->id, 'trial_owner_verified_at' => now(),
                ]);
            }
        }, 3);
        if ($request->routeIs('api.browser.*')) return response()->json(['message' => 'Trial request recorded. Your trial clock starts only after support confirms activation.']);
        return redirect()->route('customer.account')->with('status', 'Trial request recorded. Your trial clock starts only after support confirms activation.');
    }

    public function trialPlan(Request $request, int $trial)
    {
        $trial = $request->user('web')->trialClicks()->whereNotNull('trial_owner_verified_at')->where('intent', 'trial')->findOrFail($trial);
        $package = $trial->trial_package_id ? Package::find($trial->trial_package_id) : null;
        if (! $package || ! $package->active || ! $package->is_available || $package->price_amount <= 0 || ! $this->checkoutOrigin()) {
            if ($request->routeIs('api.browser.*')) throw ValidationException::withMessages(['trial' => 'This paid plan is not available here. Please choose a plan or contact support.']);
            return redirect()->route('customer.account')->withErrors(['trial' => 'This paid plan is not available here. Please choose a plan or contact support.']);
        }
        $url = URL::temporarySignedRoute('packages.purchase', now()->addMinutes(10), ['package' => $package->id]);
        if ($request->routeIs('api.browser.*')) return response()->json(['redirect_url' => $url]);
        return redirect()->to($url);
    }

    private function orderData(Order $order): array
    {
        return [
            'id' => $order->id, 'package' => $order->package, 'status' => $order->status,
            'payment_status' => $order->payment_status, 'state' => $this->orderState($order),
            'buying_date' => $order->buying_date?->format('Y-m-d'), 'expiry_date' => $order->expiry_date?->format('Y-m-d'),
            'paid_at' => $order->payment_status === 'paid' ? $order->paid_at?->toIso8601String() : null,
            'paid_amount' => $order->payment_status === 'paid' ? $order->paid_amount : null,
            'paid_currency' => $order->paid_currency ?: $order->currency,
            'remaining_days' => $order->status === 'active' && $order->payment_status === 'paid' && $order->expiry_date
                ? max(0, (int) ceil(now()->diffInDays($order->expiry_date->copy()->endOfDay(), false))) : null,
            'url' => route('customer.orders.show', $order->id),
        ];
    }

    private function cancelRenewalEmails(User $customer): void
    {
        MarketingDelivery::query()->where('user_id', $customer->id)->where('workflow', 'renewal')->where('channel', 'email')
            ->whereNull('sent_at')->whereNull('failed_at')->update(['failed_at' => now(), 'last_error' => 'Expiry reminder preference withdrawn.']);
    }

    private function orderState(Order $order): array
    {
        if ($order->payment_status === 'test_paid') return ['label' => 'Test payment only', 'tone' => 'neutral', 'step' => 0];
        if ($order->payment_status !== 'paid') return ['label' => 'Payment not confirmed', 'tone' => 'warning', 'step' => 0];
        if ($order->status === 'expired' || ($order->expiry_date && $order->expiry_date->lt(now()->startOfDay()))) {
            return ['label' => 'Service expired', 'tone' => 'neutral', 'step' => 3];
        }
        if ($order->status === 'active') return ['label' => 'Service active', 'tone' => 'success', 'step' => 3];
        if ($order->status === 'pending') return ['label' => 'Activation pending', 'tone' => 'warning', 'step' => 2];
        return ['label' => 'Contact support for service status', 'tone' => 'neutral', 'step' => 1];
    }

    private function checkoutOrigin(): ?string
    {
        $origin = rtrim((string) config('services.opplexify.url'), '/');
        $parts = parse_url($origin);
        if (! is_array($parts) || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
            || ! empty($parts['path'])) return null;
        $host = strtolower($parts['host'] ?? '');
        $local = in_array($host, ['127.0.0.1', 'localhost', '[::1]'], true);
        if (app()->environment('local')) return $local && in_array($parts['scheme'] ?? '', ['http', 'https'], true) ? $origin : null;
        if (app()->environment('testing') && $local) return $origin;
        return ($parts['scheme'] ?? '') === 'https' && in_array($host, ['opplexify.com', 'www.opplexify.com'], true) ? $origin : null;
    }

    private function invoiceUrl(Order $order): ?string
    {
        if ($order->payment_status !== 'paid' || $order->payment_provider !== 'safepay' || ! ($origin = $this->checkoutOrigin())) return null;
        $links = CustomerExternalLink::query()->where('source', 'opplexify')->where('entity_type', 'streaming_order')
            ->where('user_id', $order->user_id)->where('order_id', $order->id)->get();
        $references = [];
        foreach ($links as $link) {
            foreach (['payment', 'invoice', 'checkout'] as $group) {
                $reference = $link->state[$group]['data']['reference'] ?? null;
                if (is_string($reference) && preg_match('/^[A-Za-z0-9_-]{16,128}$/', $reference)) $references[] = $reference;
            }
        }
        $references = array_values(array_unique($references));
        return count($references) === 1 ? $origin.'/public/safepay/invoice/'.rawurlencode($references[0]) : null;
    }
}
