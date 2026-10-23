# Deploying to Laravel Forge

## Server

- **Server:** a Forge app server with PHP 8.3+ (8.4 recommended), Postgres
  and Node 22.
- **Site:** a site for the domain, with the project directory left as `/public`.
- **Database:** create a Postgres database and user in Forge, and put the
  details in the site's environment.
- **SSL:** add a Let's Encrypt certificate once DNS points at the server.

## Environment

Set these in Forge's Environment panel, and see `docs/spec.md` for the full list:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://swvh.org.uk

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=swvh
DB_USERNAME=forge
DB_PASSWORD=…

MAIL_MAILER=resend
RESEND_API_KEY=re_…
MAIL_FROM_ADDRESS=noreply@headforcode.com
MAIL_FROM_NAME="Spencers Wood Village Hall"
CONTACT_RECIPIENT=admin@swvh.org.uk
CONTACT_CC_RECIPIENT=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Deploy script

```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci
npm run build:ssr

$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan storage:link --force
$FORGE_PHP artisan optimize
$FORGE_PHP artisan filament:optimize

# The SSR daemon below restarts itself once stopped, picking up the new bundle.
$FORGE_PHP artisan inertia:stop-ssr

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock
```

If you use Forge's zero-downtime deployments, keep `storage` in the shared
paths (Forge does this by default) so uploaded images survive each release.

## Daemons

| Command                            | Why                                                                                          |
| ---------------------------------- | -------------------------------------------------------------------------------------------- |
| `php artisan inertia:start-ssr`    | Serves the server-rendered HTML. Without it, pages still work but render in the browser only |
| `php artisan queue:work --tries=3` | Not needed yet: mail is sent straight away. Add it if mail is ever queued                    |

The scheduler is not needed yet: there are no scheduled tasks.

## Mail through Resend

The Nuxt site had no mail of its own: Netlify Forms emailed the enquiries.
Here Laravel sends them through Resend's HTTPS API (the `resend` mailer from
`resend/resend-laravel`), the same provider the Rails site used, so no SMTP
port is needed on the server.

1. In Resend, verify the sending domain, then add its DKIM and SPF DNS records.
2. Create an API key with sending access and set `RESEND_API_KEY`.
3. Set `MAIL_FROM_ADDRESS` to an address on the verified domain.
4. Send a test enquiry from `/contact`. Then use "Forgot password" on
   `/admin/login` to check the reset email.

Every enquiry is also saved to the admin inbox, so if Resend is down nothing
is lost; the failure goes to the log (`storage/logs/laravel.log`).

## Moving the data across from the Nuxt site

1. Get the Neon connection string from the Netlify site's database settings
   and set `NUXT_DB_URL` on the Forge site, temporarily.
2. Dry run first:

    ```bash
    php artisan swvh:import-nuxt
    ```

3. Import for real. `--site` is the live Nuxt site, where uploaded images are
   fetched from:

    ```bash
    php artisan swvh:import-nuxt --apply --site=https://<the live nuxt site>
    ```

4. The Nuxt admins are created with random passwords, because their scrypt
   hashes can't be checked by Laravel. Each admin uses "Forgot password" at
   `/admin/login`, or you run `php artisan swvh:admin them@example.com`.
5. Remove `NUXT_DB_URL`.

The import is safe to re-run; it updates in place. On a fresh server with no
import, `php artisan db:seed --class=HallSeeder --force` loads the starting
activities and spaces instead.

## Cutover checklist

1. Deploy, import and check every page against the Nuxt site.
2. Confirm the Resend domain is verified and a test enquiry arrives.
3. Point DNS at the Forge server and add the certificate.
4. Turn off the Netlify Forms submission notifications, then the Netlify site.
