<?php

if (!function_exists('textAlignment')) {
    /**
     * Return text alignment class based on RTL or LTR
     */
    function textAlignment($isRtl = null)
    {
        if (is_null($isRtl)) {
            $locale = app()->getLocale();
            $isRtl = in_array($locale, ['ar', 'ur']);
        }
        // Return simple class for custom CSS
        return $isRtl ? 'rtl-text' : 'ltr-text';
    }
}

if (!function_exists('arrowDirection')) {
    /**
     * Return arrow icon class based on RTL or LTR
     */
    function arrowDirection($isRtl = null)
    {
        if (is_null($isRtl)) {
            $locale = app()->getLocale();
            $isRtl = in_array($locale, ['ar', 'ur']);
        }
        return $isRtl ? 'lnr-arrow-left' : 'lnr-arrow-right';
    }
}
