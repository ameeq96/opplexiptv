<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Blog;
use App\Models\HomeService;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Package;
use App\Models\Purchasing;
use App\Models\User;
use App\Http\Responses\AdminApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminApiReadTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('mail.default'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
        Notification::fake();
    }

    private function signIn(): Admin
    {
        $admin = Admin::create(['name' => 'API Owner', 'email' => 'owner@example.test', 'password' => bcrypt('Isolated!Password123'), 'role' => 'owner', 'auth_version' => 1, 'must_change_password' => false, 'two_factor_confirmed_at' => now()]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession(['admin_auth_version' => 1, 'admin_two_factor_verified_id' => $admin->id, 'admin_sensitive_confirmed_id' => $admin->id, 'admin_sensitive_confirmed_version' => 1, 'admin_sensitive_confirmed_at' => time()]);
        return $admin;
    }

    public function test_all_active_admin_index_and_create_reads_return_structured_json(): void
    {
        $this->signIn();
        foreach (['dashboard', 'clients', 'clients/create', 'clients/unlinked', 'orders', 'orders/create', 'panel-orders', 'panel-orders/create', 'purchasing', 'purchasing/create', 'trial-clicks', 'blogs', 'blogs/create', 'products', 'shop-products', 'shop-products/create', 'home-services', 'home-services/create', 'testimonials', 'testimonials/create', 'channel-logos', 'channel-logos/create', 'menu-items', 'menu-items/create', 'packages', 'packages/create', 'pricing-section', 'footer-settings', 'footer-links', 'footer-links/create', 'social-links', 'social-links/create', 'admin-users', 'admin-users/create', 'audit-logs', 'maintenance', 'password/change', 'security/confirm'] as $path) {
            $response = $this->getJson('/api/browser/admin/'.$path);
            $response->assertOk()->assertJsonStructure(['data', 'view', 'csrf_token']);
            $this->assertStringNotContainsString('<!DOCTYPE', $response->getContent(), $path);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
    }

    public function test_customer_profile_and_edit_return_allowed_data_and_pagination(): void
    {
        $this->signIn();
        $client = User::factory()->create(['name' => 'API Customer', 'email' => 'read@example.test', 'phone' => '+923001234567', 'notes' => 'Admin-visible note']);
        $this->getJson('/api/browser/admin/clients/'.$client->id)->assertOk()
            ->assertJsonPath('data.client.name', 'API Customer')
            ->assertJsonPath('data.orders.meta.total', 0)
            ->assertJsonMissingPath('data.client.password');
        $this->getJson('/api/browser/admin/clients/'.$client->id.'/edit')->assertOk()
            ->assertJsonPath('data.client.notes', 'Admin-visible note');
        $this->getJson('/api/browser/admin/clients')->assertOk()->assertJsonPath('data.clients.meta.total', 1);
    }

    public function test_admin_serializer_does_not_leak_credentials_or_unknown_attributes(): void
    {
        $admin = $this->signIn();
        $method = new \ReflectionMethod(AdminApiResponse::class, 'value');
        $admin->setAttribute('unexpected_private_column', 'never exposed');
        $admin->setAttribute('two_factor_secret', 'PRIVATE-SECRET');
        $data = $method->invoke(new AdminApiResponse, $admin);
        $this->assertSame('owner@example.test', $data['email']);
        foreach (['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'unexpected_private_column', 'auth_version'] as $key) {
            $this->assertArrayNotHasKey($key, $data);
        }
    }

    public function test_populated_order_and_purchase_reads_preserve_relations_dates_and_private_fields(): void
    {
        $this->signIn();
        $client = User::factory()->create(['name' => 'Read Customer', 'email' => 'orders@example.test']);
        $order = Order::withoutEvents(fn () => Order::create([
            'user_id' => $client->id, 'package' => 'Opplex', 'type' => 'package', 'price' => 12.50,
            'currency' => 'USD', 'status' => 'active', 'payment_status' => 'paid', 'paid_amount' => 12.50,
            'paid_currency' => 'USD', 'paid_at' => '2026-10-12 10:15:00', 'buying_date' => '2026-10-12',
            'expiry_date' => '2027-01-12', 'iptv_username' => 'PRIVATE-SERVICE-LOGIN',
        ]));
        $picture = $order->pictures()->create(['path' => 'private/payment-proofs/orders/read.png', 'original_name' => 'private-name.png', 'mime' => 'image/png', 'size' => 3]);
        $this->getJson('/api/browser/admin/orders/'.$order->id)->assertOk()
            ->assertJsonPath('data.order.user.id', $client->id)
            ->assertJsonPath('data.order.paid_amount', '12.50')
            ->assertJsonPath('data.order.paid_at', $order->paid_at->format(DATE_ATOM))
            ->assertJsonPath('data.order.expiry_date', $order->expiry_date->format(DATE_ATOM))
            ->assertJsonPath('data.order.pictures.0.id', $picture->id)
            ->assertJsonMissingPath('data.order.iptv_username')
            ->assertJsonMissingPath('data.order.user.password')
            ->assertJsonMissingPath('data.order.pictures.0.path');
        $this->getJson('/api/browser/admin/orders/'.$order->id.'/edit')->assertOk()
            ->assertJsonPath('data.order.package', 'Opplex')
            ->assertJsonPath('data.clients.0.email', 'orders@example.test');
        $this->getJson('/api/browser/admin/orders')->assertOk()->assertJsonPath('data.orders.meta.total', 1)
            ->assertJsonPath('data.orders.data.0.user.name', 'Read Customer');

        $purchase = Purchasing::create(['item_name' => 'Isolated purchase', 'cost_price' => 32.25, 'currency' => 'USD', 'quantity' => 2, 'purchase_date' => '2026-10-10']);
        $this->getJson('/api/browser/admin/purchasing/'.$purchase->id.'/edit')->assertOk()
            ->assertJsonPath('data.purchasing.item_name', 'Isolated purchase')
            ->assertJsonPath('data.purchasing.purchase_date', '2026-10-10');
        $this->getJson('/api/browser/admin/purchasing')->assertOk()->assertJsonPath('data.purchases.meta.total', 1)
            ->assertJsonPath('data.purchases.data.0.quantity', 2);
    }

    public function test_populated_package_and_blog_edit_reads_include_translations_and_casts(): void
    {
        $this->signIn();
        $package = Package::create([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex API read fixture', 'price_amount' => 12.50,
            'duration_months' => 3, 'features' => ['Support'], 'icons' => ['images/icon.svg'],
            'connection_prices' => ['2' => 20], 'active' => true, 'is_featured' => true,
        ]);
        $package->translations()->create(['locale' => 'en', 'title' => 'Opplex translated', 'features' => ['Translated support']]);
        $this->getJson('/api/browser/admin/packages/'.$package->id.'/edit')->assertOk()
            ->assertJsonPath('data.package.price_amount', 12.5)
            ->assertJsonPath('data.package.duration_months', 3)
            ->assertJsonPath('data.package.active', true)
            ->assertJsonPath('data.package.features.0', 'Support')
            ->assertJsonPath('data.package.connection_prices.2', 20)
            ->assertJsonPath('data.package.translations.0.title', 'Opplex translated');
        $this->getJson('/api/browser/admin/packages?q=API%20read%20fixture')->assertOk()
            ->assertJsonPath('data.packages.meta.total', 1)->assertJsonPath('data.packages.data.0.id', $package->id);

        $blog = Blog::create(['status' => 'published', 'published_at' => '2026-10-10 12:00:00', 'reading_time' => 2, 'is_featured' => true]);
        $blog->translations()->create([
            'locale' => 'en', 'title' => 'Read API guide', 'slug' => 'read-api-guide',
            'content' => '<p>Stored editorial content.</p>', 'schema_json' => ['@type' => 'Article', 'headline' => 'Read API guide'],
        ]);
        $this->getJson('/api/browser/admin/blogs/'.$blog->id.'/edit')->assertOk()
            ->assertJsonPath('data.blog.is_featured', true)
            ->assertJsonPath('data.blog.published_at', $blog->published_at->format(DATE_ATOM))
            ->assertJsonPath('data.blog.translations.0.slug', 'read-api-guide')
            ->assertJsonPath('data.blog.translations.0.content', '<p>Stored editorial content.</p>')
            ->assertJsonPath('data.blog.translations.0.schema_json.@type', 'Article');
        $this->getJson('/api/browser/admin/blogs')->assertOk()->assertJsonPath('data.blogs.meta.total', 1);
    }

    public function test_populated_cms_reads_keep_menu_children_and_edit_translation_data(): void
    {
        $this->signIn();
        $service = HomeService::create(['title' => 'Setup help', 'description' => 'Setup instructions', 'link' => '/account/setup', 'is_active' => true, 'sort_order' => 2]);
        $service->translations()->create(['locale' => 'en', 'title' => 'Translated setup', 'description' => 'Translated instructions']);
        $this->getJson('/api/browser/admin/home-services/'.$service->id.'/edit')->assertOk()
            ->assertJsonPath('data.service.is_active', true)
            ->assertJsonPath('data.service.sort_order', 2)
            ->assertJsonPath('data.service.translations.0.title', 'Translated setup');

        $parent = MenuItem::create(['label' => 'Account', 'url' => '/account', 'is_active' => true]);
        $child = MenuItem::create(['label' => 'Support', 'url' => '/account/support', 'parent_id' => $parent->id, 'is_active' => true, 'open_new_tab' => false]);
        $child->translations()->create(['locale' => 'en', 'label' => 'Translated support']);
        $this->getJson('/api/browser/admin/menu-items')->assertOk()
            ->assertJsonPath('data.items.0.children.0.id', $child->id)
            ->assertJsonPath('data.items.0.children.0.open_new_tab', false);
        $this->getJson('/api/browser/admin/menu-items/'.$child->id.'/edit')->assertOk()
            ->assertJsonPath('data.item.translations.0.label', 'Translated support')
            ->assertJsonPath('data.parents.0.id', $parent->id);
    }

    public function test_protected_proof_routes_stream_bytes_and_reject_other_record_pictures(): void
    {
        $client = User::factory()->create();
        $order = Order::withoutEvents(fn () => Order::create(['user_id' => $client->id, 'package' => 'Opplex', 'price' => 5]));
        $other = Order::withoutEvents(fn () => Order::create(['user_id' => $client->id, 'package' => 'Opplex', 'price' => 5]));
        $picture = $order->pictures()->create(['path' => 'private/payment-proofs/orders/read.png', 'mime' => 'image/png', 'size' => 3]);
        $url = '/api/browser/admin/orders/'.$order->id.'/pictures/'.$picture->id;
        $this->getJson($url)->assertUnauthorized();
        $this->signIn();
        $this->getJson('/api/browser/admin/orders/'.$other->id.'/pictures/'.$picture->id)->assertNotFound();
        $disk = \Mockery::mock();
        Storage::shouldReceive('disk')->once()->with('local')->andReturn($disk);
        $disk->shouldReceive('exists')->once()->with($picture->path)->andReturn(true);
        $disk->shouldReceive('response')->once()->with($picture->path, 'payment-proof-'.$picture->id.'.png', \Mockery::type('array'))
            ->andReturn(response()->stream(static function () { echo 'PNG'; }, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, no-store']));
        $response = $this->getJson($url)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame('PNG', $response->streamedContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_crm_export_stays_a_protected_csv_stream_with_safe_cells(): void
    {
        $url = '/api/browser/admin/clients/export/crm';
        $this->getJson($url)->assertUnauthorized();
        $this->signIn();
        $client = User::factory()->create(['name' => '=SUM(1+1)', 'email' => 'export@example.test', 'phone' => '+923001234567']);
        Order::withoutEvents(fn () => Order::create(['user_id' => $client->id, 'package' => 'Opplex', 'price' => 5, 'iptv_username' => 'SECRET-NOT-EXPORTED']));
        $response = $this->getJson($url)->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Customer ID', $csv);
        $this->assertStringContainsString("'=SUM(1+1)", $csv);
        $this->assertStringContainsString('export@example.test', $csv);
        $this->assertStringNotContainsString('SECRET-NOT-EXPORTED', $csv);
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_guest_bootstrap_and_login_data_do_not_expose_captcha_answer_or_admin_data(): void
    {
        $this->getJson('/api/browser/admin/bootstrap')->assertOk()->assertJsonPath('data.authenticated', false)->assertJsonStructure(['csrf_token']);
        $this->getJson('/api/browser/admin/login')->assertOk()->assertJsonStructure(['data' => ['num1', 'num2']])->assertJsonMissingPath('data.captcha_sum')->assertJsonMissingPath('data.admin');
    }

    public function test_original_pages_keep_html_and_load_real_api_hydration(): void
    {
        $this->signIn();
        $this->get('/admin/dashboard')->assertOk()->assertSee('js/admin-api.js')->assertSee('data-admin-api-field="totalOrders"', false);
    }
}
