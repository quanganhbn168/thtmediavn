<?php

namespace App\Support;

use DOMDocument;
use Illuminate\Support\Str;

class ArticleContent
{
    /**
     * Add stable anchors to H2/H3 headings and return a navigation model.
     *
     * @return array{content:string, toc:array<int, array{id:string, label:string, level:int}>}
     */
    public static function prepare(?string $html): array
    {
        $html = trim((string) $html);

        if ($html === '' || ! class_exists(DOMDocument::class)) {
            return ['content' => $html, 'toc' => []];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="article-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('article-root');

        if (! $root) {
            return ['content' => $html, 'toc' => []];
        }

        $toc = [];
        $usedIds = [];
        foreach ($root->getElementsByTagName('*') as $element) {
            if (! in_array(strtolower($element->tagName), ['h2', 'h3'], true)) {
                continue;
            }

            $label = trim((string) $element->textContent);
            if ($label === '') {
                continue;
            }

            $baseId = Str::slug($label) ?: 'muc-luc';
            $id = $baseId;
            $suffix = 2;
            while (in_array($id, $usedIds, true)) {
                $id = $baseId.'-'.$suffix++;
            }
            $usedIds[] = $id;
            $element->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'label' => $label, 'level' => (int) substr($element->tagName, 1)];
        }

        $content = '';
        foreach ($root->childNodes as $child) {
            $content .= $document->saveHTML($child);
        }

        return ['content' => $content, 'toc' => $toc];
    }
}
