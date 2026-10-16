<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Package;
use App\Models\ShopProduct;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicSiteApiTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('session.driver'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Bus::fake();
        Cache::flush();
        $this->mock(TmdbService::class, function ($mock) {
            $mock->shouldReceive('configured')->andReturn(false);
        });
    }

    public function test_public_page_has_structured_content_without_session_or_configuration_secrets(): void
    {
        config()->set('services.opplexify.shared_secret', 'never-expose-this');
        $response = $this->withSession(['captcha_sum' => 11])
            ->get('/api/v1/site/pages/contact');

        $response->assertOk()->assertJsonPath('data.page_name', 'contact')
            ->assertJsonPath('data.locale', 'en')->assertJsonStructure([
                'data' => ['menuItems', 'footer', 'meta' => ['title', 'canonical'], 'content'],
            ])->assertJsonMissingPath('data.assistant')
            ->assertJsonMissingPath('data.num1')->assertJsonMissingPath('data.num2')
            ->assertJsonMissingPath('data.activeEventPromotion');
        $this->assertStringNotContainsString('never-expose-this', $response->getContent());
        $this->assertStringNotContainsString('captcha_sum', $response->getContent());
        $this->assertSame(11, session('captcha_sum'));
    }

    public function test_page_context_is_explicit_and_unknown_or_invalid_pages_fail_as_json(): void
    {
        $this->get('/api/v1/site/pages/about')->assertOk()->assertJsonPath('data.page_name', 'about');
        $this->get('/api/v1/site/pages/admin')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->get('/api/v1/site/pages/movies?page=-1')->assertUnprocessable()->assertJsonValidationErrors('page');
        $this->get('/api/v1/site/pages/movies?locale=not-supported')->assertUnprocessable();
    }

    public function test_public_reads_keep_first_response_html_content_and_seo(): void
    {
        $payload = $this->get('/api/v1/site/pages/about')->assertOk()->json('data');
        $response = $this->get('/about')->assertOk();
        $response->assertSeeText($payload['content']['hero']['heading'])
            ->assertSee($payload['meta']['title'])
            ->assertSee('application/ld+json', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_every_public_page_has_localized_structured_content(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale) {
            foreach (\App\Services\Api\PublicSiteDataService::PAGES as $page) {
                $response = $this->get('/api/v1/site/pages/'.$page.'?locale='.$locale)
                    ->assertOk()->assertJsonPath('data.locale', $locale)->assertJsonPath('data.page_name', $page);
                $this->assertIsArray($response->json('data.content'), $locale.':'.$page);
                $this->assertNotEmpty($response->json('data.content'), $locale.':'.$page);
                $this->assertNotEmpty($response->json('data.meta.title'), $locale.':'.$page);
                $prefix = $locale === 'en' ? '' : '/'.$locale;
                $path = $page === 'home' ? $prefix : $prefix.'/'.$page;
                $this->assertSame($path, rtrim((string) parse_url($response->json('data.meta.canonical'), PHP_URL_PATH), '/'));
            }
        }
    }

    public function test_home_and_products_use_same_active_product_source(): void
    {
        $active = ShopProduct::create([
            'name' => 'Public device', 'asin' => 'PUBLIC-DEVICE',
            'link' => 'https://example.test/device', 'image' => 'test-device.webp', 'is_active' => true, 'sort_order' => 1,
        ]);
        ShopProduct::create([
            'name' => 'Hidden device', 'asin' => 'PRIVATE-DEVICE',
            'link' => 'https://example.test/hidden', 'image' => 'hidden-device.webp', 'is_active' => false,
        ]);
        $home = $this->get('/api/v1/site/pages/home')->assertOk();
        $this->assertContains($active->id, collect($home->json('data.homeAffiliateProducts'))->pluck('id')->all());
        $this->assertStringNotContainsString('Hidden device', $home->getContent());
        $products = $this->get('/api/v1/site/products?type=affiliate&per_page=1')->assertOk()
            ->assertJsonPath('meta.per_page', 1)->assertJsonPath('data.0.name', 'Public device');
        $this->assertStringNotContainsString('Hidden device', $products->getContent());
        $this->get('/api/v1/site/products?per_page=101')->assertUnprocessable();
        $this->get('/api/v1/site/products?type=unapproved')->assertUnprocessable();
        $this->get('/api/v1/site/products/affiliate/'.$active->id)->assertOk()
            ->assertJsonPath('data.productName', 'Public device');
        $this->get('/api/v1/site/products/unknown/'.$active->id)->assertNotFound();
        $this->get('/')->assertOk()->assertSeeText('Public device');
    }

    public function test_provider_catalog_is_structured_and_shares_compatibility_data(): void
    {
        $first = null;
        foreach ([1, 3, 6, 12] as $index => $months) {
            $first ??= $this->package('Opplex', 1);
            if ($index !== 0) {
                $this->package('Opplex - '.($months === 6 ? 'Half Yearly' : ($months === 12 ? 'Yearly' : '3 Months')), $months);
            }
        }
        $structured = $this->get('/api/v1/site/providers/'.$first->id.'/plans')->assertOk()
            ->assertJsonPath('data.provider_name', 'Opplex')->assertJsonPath('data.count', 4)
            ->assertJsonMissingPath('data.html')->assertJsonStructure(['data' => ['packages' => [['id', 'price_amount', 'duration_months']]]]);
        $compatibility = $this->get('/api/packages/providers/'.$first->id.'/plans')->assertOk();
        $this->assertSame($structured->json('data.count'), $compatibility->json('count'));
        $this->assertStringContainsString('data-package-id="'.$first->id.'"', $compatibility->json('html'));
        $first->update(['active' => false]);
        $this->get('/api/v1/site/providers/'.$first->id.'/plans')->assertNotFound();
    }

    public function test_localized_public_links_keep_language_without_changing_external_targets(): void
    {
        $product = ShopProduct::create(['name' => 'Device', 'asin' => 'LOCALE-DEVICE', 'link' => 'https://example.test/device', 'image' => 'test.webp', 'is_active' => true]);
        \App\Models\MenuItem::create(['label' => 'About', 'url' => '/about', 'is_active' => true, 'sort_order' => 1]);
        $home = $this->get('/api/v1/site/pages/home?locale=it')->assertOk();
        $this->assertSame('/it/packages', parse_url($home->json('data.features.0.link'), PHP_URL_PATH));
        $this->assertSame('direct=1', parse_url($home->json('data.features.0.link'), PHP_URL_QUERY));
        $this->assertSame('/it/contact', parse_url($home->json('data.features.3.link'), PHP_URL_PATH));
        $this->assertSame('/it/about', parse_url($home->json('data.menuItems.0.url'), PHP_URL_PATH));
        $this->assertSame('/it/product-share/affiliate/'.$product->id, parse_url($home->json('data.homeAffiliateProducts.0.share_url'), PHP_URL_PATH));
        $products = $this->get('/api/v1/site/products?locale=it&type=affiliate')->assertOk();
        $this->assertSame('/it/product-share/affiliate/'.$product->id, parse_url($products->json('data.0.share_url'), PHP_URL_PATH));
        $products->assertJsonPath('data.0.url', 'https://example.test/device');
        $detail = $this->get('/api/v1/site/products/affiliate/'.$product->id.'?locale=it')->assertOk();
        $this->assertSame('/it/product-share/affiliate/'.$product->id, parse_url($detail->json('data.pageCanonical'), PHP_URL_PATH));
        $english = $this->get('/api/v1/site/pages/home?locale=en')->assertOk();
        $this->assertSame('/packages', parse_url($english->json('data.features.0.link'), PHP_URL_PATH));
    }

    public function test_localized_blog_links_and_schema_preserve_explicit_canonical_overrides(): void
    {
        $blog = $this->article('english-entry', 'published', now()->subDay());
        $translation = $blog->translations()->create(['locale' => 'it', 'slug' => 'articolo-italiano', 'title' => 'Articolo', 'content' => '<p>Contenuto.</p>']);
        $list = $this->get('/api/v1/site/blogs?locale=it')->assertOk();
        $this->assertSame('/it/blogs', parse_url($list->json('data.meta.canonical'), PHP_URL_PATH));
        $this->assertSame('/it/blogs/articolo-italiano', parse_url($list->json('data.articles.0.url'), PHP_URL_PATH));
        $detail = $this->get('/api/v1/site/blogs/articolo-italiano?locale=it')->assertOk();
        foreach (['data.article.url', 'data.meta.canonical', 'data.schema.mainEntityOfPage.@id'] as $key) {
            $this->assertSame('/it/blogs/articolo-italiano', parse_url($detail->json($key), PHP_URL_PATH));
        }
        $translation->update(['canonical_url' => 'https://example.test/original-article']);
        $this->get('/api/v1/site/blogs/articolo-italiano?locale=it')->assertOk()
            ->assertJsonPath('data.meta.canonical', 'https://example.test/original-article')
            ->assertJsonPath('data.schema.mainEntityOfPage.@id', 'https://example.test/original-article');
    }

    public function test_configurator_is_public_but_does_not_return_personal_promotion_data(): void
    {
        $package = $this->package('Opplex', 1);
        $response = $this->withSession(['event_promotion' => [
            'recipient_email_normalized' => 'private@example.test', 'recipient_user_id' => 998,
        ]])->get('/api/v1/site/configure');
        $response->assertOk()->assertJsonPath('data.eventPromotion', null);
        $this->assertContains($package->id, collect($response->json('data.iptvPackages'))->pluck('id')->all());
        $this->assertStringNotContainsString('private@example.test', $response->getContent());
    }

    public function test_published_blog_apis_do_not_leak_author_credentials_or_increment_views(): void
    {
        $author = User::create(['name' => 'Public author', 'email' => 'private-author@example.test', 'password' => bcrypt('not-for-public-api')]);
        $blog = $this->article('public-article', 'published', now()->subDay(), $author->id);
        $this->article('draft-article', 'draft');
        $this->article('scheduled-article', 'published', now()->addDay());
        $list = $this->get('/api/v1/site/blogs')->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonPath('data.articles.0.slug', 'public-article');
        $article = $this->get('/api/v1/site/blogs/public-article')->assertOk()
            ->assertJsonPath('data.article.content', '<p>Published content.</p>')
            ->assertJsonPath('data.article.author', 'Public author');
        foreach ([$list, $article] as $response) {
            $this->assertStringNotContainsString('private-author@example.test', $response->getContent());
            $this->assertStringNotContainsString('password', $response->getContent());
        }
        $this->assertSame(0, (int) $blog->fresh()->views);
        $this->get('/blogs/public-article')->assertOk()->assertSeeText('Public article');
        $this->assertSame(1, (int) $blog->fresh()->views);
        $this->get('/api/v1/site/blogs/draft-article')->assertNotFound();
        $this->get('/api/v1/site/blogs/scheduled-article')->assertNotFound();
        $this->get('/api/v1/site/blogs?page=0')->assertUnprocessable();
    }

    public function test_blog_search_and_localized_slug_redirect_remain_available(): void
    {
        $blog = $this->article('english-entry', 'published', now()->subDay());
        $blog->translations()->create([
            'locale' => 'es', 'slug' => 'articulo-espanol', 'title' => 'Articulo publicado',
            'excerpt' => 'Busqueda unica', 'content' => '<p>Contenido.</p>',
        ]);
        $this->get('/api/v1/site/blogs?locale=es&q=Busqueda')->assertOk()
            ->assertJsonPath('data.articles.0.slug', 'articulo-espanol');
        $response = $this->get('/api/v1/site/blogs/articulo-espanol?locale=en')->assertOk();
        $this->assertStringContainsString('/es/blogs/articulo-espanol', $response->json('redirect_url'));
        $this->assertSame(0, (int) $blog->fresh()->views);
    }

    public function test_brand_landing_plans_use_their_own_stored_prices_and_durations(): void
    {
        Package::query()->update(['active' => false]);
        $brands = [
            'trex' => ['TREX IPTV', [12 => 71]],
            'opplex' => ['Opplex iptv', [1 => 2.99, 3 => 7.99, 6 => 14.99, 12 => 23.99]],
            'geo' => ['GEO World IPTV', [1 => 4, 3 => 12, 6 => 24, 12 => 48]],
            '5g' => ['5G IPTV', [1 => 6, 3 => 18, 6 => 36, 12 => 72]],
        ];
        $suffixes = [1 => '', 3 => ' - 3 Months', 6 => ' - Half Yearly', 12 => ' - Yearly'];
        foreach ($brands as [$name, $prices]) {
            foreach ($prices as $months => $price) {
                $this->package($name.$suffixes[$months], $months)->update([
                    'price_amount' => $price, 'display_price' => '$'.$price.' / '.$months.' months',
                ]);
            }
        }

        foreach ($brands as $brand => [$name, $prices]) {
            $response = $this->get('/api/v1/site/brands/'.$brand.'/plans?locale=en')
                ->assertOk()->assertJsonPath('data.brand', $brand)
                ->assertJsonPath('data.provider_name', $name)->assertJsonCount(count($prices), 'data.packages');
            $this->assertSame(array_keys($prices), array_column($response->json('data.packages'), 'duration_months'));
            $this->assertSame(array_map('floatval', array_values($prices)), array_map('floatval', array_column($response->json('data.packages'), 'price_amount')));
            foreach ($response->json('data.packages') as $plan) {
                $this->assertSame($name, $plan['service']);
            }
        }

        Package::where('title', 'GEO World IPTV')->update(['price_amount' => 4.25, 'display_price' => '$4.25 / 1 month']);
        $this->get('/api/v1/site/brands/geo/plans')->assertOk()->assertJsonPath('data.packages.0.price_amount', 4.25);
    }

    public function test_brand_catalog_excludes_retired_templates_invalid_durations_and_unavailable_plans(): void
    {
        Package::query()->update(['active' => false]);
        $yearly = $this->package('TREX IPTV - Yearly', 12);
        $this->package('TREX IPTV', 1);
        $this->package('TREX IPTV - 3 Months', 3);
        $this->package('5G Live IPTV (Zaintv)', 1);
        $this->package('GEO World IPTV', 12);
        $this->package('GEO World IPTV - 3 Months', 3)->update(['active' => false]);
        $this->package('GEO World IPTV - Half Yearly', 6)->update(['is_available' => false]);
        $this->package('GEO World IPTV - Yearly', 12)->update(['price_amount' => 0]);
        $this->package('5G IPTV', 1)->update(['vendor' => 'starshare']);
        $this->get('/api/v1/site/brands/trex/plans')->assertOk()->assertJsonCount(1, 'data.packages')
            ->assertJsonPath('data.packages.0.id', $yearly->id);
        foreach (['geo', '5g'] as $brand) {
            $this->get('/api/v1/site/brands/'.$brand.'/plans')->assertOk()->assertJsonPath('data.count', 0);
        }
        $this->get('/api/v1/site/brands/unknown/plans')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->get('/api/v1/site/brands/geo/plans?locale=not-supported')->assertUnprocessable();
    }

    public function test_brand_checkout_catalog_keeps_main_provider_choices_unchanged(): void
    {
        Package::query()->update(['active' => false]);
        $opplex = $this->package('Opplex iptv', 1);
        $filex = $this->package('FILEX IPTV', 1);
        $trex = $this->package('TREX IPTV - Yearly', 12);
        $geo = $this->package('GEO World IPTV', 1);
        $fiveG = $this->package('5G IPTV', 1);
        $retired = $this->package('TREX IPTV', 1);
        $unapproved = $this->package('Another IPTV', 1);
        $main = $this->get('/api/v1/site/pages/packages')->assertOk();
        $this->assertEqualsCanonicalizing([$opplex->id, $filex->id], array_column($main->json('data.iptvProviders'), 'id'));
        $this->get('/api/v1/site/providers/'.$trex->id.'/plans')->assertNotFound();
        $configure = $this->get('/api/v1/site/configure')->assertOk();
        $this->assertNotContains($trex->id, array_column($configure->json('data.iptvPackages'), 'id'));

        config()->set('services.opplexify.shared_secret', 'brand-catalog-test-only');
        $this->getJson('/api/integrations/opplexify/catalog')->assertUnauthorized();
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', "GET\n/api/integrations/opplexify/catalog\n".$timestamp, 'brand-catalog-test-only');
        $catalog = $this->getJson('/api/integrations/opplexify/catalog', [
            'X-Opplexify-Timestamp' => $timestamp, 'X-Opplexify-Signature' => $signature,
        ])->assertOk();
        $ids = array_column($catalog->json('data'), 'id');
        foreach ([$opplex, $filex, $trex, $geo, $fiveG] as $plan) {
            $this->assertContains($plan->id, $ids);
        }
        $this->assertNotContains($retired->id, $ids);
        $this->assertNotContains($unapproved->id, $ids);
        $this->assertStringNotContainsString('brand-catalog-test-only', $catalog->getContent());
    }

    public function test_brand_plan_uses_existing_signed_checkout_without_starting_a_payment(): void
    {
        Package::query()->update(['active' => false]);
        $plan = $this->package('TREX IPTV - Yearly', 12);
        config()->set('services.opplexify.shared_secret', 'brand-handoff-test-only');
        config()->set('services.opplexify.url', 'https://checkout.example.test');
        $response = $this->get('/checkout?package_id='.$plan->id)->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertSame('checkout.example.test', parse_url($target, PHP_URL_HOST));
        parse_str(parse_url($target, PHP_URL_QUERY), $query);
        $this->assertSame(hash_hmac('sha256', $query['selection'], 'brand-handoff-test-only'), $query['signature']);
        $payload = json_decode(base64_decode(strtr($query['selection'], '-_', '+/')), true);
        $this->assertSame($plan->id, $payload['package_id']);
        $this->assertGreaterThan(now()->timestamp, $payload['exp']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_landing_brands_do_not_hide_generic_main_checkout_plans(): void
    {
        Package::query()->update(['active' => false]);
        $monthly = $this->package('Monthly', 1);
        $yearly = $this->package('Yearly', 12);
        $trex = $this->package('TREX IPTV - Yearly', 12);
        $catalog = app(\App\Services\Api\PublicSiteDataService::class)->catalog();
        $this->assertEqualsCanonicalizing([$monthly->id, $yearly->id, $trex->id], $catalog['data']->pluck('id')->all());
    }

    private function package(string $title, int $months): Package
    {
        return Package::create([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => $title,
            'price_amount' => 5 * $months, 'display_price' => '$'.(5 * $months),
            'duration_months' => $months, 'active' => true, 'is_available' => true,
            'features' => ['Setup help'], 'sort_order' => $months,
        ]);
    }

    private function article(string $slug, string $status, $publishedAt = null, ?int $authorId = null): Blog
    {
        $blog = Blog::create(['status' => $status, 'published_at' => $publishedAt, 'author_id' => $authorId, 'views' => 0]);
        $blog->translations()->create([
            'locale' => 'en', 'slug' => $slug, 'title' => 'Public article',
            'excerpt' => 'Article excerpt', 'content' => '<p>Published content.</p>',
        ]);

        return $blog;
    }
}
