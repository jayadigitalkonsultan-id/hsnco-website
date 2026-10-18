<?php

namespace App\Services\Cms;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class RichTextSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'a', 'h2', 'h3', 'h4', 'code'];

    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'form', 'input', 'button', 'textarea', 'select', 'video', 'audio'];

    public function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }
        if (! class_exists(DOMDocument::class)) {
            return e($html);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML('<?xml encoding="utf-8"?><div id="cms-rich-root">'.$html.'</div>', LIBXML_NONET | LIBXML_HTML_NODEFDTD);
            if (! $loaded) {
                return e($html);
            }
            $root = null;
            foreach ($document->getElementsByTagName('div') as $candidate) {
                if ($candidate->getAttribute('id') === 'cms-rich-root') {
                    $root = $candidate;
                    break;
                }
            }
            if (! $root) {
                return e($html);
            }
            $this->sanitizeChildren($root);
            $output = '';
            foreach ($root->childNodes as $child) {
                $output .= $document->saveHTML($child);
            }

            return $output;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($child);
                continue;
            }
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = Str::lower($child->tagName);
            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $parent->removeChild($child);
                continue;
            }

            $this->sanitizeChildren($child);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            $href = $tag === 'a' ? $child->getAttribute('href') : null;
            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }
            if ($tag === 'a' && $href !== null && $this->safeHref($href)) {
                $child->setAttribute('href', trim($href));
            }
        }
    }

    private function safeHref(string $href): bool
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($href === '' || preg_match('/[\x00-\x20\\\\]/', $href)) {
            return false;
        }
        if (str_starts_with($href, '//')) {
            return false;
        }
        $scheme = parse_url($href, PHP_URL_SCHEME);

        return $scheme === null || in_array(Str::lower($scheme), ['http', 'https', 'mailto', 'tel'], true);
    }
}
