<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GenerateWebpImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $imageUrl,
        public int $width,
        public int $height,
        public int $quality,
        public string $webpPath
    ) {}

    public function handle(): void
    {
        $fullPath = public_path($this->webpPath);
        $dir      = dirname($fullPath);

        if (file_exists($fullPath)) {
            return;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $imageData = Http::timeout(10)->withoutVerifying()->get($this->imageUrl)->body();
            $image = @imagecreatefromstring($imageData);
            if (!$image) return;

            $resized = imagecreatetruecolor($this->width, $this->height);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $this->width, $this->height, imagesx($image), imagesy($image));

            imagewebp($resized, $fullPath, $this->quality);

            imagedestroy($image);
            imagedestroy($resized);
        } catch (\Throwable) {
            // Silent fail; fallback images are served directly.
        }
    }
}
