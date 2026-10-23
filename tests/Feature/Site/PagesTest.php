<?php

use App\Models\Activity;
use App\Models\Page;
use App\Models\Space;
use Database\Seeders\HallSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(HallSeeder::class));

it('serves every built-in page at the Nuxt address', function (string $url, string $component) {
    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component)->has('meta.title'));
})->with([
    ['/', 'site/Home'],
    ['/regular-activities', 'site/Activities/Index'],
    ['/regular-activities/yoga-with-tash', 'site/Activities/Show'],
    ['/market', 'site/Market'],
    ['/repair-cafe', 'site/RepairCafe'],
    ['/booking', 'site/Booking'],
    ['/articles', 'site/Articles/Index'],
    ['/about', 'site/About'],
    ['/hall-info', 'site/HallInfo'],
    ['/history', 'site/History'],
    ['/the-committee', 'site/Committee'],
    ['/terms-and-conditions', 'site/Terms'],
    ['/contact', 'site/Contact'],
]);

it('puts the title and description in the HTML', function () {
    $this->get('/booking')
        ->assertSee('<title>Booking — Spencers Wood Village Hall</title>', false)
        ->assertSee('name="description"', false);
});

it('shows the next six sessions and the bookable spaces on the home page', function () {
    Space::where('slug', 'garden')->update(['bookable' => false]);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('today')
        ->has('upcoming', 6)
        ->has('upcoming.0.occurrence.label')
        ->has('spaces', 4)
        ->where('spaces.0.name', 'Main Hall'));
});

it('lists activities soonest first with unscheduled ones last', function () {
    $this->travelTo(now()->setTimezone('Europe/London')->next('Monday')->setTime(12, 0));
    Activity::factory()->create(['title' => 'Aaa Unscheduled', 'day' => 'By arrangement', 'position' => -1]);

    $this->get('/regular-activities')->assertInertia(fn (Assert $page) => $page
        ->has('activities', 20)
        ->where('activities.19.title', 'Aaa Unscheduled')
        ->where('activities.19.occurrence', null)
        ->where('activities.0.occurrence.highlight', true));
});

it('shows an activity with its markdown rendered and three others', function () {
    $this->get('/regular-activities/1st-spencers-wood-brownies')->assertInertia(fn (Assert $page) => $page
        ->where('activity.title', '1st Spencers Wood Brownies')
        ->where('activity.heroImagePath', '/images/activities/1st-spencers-wood-brownies.png')
        ->where('activity.bodyHtml', fn (string $html) => str_contains($html, '<a href="https://www.girlguiding.org.uk/">Girlguiding</a>'))
        ->has('related', 3));
});

it('escapes HTML typed into a markdown body', function () {
    Activity::factory()->create(['slug' => 'sneaky', 'body' => '<script>alert(1)</script> Hello']);

    $this->get('/regular-activities/sneaky')->assertInertia(fn (Assert $page) => $page
        ->where('activity.bodyHtml', fn (string $html) => ! str_contains($html, '<script>')));
});

it('hides drafts and future activities', function () {
    Activity::factory()->draft()->create(['slug' => 'draft-club']);
    Activity::factory()->create(['slug' => 'future-club', 'published_at' => now()->addDay()]);

    $this->get('/regular-activities/draft-club')->assertNotFound();
    $this->get('/regular-activities/future-club')->assertNotFound();
    $this->get('/regular-activities/past-club')->assertNotFound();
});

it('shows the market and repair cafe next-session badges', function () {
    $this->get('/market')->assertInertia(fn (Assert $page) => $page->has('occurrence.label'));
    $this->get('/repair-cafe')->assertInertia(fn (Assert $page) => $page
        ->has('occurrence.label')
        ->has('photos', 5)
        ->where('photos.0.alt', 'Fixing a wire reindeer sculpture'));
});

it('serves admin-created pages from the catch-all, with the contact template', function () {
    Page::factory()->create(['slug' => 'hire-prices', 'title' => 'Hire prices', 'template' => 'contact']);
    Page::factory()->draft()->create(['slug' => 'secret']);

    $this->get('/hire-prices')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/ContentPage')
        ->where('page.title', 'Hire prices')
        ->where('page.template', 'contact'));
    $this->get('/secret')->assertNotFound();
});

it('renders a friendly 404 in the site layout', function () {
    $this->get('/no-such-page')->assertNotFound()->assertInertia(fn (Assert $page) => $page
        ->component('site/Error')
        ->where('status', 404));
});

it('reads articles from markdown files, hiding drafts and future ones', function () {
    $directory = base_path('content/articles');
    File::put("{$directory}/test-news.md", "---\ntitle: Test news\ndescription: Something happened\npublished_at: 2026-01-05\n---\n\nHello **world**.");
    File::put("{$directory}/test-later.md", "---\ntitle: Later\ndescription: Not yet\npublished_at: 2999-01-01\n---\n\nSoon.");

    try {
        $this->get('/articles')->assertInertia(fn (Assert $page) => $page->has('articles', 1)->where('articles.0.slug', 'test-news'));
        $this->get('/articles/test-news')->assertInertia(fn (Assert $page) => $page
            ->where('article.readingTimeMinutes', 1)
            ->where('article.bodyHtml', fn (string $html) => str_contains($html, '<strong>world</strong>')));
        $this->get('/articles/test-later')->assertNotFound();
    } finally {
        File::delete(["{$directory}/test-news.md", "{$directory}/test-later.md"]);
    }
});
