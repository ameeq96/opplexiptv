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

if (!function_exists('v')) {
    /**
     * Versioned asset helper for cache-busting.
     */
    function v(string $path)
    {
        $rel = ltrim($path, '/');
        $full = public_path($rel);
        $ver = is_file($full) ? filemtime($full) : time();
        return asset($rel) . '?v=' . $ver;
    }
}

if (!function_exists('seo')) {
    /**
     * Resolve the central JSON-LD schema builder.
     */
    function seo(): \App\Services\SchemaService
    {
        return app(\App\Services\SchemaService::class);
    }
}

if (!function_exists('jsonld')) {
    /**
     * Render a schema array into a <script type="application/ld+json"> tag.
     * Returns an empty string for null/empty input so callers can emit
     * unconditionally.
     *
     * @param  array<string,mixed>|null  $schema
     */
    function jsonld(?array $schema): string
    {
        if (empty($schema)) {
            return '';
        }

        $json = json_encode($schema, \App\Services\SchemaService::JSON_FLAGS);
        if ($json === false) {
            return '';
        }

        return '<script type="application/ld+json">' . $json . '</script>';
    }
}

if (!function_exists('admin_is_rtl')) {
    function admin_is_rtl(): bool
    {
        return in_array(admin_locale(), ['ar', 'ur', 'fa', 'he'], true);
    }
}

if (!function_exists('admin_dir')) {
    function admin_dir(): string
    {
        return admin_is_rtl() ? 'rtl' : 'ltr';
    }
}

if (!function_exists('admin_locale')) {
    function admin_locale(): string
    {
        return (string) (session('admin_locale') ?? app()->getLocale());
    }
}

if (!function_exists('admin_locales')) {
    function admin_locales(): array
    {
        $locales = config('app.locales', []);
        if (empty($locales)) {
            $locales = [config('app.fallback_locale', 'en')];
        }
        return $locales;
    }
}

if (!function_exists('admin_active_route')) {
    function admin_active_route(string $name): bool
    {
        return request()->routeIs($name);
    }
}
