<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DocumentTranslationParityTest extends TestCase
{
    /** @var array<int,string> */
    private const LOCALES = ['es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];

    /** @var array<int,string> */
    private const FILES = [
        'document_home',
        'document_commerce',
        'document_product',
        'document_support',
        'document_ui',
    ];

    public function test_every_requested_locale_matches_the_english_document_schema(): void
    {
        foreach (self::FILES as $file) {
            $english = $this->loadTranslation('en', $file);
            $englishLeaves = $this->flatten($english);

            foreach (self::LOCALES as $locale) {
                $localized = $this->loadTranslation($locale, $file);
                $localizedLeaves = $this->flatten($localized);

                $this->assertSame(
                    array_keys($englishLeaves),
                    array_keys($localizedLeaves),
                    "{$locale}/{$file}.php must preserve every English key in the same order."
                );

                foreach ($englishLeaves as $path => $englishValue) {
                    $localizedValue = $localizedLeaves[$path];

                    $this->assertSame(
                        get_debug_type($englishValue),
                        get_debug_type($localizedValue),
                        "{$locale}/{$file}.php changed the scalar type at {$path}."
                    );
                    $this->assertNotSame(
                        '',
                        trim((string) $localizedValue),
                        "{$locale}/{$file}.php has an empty value at {$path}."
                    );
                    $this->assertSame(
                        1,
                        preg_match('//u', (string) $localizedValue),
                        "{$locale}/{$file}.php contains invalid UTF-8 at {$path}."
                    );
                    $this->assertSame(
                        $this->placeholders((string) $englishValue),
                        $this->placeholders((string) $localizedValue),
                        "{$locale}/{$file}.php changed placeholders at {$path}."
                    );

                    if ($this->isMachineValue($path)) {
                        $this->assertSame(
                            $englishValue,
                            $localizedValue,
                            "{$locale}/{$file}.php translated a machine value at {$path}."
                        );
                    }
                }
            }
        }
    }

    /** @return array<mixed> */
    private function loadTranslation(string $locale, string $file): array
    {
        $path = dirname(__DIR__, 2)."/resources/lang/{$locale}/{$file}.php";

        $this->assertFileExists($path);
        $value = require $path;
        $this->assertIsArray($value, "{$path} must return an array.");

        return $value;
    }

    /**
     * @param  array<mixed>  $value
     * @return array<string,string|int|float|bool>
     */
    private function flatten(array $value, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($value as $key => $item) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($item)) {
                $flattened += $this->flatten($item, $path);
                continue;
            }

            $this->assertTrue(is_string($item) || is_int($item) || is_float($item) || is_bool($item));
            $flattened[$path] = $item;
        }

        return $flattened;
    }

    /** @return array<int,string> */
    private function placeholders(string $value): array
    {
        preg_match_all('/:[a-z_]+/i', $value, $matches);
        $placeholders = array_values(array_unique($matches[0]));
        sort($placeholders);

        return $placeholders;
    }

    private function isMachineValue(string $path): bool
    {
        return str_ends_with($path, '.icon')
            || str_ends_with($path, '.type')
            || str_ends_with($path, '.author')
            || str_ends_with($path, '.year');
    }
}
