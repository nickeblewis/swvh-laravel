# Spec

A port of the Nuxt site (`../swvh_nuxt`) to Laravel on Forge. The public site
must stay identical to the Nuxt one. Status markers: ☐ planned, 🔨 in progress,
✅ done (with date), ⏸ parked.

## Features

|               | Feature                            | Notes                                                                                                                                                                                                                   |
| ------------- | ---------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ✅ 2026-10-23 | Public pages at the Nuxt addresses | `/`, `/regular-activities(/:slug)`, `/market`, `/repair-cafe`, `/booking`, `/articles(/:slug)`, `/about`, `/hall-info`, `/history`, `/the-committee`, `/terms-and-conditions`, `/contact`, and `/:slug` for admin pages |
| ✅ 2026-10-23 | Next-session labels                | `App\Support\Schedule`, ported from `server/utils/schedule.ts` with its tests                                                                                                                                           |
| ✅ 2026-10-23 | Server-side rendering and SEO      | Inertia SSR; title, description, canonical, robots and Open Graph tags in the HTML (`App\Support\Seo`)                                                                                                                  |
| ✅ 2026-10-23 | Contact enquiries by email         | Replaces Netlify Forms. Saved to `enquiries`, then emailed through Resend                                                                                                                                               |
| ✅ 2026-10-23 | Filament admin                     | Activities, pages, spaces, bookings, hirers, payments, enquiries, admin users                                                                                                                                           |
| ✅ 2026-10-23 | Admin password reset by email      | Filament's built-in flow, through the same mailer                                                                                                                                                                       |
| ✅ 2026-10-23 | Import from the Nuxt site          | `php artisan swvh:import-nuxt`                                                                                                                                                                                          |
| ✅ 2026-10-23 | Articles from markdown files       | `content/articles/*.md`, same frontmatter as Nuxt. Empty today                                                                                                                                                          |
| ☐             | Code highlighting in markdown      | Nuxt used Shiki. No article or body uses code blocks yet                                                                                                                                                                |
| ☐             | In-app booking                     | Tables and admin exist; the public site still books through Hall Master                                                                                                                                                 |

## Rules

- **Published** means `draft = false` and `published_at` empty or in the past.
  Order is position, then title. The same rule applies to articles, using
  their frontmatter.
- **Slugs** are made from the title when the record is created and are not
  changed when the title changes. They must be unique. A page can't use a
  reserved slug (`config/site.php`, `reserved_slugs`).
- **Phone numbers**: whitespace is squished, and the number must match
  `^\+?[\d\s()-]{7,20}$`.
- **Hero images**: JPEG, PNG, WebP, GIF or AVIF, up to 5 MB. No SVG, because
  it can carry script. Replacing, removing or deleting removes the old file.
- **Admins**: you can't delete yourself or the last admin. Passwords are at
  least 12 characters.
- **Contact form**:
    - name, email and message are required; the subject (max 200) and message
      (max 5000) are length-limited
    - a blank subject becomes "Spencers Wood Village Hall contact enquiry from {name}"
    - a filled honeypot (`website`) gets a fake success
    - limited to 5 posts per 10 minutes
    - mail goes to `CONTACT_RECIPIENT`, cc `CONTACT_CC_RECIPIENT`, with
      reply-to set to the sender
    - if sending fails, the error is logged and the visitor still sees the
      thank-you panel; the enquiry is in the admin inbox
- **Schedules**: a free-text `day` ("Monday, Thursday", "Saturdays", "1st
  Sunday of month", "Last Friday of month") and `time` ("7:30 PM - 9:00 PM",
  "8pm") are turned into the next session within 62 days, in Europe/London.
  With no end time a session lasts an hour; an end at or before the start
  means it finishes the next day.

## Environment variables

| Variable                                                      | Purpose                                                           |
| ------------------------------------------------------------- | ----------------------------------------------------------------- |
| `APP_ENV`                                                     | `production` on Forge. Any other value makes every page `noindex` |
| `APP_URL`                                                     | The site's address; used for canonical links and upload URLs      |
| `DB_CONNECTION`, `DB_*`                                       | `sqlite` locally, `pgsql` on Forge                                |
| `MAIL_MAILER`                                                 | `resend` in production, `log` locally                             |
| `RESEND_API_KEY`                                              | Resend API key                                                    |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`                         | Sender. The domain must be verified in Resend                     |
| `CONTACT_RECIPIENT`                                           | Where enquiries go (default `admin@swvh.org.uk`)                  |
| `CONTACT_CC_RECIPIENT`                                        | Optional copy of every enquiry                                    |
| `INERTIA_SSR_ENABLED`                                         | Server-side rendering (default true)                              |
| `NUXT_DB_URL`                                                 | The Nuxt site's Neon database, for the import only                |
| `SITE_EMAIL`, `SITE_MARKET_EMAIL`, `SITE_HALLMASTER_DIARY_ID` | Optional overrides of `config/site.php`                           |

## Differences from the Nuxt site

- Netlify Forms is replaced by `POST /contact`, an `enquiries` table and email.
- Netlify Blobs is replaced by the `public` disk (`storage/app/public/heroes`).
  Old `/images/uploads/<key>` links redirect to the new files.
- The hand-built admin is replaced by Filament. It adds Publish from
  (`published_at`), Spaces, Bookings, Hirers, Payments and the enquiry inbox,
  which Nuxt had no screens for.
- The Nuxt `users` table (hirers) is `hirers`; `users` holds admin logins.
- Code blocks in markdown are not highlighted yet (see the feature list).

## Open questions

- What is the live Nuxt site's address, for `--site` when importing images?
- Should `CONTACT_CC_RECIPIENT` be set? Rails copied enquiries to `nickeblewis@gmail.com`.
- Which domain will mail be sent from: `headforcode.com` (as Rails did) or `swvh.org.uk`?
