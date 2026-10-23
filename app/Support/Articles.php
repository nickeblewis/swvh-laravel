<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Throwable;

/**
 * Articles are markdown files in `content/articles/`, one per article, with
 * the filename as the slug. Frontmatter: `title` and `description` required;
 * `published_at` (date or ISO string; a future date hides the article),
 * `draft`, `hero_image` and `hero_image_alt` optional.
 */
final class Articles
{
    public static function directory(): string
    {
        return base_path('content/articles');
    }

    /**
     * Published articles, newest first, undated last.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function all(): Collection
    {
        if (! File::isDirectory(self::directory())) {
            return collect();
        }

        return collect(File::glob(self::directory().'/*.md'))
            ->map(fn (string $path): ?array => self::read($path))
            ->filter()
            ->map(fn (array $article): array => collect($article)->except('bodyHtml')->all())
            ->sortByDesc(fn (array $article): int => $article['publishedAt'] ? CarbonImmutable::parse($article['publishedAt'])->getTimestamp() : 0)
            ->values();
    }

    /**
     * One published article with its rendered body, or null.
     *
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/i', $slug)) {
            return null;
        }

        $path = self::directory()."/{$slug}.md";

        return File::exists($path) ? self::read($path) : null;
    }

    /** `max(ceil(words / 200), 1)`, counted from the raw markdown. */
    public static function readingTime(?string $body): int
    {
        $words = preg_match_all('/\S+/', $body ?? '');

        return max((int) ceil($words / 200), 1);
    }

    /** @return array<string, mixed>|null */
    private static function read(string $path): ?array
    {
        $document = YamlFrontMatter::parse(File::get($path));
        $publishedAt = self::date($document->matter('published_at'));

        if ($document->matter('draft') || ($publishedAt && $publishedAt->isFuture())) {
            return null;
        }

        $slug = pathinfo($path, PATHINFO_FILENAME);

        return [
            'slug' => $slug,
            'title' => (string) ($document->matter('title') ?? $slug),
            'description' => (string) ($document->matter('description') ?? ''),
            'publishedAt' => $publishedAt?->toIso8601ZuluString(),
            'readingTimeMinutes' => self::readingTime($document->body()),
            'heroImageUrl' => $document->matter('hero_image'),
            'heroImageAlt' => $document->matter('hero_image_alt'),
            'bodyHtml' => Markdown::html($document->body()),
        ];
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        try {
            // YAML turns a bare date into a timestamp.
            return is_int($value) ? CarbonImmutable::createFromTimestampUTC($value) : CarbonImmutable::parse((string) $value);
        } catch (Throwable) {
            return null;
        }
    }
}
