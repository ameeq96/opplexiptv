<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Throwable;

class BlogCoverImageService
{
    private const FEATURED_SIZES = [
        480 => 300,
        672 => 420,
    ];

    private const WEBP_QUALITY = 70;

    private const MAX_SOURCE_PIXELS = 16_000_000;

    /**
     * Build cached, responsive WebP variants for the featured image on the blog index.
     *
     * @return array{src: string, srcset: string, width: int, height: int}|null
     */
    public function featured(?string $sourcePath): ?array
    {
        $sourcePath = str_replace('\\', '/', trim((string) $sourcePath));

        if (
            $sourcePath === ''
            || ! str_starts_with($sourcePath, 'blogs/')
            || str_contains($sourcePath, '../')
            || ! function_exists('imagecreatefromstring')
            || ! function_exists('imagewebp')
        ) {
            return null;
        }

        $disk = Storage::disk('public');

        try {
            if (! $disk->exists($sourcePath)) {
                return null;
            }

            $signature = sha1(implode('|', [
                $sourcePath,
                (string) $disk->size($sourcePath),
                (string) $disk->lastModified($sourcePath),
                (string) self::WEBP_QUALITY,
            ]));

            $variantPaths = [];
            foreach (self::FEATURED_SIZES as $width => $height) {
                $variantPaths[$width] = "blogs/variants/{$signature}-{$width}x{$height}.webp";
            }

            $missingVariants = array_filter(
                $variantPaths,
                fn (string $path): bool => ! $disk->exists($path)
            );

            if ($missingVariants !== []) {
                $sourceData = $disk->get($sourcePath);
                $sourceInfo = @getimagesizefromstring($sourceData);

                if (
                    ! is_array($sourceInfo)
                    || ($sourceInfo[0] * $sourceInfo[1]) > self::MAX_SOURCE_PIXELS
                ) {
                    return null;
                }

                $sourceImage = @imagecreatefromstring($sourceData);
                if ($sourceImage === false) {
                    return null;
                }

                try {
                    [$cropX, $cropY, $cropWidth, $cropHeight] = $this->centerCrop(
                        imagesx($sourceImage),
                        imagesy($sourceImage),
                        8 / 5
                    );

                    foreach ($missingVariants as $width => $variantPath) {
                        $height = self::FEATURED_SIZES[$width];
                        $variant = imagecreatetruecolor($width, $height);
                        if ($variant === false) {
                            continue;
                        }

                        imagealphablending($variant, false);
                        imagesavealpha($variant, true);
                        $transparent = imagecolorallocatealpha($variant, 0, 0, 0, 127);
                        imagefill($variant, 0, 0, $transparent);

                        imagecopyresampled(
                            $variant,
                            $sourceImage,
                            0,
                            0,
                            $cropX,
                            $cropY,
                            $width,
                            $height,
                            $cropWidth,
                            $cropHeight
                        );

                        $bufferLevel = ob_get_level();
                        ob_start();

                        try {
                            $encoded = imagewebp($variant, null, self::WEBP_QUALITY);
                            $webpData = ob_get_contents();
                        } finally {
                            while (ob_get_level() > $bufferLevel) {
                                ob_end_clean();
                            }
                            imagedestroy($variant);
                        }

                        if ($encoded && is_string($webpData) && $webpData !== '') {
                            $disk->put($variantPath, $webpData);
                        }
                    }
                } finally {
                    imagedestroy($sourceImage);
                }
            }

            $urls = [];
            foreach ($variantPaths as $width => $variantPath) {
                if ($disk->exists($variantPath)) {
                    $urls[$width] = $disk->url($variantPath);
                }
            }

            if ($urls === []) {
                return null;
            }

            $largestWidth = max(array_keys($urls));

            return [
                'src' => $urls[$largestWidth],
                'srcset' => collect($urls)
                    ->map(fn (string $url, int $width): string => "{$url} {$width}w")
                    ->implode(', '),
                'width' => $largestWidth,
                'height' => self::FEATURED_SIZES[$largestWidth],
            ];
        } catch (Throwable) {
            // The original cover remains the fail-open fallback in the view.
            return null;
        }
    }

    /**
     * @return array{int, int, int, int}
     */
    private function centerCrop(int $sourceWidth, int $sourceHeight, float $targetRatio): array
    {
        if (($sourceWidth / $sourceHeight) > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $cropX = (int) floor(($sourceWidth - $cropWidth) / 2);

            return [$cropX, 0, $cropWidth, $cropHeight];
        }

        $cropWidth = $sourceWidth;
        $cropHeight = (int) round($sourceWidth / $targetRatio);
        $cropY = (int) floor(($sourceHeight - $cropHeight) / 2);

        return [0, $cropY, $cropWidth, $cropHeight];
    }
}
