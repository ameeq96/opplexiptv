<?php

namespace App\Services;

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

        try {
            $imageData = Http::timeout(10)->withoutVerifying()->get($imageUrl)->body();
            $image = @imagecreatefromstring($imageData);
            if (!$image) return $imageUrl;

            $resized = imagecreatetruecolor($width, $height);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

            if (!is_dir($webpDir)) mkdir($webpDir, 0755, true);
            imagewebp($resized, $fullPath, $quality);

            imagedestroy($image);
            imagedestroy($resized);

            return asset($webpPath);
        } catch (\Throwable $e) {
            return $imageUrl;
        }
    }
}
