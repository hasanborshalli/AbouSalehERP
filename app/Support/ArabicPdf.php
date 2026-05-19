<?php

namespace App\Support;

/**
 * Arabic text helper for DomPDF.
 *
 * Wraps khaled.alshamaa/ar-php for proper Arabic ligature shaping.
 * Falls back to plain UTF-8 when the package is not installed.
 *
 * Note: ar-php v6+ removed setCharset() — always UTF-8 now.
 */
class ArabicPdf
{
    public static function shape(string $text): string
    {
        if (class_exists(\ArPHP\I18N\Arabic::class)) {
            // Strip Arabic diacritics (harakat U+064B–U+065F) before shaping.
            // utf8Glyphs() outputs '?' for these combining marks — stripping them
            // keeps the text fully readable while allowing correct glyph shaping.
            $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);
            $ar = new \ArPHP\I18N\Arabic();
            return $ar->utf8Glyphs($text);
        }
        return $text;
    }

    public static function isAvailable(): bool
    {
        return class_exists(\ArPHP\I18N\Arabic::class);
    }
}