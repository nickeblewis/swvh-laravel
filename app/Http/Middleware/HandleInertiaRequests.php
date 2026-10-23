<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'site' => fn (): array => $this->site(),
            // Set by ContactController after an enquiry; the page swaps to the thank-you panel.
            'enquirySent' => fn (): bool => (bool) $request->session()->get('enquiry_sent'),
        ];
    }

    /**
     * The hall's identity, the same fields the Nuxt site's `useSite()` gave.
     * Sent with every response, so kept small.
     *
     * @return array<string, mixed>
     */
    private function site(): array
    {
        return [
            'name' => config('site.name'),
            'shortName' => config('site.short_name'),
            'tagline' => config('site.tagline'),
            'locality' => config('site.locality'),
            'charityNumber' => config('site.charity_number'),
            'email' => config('site.email'),
            'marketEmail' => config('site.market_email'),
            'address' => config('site.address'),
            'mapQuery' => config('site.map_query'),
            'hallmasterDiaryId' => config('site.hallmaster_diary_id'),
            'timeZone' => config('site.time_zone'),
            'social' => config('site.social'),
        ];
    }
}
