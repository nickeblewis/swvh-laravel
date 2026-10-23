<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\Hirer;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Space;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * Copy the live data from the Nuxt site's Neon database (`NUXT_DB_URL`,
 * the `nuxt` connection) into this one. Safe to re-run: records are matched
 * by slug, email or (for bookings) title, start and hirer, and updated in
 * place.
 *
 * Without --apply it is a dry run: everything happens inside a transaction
 * that is rolled back, and no images are downloaded.
 *
 * Uploaded hero images are fetched over HTTP from the live site
 * (`/images/uploads/<key>`), so no Netlify token is needed, and saved to
 * `heroes/<key>` on the public disk.
 *
 * Admin passwords are scrypt hashes Laravel can't check, so imported admins
 * get a random password and must use "Forgot password" (or `swvh:admin`).
 */
#[Signature('swvh:import-nuxt
    {--apply : Write the changes (otherwise a dry run)}
    {--site= : The live Nuxt site, for uploaded images, e.g. https://swvh-nuxt.netlify.app}')]
#[Description('Import activities, pages, spaces, bookings and admins from the Nuxt site')]
class ImportNuxt extends Command
{
    /** @var array<string, array{created: int, updated: int}> */
    private array $counts = [];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $site = rtrim((string) ($this->option('site') ?? ''), '/');

        if (blank(config('database.connections.nuxt.url')) && blank(config('database.connections.nuxt.database'))) {
            $this->components->error('NUXT_DB_URL is not set. Add the Neon connection string to the environment, then run `php artisan config:clear` (a cached config ignores new variables).');

            return self::FAILURE;
        }

        $source = DB::connection('nuxt');

        $this->components->info($apply ? 'Importing from the Nuxt database.' : 'Dry run: nothing will be saved. Add --apply to import.');

        DB::beginTransaction();

        try {
            $this->content($source, 'activities', Activity::class, $apply, $site);
            $this->content($source, 'pages', Page::class, $apply, $site);
            $spaces = $this->spaces($source);
            $hirers = $this->hirers($source);
            $this->bookings($source, $hirers, $spaces);
            $this->admins($source);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $apply ? DB::commit() : DB::rollBack();

        $this->table(['Table', 'Created', 'Updated'], collect($this->counts)
            ->map(fn (array $count, string $table): array => [$table, $count['created'], $count['updated']])
            ->values()
            ->all());

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Activity|Page>  $model
     */
    private function content(ConnectionInterface $source, string $table, string $model, bool $apply, string $site): void
    {
        foreach ($source->table($table)->orderBy('id')->get() as $row) {
            $values = collect((array) $row)->except(['id', 'hero_image_key'])->all();
            $key = $row->hero_image_key ?? null;

            if (filled($key)) {
                $values['hero_image'] = "heroes/{$key}";
                $values['hero_image_path'] = null;

                if ($apply) {
                    $this->downloadImage($site, $key);
                }
            }

            $this->upsert($model, ['slug' => $row->slug], $values, $table);
        }
    }

    /** @return array<int, int> Nuxt id => our id */
    private function spaces(ConnectionInterface $source): array
    {
        $ids = [];

        foreach ($source->table('spaces')->orderBy('id')->get() as $row) {
            $ids[$row->id] = $this->upsert(Space::class, ['slug' => $row->slug], collect((array) $row)->except('id')->all(), 'spaces')->getKey();
        }

        return $ids;
    }

    /** @return array<int, int> Nuxt id => our id */
    private function hirers(ConnectionInterface $source): array
    {
        $ids = [];

        foreach ($source->table('users')->orderBy('id')->get() as $row) {
            $email = filled($row->email) ? strtolower($row->email) : null;
            $values = [
                'name' => $row->name,
                'email' => $email,
                'phone' => $row->phone,
                'approved_at' => $row->approved_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];

            $ids[$row->id] = $this->upsert(Hirer::class, $email ? ['email' => $email] : ['name' => $row->name, 'email' => null], $values, 'hirers')->getKey();
        }

        return $ids;
    }

    /**
     * @param  array<int, int>  $hirers
     * @param  array<int, int>  $spaces
     */
    private function bookings(ConnectionInterface $source, array $hirers, array $spaces): void
    {
        $bookingSpaces = $source->table('booking_spaces')->get()->groupBy('booking_id');
        $payments = $source->table('payments')->get()->keyBy('booking_id');

        foreach ($source->table('bookings')->orderBy('id')->get() as $row) {
            $hirerId = $hirers[$row->user_id] ?? throw new RuntimeException("Booking {$row->id} has an unknown hirer.");

            /** @var Booking $booking */
            $booking = $this->upsert(Booking::class, [
                'title' => $row->title,
                'starts_at' => $row->starts_at,
                'hirer_id' => $hirerId,
            ], [
                'ends_at' => $row->ends_at,
                'status' => $row->status,
                'notes' => $row->notes,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], 'bookings');

            $booking->spaces()->sync(
                collect($bookingSpaces->get($row->id, []))->map(fn (stdClass $link): ?int => $spaces[$link->space_id] ?? null)->filter()->all(),
            );

            if ($payment = $payments->get($row->id)) {
                $this->upsert(Payment::class, ['booking_id' => $booking->id], collect((array) $payment)->except(['id', 'booking_id'])->all(), 'payments');
            }
        }
    }

    private function admins(ConnectionInterface $source): void
    {
        foreach ($source->table('admin_users')->orderBy('id')->get() as $row) {
            $email = strtolower($row->email);

            if (User::where('email', $email)->exists()) {
                $this->count('users', false);

                continue;
            }

            User::create([
                'name' => Str::headline(Str::before($email, '@')),
                'email' => $email,
                'password' => Str::password(32),
            ])->forceFill(['email_verified_at' => now(), ...self::localTimes(['created_at' => $row->created_at])])->save();

            $this->count('users', true);
        }
    }

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $model
     * @param  array<string, mixed>  $match
     * @param  array<string, mixed>  $values
     * @return T
     */
    private function upsert(string $model, array $match, array $values, string $label): Model
    {
        $record = $model::query()->firstOrNew(self::localTimes($match));
        $created = ! $record->exists;
        $record->forceFill(self::localTimes($values));
        $record->timestamps = false;
        $record->save();
        $this->count($label, $created);

        return $record;
    }

    /**
     * Postgres hands back timestamps with an offset ("…+00"). Turn them into
     * the app's time zone first, or they would be stored as UTC wall-clock
     * times and read back an hour out in summer.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function localTimes(array $values): array
    {
        foreach (['created_at', 'updated_at', 'published_at', 'starts_at', 'ends_at', 'paid_at', 'approved_at'] as $column) {
            if (filled($values[$column] ?? null)) {
                $values[$column] = CarbonImmutable::parse($values[$column])->setTimezone(config('app.timezone'));
            }
        }

        return $values;
    }

    private function count(string $table, bool $created): void
    {
        $this->counts[$table] ??= ['created' => 0, 'updated' => 0];
        $this->counts[$table][$created ? 'created' : 'updated']++;
    }

    private function downloadImage(string $site, string $key): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists("heroes/{$key}")) {
            return;
        }

        if ($site === '') {
            throw new RuntimeException('Pass --site=https://… so uploaded images can be downloaded.');
        }

        $response = Http::timeout(30)->get("{$site}/images/uploads/{$key}")->throw();
        $disk->put("heroes/{$key}", $response->body());
        $this->line("  Downloaded {$key}");
    }
}
