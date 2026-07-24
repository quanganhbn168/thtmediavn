<?php

declare(strict_types=1);

namespace App;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

final class HtmlDocument
{
    public readonly DOMDocument $dom;
    public readonly DOMXPath $xpath;

    public function __construct(string $html)
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $this->dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_COMPACT
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $this->xpath = new DOMXPath($this->dom);
    }

    public function firstString(string $query, ?DOMNode $context = null): ?string
    {
        $nodes = $this->xpath->query($query, $context);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $value = trim((string) $nodes->item(0)?->textContent);

        return $value !== '' ? $this->cleanText($value) : null;
    }

    public function firstAttribute(string $query, string $attribute, ?DOMNode $context = null): ?string
    {
        $nodes = $this->xpath->query($query, $context);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        if (!$node instanceof DOMElement || !$node->hasAttribute($attribute)) {
            return null;
        }

        $value = trim($node->getAttribute($attribute));

        return $value !== '' ? $value : null;
    }

    public function innerHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $this->dom->saveHTML($child);
        }

        return trim($html);
    }

    public function cleanText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\x{00A0}\s]+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    public static function absoluteUrl(string $url, string $baseUrl): string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($url === '' || str_starts_with($url, 'data:') || str_starts_with($url, 'javascript:')) {
            return '';
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
