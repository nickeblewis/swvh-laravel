<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render editor-written Markdown to HTML that is safe to output raw:
     * any HTML typed into the Markdown is escaped, and javascript: links
     * are dropped. The only source allowed in `v-html`.
     */
    public static function html(?string $markdown): string
    {
        if (blank($markdown)) {
            return '';
        }

        return Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Plain text for meta descriptions.
     */
    public static function text(?string $markdown, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags(static::html($markdown))) ?? '');

        return Str::limit(html_entity_decode($text, ENT_QUOTES | ENT_HTML5), $limit);
    }
}
