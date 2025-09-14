<?php

use App\Http\Controllers\{AdminController, HomeController, OrderController, PanelOrderController, PurchasingController, UserClientController, WhatsAppController};
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Support\Facades\Artisan;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::get('/admin', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->group(function () {

    Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('clients/import', [UserClientController::class, 'import'])->name('clients.import');


    Route::get('whatsapp-broadcast', [WhatsAppController::class, 'broadcast'])->name('whatsapp.broadcast');
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
    Route::delete(
        '/purchasing/{purchasing}/pictures/{picture}',
        [PurchasingController::class, 'destroyPicture']
    )->name('purchasing.pictures.destroy');
});

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => [
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        ]
    ],
    function () {

        Route::get("/", [HomeController::class, "home"])->name("home");
        Route::get("about", [HomeController::class, "about"])->name("about");
        Route::get("faqs", [HomeController::class, "faq"])->name("faqs");
        Route::get("buy-now-panel", [HomeController::class, "buynowpanel"])->name("buy-now-panel");
        Route::get("contact", [HomeController::class, "contact"])->name("contact");
        Route::get("buynow", [HomeController::class, "buynow"])->name("buynow");
        Route::get("pricing", [HomeController::class, "pricing"])->name("pricing");
        Route::get("movies", [HomeController::class, "movies"])->name("movies");
        Route::get("packages", [HomeController::class, "packages"])->name("packages");
        Route::get("reseller-panel", [HomeController::class, "resellerPanel"])->name("reseller-panel");
        Route::get('/iptv-applications', [HomeController::class, 'iptvApplications'])->name('iptv-applications');

        Route::post('/send-email', [HomeController::class, 'send'])->name('contact.send');
        Route::post('/buy-now', [HomeController::class, 'sendBuynow'])->name('buynow.send');
        Route::post('/buy-now-panel', [HomeController::class, 'postBuyNowPanel'])->name('buynow.panel');
        Route::post('/subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
        Route::get('/trending', [HomeController::class, 'getTrending']);
        Route::get('/redirect', [HomeController::class, 'redirect'])->name('redirect.ad');
    }
);
