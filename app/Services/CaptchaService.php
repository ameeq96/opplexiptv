<?php

namespace App\Services;

class CaptchaService
{
    public function generate(): array
    {
        $a = rand(1,10); $b = rand(1,10);
        session(['captcha_sum' => $a + $b]);
        return ['num1' => $a, 'num2' => $b];
    }

    public function check(int|string|null $input): bool
    {
        return (int) $input === (int) session('captcha_sum');
    }
}
