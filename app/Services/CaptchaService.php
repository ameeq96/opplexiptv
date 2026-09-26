<?php

namespace App\Services;

class CaptchaService
{
    public function generate(): array
    {
        $a = random_int(1, 10); $b = random_int(1, 10);
        session(['captcha_sum' => $a + $b]);
        return ['num1' => $a, 'num2' => $b];
    }

    public function check(int|string|null $input): bool
    {
        $expected = session()->pull('captcha_sum');

        return $expected !== null && (int) $input === (int) $expected;
    }
}
