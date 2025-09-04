<?php

namespace App\Traits;

trait HelperFunction
{
    /**
     * Check if current locale is RTL
     *
     * @param string|null $locale
     * @return bool
     */
    public function isRtl($locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();

        $shortLocale = substr($locale, 0, 2);

        $rtlLocales = ['ar', 'ur', 'fa', 'he'];

        return in_array($shortLocale, $rtlLocales);
    }
}
