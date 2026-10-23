<?php

namespace App\Http\Controllers\Site;

use App\Models\Activity;
use App\Support\Gallery;
use App\Support\Schedule;
use App\Support\Seo;
use Inertia\Response;

/** The built-in pages, whose words live in their Vue files. */
class StaticPageController extends SiteController
{
    public function market(): Response
    {
        return $this->page('Market', [
            'occurrence' => $this->nextSession('spencers-wood-market'),
        ], new Seo(
            title: 'Spencers Wood Market',
            description: 'Held every 3rd Sunday of the month, 10 am – 1 pm at Spencers Wood Village Hall. Free entry.',
            image: '/images/village-hall/market.jpg',
        ));
    }

    public function repairCafe(): Response
    {
        return $this->page('RepairCafe', [
            'occurrence' => $this->nextSession('repair-cafe'),
            'photos' => Gallery::from('repair-cafe'),
        ], new Seo(
            title: 'Repair Cafe',
            description: 'A friendly group of expert volunteers running a repair session at the Village Hall on the first Sunday of the month, 10 am to 1 pm.',
        ));
    }

    public function booking(): Response
    {
        return $this->page('Booking', [], new Seo(
            title: 'Booking',
            description: 'Hire the hall: what is included, how to book through the diary, and the terms of hire.',
            image: '/images/village-hall/booking-garden.jpeg',
        ));
    }

    public function about(): Response
    {
        return $this->page('About', [], new Seo(
            title: 'About',
            description: 'Spencers Wood Village Hall is a registered charity run by trustees and a management committee, serving the community since 1911.',
        ));
    }

    public function hallInfo(): Response
    {
        return $this->page('HallInfo', [], new Seo(
            title: 'Hall Info',
            description: 'Specifications of the Main Hall and Wellington Room, licensing, kitchen and facilities.',
            image: '/images/village-hall/hall-plan.jpeg',
        ));
    }

    public function history(): Response
    {
        return $this->page('History', [], new Seo(
            title: 'History',
            description: "How Spencers Wood Village Hall came to be — from its origins in 1911 as St. Michael's Hall to today.",
            image: '/images/village-hall/history-1.jpeg',
        ));
    }

    public function committee(): Response
    {
        return $this->page('Committee', [], new Seo(
            title: 'The Committee',
            description: config('site.name').' is a registered charity (Number '.config('site.charity_number').'). The hall is managed by Trustees and a Management Committee.',
        ));
    }

    public function terms(): Response
    {
        return $this->page('Terms', [], new Seo(
            title: 'Terms and Conditions',
            description: 'The conditions of hire for Spencers Wood Village Hall.',
        ));
    }

    /**
     * The next session of one activity, shown as a badge on the built-in
     * pages for it. Null when the activity is missing, unpublished or has no
     * parseable schedule: the page just shows no badge.
     *
     * @return array<string, mixed>|null
     */
    private function nextSession(string $slug): ?array
    {
        $activity = Activity::published()->where('slug', $slug)->first();

        return $activity ? Schedule::next($activity->day, $activity->time)?->toArray() : null;
    }
}
