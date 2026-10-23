<?php

/*
| The hall's identity. Never hard-code these in PHP, Blade, mail or Vue: read
| them from here (Vue gets them as the shared `site` prop).
*/

return [
    'name' => env('SITE_NAME', 'Spencers Wood Village Hall'),
    'short_name' => env('SITE_SHORT_NAME', 'SWVH'),
    'tagline' => 'At the heart of the community',
    'locality' => 'Spencers Wood, Reading',
    'charity_number' => '300204',
    'email' => env('SITE_EMAIL', 'admin@swvh.org.uk'),
    'market_email' => env('SITE_MARKET_EMAIL', 'market@swvh.org.uk'),
    'address' => ['Basingstoke Road', 'Spencers Wood', 'Reading', 'Berkshire', 'RG7 1AP'],
    'map_query' => 'Basingstoke Road, Spencers Wood, Reading, Berkshire RG7 1AP',
    'hallmaster_diary_id' => (int) env('SITE_HALLMASTER_DIARY_ID', 7878),
    'time_zone' => 'Europe/London',

    'social' => [
        'market_facebook' => 'https://www.facebook.com/Spencerswoodmarketuk/',
        'repair_cafe_facebook' => 'https://www.facebook.com/groups/453985379084488/',
    ],

    /*
    | Who contact-form enquiries are emailed to. Every enquiry is also kept in
    | the admin inbox, so nothing is lost if mail fails.
    */
    'contact' => [
        'recipient' => env('CONTACT_RECIPIENT', 'admin@swvh.org.uk'),
        'cc' => env('CONTACT_CC_RECIPIENT'),
    ],

    /*
    | Slugs an admin-created page can't use: the built-in pages and every other
    | routed path. The catch-all `/{slug}` route comes after all of these.
    */
    'reserved_slugs' => [
        'market', 'repair-cafe', 'booking', 'contact', 'about',
        'hall-info', 'history', 'the-committee', 'terms-and-conditions',
        'home', 'regular-activities', 'articles', 'admin', 'admin_users', 'users', 'up', 'api',
        'storage', 'images', 'livewire', 'filament', 'build',
    ],
];
