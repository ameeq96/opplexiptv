<?php

namespace App\Http\Controllers\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Models\Digital\DigitalProduct;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        return redirect()->route('shop', ['type' => 'digital']);
    }

    public function show(string $slug)
    {
        $product = DigitalProduct::query()
            ->with('category:id,name,slug')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $productUrl = route('digital.product.show', $product->slug);
        $productImage = $product->image
            ? asset('images/digital-products/' . $product->image)
            : asset('images/placeholder.webp');
        $descriptionSource = $product->short_description
            ?: $product->full_description
            ?: "Buy {$product->title} from Opplex IPTV with digital delivery.";
        $productDescription = Str::limit(
            trim((string) preg_replace('/\s+/', ' ', strip_tags($descriptionSource))),
            250
        );
        $organizationId = url('/') . '#organization';
        $currency = strtoupper((string) ($product->currency ?: 'USD'));
        $price = number_format((float) $product->price, 2, '.', '');

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'OnlineStore',
                    '@id' => $organizationId,
                    'name' => 'Opplex IPTV',
                    'url' => url('/'),
                    'description' => 'Opplex IPTV provides IPTV subscription services and digital products with online delivery.',
                    'logo' => asset('images/opplexiptvlogo.webp'),
                    'telephone' => '+1-639-390-3194',
                    'email' => 'info@opplexiptv.com',
                    'areaServed' => 'Worldwide',
                    'hasMerchantReturnPolicy' => [
                        '@type' => 'MerchantReturnPolicy',
                        'merchantReturnLink' => route('refund-policy'),
                    ],
                    'hasShippingService' => [
                        '@type' => 'ShippingService',
                        'name' => 'Digital delivery only',
                        'shippingConditions' => [
                            [
                                '@type' => 'ShippingConditions',
                                'doesNotShip' => true,
                            ],
                        ],
                    ],
                ],
                [
                    '@type' => 'Product',
                    '@id' => $productUrl . '#product',
                    'name' => $product->title,
                    'url' => $productUrl,
                    'image' => [$productImage],
                    'description' => $productDescription,
                    'sku' => 'digital-' . $product->id,
                    'brand' => [
                        '@type' => 'Brand',
                        'name' => 'Opplex IPTV',
                    ],
                    'category' => $product->category?->name ?: 'Digital product',
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => $productUrl,
                        'priceCurrency' => $currency,
                        'price' => $price,
                        'priceValidUntil' => now()->addDays(30)->toDateString(),
                        'availability' => 'https://schema.org/InStock',
                        'itemCondition' => 'https://schema.org/NewCondition',
                        'shippingDetails' => [
                            '@type' => 'OfferShippingDetails',
                            'doesNotShip' => true,
                        ],
                        'seller' => [
                            '@id' => $organizationId,
                        ],
                    ],
                ],
            ],
        ];

        return view('pages.digital-commerce.product', [
            'product' => $product,
            'jsonLd' => $jsonLd,
            'productImage' => $productImage,
            'productDescription' => $productDescription,
            'pageMetaTitle' => $product->title . ' | Opplex IPTV',
            'pageMetaDescription' => $productDescription,
            'pageMetaImage' => $productImage,
            'pageCanonical' => $productUrl,
            'pageOgTitle' => $product->title . ' | Opplex IPTV',
            'pageOgDescription' => $productDescription,
            'pageOgType' => 'product',
        ]);
    }
}
