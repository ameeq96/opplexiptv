<?php

namespace App\Services;

use App\Models\Picture;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivatePaymentProofStorage
{
    public const ROOT = 'private/payment-proofs';

    public function store(UploadedFile $file, string $directory): string
    {
        $extension = $file->guessExtension() ?: 'bin';
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs(self::ROOT.'/'.$directory, $filename, 'local');

        if (! is_string($path)) {
            throw new \RuntimeException('The payment proof could not be stored.');
        }

        return $path;
    }

    public function delete(Picture $picture): void
    {
        if ($this->isPrivatePaymentProof($picture->path)) {
            $disk = Storage::disk('local');

            if ($disk->exists($picture->path) && ! $disk->delete($picture->path)) {
                throw new \RuntimeException('The payment proof could not be deleted.');
            }

            return;
        }

        $legacyPath = str_replace('\\', '/', ltrim($picture->path, '/'));

        if (str_starts_with($legacyPath, 'screenshots/') || str_starts_with($legacyPath, 'uploads/purchases/')) {
            $legacyDirectory = str_starts_with($legacyPath, 'uploads/purchases/')
                ? 'uploads/purchases'
                : 'screenshots';
            $file = public_path($legacyDirectory.DIRECTORY_SEPARATOR.basename($legacyPath));

            if (is_file($file) && ! unlink($file)) {
                throw new \RuntimeException('The legacy payment proof could not be deleted.');
            }
        }
    }

    public function response(Picture $picture): StreamedResponse
    {
        abort_unless($this->isPrivatePaymentProof($picture->path), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($picture->path), 404);

        $extension = pathinfo($picture->path, PATHINFO_EXTENSION);
        $filename = 'payment-proof-'.$picture->id.($extension ? '.'.$extension : '');

        return $disk->response($picture->path, $filename, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function isPrivatePaymentProof(string $path): bool
    {
        return str_starts_with(str_replace('\\', '/', $path), self::ROOT.'/');
    }
}
