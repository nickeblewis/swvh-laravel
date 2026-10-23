<?php

namespace App\Http\Controllers\Site;

use App\Models\Page;
use App\Support\Present;
use App\Support\Seo;
use Inertia\Response;

/** `/{slug}`: an admin-created page. Drafts and unknown slugs are a 404. */
class PageController extends SiteController
{
    public function __invoke(string $slug): Response
    {
        $page = Page::published()->where('slug', $slug)->first() ?? abort(404);

        return $this->page('ContentPage', [
            'page' => Present::page($page),
        ], new Seo(
            title: $page->title,
            description: $page->description ?? $page->body,
            image: $page->heroImageUrl(),
        ));
    }
}
