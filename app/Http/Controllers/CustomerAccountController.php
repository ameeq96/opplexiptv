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
                    return back()->withErrors(['email' => 'Account email is temporarily unavailable. Please try again shortly.'])->withInput();
                }
            }
        }

        return redirect()->route('customer.login')->with('status', $status);
    }

    public function access(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:191'], 'token' => ['required', 'string', 'size:64']]);
        $action = URL::temporarySignedRoute('customer.access.consume', \Illuminate\Support\Carbon::createFromTimestamp((int) $request->query('expires')), [
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
            return redirect()->to($intended);
        }
        return redirect()->route('customer.account');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['customer_verified_email', 'customer_intended_path']);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();
        return redirect()->route('customer.login')->with('status', 'You have signed out of your customer account.');
    }

    public function dashboard(Request $request)
    {
        $customer = $request->user('web');
        $orders = $customer->orders()->latest('id')->paginate(12);
        $orderStates = $orders->getCollection()->mapWithKeys(fn ($order) => [$order->id => $this->orderState($order)]);
        $activeCount = $customer->orders()->where('status', 'active')->where('payment_status', 'paid')
            ->where(function ($query) { $query->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay()); })->count();
        $pendingCount = $customer->orders()->where('status', 'pending')->where('payment_status', 'paid')
            ->where(function ($query) { $query->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay()); })->count();
        $openTickets = $customer->customerConversations()->where('channel', 'portal')->where('property_id', 'customer-account')
            ->whereIn('status', ['open', 'in_progress'])->count();
        $trials = $customer->trialClicks()->whereNotNull('trial_owner_verified_at')->where('intent', 'trial')->latest('id')->limit(10)->get();
        $trialPackages = Package::query()->where('active', true)->where('is_available', true)->where('type', 'iptv')
            ->where('free_trial_hours', '>', 0)->sorted()->get()
            ->filter(fn (Package $package) => $package->isSelectableIptvProvider())->values();

        return view('pages.account.dashboard', compact('customer', 'orders', 'orderStates', 'activeCount', 'pendingCount', 'openTickets', 'trials', 'trialPackages'));
    }

    public function order(Request $request, int $order)
    {
        $order = $request->user('web')->orders()->findOrFail($order);
        $state = $this->orderState($order);
        $package = $order->package_id ? Package::find($order->package_id) : null;
        $renewable = $package && $package->active && $package->is_available && $package->price_amount > 0;
        $invoiceUrl = $this->invoiceUrl($order);
        return view('pages.account.order', compact('order', 'state', 'renewable', 'invoiceUrl'));
    }

    public function renew(Request $request, int $order)
    {
        $order = $request->user('web')->orders()->findOrFail($order);
        $package = $order->package_id ? Package::find($order->package_id) : null;
        if (! $package || ! $package->active || ! $package->is_available || $package->price_amount <= 0) {
            return redirect()->route('customer.orders.show', $order->id)->withErrors(['renewal' => 'This plan is not currently available for renewal. Please choose a plan or contact support.']);
        }
        if (! $this->checkoutOrigin()) {
            return redirect()->route('customer.orders.show', $order->id)->withErrors(['renewal' => 'Checkout is unavailable in this environment. No payment has been started.']);
        }
        return redirect()->to(URL::temporarySignedRoute('packages.purchase', now()->addMinutes(10), ['package' => $package->id]));
    }

    public function preferences(Request $request)
    {
        $data = $request->validate(['renewal_email_enabled' => ['required', 'boolean']]);
        $customer = $request->user('web');
        $customer->forceFill(['renewal_email_enabled' => (bool) $data['renewal_email_enabled'], 'renewal_email_updated_at' => now()])->save();
        if (! $customer->renewal_email_enabled) {
            $this->cancelRenewalEmails($customer);
        }
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
        return view('pages.account.unsubscribe', ['confirmed' => $confirmed, 'action' => $request->fullUrl()]);
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
        return redirect()->route('customer.account')->with('status', 'Trial request recorded. Your trial clock starts only after support confirms activation.');
    }

    public function trialPlan(Request $request, int $trial)
    {
        $trial = $request->user('web')->trialClicks()->whereNotNull('trial_owner_verified_at')->where('intent', 'trial')->findOrFail($trial);
        $package = $trial->trial_package_id ? Package::find($trial->trial_package_id) : null;
        if (! $package || ! $package->active || ! $package->is_available || $package->price_amount <= 0 || ! $this->checkoutOrigin()) {
            return redirect()->route('customer.account')->withErrors(['trial' => 'This paid plan is not available here. Please choose a plan or contact support.']);
        }
        return redirect()->to(URL::temporarySignedRoute('packages.purchase', now()->addMinutes(10), ['package' => $package->id]));
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
