<?php

namespace App\Support;

/**
 * What a public page tells search engines about itself. Every public
 * controller action builds one; the root Blade view prints it as meta tags,
 * so it is in the HTML with or without JavaScript.
 */
final class Seo
{
    /**
     * @param  string  $title  the page's own title, without the site name
     * @param  string|null  $image  absolute URL or site path of the sharing image
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $image = null,
        public bool $index = true,
    ) {}

    /** "Booking — Spencers Wood Village Hall", or just the site name on the home page. */
    public function fullTitle(): string
    {
        $name = (string) config('site.name');

        return $this->title === '' || $this->title === $name ? $name : "{$this->title} — {$name}";
    }

    public function description(): string
    {
        return Markdown::text($this->description, 200);
    }

    public function canonical(): string
    {
        return url(request()->getPathInfo() === '/' ? '/' : rtrim(request()->getPathInfo(), '/'));
    }

    public function imageUrl(): ?string
    {
        return $this->image === null ? null : url($this->image);
    }

    public function robots(): string
    {
        return $this->index && app()->environment('production') ? 'index, follow' : 'noindex, nofollow';
    }

    /**
     * What the browser needs to keep the tab title and description right
     * when moving between pages without a full reload.
     *
     * @return array{title: string, description: string, canonical: string}
     */
    public function forClient(): array
    {
        return [
            'title' => $this->fullTitle(),
            'description' => $this->description(),
            'canonical' => $this->canonical(),
        ];
    }
}
