<?php

namespace App\Http\Controllers\Site;

use App\Models\Activity;
use App\Models\Space;
use App\Support\Present;
use App\Support\Schedule;
use App\Support\Seo;
use Inertia\Response;

class HomeController extends SiteController
{
    /** The next six sessions across the hall, and the spaces that can be hired. */
    public function __invoke(): Response
    {
        $now = now();

        return $this->page('Home', [
            'today' => Schedule::todayLabel($now),
            'upcoming' => array_map(
                fn (array $pair): array => Present::activityItem($pair[0], $pair[1], $now),
                Schedule::upcoming(Activity::published()->get()->all(), 6, $now),
            ),
            'spaces' => Space::bookable()->get()->map(Present::space(...)),
        ], new Seo(
            title: (string) config('site.name'),
            description: 'A welcoming village hall for local activities, events, celebrations and private hire.',
        ));
    }
}
