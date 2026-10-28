<?php

namespace App\Services\Blogs;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlogQualityService
{
    private const SOURCE_HOSTS = [
        'support.google.com', 'developer.android.com', 'developers.google.com',
        'support.apple.com', 'developer.apple.com', 'docs.videolan.me', 'www.videolan.org',
        'www.amazon.com', 'developer.amazon.com', 'www.indexnow.org',
    ];

    public function normalize(array $input, string $site): array
    {
        $this->site($site);
        $allowed = ['version', 'site_key', 'publishing_date', 'topic', 'primary_keyword', 'slug', 'translations', 'sources'];
        if (array_diff(array_keys($input), $allowed)) {
            $this->fail('payload', 'The article contains unsupported fields.');
        }
        Validator::make($input, [
            'version' => ['required', 'integer', 'in:1'],
            'site_key' => ['required', 'in:'.$site],
            'publishing_date' => ['sometimes', 'date_format:Y-m-d'],
            'topic' => ['required', 'string', 'min:12', 'max:200'],
            'primary_keyword' => ['required', 'string', 'min:3', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'regex:/^'.preg_quote($site, '/').'-(?:[a-z0-9]+-)*[a-z0-9]+$/D'],
            'translations' => ['required', 'array'],
            'sources' => ['required', 'array', 'min:1', 'max:8'],
        ])->validate();
        if (!array_is_list($input['sources'])) {
            $this->fail('sources', 'References must be a JSON list.');
        }
        $locales = config('blog-publishing.supported_locales');
        $keys = array_keys($input['translations']);
        sort($keys);
        $expected = $locales;
        sort($expected);
        if ($keys !== $expected) {
            $this->fail('translations', 'All ten supported translations are required before publication.');
        }
        $sources = array_map(fn ($source) => $this->link($source, $site), $input['sources']);
        $translations = [];
        $seen = [];
        foreach ($locales as $locale) {
            $row = $input['translations'][$locale];
            if (!is_array($row) || array_diff(array_keys($row), ['title', 'excerpt', 'seo_title', 'seo_description', 'sections'])) {
                $this->fail('translations.'.$locale, 'Unsupported article fields.');
            }
            Validator::make($row, [
                'title' => ['required', 'string', 'min:12', 'max:200'],
                'excerpt' => ['required', 'string', 'min:40', 'max:500'],
                'seo_title' => ['required', 'string', 'min:10', 'max:160'],
                'seo_description' => ['required', 'string', 'min:60', 'max:255'],
                'sections' => ['required', 'array', 'min:2', 'max:12'],
            ])->validate();
            if (!array_is_list($row['sections'])) {
                $this->fail('sections', 'Sections must be a JSON list.');
            }
            $translation = [];
            foreach (['title', 'excerpt', 'seo_title', 'seo_description'] as $field) {
                $translation[$field] = $this->plain($row[$field]);
            }
            $translation['sections'] = [];
            foreach ($row['sections'] as $section) {
                if (!is_array($section) || array_diff(array_keys($section), ['heading', 'paragraphs', 'items', 'links'])) {
                    $this->fail('sections', 'Only plain structured article sections are allowed.');
                }
                Validator::make($section, [
                    'heading' => ['required', 'string', 'min:3', 'max:180'],
                    'paragraphs' => ['present', 'array', 'max:8'],
                    'paragraphs.*' => ['string', 'min:25', 'max:4000'],
                    'items' => ['present', 'array', 'max:12'],
                    'items.*' => ['string', 'min:8', 'max:1000'],
                    'links' => ['present', 'array', 'max:8'],
                ])->validate();
                foreach (['paragraphs', 'items', 'links'] as $list) {
                    if (!array_is_list($section[$list])) {
                        $this->fail($list, 'Section contents must be JSON lists.');
                    }
                }
                if ($section['paragraphs'] === [] && $section['items'] === []) {
                    $this->fail('sections', 'Every section needs useful body content.');
                }
                $translation['sections'][] = [
                    'heading' => $this->plain($section['heading']),
                    'paragraphs' => array_map(fn ($text) => $this->plain($text), $section['paragraphs']),
                    'items' => array_map(fn ($text) => $this->plain($text), $section['items']),
                    'links' => array_map(fn ($link) => $this->link($link, $site), $section['links']),
                ];
            }
            $body = $this->bodyText($translation);
            if (mb_strlen($body) < 650 || mb_strlen($body) > 35000) {
                $this->fail('translations.'.$locale, 'The article is incomplete or exceeds the bounded publishing payload.');
            }
            $hash = hash('sha256', mb_strtolower(preg_replace('/\s+/u', ' ', $body)));
            if (isset($seen[$hash])) {
                $this->fail('translations.'.$locale, 'A translation duplicates another language verbatim.');
            }
            $seen[$hash] = true;
            $translations[$locale] = $translation;
        }
        $english = $translations['en']['title'].' '.$this->bodyText($translations['en']);
        if (preg_match('/\b(?:we guarantee|guaranteed uninterrupted|zero buffering guaranteed|fully licensed IPTV|we tested|our hands-on tests|our tests prove)\b/i', $english)
            || preg_match('/\bFilex\s+(?:includes|allows|supports|offers)\s+(?:one|two|three|unlimited|[0-9]+)\s+(?:simultaneous\s+)?connections?\b/i', $english)) {
            $this->fail('claims', 'Unverified service allowances, guarantees or first-hand testing claims require editorial correction.');
        }
        $keyword = $this->plain($input['primary_keyword']);
        if (mb_stripos($english, $keyword) === false) {
            $this->fail('primary_keyword', 'The primary keyword should fit the English article naturally.');
        }
        if (mb_substr_count(mb_strtolower($english), mb_strtolower($keyword)) > max(6, (int) ceil(str_word_count($english) / 100))) {
            $this->fail('primary_keyword', 'The primary keyword is repeated too often.');
        }
        return [
            'version' => 1, 'site_key' => $site, 'topic' => $this->plain($input['topic']),
            'primary_keyword' => $keyword, 'slug' => $input['slug'],
            'translations' => $translations, 'sources' => $sources,
        ];
    }

    public function site(string $site): array
    {
        $definition = config('blog-publishing.sites.'.$site);
        if (!is_array($definition)) {
            $this->fail('site_key', 'Unknown publishing website.');
        }
        return $definition;
    }

    public function plain(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', $text)
            || preg_match('~</?[a-z][^>]*>~i', $text)) {
            $this->fail('content', 'Use UTF-8 plain text, not HTML or executable content.');
        }
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            $this->fail('content', 'Article text must not be empty.');
        }
        return $text;
    }

    public function link(mixed $link, string $site): array
    {
        if (!is_array($link) || array_diff(array_keys($link), ['label', 'url'])) {
            $this->fail('links', 'A reference must contain only label and URL.');
        }
        Validator::make($link, ['label' => ['required', 'string', 'max:180'], 'url' => ['required', 'string', 'max:1200']])->validate();
        $url = parse_url($link['url']);
        $hosts = [...self::SOURCE_HOSTS, $this->site($site)['domain'], 'opplexiptv.com'];
        if (!is_array($url) || ($url['scheme'] ?? '') !== 'https' || !in_array($url['host'] ?? '', $hosts, true)
            || isset($url['user']) || isset($url['pass']) || (isset($url['port']) && $url['port'] !== 443)
            || preg_match('/(?:token|password|secret|signature|checkout|\/account)(?:[=\/&?]|$)/i', $link['url'])) {
            $this->fail('links', 'Only approved public HTTPS references are allowed.');
        }
        return ['label' => $this->plain($link['label']), 'url' => $link['url']];
    }

    public function bodyText(array $translation): string
    {
        return implode(' ', array_map(fn ($section) => implode(' ', [$section['heading'], ...$section['paragraphs'], ...$section['items']]), $translation['sections']));
    }

    public function similarity(string $left, string $right): float
    {
        $grams = function (string $text): array {
            $text = mb_strtolower($text);
            foreach (config('blog-publishing.sites') as $site) {
                $text = str_replace(mb_strtolower($site['name']), ' ', $text);
            }
            $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
            $result = [];
            for ($i = 0; $i + 4 < count($words); $i++) {
                $result[implode(' ', array_slice($words, $i, 5))] = true;
            }
            return $result;
        };
        $a = $grams($left);
        $b = $grams($right);
        return min(count($a), count($b)) === 0 ? 0 : count(array_intersect_key($a, $b)) / min(count($a), count($b));
    }

    public function render(array $translation): string
    {
        $escape = fn ($value) => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '';
        foreach ($translation['sections'] as $section) {
            $html .= '<h2>'.$escape($section['heading']).'</h2>';
            foreach ($section['paragraphs'] as $paragraph) {
                $html .= '<p>'.$escape($paragraph).'</p>';
            }
            if ($section['items']) {
                $html .= '<ul>'.implode('', array_map(fn ($item) => '<li>'.$escape($item).'</li>', $section['items'])).'</ul>';
            }
            foreach ($section['links'] as $link) {
                $html .= '<p><a href="'.$escape($link['url']).'" rel="noopener noreferrer">'.$escape($link['label']).'</a></p>';
            }
        }
        return $html;
    }

    public function sectionsFromHtml(string $html, string $title, string $site): array
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><div id="blog-structured-root">'.$html.'</div>', LIBXML_NONET | LIBXML_HTML_NODEFDTD);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        foreach ($document->getElementsByTagName('*') as $node) {
            if (!in_array(strtolower($node->tagName), ['html', 'body', 'div', 'h2', 'h3', 'p', 'ul', 'ol', 'li', 'a', 'strong', 'em', 'b', 'i', 'br'], true)) {
                $this->fail('content', 'This article contains unsupported markup.');
            }
        }
        $root = $document->getElementById('blog-structured-root');
        if (!$root) {
            $this->fail('content', 'The article body could not be read.');
        }
        $sections = [];
        $current = ['heading' => $title, 'paragraphs' => [], 'items' => [], 'links' => []];
        foreach ($root->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                if (trim($node->textContent) !== '') {
                    $current['paragraphs'][] = $this->plain($node->textContent);
                }
                continue;
            }
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['h2', 'h3'], true)) {
                if ($current['paragraphs'] || $current['items'] || $current['links']) {
                    $sections[] = $current;
                }
                $current = ['heading' => $this->plain($node->textContent), 'paragraphs' => [], 'items' => [], 'links' => []];
            } elseif (in_array($tag, ['ul', 'ol'], true)) {
                foreach ($node->getElementsByTagName('li') as $item) {
                    $current['items'][] = $this->plain($item->textContent);
                }
            } else {
                $anchors = $node->getElementsByTagName('a');
                $linkOnly = $anchors->length === 1 && trim($node->textContent) === trim($anchors->item(0)->textContent);
                if (trim($node->textContent) !== '' && !$linkOnly) {
                    $current['paragraphs'][] = $this->plain($node->textContent);
                }
                foreach ($anchors as $anchor) {
                    $current['links'][] = $this->link(['label' => $anchor->textContent, 'url' => $anchor->getAttribute('href')], $site);
                }
            }
        }
        if ($current['paragraphs'] || $current['items'] || $current['links']) {
            $sections[] = $current;
        }
        return $sections;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
