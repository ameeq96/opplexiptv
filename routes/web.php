<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\ShopProductController as AdminShopProductController;
use App\Http\Controllers\Admin\HomeServiceController as AdminHomeServiceController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\ChannelLogoController as AdminChannelLogoController;
use App\Http\Controllers\Admin\MenuItemController as AdminMenuItemController;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PricingSectionController as AdminPricingSectionController;
use App\Http\Controllers\Admin\FooterSettingController as AdminFooterSettingController;
use App\Http\Controllers\Admin\FooterLinkController as AdminFooterLinkController;
use App\Http\Controllers\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Admin\ProductHubController as AdminProductHubController;
use App\Http\Controllers\Admin\DigitalCommerce\DigitalCategoryController as AdminDigitalCategoryController;
use App\Http\Controllers\Admin\DigitalCommerce\DigitalProductController as AdminDigitalProductController;
use App\Http\Controllers\Admin\DigitalCommerce\DigitalDeliveryPayloadController as AdminDigitalDeliveryPayloadController;
use App\Http\Controllers\Admin\DigitalCommerce\DigitalOrderController as AdminDigitalOrderController;

use App\Http\Controllers\{
    HomeController,
    BlogController,
    OrderController,
    PanelOrderController,
    PurchasingController,
    TrackingController,
    UserClientController,
    AdminNotificationController,
    CustomerOrderStatusController,
    CheckoutDraftController,
    ReferralController,
    EventPromotionController,
    MarketingUnsubscribeController,
    TrackingConsentController,
};
use App\Http\Controllers\DigitalCommerce\ProductController as DigitalProductController;
use App\Http\Controllers\DigitalCommerce\CartController as DigitalCartController;
use App\Http\Controllers\DigitalCommerce\CheckoutController as DigitalCheckoutController;
use App\Http\Controllers\DigitalCommerce\CustomerOrderController as DigitalCustomerOrderController;
use App\Http\Controllers\Admin\TrialClickController;
use App\Http\Middleware\AuditAdminAction;
use App\Http\Middleware\EnsureAdminPasswordChanged;
use App\Http\Middleware\EnsureAdminSessionIsCurrent;
use App\Http\Middleware\EnsureAdminTwoFactorAuthenticated;
use App\Http\Middleware\EnsureRecentAdminConfirmation;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ---------------------------
// Admin Area (prefixed + named)
// ---------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Admin dashboard landing
    Route::get('/', function () {
        return auth('admin')->check()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('admin.login');
    });

    // Guests (not logged in as admin)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    });

    // Authenticated admins
    Route::middleware(['auth:admin', EnsureAdminSessionIsCurrent::class, AuditAdminAction::class])->group(function () {

        Route::get('two-factor-challenge', [TwoFactorController::class, 'showChallenge'])
            ->name('two-factor.challenge');

        Route::get('password/change', [AuthController::class, 'showPasswordChange'])
            ->name('password.change');

        Route::middleware(EnsureAdminPasswordChanged::class)->group(function () {
            Route::get('security/two-factor', [TwoFactorController::class, 'settings'])
                ->name('two-factor.settings');

            Route::middleware([EnsureAdminTwoFactorAuthenticated::class, 'admin.access'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // === Everything that was previously under Route::middleware('admin') ===

        Route::get(
            'clients/export/facebook-csv',
            [UserClientController::class, 'exportFacebookCsv']
        )->name('clients.export.facebook');


        Route::get('clients/export/crm', [UserClientController::class, 'exportCrm'])->name('clients.export.crm');
        Route::get('clients/unlinked', [UserClientController::class, 'unlinked'])->name('clients.unlinked');
        Route::resource('clients', UserClientController::class)->only(['index', 'create', 'show', 'edit']);

        Route::resource('orders', OrderController::class)->only(['index', 'create', 'show', 'edit']);
        Route::get('orders/{order}/pictures/{picture}', [OrderController::class, 'showPicture'])
            ->name('orders.pictures.show');

        Route::resource('panel-orders', PanelOrderController::class)->only(['index', 'create', 'show', 'edit']);

        Route::resource('purchasing', PurchasingController::class)->only(['index', 'create', 'show', 'edit']);
        Route::get('purchasing/{purchasing}/pictures/{picture}', [PurchasingController::class, 'showPicture'])
            ->name('purchasing.pictures.show');



        Route::resource('blogs', AdminBlogController::class)->only(['index', 'create', 'show', 'edit']);

        Route::resource('shop-products', AdminShopProductController::class)->only(['index', 'create', 'show', 'edit']);
        Route::get('products', [AdminProductHubController::class, 'index'])->name('products.index');
        Route::resource('home-services', AdminHomeServiceController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('testimonials', AdminTestimonialController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('channel-logos', AdminChannelLogoController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('menu-items', AdminMenuItemController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('packages', AdminPackageController::class)->only(['index', 'create', 'show', 'edit']);
        Route::get('pricing-section', [AdminPricingSectionController::class, 'edit'])->name('pricing-section.edit');
        Route::get('footer-settings', [AdminFooterSettingController::class, 'edit'])->name('footer-settings.edit');
        Route::resource('footer-links', AdminFooterLinkController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('social-links', AdminSocialLinkController::class)->only(['index', 'create', 'show', 'edit']);
        Route::get('security/confirm', [TwoFactorController::class, 'showSensitiveConfirmation'])
            ->name('security.confirm');
        Route::middleware(EnsureRecentAdminConfirmation::class)->group(function () {
            Route::resource('admin-users', AdminUserController::class)->except('show')->only(['index', 'create', 'show', 'edit']);
        });
        Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');
        /* ----- Digital-commerce admin routes DISABLED (feature hidden) -----
        Route::resource('digital-categories', AdminDigitalCategoryController::class)->except(['show']);
        Route::resource('digital-products', AdminDigitalProductController::class)->except(['show']);
        Route::resource('digital-delivery-payloads', AdminDigitalDeliveryPayloadController::class)
            ->only(['index', 'create', 'store', 'destroy']);
        Route::get('digital-orders', [AdminDigitalOrderController::class, 'index'])->name('digital-orders.index');
        Route::get('digital-orders/{digital_order}', [AdminDigitalOrderController::class, 'show'])->name('digital-orders.show');
        Route::post('digital-orders/{digital_order}/mark-paid', [AdminDigitalOrderController::class, 'markPaid'])->name('digital-orders.mark-paid');
        Route::post('digital-orders/{digital_order}/mark-delivered', [AdminDigitalOrderController::class, 'markDelivered'])->name('digital-orders.mark-delivered');
        Route::post('digital-orders/{digital_order}/items/{item}/assign-delivery', [AdminDigitalOrderController::class, 'assignDelivery'])->name('digital-orders.assign-delivery');
        Route::post('digital-orders/{digital_order}/resend-email', [AdminDigitalOrderController::class, 'resendEmail'])->name('digital-orders.resend-email');
        ------------------------------------------------------------------- */

        Route::get('/trial-clicks', [TrialClickController::class, 'index'])->name('trial_clicks.index');
        Route::get('/trial-clicks/export', [TrialClickController::class, 'export'])->name('trial_clicks.export');

        Route::get('maintenance', [AdminMaintenanceController::class, 'index'])
            ->name('maintenance.index');
            });
        });
    });
});

Route::prefix('account')->name('customer.')->middleware(\App\Http\Middleware\EnsureCustomerAccount::class.':public')->group(function () {
    Route::get('login', [\App\Http\Controllers\CustomerAccountController::class, 'login'])->name('login');
    Route::get('access', [\App\Http\Controllers\CustomerAccountController::class, 'access'])
        ->middleware(['signed', 'throttle:30,1'])->name('access');
    Route::view('setup', 'pages.account.guides')->name('guides');

    Route::middleware(\App\Http\Middleware\EnsureCustomerAccount::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\CustomerAccountController::class, 'dashboard'])->name('account');
        Route::get('orders/{order}', [\App\Http\Controllers\CustomerAccountController::class, 'order'])->whereNumber('order')->name('orders.show');
        Route::get('orders/{order}/renew', [\App\Http\Controllers\CustomerAccountController::class, 'renew'])
            ->whereNumber('order')->middleware('throttle:10,1')->name('orders.renew');
        Route::get('trials/{trial}/plan', [\App\Http\Controllers\CustomerAccountController::class, 'trialPlan'])
            ->whereNumber('trial')->middleware('throttle:10,1')->name('trials.plan');
        Route::get('support', [\App\Http\Controllers\CustomerSupportController::class, 'index'])->name('support.index');
        Route::get('support/{conversation}', [\App\Http\Controllers\CustomerSupportController::class, 'show'])
            ->whereNumber('conversation')->name('support.show');
    });
});

// ---------------------------
// Public (localized) routes
// ---------------------------
Route::permanentRedirect('/index', '/');
Route::permanentRedirect('/index.html', '/');

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => [
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        ]
    ],
    function () {
        Route::get('/',               [HomeController::class, 'home'])->name('home');
        Route::get('about',           [HomeController::class, 'about'])->name('about');
        Route::get('faqs',            [HomeController::class, 'faq'])->name('faqs');
        Route::get('activate',        [HomeController::class, 'activate'])->name('activate');
        Route::get('activate-info',   [HomeController::class, 'activateInfo'])->name('activate-info');
        Route::get('contact',         [HomeController::class, 'contact'])->name('contact');
        Route::get('pricing',         [HomeController::class, 'pricing'])->name('pricing');
        Route::get('movies',          [HomeController::class, 'movies'])->name('movies');
        Route::get('packages',        [HomeController::class, 'packages'])->name('packages');
        Route::get('purchase/{package}', [HomeController::class, 'purchase'])
            ->whereNumber('package')
            ->middleware(['signed', 'throttle:30,1'])
            ->name('packages.purchase');
        Route::get('iptv-subscription-service', [HomeController::class, 'iptvSubscriptionService'])->name('iptv-subscription-service');
        Route::get('reseller-panel',  [HomeController::class, 'resellerPanel'])->name('reseller-panel');
        Route::get('iptv-applications', [HomeController::class, 'iptvApplications'])->name('iptv-applications');
        Route::get('shop', [HomeController::class, 'shop'])->name('shop');
        Route::get('product-share/{type}/{id}', [HomeController::class, 'sharedProduct'])->name('products.share');
        /* ----- Digital-commerce public routes DISABLED (feature hidden) -----
        Route::get('digital-shop', [DigitalProductController::class, 'index'])->name('digital.shop');
        Route::get('digital-shop/{slug}', [DigitalProductController::class, 'show'])->name('digital.product.show');
        Route::get('digital-cart', [DigitalCartController::class, 'index'])->name('digital.cart.index');
        Route::post('digital-cart/{digital_product}', [DigitalCartController::class, 'add'])->name('digital.cart.add')->middleware('throttle:40,1');
        Route::patch('digital-cart/{digital_product}', [DigitalCartController::class, 'update'])->name('digital.cart.update')->middleware('throttle:40,1');
        Route::delete('digital-cart/{digital_product}', [DigitalCartController::class, 'remove'])->name('digital.cart.remove')->middleware('throttle:40,1');
        Route::get('digital-checkout', [DigitalCheckoutController::class, 'show'])->name('digital.checkout.show');
        Route::post('digital-checkout', [DigitalCheckoutController::class, 'store'])->name('digital.checkout.store')->middleware('throttle:10,1');
        Route::get('digital-orders/access/{token}', [DigitalCustomerOrderController::class, 'access'])->name('digital.orders.access');
        Route::get('digital-orders', [DigitalCustomerOrderController::class, 'index'])->name('digital.orders.index');
        Route::get('digital-orders/{digital_order}', [DigitalCustomerOrderController::class, 'show'])->name('digital.orders.show');
        ------------------------------------------------------------------- */
        Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
        Route::get('blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

        Route::get('redirect',        [HomeController::class, 'redirect'])->name('redirect.ad');

        Route::view('terms-of-service', 'policies.terms')->name('terms-of-service');
        Route::view('privacy-policy', 'policies.privacy')->name('privacy-policy');
        Route::view('refund-policy', 'policies.refund')->name('refund-policy');

        Route::get('configure',        [HomeController::class, 'configure'])->name('configure');
        Route::get('checkout',         [HomeController::class, 'checkoutStep1'])->name('checkout');
        Route::get('thank-you', [HomeController::class, 'thankYou'])->name('thankyou');
        Route::get('refer/{code}', [ReferralController::class, 'capture'])
            ->where('code', '[A-Za-z0-9]+')
            ->name('referrals.capture');
    }
);

Route::get('/orders/{orderId}/status', [CustomerOrderStatusController::class, 'show'])
    ->whereNumber('orderId')
    ->middleware(['signed', 'throttle:30,1'])
    ->name('orders.status');


Route::get('/event-promotion/{delivery}/activate', EventPromotionController::class)
    ->whereNumber('delivery')
    ->middleware(['signed', 'throttle:20,1'])
    ->name('event-promotions.activate');
