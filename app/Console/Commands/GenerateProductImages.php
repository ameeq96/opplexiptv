<?php

namespace App\Console\Commands;

use App\Models\Digital\DigitalProduct;
use App\Models\ShopProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GenerateProductImages extends Command
{
    protected $signature = 'products:generate-images
                            {--type=all : all|digital|affiliate}
                            {--limit=0 : Max products to process (0 = all)}
                            {--force : Regenerate even if image already exists}';

    protected $description = 'Generate AI images for digital and affiliate products and attach them to product records.';

    public function handle(): int
    {
        $apiKey = (string) env('OPENAI_API_KEY', '');
        if ($apiKey === '') {
            $this->error('OPENAI_API_KEY is missing in .env. Add key and rerun command.');
            return self::FAILURE;
        }

        $type = strtolower((string) $this->option('type'));
        if (!in_array($type, ['all', 'digital', 'affiliate'], true)) {
            $this->error('Invalid --type. Use all|digital|affiliate');
            return self::FAILURE;
        }

        $limit = max(0, (int) $this->option('limit'));
        $force = (bool) $this->option('force');

        $processed = 0;
        $failed = 0;

        if (in_array($type, ['all', 'digital'], true)) {
            $digitalQuery = DigitalProduct::query()->orderBy('sort_order')->orderByDesc('id');
            if ($limit > 0) {
                $digitalQuery->limit($limit);
            }

            foreach ($digitalQuery->get() as $product) {
                if (!$force && !empty($product->image)) {
                    $this->line("[skip][digital] {$product->title}");
                    continue;
                }

                $filename = $this->generateAndStore(
                    apiKey: $apiKey,
                    prompt: $this->buildDigitalPrompt($product->title, (string) $product->short_description),
                    directory: public_path('images/digital-products'),
                    slugBase: $product->slug ?: Str::slug($product->title),
                );

                if (!$filename) {
                    $failed++;
                    $this->warn("[fail][digital] {$product->title}");
                    continue;
                }

                $product->update(['image' => $filename]);
                $processed++;
                $this->info("[ok][digital] {$product->title} -> {$filename}");
                usleep(300000);
            }
        }

        if (in_array($type, ['all', 'affiliate'], true)) {
            $affiliateQuery = ShopProduct::query()->orderBy('sort_order')->orderByDesc('id');
            if ($limit > 0) {
                $affiliateQuery->limit($limit);
            }

            foreach ($affiliateQuery->get() as $product) {
                if (!$force && !empty($product->image)) {
                    $this->line("[skip][affiliate] {$product->name}");
                    continue;
                }

                $filename = $this->generateAndStore(
                    apiKey: $apiKey,
                    prompt: $this->buildAffiliatePrompt($product->name, (string) $product->asin),
                    directory: public_path('images/shop'),
                    slugBase: $product->asin ? Str::slug($product->asin) : Str::slug($product->name),
                );

                if (!$filename) {
                    $failed++;
                    $this->warn("[fail][affiliate] {$product->name}");
                    continue;
                }

                $product->update(['image' => $filename]);
                $processed++;
                $this->info("[ok][affiliate] {$product->name} -> {$filename}");
                usleep(300000);
            }
        }

        $this->newLine();
        $this->info("Completed. Processed: {$processed}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function generateAndStore(string $apiKey, string $prompt, string $directory, string $slugBase): ?string
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $response = Http::timeout(120)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'gpt-image-1',
                'prompt' => $prompt,
                'size' => '1024x1024',
            ]);

        if (!$response->successful()) {
            $this->warn('API error: ' . $response->status());
            return null;
        }

        $payload = $response->json();
        $b64 = data_get($payload, 'data.0.b64_json');

        if (!is_string($b64) || $b64 === '') {
            return null;
        }

        $binary = base64_decode($b64, true);
        if ($binary === false) {
            return null;
        }

        $filename = Str::slug($slugBase) . '-' . time() . '.png';
        $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($path, $binary);

        return $filename;
    }

    private function buildDigitalPrompt(string $title, string $desc = ''): string
    {
        $desc = trim($desc);

        return "Create a high-quality e-commerce product image for '{$title}'. "
            . ($desc !== '' ? "Context: {$desc}. " : '')
            . 'Style: realistic premium product card visual, clean dark studio background, centered composition, no text, no logos, no watermark, no brand infringement.';
    }

    private function buildAffiliatePrompt(string $name, string $asin = ''): string
    {
        $asinText = trim($asin) !== '' ? "ASIN context: {$asin}. " : '';

        return "Create a clean realistic e-commerce hero product image for '{$name}'. "
            . $asinText
            . 'Style: studio product shot on neutral background, modern lighting, sharp details, no text, no watermark.';
    }
}
