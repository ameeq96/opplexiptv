<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
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



Route::get('test-email', function () {
    try {
        Mail::raw('Test email body', function ($message) {
            $message->to('ameeqkhan183@gmail.com')->subject('Test Email');
        });

        return 'Email Sent';
    } catch (\Exception $e) {
        return 'Email not sent. Error: ' . $e->getMessage();
    }
});

Route::get('storage-link', function () {
    try {
        Artisan::call('storage:link');
        return "Storage linked successfully!";
    } catch (\Exception $e) {
        return "Failed to link storage: " . $e->getMessage();
    }
});