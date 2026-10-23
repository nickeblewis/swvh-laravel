/**
 * The shapes the server hands to pages (see App\Support\Present). Database
 * rows and markdown files are both reduced to these before they leave the
 * server. Markdown arrives as safe HTML in `bodyHtml`.
 */

/** The hall's identity, shared with every page (HandleInertiaRequests). */
export interface Site {
    name: string;
    shortName: string;
    tagline: string;
    locality: string;
    charityNumber: string;
    email: string;
    marketEmail: string;
    address: string[];
    mapQuery: string;
    hallmasterDiaryId: number;
    timeZone: string;
    social: { market_facebook: string; repair_cafe_facebook: string };
}

/**
 * One dated session of a regular activity.
 *
 * `label` and `highlight` are worked out on the server, in the hall's time
 * zone, so the server render and the browser always agree on "Today".
 */
export interface Occurrence {
    /** ISO 8601. */
    startsAt: string;
    endsAt: string;
    /** "On now", "Today · 7:30 PM", "Tomorrow · 10:00 AM" or "Sun 4 Oct · 10:00 AM". */
    label: string;
    /** True when the session is today or under way: shown in amber. */
    highlight: boolean;
}

export interface ActivityItem {
    slug: string;
    title: string;
    day: string | null;
    time: string | null;
    price: string | null;
    heroImagePath: string | null;
    heroImageAlt: string | null;
    /** Null when the day or time can't be parsed into a schedule. */
    occurrence: Occurrence | null;
}

export interface ActivityDetail extends ActivityItem {
    bodyHtml: string;
    description: string | null;
    ageRange: string | null;
    contact: string | null;
    phone: string | null;
    externalUrl: string | null;
}

export interface SpaceItem {
    slug: string;
    name: string;
    capacity: number | null;
    dimensions: string | null;
    description: string | null;
}

export type PageTemplate = 'standard' | 'contact';

export interface PageDetail {
    slug: string;
    title: string;
    description: string | null;
    bodyHtml: string;
    template: PageTemplate;
    heroImagePath: string | null;
    heroImageAlt: string | null;
}

export interface ArticleItem {
    slug: string;
    title: string;
    description: string;
    /** ISO 8601, or null when the frontmatter omitted it. */
    publishedAt: string | null;
    readingTimeMinutes: number;
    heroImageUrl: string | null;
    heroImageAlt: string | null;
}

export interface ArticleDetail extends ArticleItem {
    bodyHtml: string;
}

/** A photo in a folder under `public/images`. */
export interface GalleryPhoto {
    path: string;
    alt: string;
}
