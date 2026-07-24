<?php

namespace App\Support;

final class MapEmbed
{
    /**
     * Extract and validate a Google Maps iframe source without rendering admin HTML raw.
     */
    public static function url(?string $value): ?string
    {
        $value = html_entity_decode(trim((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($value === '') {
            return null;
        }

        if (str_contains(strtolower($value), '<iframe')) {
            preg_match('/<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $value, $matches);
            $value = html_entity_decode(trim((string) ($matches[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($value);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $isGoogleMaps = $host === 'google.com' || str_ends_with($host, '.google.com');

        return $scheme === 'https' && $isGoogleMaps ? $value : null;
    }
}
