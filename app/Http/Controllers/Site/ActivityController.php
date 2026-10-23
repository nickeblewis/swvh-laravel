<?php

namespace App\Http\Controllers\Site;

use App\Models\Activity;
use App\Support\Present;
use App\Support\Schedule;
use App\Support\Seo;
use Inertia\Response;

class ActivityController extends SiteController
{
    /** Every published activity, soonest session first. */
    public function index(): Response
    {
        $now = now();

        return $this->page('Activities/Index', [
            'today' => Schedule::todayLabel($now),
            'activities' => array_map(
                fn (array $pair): array => Present::activityItem($pair[0], $pair[1], $now),
                Schedule::byNext(Activity::published()->get()->all(), $now),
            ),
        ], new Seo(
            title: 'Regular Activities',
            description: 'The hall hosts a range of regular activities for all ages. Whether it is keeping fit or learning a new skill, there is something for everyone.',
        ));
    }

    /** One activity, plus the first three others for "More from the hall". */
    public function show(string $slug): Response
    {
        $activities = Activity::published()->get();
        $activity = $activities->firstWhere('slug', $slug) ?? abort(404);

        return $this->page('Activities/Show', [
            'activity' => Present::activityDetail($activity),
            'related' => $activities->where('slug', '!=', $slug)->take(3)
                ->map(fn (Activity $related): array => Present::activityItem($related, null))
                ->values(),
        ], new Seo(
            title: $activity->title,
            description: $activity->description ?? collect([$activity->day, $activity->time])->filter()->implode(', '),
            image: $activity->heroImageUrl(),
        ));
    }
}
