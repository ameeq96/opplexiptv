<?php

namespace App\Services;

class LocaleService
{
    public function isRtl(?string $locale = null): bool
    {
        $rtlLocales = ['ar','ur','fa','he'];
        $locale = $locale ?: app()->getLocale();
        return in_array(substr($locale, 0, 2), $rtlLocales, true);
    }

    public function langCode(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return match ($locale) {
            'fr' => 'fr-FR', 'it' => 'it-IT', 'ur' => 'ur-PK', 'ru' => 'ru-RU',
            'es' => 'es-ES', 'pt' => 'pt-BR', 'nl' => 'nl-NL', 'ar' => 'ar-SA',
            'hi' => 'hi-IN', default => 'en-US',
        };
    }
}
