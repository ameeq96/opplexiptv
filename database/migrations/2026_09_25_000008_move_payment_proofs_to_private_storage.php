<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pictures')
            ->where(function ($query) {
                $query->where('path', 'like', 'screenshots/%')
                    ->orWhere('path', 'like', 'uploads/purchases/%');
            })
            ->orderBy('id')
            ->chunkById(100, function ($pictures): void {
                foreach ($pictures as $picture) {
                    $this->moveToPrivateStorage($picture);
                }
            });
    }

    public function down(): void
    {
        // Deliberately irreversible: payment proofs must never be copied back to public storage.
    }

    private function moveToPrivateStorage(object $picture): void
    {
        $legacyPath = str_replace('\\', '/', ltrim((string) $picture->path, '/'));
        $isPurchase = str_starts_with($legacyPath, 'uploads/purchases/');
        $legacyDirectory = $isPurchase ? 'uploads/purchases' : 'screenshots';
        $privateDirectory = $isPurchase ? 'purchases' : 'orders';
        $legacyFilename = basename($legacyPath);
        $source = public_path($legacyDirectory.DIRECTORY_SEPARATOR.$legacyFilename);

        $extension = strtolower((string) pathinfo($legacyFilename, PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{1,10}$/', $extension) ? '.'.$extension : '';
        $hash = substr(hash('sha256', $picture->id.'|'.$legacyPath), 0, 20);
        $destination = 'private/payment-proofs/'.$privateDirectory.'/'.$picture->id.'-'.$hash.$extension;
        $disk = Storage::disk('local');

        if (! $disk->exists($destination)) {
            if (! is_file($source)) {
                return;
            }

            $stream = fopen($source, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Unable to read legacy payment proof: '.$legacyPath);
            }

            try {
                if (! $disk->put($destination, $stream)) {
                    throw new RuntimeException('Unable to move payment proof to private storage: '.$legacyPath);
                }
            } finally {
                fclose($stream);
            }
        }

        if (is_file($source) && ! unlink($source)) {
            throw new RuntimeException('Payment proof was copied but its public copy could not be removed: '.$legacyPath);
        }

        DB::table('pictures')->where('id', $picture->id)->update(['path' => $destination]);
    }
};
