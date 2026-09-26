<?php

namespace App\Services;

use InvalidArgumentException;

class AdminTotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes(max(16, $bytes)));
    }

    public function code(string $secret, ?int $timestamp = null, int $digits = 6): string
    {
        if ($digits < 6 || $digits > 8) {
            throw new InvalidArgumentException('TOTP digits must be between 6 and 8.');
        }

        $counter = intdiv($timestamp ?? time(), 30);
        $binaryCounter = pack('N2', intdiv($counter, 4294967296), $counter % 4294967296);
        $hash = hash_hmac('sha1', $binaryCounter, $this->base32Decode($secret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $value = (
            ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff)
        ) % (10 ** $digits);

        return str_pad((string) $value, $digits, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code, ?int $timestamp = null, int $window = 1): bool
    {
        return $this->matchingCounter($secret, $code, $timestamp, $window) !== null;
    }

    public function matchingCounter(string $secret, string $code, ?int $timestamp = null, int $window = 1): ?int
    {
        $code = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($code) !== 6) {
            return null;
        }

        $timestamp ??= time();
        for ($offset = -max(0, $window); $offset <= max(0, $window); $offset++) {
            $candidateTimestamp = $timestamp + ($offset * 30);
            if (hash_equals($this->code($secret, $candidateTimestamp), $code)) {
                return intdiv($candidateTimestamp, 30);
            }
        }

        return null;
    }

    /** @return array<int,string> */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($index = 0; $index < max(1, $count); $index++) {
            $raw = strtoupper(bin2hex(random_bytes(4)));
            $codes[] = substr($raw, 0, 4) . '-' . substr($raw, 4, 4);
        }

        return $codes;
    }

    public function provisioningUri(string $secret, string $email, string $issuer = 'Opplex IPTV'): string
    {
        $label = rawurlencode($issuer . ':' . $email);

        return 'otpauth://totp/' . $label
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    private function base32Encode(string $value): string
    {
        $bits = '';
        foreach (str_split($value) as $character) {
            $bits .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $value): string
    {
        $value = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $value) ?? '');
        if ($value === '') {
            throw new InvalidArgumentException('TOTP secret is invalid.');
        }

        $bits = '';
        foreach (str_split($value) as $character) {
            $position = strpos(self::ALPHABET, $character);
            if ($position === false) {
                throw new InvalidArgumentException('TOTP secret is invalid.');
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
