<?php

namespace App\Services\Orders;

use App\Models\{Order, Picture};
use Illuminate\Http\UploadedFile;

class OrderMediaService
{
    public function storeScreenshots(Order $order, array $files = []): void
    {
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) {
                continue;
            }

            $original = $file->getClientOriginalName();
            $mime     = $file->getClientMimeType();
            $size     = $file->getSize();
            $filename = time() . '_' . uniqid() . '_' . $original;

            $file->move(public_path('screenshots'), $filename);

            $order->pictures()->create([
                'path'          => 'screenshots/' . $filename,
                'original_name' => $original,
                'mime'          => $mime,
                'size'          => $size,
            ]);
        }
    }

    public function deletePicture(Order $order, Picture $picture): void
    {
        if ($picture->imageable_id !== $order->id || $picture->imageable_type !== Order::class) {
            abort(404);
        }

        $fullPath = public_path($picture->path);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $picture->delete();
    }
}
