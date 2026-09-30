<?php

namespace App\Services;

use Throwable;

class ResponsiveProductImageService
{
    /** @var list<int> */
    private const WIDTHS = [320, 640, 960];

    private const WEBP_QUALITY = 78;

    private const AVIF_QUALITY = 58;

    private const MAX_SOURCE_PIXELS = 25_000_000;

    /** @var list<string> */
    private const ALLOWED_DIRECTORIES = [
        'images/digital-products/',
        'images/shop/',
        'images/resource/',
    ];

    /**
     * Return fail-open image metadata. The original image always remains the
     * final fallback, even when GD or generated variants are unavailable.
     *
     * @return array{
     *     src:string,
     *     width:int|null,
     *     height:int|null,
     *     avif_srcset:string|null,
     *     webp_srcset:string|null,
     *     avif_fallback:string|null,
     *     webp_fallback:string|null
     * }|null
     */
    public function metadata(?string $relativePath): ?array
    {
        $relativePath = $this->normalizeRelativePath($relativePath);
        if ($relativePath === null) {
            return null;
        }

        $metadata = [
            'src' => asset($relativePath),
            'width' => null,
            'height' => null,
            'avif_srcset' => null,
            'webp_srcset' => null,
            'avif_fallback' => null,
            'webp_fallback' => null,
        ];

        try {
            $sourcePath = public_path($relativePath);
            $sourceInfo = @getimagesize($sourcePath);
            if (! is_array($sourceInfo)) {
                return $metadata;
            }

            $sourceWidth = (int) ($sourceInfo[0] ?? 0);
            $sourceHeight = (int) ($sourceInfo[1] ?? 0);
            if ($sourceWidth < 1 || $sourceHeight < 1) {
                return $metadata;
            }

            $metadata['width'] = $sourceWidth;
            $metadata['height'] = $sourceHeight;

            foreach (['avif', 'webp'] as $format) {
                $sources = [];

                foreach (self::WIDTHS as $width) {
                    if ($width > $sourceWidth) {
                        continue;
                    }

                    $variant = $this->variantRelativePath($relativePath, $width, $format);
                    if (is_file(public_path($variant))) {
                        $sources[$width] = asset($variant);
                    }
                }

                if ($sources === []) {
                    continue;
                }

                $metadata["{$format}_srcset"] = collect($sources)
                    ->map(fn (string $url, int $width): string => "{$url} {$width}w")
                    ->implode(', ');
                $metadata["{$format}_fallback"] = $sources[max(array_keys($sources))];
            }
        } catch (Throwable) {
            // The original source in `src` remains usable when inspection fails.
        }

        return $metadata;
    }

    /**
     * Create proportional AVIF/WebP variants without ever enlarging a source.
     *
     * @return array<string,mixed>|null
     */
    public function generateVariants(?string $relativePath): ?array
    {
        $relativePath = $this->normalizeRelativePath($relativePath);
        if ($relativePath === null) {
            return null;
        }

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return $this->metadata($relativePath);
        }

        try {
            $sourcePath = public_path($relativePath);
            $sourceData = @file_get_contents($sourcePath);
            $sourceInfo = is_string($sourceData) ? @getimagesizefromstring($sourceData) : false;

            if (
                ! is_string($sourceData)
                || ! is_array($sourceInfo)
                || (int) $sourceInfo[0] < 1
                || (int) $sourceInfo[1] < 1
                || ((int) $sourceInfo[0] * (int) $sourceInfo[1]) > self::MAX_SOURCE_PIXELS
            ) {
                return $this->metadata($relativePath);
            }

            $source = @imagecreatefromstring($sourceData);
            if ($source === false) {
                return $this->metadata($relativePath);
            }

            try {
                $sourceWidth = imagesx($source);
                $sourceHeight = imagesy($source);

                foreach (self::WIDTHS as $width) {
                    if ($width > $sourceWidth) {
                        continue;
                    }

                    $height = max(1, (int) round($sourceHeight * ($width / $sourceWidth)));
                    $variant = imagecreatetruecolor($width, $height);
                    if ($variant === false) {
                        continue;
                    }

                    try {
                        imagealphablending($variant, false);
                        imagesavealpha($variant, true);
                        $transparent = imagecolorallocatealpha($variant, 0, 0, 0, 127);
                        imagefill($variant, 0, 0, $transparent);
                        imagecopyresampled(
                            $variant,
                            $source,
                            0,
                            0,
                            0,
                            0,
                            $width,
                            $height,
                            $sourceWidth,
                            $sourceHeight
                        );

                        if (function_exists('imageavif')) {
                            $this->writeVariant(
                                $variant,
                                $this->variantRelativePath($relativePath, $width, 'avif'),
                                'avif'
                            );
                        }

                        if (function_exists('imagewebp')) {
                            $this->writeVariant(
                                $variant,
                                $this->variantRelativePath($relativePath, $width, 'webp'),
                                'webp'
                            );
                        }
                    } finally {
                        imagedestroy($variant);
                    }
                }
            } finally {
                imagedestroy($source);
            }
        } catch (Throwable) {
            // Image optimization is best-effort and must never block an upload.
        }

        return $this->metadata($relativePath);
    }

    public function deleteVariants(?string $relativePath): void
    {
        $relativePath = $this->normalizeRelativePath($relativePath);
        if ($relativePath === null) {
            return;
        }

        try {
            foreach (self::WIDTHS as $width) {
                foreach (['avif', 'webp'] as $format) {
                    $path = public_path($this->variantRelativePath($relativePath, $width, $format));
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        } catch (Throwable) {
            // Removing an original image must remain possible if cleanup fails.
        }
    }

    private function writeVariant(\GdImage $image, string $relativePath, string $format): void
    {
        $path = public_path($relativePath);
        if (is_file($path)) {
            return;
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return;
        }

        $bufferLevel = ob_get_level();
        ob_start();

        try {
            $encoded = $format === 'avif'
                ? @imageavif($image, null, self::AVIF_QUALITY)
                : @imagewebp($image, null, self::WEBP_QUALITY);
            $contents = ob_get_contents();
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }

        if ($encoded && is_string($contents) && $contents !== '') {
            @file_put_contents($path, $contents, LOCK_EX);
        }
    }

    private function variantRelativePath(string $relativePath, int $width, string $format): string
    {
        $directory = str_replace('\\', '/', dirname($relativePath));
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
        $stem = preg_replace('/[^a-z0-9_-]+/i', '-', "{$filename}-{$extension}") ?: 'image';

        return "{$directory}/responsive/{$stem}-{$width}.{$format}";
    }

    private function normalizeRelativePath(?string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', trim((string) $relativePath)), '/');

        if (
            $relativePath === ''
            || str_contains($relativePath, '../')
            || str_contains($relativePath, '://')
            || ! collect(self::ALLOWED_DIRECTORIES)->contains(
                fn (string $directory): bool => str_starts_with($relativePath, $directory)
            )
        ) {
            return null;
        }

        return $relativePath;
    }
}
