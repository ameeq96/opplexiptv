<?php

namespace App\Services\Purchasing;

use App\Models\{Purchasing, Picture};
use App\Services\PrivatePaymentProofStorage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchasingMediaService
{
    public function __construct(private PrivatePaymentProofStorage $storage)
    {
    }

    public function storeScreenshots(Purchasing $purchase, array $files = []): void
    {
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) continue;

            $original = $file->getClientOriginalName();
            $mime     = $file->getMimeType();
            $size     = $file->getSize();
            $path     = $this->storage->store($file, 'purchases');

            $purchase->pictures()->create([
                'path'          => $path,
                'original_name' => $original,
                'mime'          => $mime,
                'size'          => $size,
            ]);
        }
    }

    public function deletePicture(Purchasing $purchase, Picture $picture): void
    {
        $this->assertBelongsToPurchase($purchase, $picture);
        $this->storage->delete($picture);
        $picture->delete();
    }

    public function response(Purchasing $purchase, Picture $picture): StreamedResponse
    {
        $this->assertBelongsToPurchase($purchase, $picture);

        return $this->storage->response($picture);
    }

    public function cleanupPictures(iterable $purchases): void
    {
        foreach ($purchases as $purchase) {
            foreach ($purchase->pictures as $picture) {
                $this->storage->delete($picture);
                $picture->delete();
            }
        }
    }

    private function assertBelongsToPurchase(Purchasing $purchase, Picture $picture): void
    {
        if ($picture->imageable_id !== $purchase->id || $picture->imageable_type !== Purchasing::class) {
            abort(404);
        }
    }
}
