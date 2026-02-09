<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\ShopProductController as AdminShopProductController;
use App\Http\Controllers\Admin\HomeServiceController as AdminHomeServiceController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;

use App\Http\Controllers\{
    HomeController,
    BlogController,
    OrderController,
    PanelOrderController,
    PurchasingController,
    TrackingController,
    UserClientController,
    AdminNotificationController,
};
use App\Http\Controllers\Admin\TrialClickController;
use Illuminate\Support\Facades\Artisan;

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
        Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    });

    // Authenticated admins
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // === Everything that was previously under Route::middleware('admin') ===
        Route::post('clients/import', [UserClientController::class, 'import'])->name('clients.import');

        Route::get(
            'clients/export/facebook-csv',
            [UserClientController::class, 'exportFacebookCsv']
        )->name('clients.export.facebook');

        Route::delete('clients/bulk-delete', [UserClientController::class, 'bulkDelete'])->name('clients.bulkDelete');
        Route::delete('orders/bulk-delete', [OrderController::class, 'bulkDelete'])->name('orders.bulkDelete');
        Route::delete('reseller-orders/bulk-delete', [PanelOrderController::class, 'bulkDelete'])->name('reseller-orders.bulkDelete');
        Route::delete('purchasing/bulk-delete', [PurchasingController::class, 'bulkDelete'])->name('purchasing.bulkDelete');

        Route::resource('clients', UserClientController::class);

        Route::resource('orders', OrderController::class);
        Route::delete('orders/{order}/pictures/{picture}', [OrderController::class, 'destroyPicture'])
            ->name('orders.pictures.destroy');

        Route::resource('panel-orders', PanelOrderController::class);

        Route::resource('purchasing', PurchasingController::class);
        Route::delete('purchasing/{purchasing}/pictures/{picture}', [PurchasingController::class, 'destroyPicture'])
            ->name('purchasing.pictures.destroy');

        Route::post('orders/bulk-action', [OrderController::class, 'bulkAction'])->name('orders.bulkAction');
        Route::post('panel-orders/bulk-action', [PanelOrderController::class, 'bulkAction'])
            ->name('reseller-orders.bulkAction');

        Route::post('orders/{order}/mark-messaged', [OrderController::class, 'markOneMessaged'])
            ->name('orders.markOneMessaged');

        Route::post('locale', [DashboardController::class, 'setLocale'])->name('locale');
        Route::resource('blogs', AdminBlogController::class);
        Route::post('blogs/{blog}/publish', [AdminBlogController::class, 'publish'])->name('blogs.publish');
        Route::post('blogs/{blog}/archive', [AdminBlogController::class, 'archive'])->name('blogs.archive');
        Route::post('blogs/{blog}/duplicate', [AdminBlogController::class, 'duplicate'])->name('blogs.duplicate');

        Route::resource('shop-products', AdminShopProductController::class);
        Route::resource('home-services', AdminHomeServiceController::class);
        Route::resource('testimonials', AdminTestimonialController::class);

        // Notifications
        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])
            ->name('notifications.read');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])
            ->name('notifications.readAll');

        Route::get('/trial-clicks', [TrialClickController::class, 'index'])->name('trial_clicks.index');
        Route::get('/trial-clicks/export', [TrialClickController::class, 'export'])->name('trial_clicks.export');
        Route::delete('/trial-clicks/bulk-delete', [TrialClickController::class, 'bulkDelete'])->name('trial_clicks.bulkDelete');
        Route::delete('/trial-clicks/{trialClick}', [TrialClickController::class, 'destroy'])->name('trial_clicks.destroy');
    });
});

// ---------------------------
// Public (localized) routes
// ---------------------------
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
        Route::get('movies',          [HomeController::class, 'movies'])->name('movies')->middleware('noindex.pagination');
        Route::get('packages',        [HomeController::class, 'packages'])->name('packages');
        Route::get('reseller-panel',  [HomeController::class, 'resellerPanel'])->name('reseller-panel');
        Route::get('iptv-applications', [HomeController::class, 'iptvApplications'])->name('iptv-applications');
        Route::get('shop', [HomeController::class, 'shop'])->name('shop');
        Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
        Route::get('blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

        Route::post('send-email',     [HomeController::class, 'send'])->name('contact.send');
        Route::post('subscribe',      [HomeController::class, 'subscribe'])->name('subscribe');
        Route::get('trending',        [HomeController::class, 'getTrending']);
        Route::get('redirect',        [HomeController::class, 'redirect'])->name('redirect.ad');

        Route::view('terms-of-service', 'policies.terms')->name('terms-of-service');
        Route::view('privacy-policy', 'policies.privacy')->name('privacy-policy');
        Route::view('refund-policy', 'policies.refund')->name('refund-policy');

        Route::get('configure',        [HomeController::class, 'configure'])->name('configure');
        Route::get('checkout',         [HomeController::class, 'checkoutStep1'])->name('checkout');
        Route::post('checkout/payment', [HomeController::class, 'checkoutStep2'])->name('step2');
        Route::get('thank-you', [HomeController::class, 'thankYou'])->name('thankyou');
    }
);

Route::post('/track/whatsapp-trial', [TrackingController::class, 'whatsappTrial'])
    ->name('track.whatsapp.trial')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('maintenance/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');

    return back()->with('success', 'Caches cleared successfully.');
})->name('maintenance.clear');
