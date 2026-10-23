<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Page;
use App\Models\Space;
use DateTimeInterface;

/**
 * The shapes public pages receive, matching the Nuxt site's
 * `shared/types/content.ts` so the Vue components keep their props. This is
 * the only place that decides what a page may see. Markdown bodies arrive as
 * safe HTML (`bodyHtml`).
 */
final class Present
{
    /** @return array<string, mixed> */
    public static function activityItem(Activity $activity, ?Session $session, ?DateTimeInterface $now = null): array
    {
        return [
            'slug' => $activity->slug,
            'title' => $activity->title,
            'day' => $activity->day,
            'time' => $activity->time,
            'price' => $activity->price,
            'heroImagePath' => $activity->heroImageUrl(),
            'heroImageAlt' => $activity->hero_image_alt,
            'occurrence' => $session?->toArray($now),
        ];
    }

    /** @return array<string, mixed> */
    public static function activityDetail(Activity $activity, ?DateTimeInterface $now = null): array
    {
        return [
            ...self::activityItem($activity, Schedule::next($activity->day, $activity->time, $now), $now),
            'bodyHtml' => Markdown::html($activity->body),
            'description' => $activity->description,
            'ageRange' => $activity->age_range,
            'contact' => $activity->contact,
            'phone' => $activity->phone,
            'externalUrl' => $activity->external_url,
        ];
    }

    /** @return array<string, mixed> */
    public static function space(Space $space): array
    {
        return [
            'slug' => $space->slug,
            'name' => $space->name,
            'capacity' => $space->capacity,
            'dimensions' => $space->dimensions,
            'description' => $space->description,
        ];
    }

    /** @return array<string, mixed> */
    public static function page(Page $page): array
    {
        return [
            'slug' => $page->slug,
            'title' => $page->title,
            'description' => $page->description,
            'bodyHtml' => Markdown::html($page->body),
            'template' => $page->template->value,
            'heroImagePath' => $page->heroImageUrl(),
            'heroImageAlt' => $page->hero_image_alt,
        ];
    }
}
