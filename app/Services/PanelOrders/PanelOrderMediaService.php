<?php

namespace App\Services\PanelOrders;

use App\Models\{Order, Picture};
use App\Services\PrivatePaymentProofStorage;
use Illuminate\Http\UploadedFile;

class PanelOrderMediaService
{
    public function __construct(private PrivatePaymentProofStorage $storage)
    {
    }

    public function storeScreenshots(Order $order, array $files = []): void
    {
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) continue;

            $original = $file->getClientOriginalName();
            $mime     = $file->getMimeType();
            $size     = $file->getSize();
            $path     = $this->storage->store($file, 'orders');

            $order->pictures()->create([
                'path'          => $path,
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
        $this->storage->delete($picture);
        $picture->delete();
    }

    public function cleanupPictures(iterable $orders): void
    {
        foreach ($orders as $order) {
            foreach ($order->pictures as $picture) {
                $this->storage->delete($picture);
                $picture->delete();
            }
        }
    }
}
