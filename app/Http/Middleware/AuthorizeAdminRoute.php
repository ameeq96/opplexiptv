<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeAdminRoute
{
    private const COMMON_ROUTES = [
        'admin.locale',
    ];

    private const SALES_ROUTES = [
        'admin.dashboard',
        'admin.clients.index',
        'admin.clients.create',
        'admin.clients.store',
        'admin.clients.show',
        'admin.clients.edit',
        'admin.clients.update',
        'admin.clients.import',
        'admin.clients.export.facebook',
        'admin.clients.contact-number-update',
        'admin.orders.index',
        'admin.orders.create',
        'admin.orders.store',
        'admin.orders.show',
        'admin.orders.pictures.show',
        'admin.orders.edit',
        'admin.orders.update',
        'admin.orders.markOneMessaged',
        'admin.orders.verifyPayment',
        'admin.panel-orders.index',
        'admin.panel-orders.create',
        'admin.panel-orders.store',
        'admin.panel-orders.show',
        'admin.panel-orders.edit',
        'admin.panel-orders.update',
        'admin.trial_clicks.index',
        'admin.trial_clicks.export',
        'admin.trial_clicks.status',
        'admin.notifications.*',
    ];

    private const SUPPORT_ROUTES = [
        'admin.clients.index',
        'admin.clients.show',
        'admin.clients.edit',
        'admin.clients.update',
        'admin.orders.index',
        'admin.orders.show',
        'admin.orders.pictures.show',
        'admin.orders.markOneMessaged',
        'admin.panel-orders.index',
        'admin.panel-orders.show',
        'admin.trial_clicks.index',
        'admin.trial_clicks.status',
        'admin.notifications.*',
    ];

    private const CONTENT_ROUTES = [
        'admin.blogs.*',
        'admin.products.*',
        'admin.shop-products.*',
        'admin.home-services.*',
        'admin.testimonials.*',
        'admin.channel-logos.*',
        'admin.menu-items.*',
        'admin.packages.*',
        'admin.pricing-section.*',
        'admin.footer-settings.*',
        'admin.footer-links.*',
        'admin.social-links.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Admin|null $admin */
        $admin = $request->user('admin');

        if (! $admin) {
            abort(403);
        }

        if ($admin->isOwner()) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();

        if ($routeName === 'admin.dashboard') {
            return match ($admin->role) {
                Admin::ROLE_SALES => $next($request),
                Admin::ROLE_SUPPORT => redirect()->route('admin.clients.index'),
                Admin::ROLE_CONTENT => redirect()->route('admin.blogs.index'),
                default => abort(403),
            };
        }

        $allowedRoutes = match ($admin->role) {
            Admin::ROLE_SALES => self::SALES_ROUTES,
            Admin::ROLE_SUPPORT => self::SUPPORT_ROUTES,
            Admin::ROLE_CONTENT => self::CONTENT_ROUTES,
            default => [],
        };

        if ($this->matches($routeName, [...self::COMMON_ROUTES, ...$allowedRoutes])) {
            return $next($request);
        }

        abort(403);
    }

    private function matches(string $routeName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
