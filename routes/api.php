<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OpplexifyOrderController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\TrackingConsentController;
use App\Http\Controllers\CheckoutDraftController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\TrialClickController;
use App\Http\Controllers\Api\CustomerDataController;
use App\Http\Middleware\ApiJsonResponse;
use App\Http\Middleware\DataApiAccess;
use App\Http\Middleware\AuditAdminAction;
use App\Http\Middleware\EnsureAdminSessionIsCurrent;
use App\Http\Middleware\EnsureAdminPasswordChanged;
use App\Http\Middleware\EnsureAdminTwoFactorAuthenticated;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| The groups below explicitly distinguish stateless APIs from session/CSRF
| browser endpoints. Legacy URLs remain registered for existing callers.
|
*/

Route::prefix('api')->middleware([ApiJsonResponse::class, 'api'])->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        abort_unless($request->user() instanceof \App\Models\User, 401);
        return $request->user();
    });

    Route::post('/payments/webhook', PaymentWebhookController::class)
        ->middleware('throttle:60,1')
        ->name('payments.webhook');

    Route::post('/integrations/tawk/webhook', \App\Http\Controllers\TawkWebhookController::class)
        ->middleware('throttle:60,1')
        ->name('integrations.tawk.webhook');

    Route::get('movies/{mediaType}/{id}/trailer', [HomeController::class, 'movieTrailer'])
        ->where(['mediaType' => 'movie|tv', 'id' => '[0-9]+'])
        ->name('api.movies.trailer');
    Route::get('trending', [HomeController::class, 'getTrending'])->name('api.trending');

    Route::prefix('v1')->name('api.v1.')->group(function () {
        Route::middleware(DataApiAccess::class.':customers:read')->group(function () {
            Route::get('customers', [CustomerDataController::class, 'customers'])->name('customers.index');
            Route::get('customers/{customer}', [CustomerDataController::class, 'customer'])
                ->whereNumber('customer')->name('customers.show');
            Route::get('customers/{customer}/trials', [CustomerDataController::class, 'customerTrials'])
                ->whereNumber('customer')->name('customers.trials');
        });
        Route::middleware(DataApiAccess::class.':orders:read')->group(function () {
            Route::get('customers/{customer}/orders', [CustomerDataController::class, 'customerOrders'])
                ->whereNumber('customer')->name('customers.orders');
            Route::get('orders', [CustomerDataController::class, 'orders'])->name('orders.index');
            Route::get('orders/{order}', [CustomerDataController::class, 'order'])
                ->whereNumber('order')->name('orders.show');
            Route::get('subscriptions', [CustomerDataController::class, 'subscriptions'])->name('subscriptions.index');
            Route::get('subscriptions/{order}', [CustomerDataController::class, 'subscription'])
                ->whereNumber('order')->name('subscriptions.show');
        });
        Route::middleware(DataApiAccess::class.':support:read')->group(function () {
            Route::get('customers/{customer}/conversations', [CustomerDataController::class, 'customerConversations'])
                ->whereNumber('customer')->name('customers.conversations');
            Route::get('support/conversations', [CustomerDataController::class, 'conversations'])->name('support.conversations.index');
            Route::get('support/conversations/{conversation}', [CustomerDataController::class, 'conversation'])
                ->whereNumber('conversation')->name('support.conversations.show');
            Route::get('support/conversations/{conversation}/messages', [CustomerDataController::class, 'messages'])
                ->whereNumber('conversation')->name('support.messages.index');
        });
    });
});

$opplexifyRoutes = function () {
    Route::get('integrations/opplexify/catalog', [HomeController::class, 'opplexifyCatalog'])
        ->middleware('throttle:60,1')->name('integrations.opplexify.catalog');
    Route::post('integrations/opplexify/orders/prepare', [OpplexifyOrderController::class, 'prepare'])
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->middleware('throttle:60,1,opplexify-orders-prepare')->name('integrations.opplexify.orders.prepare');
    Route::post('integrations/opplexify/orders/complete', [OpplexifyOrderController::class, 'complete'])
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->middleware('throttle:60,1,opplexify-orders-complete')->name('integrations.opplexify.orders.complete');
    Route::post('integrations/opplexify/customer-events', [OpplexifyOrderController::class, 'customerEvent'])
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->middleware('throttle:120,1,opplexify-customer-events')->name('integrations.opplexify.customer-events');
};

Route::middleware('web')->group($opplexifyRoutes);
Route::prefix('api')->name('api.')->middleware([ApiJsonResponse::class, SubstituteBindings::class])->group($opplexifyRoutes);

$captureRoutes = function () {
    Route::post('track/whatsapp-trial', [TrackingController::class, 'whatsappTrial'])
        ->name('track.whatsapp.trial')->middleware('throttle:30,1')
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    Route::get('whatsapp-leads/token', [TrackingController::class, 'whatsappLeadToken'])
        ->name('whatsapp.leads.token')->middleware('throttle:30,1');
    Route::post('whatsapp-leads', [TrackingController::class, 'storeWhatsAppLead'])
        ->name('whatsapp.leads.store')->middleware('throttle:10,1');
    Route::post('tawk-leads', [TrackingController::class, 'storeTawkLead'])
        ->name('tawk.leads.store')->middleware('throttle:10,1,tawk-leads')->block(10, 10);
    Route::post('tracking/consent', [TrackingConsentController::class, 'update'])
        ->name('tracking.consent')->middleware('throttle:20,1');
};

Route::middleware('web')->group($captureRoutes);
Route::prefix('api/browser')->name('api.browser.')->middleware([ApiJsonResponse::class, 'web'])->group(function () use ($captureRoutes) {
    $captureRoutes();
    Route::post('checkout/draft', [CheckoutDraftController::class, 'store'])
        ->middleware('throttle:20,1')->name('checkout.draft');
});

Route::middleware([ApiJsonResponse::class, 'web'])->prefix('api')->group(function () {
    Route::get('packages/providers/{package}/plans', [HomeController::class, 'providerPlans'])
        ->whereNumber('package')->middleware('throttle:60,1')->name('api.packages.provider-plans');
});

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [
        'web',
        \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
    ],
], function () {
    Route::get('movies/{mediaType}/{id}/trailer', [HomeController::class, 'movieTrailer'])
        ->where(['mediaType' => 'movie|tv', 'id' => '[0-9]+'])
        ->middleware('throttle:60,1')->name('movies.trailer');
    Route::get('trending', [HomeController::class, 'getTrending']);
    Route::get('packages/providers/{package}/plans', [HomeController::class, 'providerPlans'])
        ->whereNumber('package')->name('packages.provider-plans');
    Route::post('checkout/draft', [CheckoutDraftController::class, 'store'])
        ->middleware('throttle:20,1')->name('checkout.draft');
});

Route::prefix('api/v1/site')->name('api.site.')->middleware([ApiJsonResponse::class, 'api'])->group(function () {
    Route::get('pages/{page}', [\App\Http\Controllers\Api\PublicSiteController::class, 'page'])->name('pages.show');
    Route::get('products', [\App\Http\Controllers\Api\PublicSiteController::class, 'products'])->name('products.index');
    Route::get('products/{type}/{id}', [\App\Http\Controllers\Api\PublicSiteController::class, 'product'])->whereNumber('id')->name('products.show');
    Route::get('configure', [\App\Http\Controllers\Api\PublicSiteController::class, 'configure'])->name('configure');
    Route::get('providers/{package}/plans', [\App\Http\Controllers\Api\PublicSiteController::class, 'providerPlans'])->whereNumber('package')->name('providers.plans');
    Route::get('blogs', [\App\Http\Controllers\Api\PublicSiteController::class, 'blogs'])->name('blogs.index');
    Route::get('blogs/{slug}', [\App\Http\Controllers\Api\PublicSiteController::class, 'blog'])->name('blogs.show');
});

Route::prefix('api/browser')->name('api.browser.')->middleware([ApiJsonResponse::class, 'web'])->group(function () {
    Route::get('site/context', [\App\Http\Controllers\Api\PublicSiteController::class, 'browserContext'])->name('site.context');
    Route::get('contact/challenge', [HomeController::class, 'contactChallenge'])->name('contact.challenge');
    Route::post('contact', [HomeController::class, 'send'])->middleware('throttle:5,10,contact')->name('contact.send');
    Route::post('subscribe', [HomeController::class, 'subscribe'])->middleware('throttle:5,10,subscribe')->name('subscribe');
    Route::get('checkout', [HomeController::class, 'checkoutStep1'])->name('checkout');
    Route::post('checkout/payment', [HomeController::class, 'checkoutStep2'])->middleware('throttle:10,1,checkout-payment')->name('checkout.payment');

    Route::prefix('account')->name('customer.')->middleware(\App\Http\Middleware\EnsureCustomerAccount::class.':public')->group(function () {
        Route::get('session', [\App\Http\Controllers\CustomerAccountController::class, 'session'])->name('session');
        Route::post('access', [\App\Http\Controllers\CustomerAccountController::class, 'requestAccess'])->middleware('throttle:5,10')->name('access.request');
        Route::post('access/confirm', [\App\Http\Controllers\CustomerAccountController::class, 'consumeAccess'])->middleware(['signed', 'throttle:10,1'])->name('access.consume');
        Route::post('renewal/unsubscribe', [\App\Http\Controllers\CustomerAccountController::class, 'unsubscribe'])->middleware(['signed', 'throttle:20,1'])->name('renewal.unsubscribe');
        Route::middleware(\App\Http\Middleware\EnsureCustomerAccount::class)->group(function () {
            Route::get('overview', [\App\Http\Controllers\CustomerAccountController::class, 'overview'])->name('overview');
            Route::get('orders', [\App\Http\Controllers\CustomerAccountController::class, 'orders'])->name('orders.index');
            Route::get('orders/{order}', [\App\Http\Controllers\CustomerAccountController::class, 'order'])->whereNumber('order')->name('orders.show');
            Route::post('orders/{order}/renew', [\App\Http\Controllers\CustomerAccountController::class, 'renew'])->whereNumber('order')->middleware('throttle:10,1')->name('orders.renew');
            Route::post('logout', [\App\Http\Controllers\CustomerAccountController::class, 'logout'])->name('logout');
            Route::patch('preferences', [\App\Http\Controllers\CustomerAccountController::class, 'preferences'])->middleware('throttle:10,1')->name('preferences');
            Route::get('trials', [\App\Http\Controllers\CustomerAccountController::class, 'trials'])->name('trials.index');
            Route::get('trial-packages', [\App\Http\Controllers\CustomerAccountController::class, 'trialPackages'])->name('trial-packages');
            Route::post('trials', [\App\Http\Controllers\CustomerAccountController::class, 'requestTrial'])->middleware('throttle:5,10')->name('trials.request');
            Route::post('trials/{trial}/plan', [\App\Http\Controllers\CustomerAccountController::class, 'trialPlan'])->whereNumber('trial')->middleware('throttle:10,1')->name('trials.plan');
            Route::get('support', [\App\Http\Controllers\CustomerSupportController::class, 'data'])->name('support.index');
            Route::get('support/{conversation}', [\App\Http\Controllers\CustomerSupportController::class, 'show'])->whereNumber('conversation')->name('support.show');
            Route::get('support/{conversation}/messages', [\App\Http\Controllers\CustomerSupportController::class, 'messages'])->whereNumber('conversation')->name('support.messages');
            Route::post('support', [\App\Http\Controllers\CustomerSupportController::class, 'store'])->middleware('throttle:5,10')->name('support.store');
            Route::post('support/{conversation}/messages', [\App\Http\Controllers\CustomerSupportController::class, 'reply'])->whereNumber('conversation')->middleware('throttle:15,1')->name('support.reply');
        });
    });
    Route::get('orders/{orderId}/status', [\App\Http\Controllers\CustomerOrderStatusController::class, 'show'])->whereNumber('orderId')->middleware(['signed', 'throttle:30,1'])->name('orders.status');
    Route::post('orders/{orderId}/payment-proof', [\App\Http\Controllers\CustomerOrderStatusController::class, 'storeProof'])->whereNumber('orderId')->middleware(['signed', 'throttle:5,10'])->name('orders.payment-proof');
    Route::post('referrals/{code}', [\App\Http\Controllers\ReferralController::class, 'capture'])->where('code', '[A-Za-z0-9]+')->middleware('throttle:30,1')->name('referrals.capture');
    Route::post('event-promotion/{delivery}/activate', \App\Http\Controllers\EventPromotionController::class)->whereNumber('delivery')->middleware(['signed', 'throttle:20,1'])->name('event-promotions.activate');
    Route::post('marketing/unsubscribe', \App\Http\Controllers\MarketingUnsubscribeController::class)->middleware('signed')->name('marketing.unsubscribe');
});

// Compatibility actions retain their original URL, name and middleware.
Route::middleware('web')->group(function () {
    Route::prefix('account')->name('customer.')->middleware(\App\Http\Middleware\EnsureCustomerAccount::class.':public')->group(function () {
        Route::post('access', [\App\Http\Controllers\CustomerAccountController::class, 'requestAccess'])->middleware('throttle:5,10')->name('access.request');
        Route::post('access/confirm', [\App\Http\Controllers\CustomerAccountController::class, 'consumeAccess'])->middleware(['signed', 'throttle:10,1'])->name('access.consume');
        Route::match(['get', 'post'], 'renewal/unsubscribe', [\App\Http\Controllers\CustomerAccountController::class, 'unsubscribe'])->middleware(['signed', 'throttle:20,1'])->name('renewal.unsubscribe');
        Route::middleware(\App\Http\Middleware\EnsureCustomerAccount::class)->group(function () {
            Route::post('logout', [\App\Http\Controllers\CustomerAccountController::class, 'logout'])->name('logout');
            Route::patch('preferences', [\App\Http\Controllers\CustomerAccountController::class, 'preferences'])->middleware('throttle:10,1')->name('preferences');
            Route::post('trials', [\App\Http\Controllers\CustomerAccountController::class, 'requestTrial'])->middleware('throttle:5,10')->name('trials.request');
            Route::post('support', [\App\Http\Controllers\CustomerSupportController::class, 'store'])->middleware('throttle:5,10')->name('support.store');
            Route::post('support/{conversation}/messages', [\App\Http\Controllers\CustomerSupportController::class, 'reply'])->whereNumber('conversation')->middleware('throttle:15,1')->name('support.reply');
        });
    });
    Route::post('orders/{orderId}/payment-proof', [\App\Http\Controllers\CustomerOrderStatusController::class, 'storeProof'])->whereNumber('orderId')->middleware(['signed', 'throttle:5,10'])->name('orders.payment-proof');
    Route::match(['get', 'post'], 'marketing/unsubscribe', \App\Http\Controllers\MarketingUnsubscribeController::class)->middleware('signed')->name('marketing.unsubscribe');
});
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['web', \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class, \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class],
], function () {
    Route::post('send-email', [HomeController::class, 'send'])->middleware('throttle:5,10,contact')->name('contact.send');
    Route::post('subscribe', [HomeController::class, 'subscribe'])->middleware('throttle:5,10,subscribe')->name('subscribe');
    Route::post('checkout/payment', [HomeController::class, 'checkoutStep2'])->middleware('throttle:10,1,checkout-payment')->name('step2');
});

// Static contracts for active admin workflows; the legacy HTML routes stay in web.php.
$adminGuardSets = [
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access'],
    ['web', 'guest:admin'],
    ['web', 'guest:admin', 'throttle:20,1', \App\Http\Middleware\AuditAdminAction::class],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, 'throttle:5,1'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, 'throttle:6,1'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', 'throttle:2,10'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', 'throttle:30,1'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', 'throttle:10,1'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', 'throttle:5,1'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', \App\Http\Middleware\EnsureRecentAdminConfirmation::class],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', 'throttle:20,1'],
    ['web', 'auth:admin', \App\Http\Middleware\EnsureAdminSessionIsCurrent::class, \App\Http\Middleware\AuditAdminAction::class, \App\Http\Middleware\EnsureAdminPasswordChanged::class, \App\Http\Middleware\EnsureAdminTwoFactorAuthenticated::class, 'admin.access', 'throttle:3,1'],
];
$adminEndpoints = [
    [['GET'], 'notifications', \App\Http\Controllers\AdminNotificationController::class, 'index', 'notifications.index', 0, []],
    [['POST'], 'notifications/{id}/read', \App\Http\Controllers\AdminNotificationController::class, 'markAsRead', 'notifications.read', 0, []],
    [['POST'], 'notifications/read-all', \App\Http\Controllers\AdminNotificationController::class, 'markAllAsRead', 'notifications.readAll', 0, []],
    [['POST'], 'orders/{order}/mark-messaged', \App\Http\Controllers\OrderController::class, 'markOneMessaged', 'orders.markOneMessaged', 0, []],
    [['PATCH'], 'trial-clicks/{trialClick}/status', \App\Http\Controllers\Admin\TrialClickController::class, 'updateStatus', 'trial_clicks.status', 0, []],
    [['GET'], 'login', \App\Http\Controllers\Admin\AuthController::class, 'showLogin', 'login', 1, []],
    [['POST'], 'login', \App\Http\Controllers\Admin\AuthController::class, 'login', 'login.attempt', 2, []],
    [['POST'], 'logout', \App\Http\Controllers\Admin\AuthController::class, 'logout', 'logout', 3, []],
    [['GET'], 'two-factor-challenge', \App\Http\Controllers\Admin\TwoFactorController::class, 'showChallenge', 'two-factor.challenge', 3, []],
    [['POST'], 'two-factor-challenge', \App\Http\Controllers\Admin\TwoFactorController::class, 'verifyChallenge', 'two-factor.verify', 3, []],
    [['GET'], 'password/change', \App\Http\Controllers\Admin\AuthController::class, 'showPasswordChange', 'password.change', 3, []],
    [['PUT'], 'password/change', \App\Http\Controllers\Admin\AuthController::class, 'updatePassword', 'password.update', 4, []],
    [['GET'], 'security/two-factor', \App\Http\Controllers\Admin\TwoFactorController::class, 'settings', 'two-factor.settings', 5, []],
    [['POST'], 'security/two-factor', \App\Http\Controllers\Admin\TwoFactorController::class, 'confirm', 'two-factor.confirm', 6, []],
    [['POST'], 'security/two-factor/recovery-codes', \App\Http\Controllers\Admin\TwoFactorController::class, 'regenerateRecoveryCodes', 'two-factor.recovery-codes', 6, []],
    [['DELETE'], 'security/two-factor', \App\Http\Controllers\Admin\TwoFactorController::class, 'disable', 'two-factor.disable', 6, []],
    [['GET'], 'dashboard', \App\Http\Controllers\Admin\DashboardController::class, 'index', 'dashboard', 0, []],
    [['POST'], 'clients/import', \App\Http\Controllers\UserClientController::class, 'import', 'clients.import', 0, []],
    [['GET'], 'clients/export/facebook-csv', \App\Http\Controllers\UserClientController::class, 'exportFacebookCsv', 'clients.export.facebook', 0, []],
    [['DELETE'], 'clients/bulk-delete', \App\Http\Controllers\UserClientController::class, 'bulkDelete', 'clients.bulkDelete', 0, []],
    [['POST'], 'clients/contact-number-update', \App\Http\Controllers\UserClientController::class, 'scheduleContactNumberUpdate', 'clients.contact-number-update', 7, []],
    [['DELETE'], 'orders/bulk-delete', \App\Http\Controllers\OrderController::class, 'bulkDelete', 'orders.bulkDelete', 0, []],
    [['DELETE'], 'reseller-orders/bulk-delete', \App\Http\Controllers\PanelOrderController::class, 'bulkDelete', 'reseller-orders.bulkDelete', 0, []],
    [['DELETE'], 'purchasing/bulk-delete', \App\Http\Controllers\PurchasingController::class, 'bulkDelete', 'purchasing.bulkDelete', 0, []],
    [['GET'], 'clients/export/crm', \App\Http\Controllers\UserClientController::class, 'exportCrm', 'clients.export.crm', 0, []],
    [['GET'], 'clients/unlinked', \App\Http\Controllers\UserClientController::class, 'unlinked', 'clients.unlinked', 0, []],
    [['POST'], 'clients/link-activity', \App\Http\Controllers\UserClientController::class, 'linkActivity', 'clients.link-activity', 8, []],
    [['PATCH'], 'clients/{client}/assignment', \App\Http\Controllers\Admin\CustomerInteractionController::class, 'assign', 'clients.assignment', 0, []],
    [['POST'], 'clients/{client}/notes', \App\Http\Controllers\Admin\CustomerInteractionController::class, 'storeNote', 'clients.notes.store', 0, []],
    [['POST'], 'clients/{client}/follow-ups', \App\Http\Controllers\Admin\CustomerInteractionController::class, 'storeFollowUp', 'clients.follow-ups.store', 0, []],
    [['PATCH'], 'clients/{client}/follow-ups/{followUp}/complete', \App\Http\Controllers\Admin\CustomerInteractionController::class, 'completeFollowUp', 'clients.follow-ups.complete', 0, []],
    [['POST'], 'clients/{client}/tickets/{conversation}/reply', \App\Http\Controllers\Admin\CustomerInteractionController::class, 'replyTicket', 'clients.tickets.reply', 8, ['conversation' => '[0-9]+']],
    [['PATCH'], 'clients/{client}/tickets/{conversation}/status', \App\Http\Controllers\Admin\CustomerInteractionController::class, 'updateTicketStatus', 'clients.tickets.status', 8, ['conversation' => '[0-9]+']],
    [['GET'], 'clients', \App\Http\Controllers\UserClientController::class, 'index', 'clients.index', 0, []],
    [['GET'], 'clients/create', \App\Http\Controllers\UserClientController::class, 'create', 'clients.create', 0, []],
    [['POST'], 'clients', \App\Http\Controllers\UserClientController::class, 'store', 'clients.store', 0, []],
    [['GET'], 'clients/{client}', \App\Http\Controllers\UserClientController::class, 'show', 'clients.show', 0, []],
    [['GET'], 'clients/{client}/edit', \App\Http\Controllers\UserClientController::class, 'edit', 'clients.edit', 0, []],
    [['PUT', 'PATCH'], 'clients/{client}', \App\Http\Controllers\UserClientController::class, 'update', 'clients.update', 0, []],
    [['DELETE'], 'clients/{client}', \App\Http\Controllers\UserClientController::class, 'destroy', 'clients.destroy', 0, []],
    [['GET'], 'orders', \App\Http\Controllers\OrderController::class, 'index', 'orders.index', 0, []],
    [['GET'], 'orders/create', \App\Http\Controllers\OrderController::class, 'create', 'orders.create', 0, []],
    [['POST'], 'orders', \App\Http\Controllers\OrderController::class, 'store', 'orders.store', 0, []],
    [['GET'], 'orders/{order}', \App\Http\Controllers\OrderController::class, 'show', 'orders.show', 0, []],
    [['GET'], 'orders/{order}/edit', \App\Http\Controllers\OrderController::class, 'edit', 'orders.edit', 0, []],
    [['PUT', 'PATCH'], 'orders/{order}', \App\Http\Controllers\OrderController::class, 'update', 'orders.update', 0, []],
    [['DELETE'], 'orders/{order}', \App\Http\Controllers\OrderController::class, 'destroy', 'orders.destroy', 0, []],
    [['GET'], 'orders/{order}/pictures/{picture}', \App\Http\Controllers\OrderController::class, 'showPicture', 'orders.pictures.show', 0, []],
    [['DELETE'], 'orders/{order}/pictures/{picture}', \App\Http\Controllers\OrderController::class, 'destroyPicture', 'orders.pictures.destroy', 0, []],
    [['GET'], 'panel-orders', \App\Http\Controllers\PanelOrderController::class, 'index', 'panel-orders.index', 0, []],
    [['GET'], 'panel-orders/create', \App\Http\Controllers\PanelOrderController::class, 'create', 'panel-orders.create', 0, []],
    [['POST'], 'panel-orders', \App\Http\Controllers\PanelOrderController::class, 'store', 'panel-orders.store', 0, []],
    [['GET'], 'panel-orders/{panel_order}', \App\Http\Controllers\PanelOrderController::class, 'show', 'panel-orders.show', 0, []],
    [['GET'], 'panel-orders/{panel_order}/edit', \App\Http\Controllers\PanelOrderController::class, 'edit', 'panel-orders.edit', 0, []],
    [['PUT', 'PATCH'], 'panel-orders/{panel_order}', \App\Http\Controllers\PanelOrderController::class, 'update', 'panel-orders.update', 0, []],
    [['DELETE'], 'panel-orders/{panel_order}', \App\Http\Controllers\PanelOrderController::class, 'destroy', 'panel-orders.destroy', 0, []],
    [['GET'], 'purchasing', \App\Http\Controllers\PurchasingController::class, 'index', 'purchasing.index', 0, []],
    [['GET'], 'purchasing/create', \App\Http\Controllers\PurchasingController::class, 'create', 'purchasing.create', 0, []],
    [['POST'], 'purchasing', \App\Http\Controllers\PurchasingController::class, 'store', 'purchasing.store', 0, []],
    [['GET'], 'purchasing/{purchasing}', \App\Http\Controllers\PurchasingController::class, 'show', 'purchasing.show', 0, []],
    [['GET'], 'purchasing/{purchasing}/edit', \App\Http\Controllers\PurchasingController::class, 'edit', 'purchasing.edit', 0, []],
    [['PUT', 'PATCH'], 'purchasing/{purchasing}', \App\Http\Controllers\PurchasingController::class, 'update', 'purchasing.update', 0, []],
    [['DELETE'], 'purchasing/{purchasing}', \App\Http\Controllers\PurchasingController::class, 'destroy', 'purchasing.destroy', 0, []],
    [['GET'], 'purchasing/{purchasing}/pictures/{picture}', \App\Http\Controllers\PurchasingController::class, 'showPicture', 'purchasing.pictures.show', 0, []],
    [['DELETE'], 'purchasing/{purchasing}/pictures/{picture}', \App\Http\Controllers\PurchasingController::class, 'destroyPicture', 'purchasing.pictures.destroy', 0, []],
    [['POST'], 'orders/bulk-action', \App\Http\Controllers\OrderController::class, 'bulkAction', 'orders.bulkAction', 0, []],
    [['POST'], 'panel-orders/bulk-action', \App\Http\Controllers\PanelOrderController::class, 'bulkAction', 'reseller-orders.bulkAction', 0, []],
    [['POST'], 'orders/{order}/verify-payment', \App\Http\Controllers\OrderController::class, 'verifyPayment', 'orders.verifyPayment', 9, []],
    [['POST'], 'locale', \App\Http\Controllers\Admin\DashboardController::class, 'setLocale', 'locale', 0, []],
    [['GET'], 'blogs', \App\Http\Controllers\Admin\BlogController::class, 'index', 'blogs.index', 0, []],
    [['GET'], 'blogs/create', \App\Http\Controllers\Admin\BlogController::class, 'create', 'blogs.create', 0, []],
    [['POST'], 'blogs', \App\Http\Controllers\Admin\BlogController::class, 'store', 'blogs.store', 0, []],
    [['GET'], 'blogs/{blog}', \App\Http\Controllers\Admin\BlogController::class, 'show', 'blogs.show', 0, []],
    [['GET'], 'blogs/{blog}/edit', \App\Http\Controllers\Admin\BlogController::class, 'edit', 'blogs.edit', 0, []],
    [['PUT', 'PATCH'], 'blogs/{blog}', \App\Http\Controllers\Admin\BlogController::class, 'update', 'blogs.update', 0, []],
    [['DELETE'], 'blogs/{blog}', \App\Http\Controllers\Admin\BlogController::class, 'destroy', 'blogs.destroy', 0, []],
    [['POST'], 'blogs/{blog}/publish', \App\Http\Controllers\Admin\BlogController::class, 'publish', 'blogs.publish', 0, []],
    [['POST'], 'blogs/{blog}/archive', \App\Http\Controllers\Admin\BlogController::class, 'archive', 'blogs.archive', 0, []],
    [['POST'], 'blogs/{blog}/duplicate', \App\Http\Controllers\Admin\BlogController::class, 'duplicate', 'blogs.duplicate', 0, []],
    [['GET'], 'shop-products', \App\Http\Controllers\Admin\ShopProductController::class, 'index', 'shop-products.index', 0, []],
    [['GET'], 'shop-products/create', \App\Http\Controllers\Admin\ShopProductController::class, 'create', 'shop-products.create', 0, []],
    [['POST'], 'shop-products', \App\Http\Controllers\Admin\ShopProductController::class, 'store', 'shop-products.store', 0, []],
    [['GET'], 'shop-products/{shop_product}', \App\Http\Controllers\Admin\ShopProductController::class, 'show', 'shop-products.show', 0, []],
    [['GET'], 'shop-products/{shop_product}/edit', \App\Http\Controllers\Admin\ShopProductController::class, 'edit', 'shop-products.edit', 0, []],
    [['PUT', 'PATCH'], 'shop-products/{shop_product}', \App\Http\Controllers\Admin\ShopProductController::class, 'update', 'shop-products.update', 0, []],
    [['DELETE'], 'shop-products/{shop_product}', \App\Http\Controllers\Admin\ShopProductController::class, 'destroy', 'shop-products.destroy', 0, []],
    [['GET'], 'products', \App\Http\Controllers\Admin\ProductHubController::class, 'index', 'products.index', 0, []],
    [['POST'], 'products/toggle-status', \App\Http\Controllers\Admin\ProductHubController::class, 'toggleStatus', 'products.toggle-status', 0, []],
    [['GET'], 'home-services', \App\Http\Controllers\Admin\HomeServiceController::class, 'index', 'home-services.index', 0, []],
    [['GET'], 'home-services/create', \App\Http\Controllers\Admin\HomeServiceController::class, 'create', 'home-services.create', 0, []],
    [['POST'], 'home-services', \App\Http\Controllers\Admin\HomeServiceController::class, 'store', 'home-services.store', 0, []],
    [['GET'], 'home-services/{home_service}', \App\Http\Controllers\Admin\HomeServiceController::class, 'show', 'home-services.show', 0, []],
    [['GET'], 'home-services/{home_service}/edit', \App\Http\Controllers\Admin\HomeServiceController::class, 'edit', 'home-services.edit', 0, []],
    [['PUT', 'PATCH'], 'home-services/{home_service}', \App\Http\Controllers\Admin\HomeServiceController::class, 'update', 'home-services.update', 0, []],
    [['DELETE'], 'home-services/{home_service}', \App\Http\Controllers\Admin\HomeServiceController::class, 'destroy', 'home-services.destroy', 0, []],
    [['GET'], 'testimonials', \App\Http\Controllers\Admin\TestimonialController::class, 'index', 'testimonials.index', 0, []],
    [['GET'], 'testimonials/create', \App\Http\Controllers\Admin\TestimonialController::class, 'create', 'testimonials.create', 0, []],
    [['POST'], 'testimonials', \App\Http\Controllers\Admin\TestimonialController::class, 'store', 'testimonials.store', 0, []],
    [['GET'], 'testimonials/{testimonial}', \App\Http\Controllers\Admin\TestimonialController::class, 'show', 'testimonials.show', 0, []],
    [['GET'], 'testimonials/{testimonial}/edit', \App\Http\Controllers\Admin\TestimonialController::class, 'edit', 'testimonials.edit', 0, []],
    [['PUT', 'PATCH'], 'testimonials/{testimonial}', \App\Http\Controllers\Admin\TestimonialController::class, 'update', 'testimonials.update', 0, []],
    [['DELETE'], 'testimonials/{testimonial}', \App\Http\Controllers\Admin\TestimonialController::class, 'destroy', 'testimonials.destroy', 0, []],
    [['GET'], 'channel-logos', \App\Http\Controllers\Admin\ChannelLogoController::class, 'index', 'channel-logos.index', 0, []],
    [['GET'], 'channel-logos/create', \App\Http\Controllers\Admin\ChannelLogoController::class, 'create', 'channel-logos.create', 0, []],
    [['POST'], 'channel-logos', \App\Http\Controllers\Admin\ChannelLogoController::class, 'store', 'channel-logos.store', 0, []],
    [['GET'], 'channel-logos/{channel_logo}', \App\Http\Controllers\Admin\ChannelLogoController::class, 'show', 'channel-logos.show', 0, []],
    [['GET'], 'channel-logos/{channel_logo}/edit', \App\Http\Controllers\Admin\ChannelLogoController::class, 'edit', 'channel-logos.edit', 0, []],
    [['PUT', 'PATCH'], 'channel-logos/{channel_logo}', \App\Http\Controllers\Admin\ChannelLogoController::class, 'update', 'channel-logos.update', 0, []],
    [['DELETE'], 'channel-logos/{channel_logo}', \App\Http\Controllers\Admin\ChannelLogoController::class, 'destroy', 'channel-logos.destroy', 0, []],
    [['GET'], 'menu-items', \App\Http\Controllers\Admin\MenuItemController::class, 'index', 'menu-items.index', 0, []],
    [['GET'], 'menu-items/create', \App\Http\Controllers\Admin\MenuItemController::class, 'create', 'menu-items.create', 0, []],
    [['POST'], 'menu-items', \App\Http\Controllers\Admin\MenuItemController::class, 'store', 'menu-items.store', 0, []],
    [['GET'], 'menu-items/{menu_item}', \App\Http\Controllers\Admin\MenuItemController::class, 'show', 'menu-items.show', 0, []],
    [['GET'], 'menu-items/{menu_item}/edit', \App\Http\Controllers\Admin\MenuItemController::class, 'edit', 'menu-items.edit', 0, []],
    [['PUT', 'PATCH'], 'menu-items/{menu_item}', \App\Http\Controllers\Admin\MenuItemController::class, 'update', 'menu-items.update', 0, []],
    [['DELETE'], 'menu-items/{menu_item}', \App\Http\Controllers\Admin\MenuItemController::class, 'destroy', 'menu-items.destroy', 0, []],
    [['GET'], 'packages', \App\Http\Controllers\Admin\PackageController::class, 'index', 'packages.index', 0, []],
    [['GET'], 'packages/create', \App\Http\Controllers\Admin\PackageController::class, 'create', 'packages.create', 0, []],
    [['POST'], 'packages', \App\Http\Controllers\Admin\PackageController::class, 'store', 'packages.store', 0, []],
    [['GET'], 'packages/{package}', \App\Http\Controllers\Admin\PackageController::class, 'show', 'packages.show', 0, []],
    [['GET'], 'packages/{package}/edit', \App\Http\Controllers\Admin\PackageController::class, 'edit', 'packages.edit', 0, []],
    [['PUT', 'PATCH'], 'packages/{package}', \App\Http\Controllers\Admin\PackageController::class, 'update', 'packages.update', 0, []],
    [['DELETE'], 'packages/{package}', \App\Http\Controllers\Admin\PackageController::class, 'destroy', 'packages.destroy', 0, []],
    [['GET'], 'pricing-section', \App\Http\Controllers\Admin\PricingSectionController::class, 'edit', 'pricing-section.edit', 0, []],
    [['PUT'], 'pricing-section', \App\Http\Controllers\Admin\PricingSectionController::class, 'update', 'pricing-section.update', 0, []],
    [['GET'], 'footer-settings', \App\Http\Controllers\Admin\FooterSettingController::class, 'edit', 'footer-settings.edit', 0, []],
    [['PUT'], 'footer-settings', \App\Http\Controllers\Admin\FooterSettingController::class, 'update', 'footer-settings.update', 0, []],
    [['GET'], 'footer-links', \App\Http\Controllers\Admin\FooterLinkController::class, 'index', 'footer-links.index', 0, []],
    [['GET'], 'footer-links/create', \App\Http\Controllers\Admin\FooterLinkController::class, 'create', 'footer-links.create', 0, []],
    [['POST'], 'footer-links', \App\Http\Controllers\Admin\FooterLinkController::class, 'store', 'footer-links.store', 0, []],
    [['GET'], 'footer-links/{footer_link}', \App\Http\Controllers\Admin\FooterLinkController::class, 'show', 'footer-links.show', 0, []],
    [['GET'], 'footer-links/{footer_link}/edit', \App\Http\Controllers\Admin\FooterLinkController::class, 'edit', 'footer-links.edit', 0, []],
    [['PUT', 'PATCH'], 'footer-links/{footer_link}', \App\Http\Controllers\Admin\FooterLinkController::class, 'update', 'footer-links.update', 0, []],
    [['DELETE'], 'footer-links/{footer_link}', \App\Http\Controllers\Admin\FooterLinkController::class, 'destroy', 'footer-links.destroy', 0, []],
    [['GET'], 'social-links', \App\Http\Controllers\Admin\SocialLinkController::class, 'index', 'social-links.index', 0, []],
    [['GET'], 'social-links/create', \App\Http\Controllers\Admin\SocialLinkController::class, 'create', 'social-links.create', 0, []],
    [['POST'], 'social-links', \App\Http\Controllers\Admin\SocialLinkController::class, 'store', 'social-links.store', 0, []],
    [['GET'], 'social-links/{social_link}', \App\Http\Controllers\Admin\SocialLinkController::class, 'show', 'social-links.show', 0, []],
    [['GET'], 'social-links/{social_link}/edit', \App\Http\Controllers\Admin\SocialLinkController::class, 'edit', 'social-links.edit', 0, []],
    [['PUT', 'PATCH'], 'social-links/{social_link}', \App\Http\Controllers\Admin\SocialLinkController::class, 'update', 'social-links.update', 0, []],
    [['DELETE'], 'social-links/{social_link}', \App\Http\Controllers\Admin\SocialLinkController::class, 'destroy', 'social-links.destroy', 0, []],
    [['GET'], 'security/confirm', \App\Http\Controllers\Admin\TwoFactorController::class, 'showSensitiveConfirmation', 'security.confirm', 0, []],
    [['POST'], 'security/confirm', \App\Http\Controllers\Admin\TwoFactorController::class, 'confirmSensitiveAccess', 'security.confirm.store', 10, []],
    [['DELETE'], 'admin-users/{admin_user}/two-factor', \App\Http\Controllers\Admin\AdminUserController::class, 'resetTwoFactor', 'admin-users.two-factor.reset', 11, []],
    [['GET'], 'admin-users', \App\Http\Controllers\Admin\AdminUserController::class, 'index', 'admin-users.index', 11, []],
    [['GET'], 'admin-users/create', \App\Http\Controllers\Admin\AdminUserController::class, 'create', 'admin-users.create', 11, []],
    [['POST'], 'admin-users', \App\Http\Controllers\Admin\AdminUserController::class, 'store', 'admin-users.store', 11, []],
    [['GET'], 'admin-users/{admin_user}/edit', \App\Http\Controllers\Admin\AdminUserController::class, 'edit', 'admin-users.edit', 11, []],
    [['PUT', 'PATCH'], 'admin-users/{admin_user}', \App\Http\Controllers\Admin\AdminUserController::class, 'update', 'admin-users.update', 11, []],
    [['DELETE'], 'admin-users/{admin_user}', \App\Http\Controllers\Admin\AdminUserController::class, 'destroy', 'admin-users.destroy', 11, []],
    [['GET'], 'audit-logs', \App\Http\Controllers\Admin\AdminAuditLogController::class, 'index', 'audit-logs.index', 0, []],
    [['GET'], 'audit-logs/{auditLog}', \App\Http\Controllers\Admin\AdminAuditLogController::class, 'show', 'audit-logs.show', 0, []],
    [['GET'], 'trial-clicks', \App\Http\Controllers\Admin\TrialClickController::class, 'index', 'trial_clicks.index', 0, []],
    [['GET'], 'trial-clicks/export', \App\Http\Controllers\Admin\TrialClickController::class, 'export', 'trial_clicks.export', 0, []],
    [['PATCH'], 'trial-clicks/{trialClick}/trial', \App\Http\Controllers\Admin\TrialClickController::class, 'updateTrial', 'trial_clicks.trial', 12, []],
    [['DELETE'], 'trial-clicks/bulk-delete', \App\Http\Controllers\Admin\TrialClickController::class, 'bulkDelete', 'trial_clicks.bulkDelete', 0, []],
    [['DELETE'], 'trial-clicks/{trialClick}', \App\Http\Controllers\Admin\TrialClickController::class, 'destroy', 'trial_clicks.destroy', 0, []],
    [['GET'], 'maintenance', \App\Http\Controllers\Admin\MaintenanceController::class, 'index', 'maintenance.index', 0, []],
    [['POST'], 'maintenance/run', \App\Http\Controllers\Admin\MaintenanceController::class, 'run', 'maintenance.run', 13, []],
    [['POST'], 'maintenance/clear', \App\Http\Controllers\Admin\MaintenanceController::class, 'runFull', 'maintenance.clear', 13, []],
];
foreach ($adminEndpoints as [$methods, $uri, $controller, $action, $name, $guardSet, $wheres]) {
    $guards = $adminGuardSets[$guardSet];
    if ($methods !== ['GET'] || $name === 'notifications.index') {
        Route::match($methods, 'admin/'.$uri, [$controller, $action])
            ->middleware($guards)->where($wheres)->name('admin.'.$name);
    }
    // Some legacy resources register unused show actions; do not expose new broken APIs.
    if ($action === 'show' && !in_array($controller, [\App\Http\Controllers\UserClientController::class, \App\Http\Controllers\OrderController::class, \App\Http\Controllers\PanelOrderController::class, \App\Http\Controllers\Admin\AdminAuditLogController::class], true)) {
        continue;
    }
    Route::match($methods, 'api/browser/admin/'.$uri, [$controller, $action])
        ->middleware([ApiJsonResponse::class, ...$guards, \App\Http\Responses\AdminApiResponse::class])
        ->where($wheres)->name('api.browser.admin.'.$name);
}
Route::get('api/browser/admin/bootstrap', [\App\Http\Controllers\Admin\AuthController::class, 'bootstrap'])
    ->middleware([ApiJsonResponse::class, 'web'])->name('api.browser.admin.bootstrap');
