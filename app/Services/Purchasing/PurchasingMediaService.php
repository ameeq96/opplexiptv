<?php

namespace App\Services\Purchasing;

use App\Models\{Purchasing, Picture};
use Illuminate\Http\UploadedFile;

class PurchasingMediaService
{
    public function storeScreenshots(Purchasing $purchase, array $files = []): void
    {
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) continue;

            $original = $file->getClientOriginalName();
            $mime     = $file->getClientMimeType();
            $size     = $file->getSize();
            $filename = time().'_'.uniqid().'_'.$original;

            $file->move(public_path('uploads/purchases'), $filename);

            $purchase->pictures()->create([
                'path'          => 'uploads/purchases/'.$filename,
                'original_name' => $original,
                'mime'          => $mime,
                'size'          => $size,
            ]);
        }
    }

    public function deletePicture(Purchasing $purchase, Picture $picture): void
    {
        if ($picture->imageable_id !== $purchase->id || $picture->imageable_type !== Purchasing::class) {
            abort(404);
        }
        $fullPath = public_path($picture->path);
        if (is_file($fullPath)) @unlink($fullPath);
        $picture->delete();
    }

    public function cleanupPictures(iterable $purchases): void
    {
        foreach ($purchases as $purchase) {
            foreach ($purchase->pictures as $pic) {
                $fullPath = public_path($pic->path);
                if (is_file($fullPath)) @unlink($fullPath);
                $pic->delete();
            }
        }
    }
}
