<?php
namespace App\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizerHelper {

    /**
     * Allowed HTML tag names (lowercase)
     */
    private static array $allowedTags = [
        'p', 'br', 'hr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'span', 'div',
        'a'
    ];

    /**
     * Allowed attributes per tag (lowercase)
     */
    private static array $allowedAttributes = [
        'a'    => ['href', 'title', 'target', 'rel'],
        'li'   => ['data-list', 'data-checked', 'class', 'style'],
        'span' => ['contenteditable', 'data-list', 'class', 'style'],
        '*'    => ['class', 'style', 'title', 'id']
    ];

    /**
     * Sanitize rich text HTML content safely.
     *
     * @param string|null $html
     * @return string
     */
    public static function clean(?string $html): string {
        if ($html === null) {
            return '';
        }

        $html = trim($html);
        if ($html === '') {
            return '';
        }

        // If no HTML tags are present, return encoded text formatted with paragraphs/newlines
        if (strpos($html, '<') === false) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        // Pre-cleaning: remove null bytes, comments, and obviously dangerous elements
        $html = str_replace(chr(0), '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Load into DOMDocument
        $dom = new DOMDocument('1.0', 'UTF-8');
        // Suppress warnings from malformed HTML fragments
        $prevErr = libxml_use_internal_errors(true);

        // Wrap fragment in UTF-8 HTML wrapper
        $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErr);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        // Recursively clean node tree
        self::cleanNode($body);

        // Extract cleaned inner HTML
        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return trim($result);
    }

    /**
     * Clean a DOM node and its children
     */
    private static function cleanNode(DOMNode $node): void {
        // Collect children to array first as DOMNodeList is live and mutates during removal
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                /** @var DOMElement $child */
                $tagName = strtolower($child->tagName);

                if (!in_array($tagName, self::$allowedTags, true)) {
                    // Forbidden tag: if it's executable/dangerous, remove it entirely including contents
                    if (in_array($tagName, ['script', 'style', 'iframe', 'frame', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'svg', 'math', 'base', 'meta', 'link'], true)) {
                        $node->removeChild($child);
                    } else {
                        // Otherwise, unwrap the tag (keep its text / valid child nodes)
                        while ($child->firstChild) {
                            $node->insertBefore($child->firstChild, $child);
                        }
                        $node->removeChild($child);
                    }
                    continue;
                }

                // Clean attributes
                self::cleanAttributes($child);

                // Recurse into valid child elements
                self::cleanNode($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    /**
     * Sanitize attributes on an allowed DOMElement
     */
    private static function cleanAttributes(DOMElement $element): void {
        $tagName = strtolower($element->tagName);
        $allowed = array_merge(
            self::$allowedAttributes['*'] ?? [],
            self::$allowedAttributes[$tagName] ?? []
        );

        $attrsToRemove = [];
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $attrName = strtolower($attr->name);

                // Disallow any event handlers (on*) or javascript: expressions
                if (str_starts_with($attrName, 'on') || !in_array($attrName, $allowed, true)) {
                    $attrsToRemove[] = $attr->name;
                    continue;
                }

                $attrValue = trim($attr->value);

                // Check href protocol for <a> tags
                if ($attrName === 'href') {
                    $lowerVal = strtolower($attrValue);
                    $isSafeProtocol = (
                        str_starts_with($lowerVal, 'http://') ||
                        str_starts_with($lowerVal, 'https://') ||
                        str_starts_with($lowerVal, 'mailto:') ||
                        str_starts_with($lowerVal, 'tel:') ||
                        str_starts_with($lowerVal, '/') ||
                        str_starts_with($lowerVal, '#')
                    );
                    if (!$isSafeProtocol || preg_match('/^\s*(javascript|data|vbscript):/i', $attrValue)) {
                        $attrsToRemove[] = $attr->name;
                        continue;
                    }
                    // Enforce rel and target for links
                    $element->setAttribute('rel', 'noopener noreferrer');
                    $element->setAttribute('target', '_blank');
                }

                // Validate classes (allow standard Tailwind and Quill classes)
                if ($attrName === 'class') {
                    $classes = preg_split('/\s+/', $attrValue, -1, PREG_SPLIT_NO_EMPTY);
                    $safeClasses = [];
                    foreach ($classes as $c) {
                        if (preg_match('/^[a-zA-Z0-9_\-\/:]+$/', $c)) {
                            $safeClasses[] = $c;
                        }
                    }
                    if (!empty($safeClasses)) {
                        $element->setAttribute('class', implode(' ', $safeClasses));
                    } else {
                        $attrsToRemove[] = $attr->name;
                    }
                }

                // Validate inline style
                if ($attrName === 'style') {
                    $cleanStyle = self::sanitizeStyle($attrValue);
                    if ($cleanStyle !== '') {
                        $element->setAttribute('style', $cleanStyle);
                    } else {
                        $attrsToRemove[] = $attr->name;
                    }
                }

                // Validate data-list for Quill ordered and bullet lists
                if ($attrName === 'data-list') {
                    if (!in_array($attrValue, ['ordered', 'bullet', 'check'], true)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }

                // Validate data-checked for checklist items
                if ($attrName === 'data-checked') {
                    if (!in_array($attrValue, ['true', 'false'], true)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }

                // Validate contenteditable for Quill UI helper spans
                if ($attrName === 'contenteditable') {
                    if (!in_array($attrValue, ['true', 'false'], true)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
            }
        }

        foreach ($attrsToRemove as $removeName) {
            $element->removeAttribute($removeName);
        }
    }

    /**
     * Clean inline CSS style properties
     */
    private static function sanitizeStyle(string $style): string {
        $allowedProperties = [
            'text-align', 'font-weight', 'font-style', 'text-decoration',
            'color', 'background-color', 'margin', 'margin-top', 'margin-bottom',
            'margin-left', 'margin-right', 'padding', 'line-height'
        ];

        // Block expression, url, behaviors
        if (preg_match('/(expression|javascript|behavior|url\s*\(|@import)/i', $style)) {
            return '';
        }

        $cleanRules = [];
        $rules = explode(';', $style);
        foreach ($rules as $rule) {
            if (strpos($rule, ':') === false) continue;
            list($prop, $val) = explode(':', $rule, 2);
            $prop = strtolower(trim($prop));
            $val  = trim($val);

            if (in_array($prop, $allowedProperties, true)) {
                // Ensure value does not contain characters that could break containment
                if (preg_match('/^[a-zA-Z0-9\s#.,%\-_]+$/', $val)) {
                    $cleanRules[] = "{$prop}: {$val}";
                }
            }
        }

        return !empty($cleanRules) ? implode('; ', $cleanRules) : '';
    }
}
