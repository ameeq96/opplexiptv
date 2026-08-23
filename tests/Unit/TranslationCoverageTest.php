<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TranslationCoverageTest extends TestCase
{
    /** @var array<int, string> */
    private const LOCALES = ['es', 'fr', 'it', 'nl', 'pt', 'ru', 'ur', 'ar', 'hi'];

    public function test_every_locale_matches_the_complete_english_translation_schema(): void
    {
        $languageRoot = dirname(__DIR__, 2).'/resources/lang';
        $englishFiles = glob($languageRoot.'/en/*.php');

        $this->assertNotFalse($englishFiles);
        $this->assertNotEmpty($englishFiles);

        foreach ($englishFiles as $englishPath) {
            $file = basename($englishPath);
            $english = $this->loadTranslation($englishPath);
            $englishLeaves = $this->flatten($english);
            $englishKeys = array_keys($englishLeaves);
            sort($englishKeys);

            foreach (self::LOCALES as $locale) {
                $localizedPath = "{$languageRoot}/{$locale}/{$file}";
                $localized = $this->loadTranslation($localizedPath);
                $localizedLeaves = $this->flatten($localized);
                $localizedKeys = array_keys($localizedLeaves);
                sort($localizedKeys);

                $this->assertSame(
                    $englishKeys,
                    $localizedKeys,
                    "{$locale}/{$file} must contain exactly the same keys as en/{$file}."
                );

                foreach ($englishLeaves as $key => $englishValue) {
                    $localizedValue = $localizedLeaves[$key];

                    $this->assertSame(
                        get_debug_type($englishValue),
                        get_debug_type($localizedValue),
                        "{$locale}/{$file} changed the value type at {$key}."
                    );
                    $this->assertNotSame(
                        '',
                        trim((string) $localizedValue),
                        "{$locale}/{$file} has an empty value at {$key}."
                    );
                    $this->assertSame(
                        1,
                        preg_match('//u', (string) $localizedValue),
                        "{$locale}/{$file} contains invalid UTF-8 at {$key}."
                    );
                    $this->assertSame(
                        $this->placeholders((string) $englishValue),
                        $this->placeholders((string) $localizedValue),
                        "{$locale}/{$file} changed placeholders at {$key}."
                    );
                }
            }
        }
    }

    public function test_literal_translation_calls_reference_existing_english_keys(): void
    {
        $root = dirname(__DIR__, 2);
        $directories = ["{$root}/app", "{$root}/resources/views", "{$root}/routes"];
        $pattern = "~(?:__|trans)\\(\\s*(['\"])([^'\"]+)\\1~";
        $missing = [];

        foreach ($directories as $directory) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                $this->assertNotFalse($contents);
                preg_match_all($pattern, $contents, $matches);

                foreach ($matches[2] as $key) {
                    if (str_contains($key, '$') || str_contains($key, '{')) {
                        continue;
                    }

                    if (! $this->englishTranslationExists($root, $key)) {
                        $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                        $missing[] = "{$key} ({$relativePath})";
                    }
                }
            }
        }

        $missing = array_values(array_unique($missing));
        sort($missing);

        $this->assertSame([], $missing, "Missing English translation keys:\n".implode("\n", $missing));
    }

    /** @return array<mixed> */
    private function loadTranslation(string $path): array
    {
        $this->assertFileExists($path);
        $value = require $path;
        $this->assertIsArray($value, "{$path} must return an array.");

        return $value;
    }

    private function englishTranslationExists(string $root, string $key): bool
    {
        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
        $path = "{$root}/resources/lang/en/{$file}.php";

        if (! is_file($path)) {
            return false;
        }

        if ($item === null) {
            return true;
        }

        $translations = require $path;

        // Laravel supports both literal dotted keys and nested arrays.
        if (array_key_exists($item, $translations)) {
            return true;
        }

        $value = $translations;
        foreach (explode('.', $item) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    /**
     * @param  array<mixed>  $value
     * @return array<string, string|int|float|bool>
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

            $this->assertTrue(
                is_string($item) || is_int($item) || is_float($item) || is_bool($item),
                "{$path} must be a scalar translation value."
            );
            $flattened[$path] = $item;
        }

        return $flattened;
    }

    /** @return array<int, string> */
    private function placeholders(string $value): array
    {
        preg_match_all('/(?<![a-z0-9_]):[a-z_]+/i', $value, $matches);
        $placeholders = array_values(array_unique($matches[0]));
        sort($placeholders);

        return $placeholders;
    }
}
