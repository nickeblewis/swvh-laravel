<?php

use App\Enums\BookingStatus;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Hirer;
use App\Models\Page;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/** A stand-in for the Nuxt site's Neon database, with the same tables. */
beforeEach(function () {
    config(['database.connections.nuxt' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('nuxt');
    $schema = Schema::connection('nuxt');

    $content = function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug');
        $table->text('body');
        $table->text('description')->nullable();
        $table->string('hero_image_path')->nullable();
        $table->string('hero_image_key')->nullable();
        $table->string('hero_image_alt')->nullable();
        $table->text('source_url')->nullable();
        $table->boolean('draft')->default(false);
        $table->integer('position')->default(0);
        $table->string('published_at')->nullable();
        $table->string('created_at')->nullable();
        $table->string('updated_at')->nullable();
    };
    $schema->create('activities', function (Blueprint $table) use ($content) {
        $content($table);
        foreach (['day', 'time', 'price', 'age_range', 'contact', 'phone', 'external_url', 'source_updated_on'] as $column) {
            $table->string($column)->nullable();
        }
    });
    $schema->create('pages', function (Blueprint $table) use ($content) {
        $content($table);
        $table->string('template')->default('standard');
    });
    $schema->create('spaces', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->boolean('bookable')->default(true);
        $table->integer('position')->default(0);
        $table->integer('capacity')->nullable();
        $table->text('description')->nullable();
        $table->string('dimensions')->nullable();
        $table->timestamps();
    });
    $schema->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('email')->default('');
        $table->string('password_hash')->default('');
        $table->string('name');
        $table->string('phone')->nullable();
        $table->string('approved_at')->nullable();
        $table->timestamps();
    });
    $schema->create('bookings', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('starts_at');
        $table->string('ends_at');
        $table->string('status')->default('requested');
        $table->text('notes')->nullable();
        $table->integer('user_id');
        $table->timestamps();
    });
    $schema->create('booking_spaces', function (Blueprint $table) {
        $table->id();
        $table->integer('booking_id');
        $table->integer('space_id');
        $table->timestamps();
    });
    $schema->create('payments', function (Blueprint $table) {
        $table->id();
        $table->integer('amount_pence');
        $table->integer('booking_id');
        $table->string('currency')->default('GBP');
        $table->string('status')->default('pending');
        $table->string('paid_at')->nullable();
        $table->string('provider')->nullable();
        $table->string('provider_reference')->nullable();
        $table->timestamps();
    });
    $schema->create('admin_users', function (Blueprint $table) {
        $table->id();
        $table->string('email');
        $table->string('password_hash');
        $table->timestamps();
    });

    $nuxt = DB::connection('nuxt');
    $nuxt->table('activities')->insert([
        ['title' => 'Yoga', 'slug' => 'yoga', 'body' => 'Stretch.', 'day' => 'Tuesday', 'time' => '12:00 PM - 1:00 PM', 'hero_image_path' => '/images/activities/yoga-with-tash.jpg', 'hero_image_key' => null, 'published_at' => null],
        ['title' => 'Karate', 'slug' => 'karate', 'body' => 'Kick.', 'day' => 'Monday', 'time' => '6pm', 'hero_image_path' => '/images/uploads/0b8a1c2e-1111-4222-8333-944455556666.webp', 'hero_image_key' => '0b8a1c2e-1111-4222-8333-944455556666.webp', 'published_at' => '2026-07-01 09:00:00+00'],
    ]);
    $nuxt->table('pages')->insert(['title' => 'Hire prices', 'slug' => 'hire-prices', 'body' => 'Prices.', 'template' => 'contact']);
    $nuxt->table('spaces')->insert(['name' => 'Main Hall', 'slug' => 'main-hall', 'capacity' => 100]);
    $nuxt->table('users')->insert(['email' => 'Hirer@Example.com', 'name' => 'Pat Hirer']);
    $nuxt->table('bookings')->insert(['title' => 'Party', 'starts_at' => '2026-07-04 13:00:00+00', 'ends_at' => '2026-07-04 17:00:00+00', 'status' => 'confirmed', 'user_id' => 1]);
    $nuxt->table('booking_spaces')->insert(['booking_id' => 1, 'space_id' => 1]);
    $nuxt->table('payments')->insert(['booking_id' => 1, 'amount_pence' => 12500, 'status' => 'paid']);
    $nuxt->table('admin_users')->insert(['email' => 'Nick@Example.com', 'password_hash' => '$scrypt$…']);

    Storage::fake('public');
    Http::fake(['https://nuxt.test/images/uploads/*' => Http::response('image-bytes')]);
});

it('changes nothing on a dry run', function () {
    $this->artisan('swvh:import-nuxt')->assertSuccessful();

    expect(Activity::count())->toBe(0)->and(User::count())->toBe(0);
    Http::assertNothingSent();
});

it('copies the content, bookings and admins across', function () {
    $this->artisan('swvh:import-nuxt', ['--apply' => true, '--site' => 'https://nuxt.test'])->assertSuccessful();

    expect(Activity::pluck('slug')->sort()->values()->all())->toBe(['karate', 'yoga']);

    $karate = Activity::firstWhere('slug', 'karate');
    expect($karate->hero_image)->toBe('heroes/0b8a1c2e-1111-4222-8333-944455556666.webp')
        ->and($karate->hero_image_path)->toBeNull()
        // 09:00 UTC in July is 10:00 at the hall.
        ->and($karate->published_at->format('Y-m-d H:i'))->toBe('2026-07-01 10:00');
    Storage::disk('public')->assertExists('heroes/0b8a1c2e-1111-4222-8333-944455556666.webp');

    expect(Activity::firstWhere('slug', 'yoga')->heroImageUrl())->toBe('/images/activities/yoga-with-tash.jpg')
        ->and(Page::sole()->template->value)->toBe('contact')
        ->and(Space::sole()->capacity)->toBe(100);

    $booking = Booking::sole();
    expect($booking->hirer->email)->toBe('hirer@example.com')
        ->and($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->spaces->pluck('slug')->all())->toBe(['main-hall'])
        ->and($booking->payment->formattedAmount())->toBe('£125.00')
        ->and($booking->starts_at->format('H:i'))->toBe('14:00');

    expect(User::sole()->email)->toBe('nick@example.com');
});

it('can be run again without duplicating anything', function () {
    $this->artisan('swvh:import-nuxt', ['--apply' => true, '--site' => 'https://nuxt.test'])->assertSuccessful();
    $this->artisan('swvh:import-nuxt', ['--apply' => true, '--site' => 'https://nuxt.test'])->assertSuccessful();

    expect(Activity::count())->toBe(2)
        ->and(Hirer::count())->toBe(1)
        ->and(Booking::count())->toBe(1)
        ->and(User::count())->toBe(1);
});
