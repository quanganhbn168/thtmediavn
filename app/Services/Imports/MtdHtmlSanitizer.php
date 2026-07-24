<?php

namespace App\Services\Imports;

use DOMDocument;
use DOMElement;
use DOMXPath;

class MtdHtmlSanitizer
{
    private const BLOCKED_TAGS = [
        'script', 'style', 'form', 'button', 'input', 'textarea', 'select',
        'option', 'iframe', 'object', 'embed', 'link', 'meta', 'base', 'svg',
    ];

    public function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="mtd-import-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        if (! $loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return $this->fallback($html);
        }

        $xpath = new DOMXPath($document);
        foreach (self::BLOCKED_TAGS as $tag) {
            $nodes = $xpath->query('//'.$tag);
            if ($nodes === false) {
                continue;
            }

            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $root = $document->getElementById('mtd-import-root');
        $elements = $xpath->query('//*');
        if ($elements !== false) {
            foreach ($elements as $element) {
                if ($element instanceof DOMElement) {
                    $this->sanitizeAttributes($element);
                }
            }
        }

        $result = '';
        if ($root !== null) {
            foreach ($root->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $result = trim($result);

        return $result !== '' ? $result : null;
    }

    private function sanitizeAttributes(DOMElement $element): void
    {
        $allowed = match (strtolower($element->tagName)) {
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'td', 'th' => ['colspan', 'rowspan'],
            default => [],
        };

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! $this->isSafeUrl($attribute->value)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if (strtolower($element->tagName) === 'a' && $element->hasAttribute('target')) {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    private function fallback(string $html): ?string
    {
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><table><thead><tbody><tr><td><th><a><img><hr>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html) ?? $html;
        $html = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html) ?? $html;
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*(javascript|data):.*?\2/iu', '', $html) ?? $html;
        $html = trim($html);

        return $html !== '' ? $html : null;
    }
}
