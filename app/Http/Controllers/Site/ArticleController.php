<?php

namespace App\Http\Controllers\Site;

use App\Support\Articles;
use App\Support\Seo;
use Inertia\Response;

class ArticleController extends SiteController
{
    public function index(): Response
    {
        return $this->page('Articles/Index', [
            'articles' => Articles::all(),
        ], new Seo(title: 'Articles', description: 'News from Spencers Wood Village Hall.'));
    }

    public function show(string $slug): Response
    {
        $article = Articles::find($slug) ?? abort(404);

        return $this->page('Articles/Show', [
            'article' => $article,
        ], new Seo(
            title: $article['title'],
            description: $article['description'],
            image: $article['heroImageUrl'],
        ));
    }
}
