<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\{
    HomeController,
    OrderController,
    PanelOrderController,
    PurchasingController,
    TrackingController,
    UserClientController,
};
use App\Http\Controllers\Admin\TrialClickController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ---------------------------
// Admin Area (prefixed + named)
// ---------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Optional: keep /admin -> login (like your old setup)
    Route::get('/', fn() => redirect()->route('admin.login'));

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
        Route::get('buy-now-panel',   [HomeController::class, 'buynowpanel'])->name('buy-now-panel');
        Route::get('contact',         [HomeController::class, 'contact'])->name('contact');
        Route::get('buynow',          [HomeController::class, 'buynow'])->name('buynow');
        Route::get('pricing',         [HomeController::class, 'pricing'])->name('pricing');
        Route::get('movies',          [HomeController::class, 'movies'])->name('movies')->middleware('noindex.pagination');
        Route::get('packages',        [HomeController::class, 'packages'])->name('packages');
        Route::get('reseller-panel',  [HomeController::class, 'resellerPanel'])->name('reseller-panel');
        Route::get('iptv-applications', [HomeController::class, 'iptvApplications'])->name('iptv-applications');

        Route::post('send-email',     [HomeController::class, 'send'])->name('contact.send');
        Route::post('buy-now',        [HomeController::class, 'sendBuynow'])->name('buynow.send');
        Route::post('buy-now-panel',  [HomeController::class, 'postBuyNowPanel'])->name('buynow.panel');
        Route::post('subscribe',      [HomeController::class, 'subscribe'])->name('subscribe');
        Route::get('trending',        [HomeController::class, 'getTrending']);
        Route::get('redirect',        [HomeController::class, 'redirect'])->name('redirect.ad');
    }
);

Route::post('/track/whatsapp-trial', [TrackingController::class, 'whatsappTrial'])
    ->name('track.whatsapp.trial')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
