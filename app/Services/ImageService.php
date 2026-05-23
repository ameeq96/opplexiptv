<?php

namespace App\Services;

use App\Jobs\GenerateWebpImage;
use Illuminate\Support\Facades\Http;

class ImageService
{
    public const TMDB_IMG_BASE = 'https://image.tmdb.org/t/p';

    public function logos(): array
    {
        return [
            'images/resource/5.webp',
            'images/resource/4.webp',
            'images/resource/3.webp',
            'images/resource/6.webp',
            'images/resource/7.webp',
            'images/resource/8.webp',
            'images/resource/9.webp',
        ];
    }

    public function tmdbImage(string $path, string $size): string
    {
        return rtrim(self::TMDB_IMG_BASE, '/') . '/' . $size . $path;
    }

    public function toWebp(string $imageUrl, int $width, int $height, int $quality = 75): string
    {
        $webpDir  = public_path('webp_images');
        $webpPath = 'webp_images/' . md5($imageUrl . $width . $height . $quality) . '.webp';
        $fullPath = public_path($webpPath);

        if (file_exists($fullPath)) return asset($webpPath);

        GenerateWebpImage::dispatch($imageUrl, $width, $height, $quality, $webpPath);

        return $imageUrl;
    }
}
