<?php

namespace App\Services\Clients;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ClientImportService
{
    public function importFromCsv(UploadedFile $file): int
    {
        $imported = 0;

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) return 0;

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return 0;
        }

        // Normalize header keys (lowercase)
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            // basic guards
            if (!$data) continue;
            $name  = trim((string)($data['name'] ?? ''));
            $email = trim((string)($data['email'] ?? ''));
            $phone = $this->normalizePhone((string)($data['phone'] ?? ''));
            $country = isset($data['country']) ? trim((string)$data['country']) : null;

            if ($name === '' || $phone === '') continue;

            // skip duplicates
            if (User::where('phone', $phone)->exists()) continue;
            if ($email !== '' && User::where('email', $email)->exists()) $email = '';

            // synthesize email if empty
            if ($email === '') {
                $namePart = substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 30);
                $candidate = strtolower($namePart . '@gmail.com');
                $suffix = 1;
                while (User::where('email', $candidate)->exists()) {
                    $candidate = strtolower($namePart . $suffix . '@gmail.com');
                    $suffix++;
                }
                $email = $candidate;
            }

            $v = Validator::make([
                'name'    => $name,
                'email'   => $email,
                'phone'   => $phone,
                'country' => $country,
            ], [
                'name'    => 'required|string|max:255',
                'email'   => 'nullable|email|max:255',
                'phone'   => 'required|string|max:30',
                'country' => 'nullable|string|max:100',
            ]);

            if ($v->fails()) continue;

            User::create([
                'name'     => $name,
                'email'    => $email,
                'phone'    => $phone,
                'country'  => $country,
                'password' => Hash::make('defaultpassword'),
            ]);

            $imported++;
        }

        fclose($handle);
        return $imported;
    }

    private function normalizePhone(string $raw): string
    {
        if (stripos($raw, 'e') !== false) {
            $raw = number_format((float)$raw, 0, '', '');
        } else {
            $raw = preg_replace('/[^0-9+]/', '', $raw);
        }

        if (preg_match('/^0[0-9]+$/', $raw)) {
            $raw = '+92' . substr($raw, 1);
        }

        return $raw;
    }
}
