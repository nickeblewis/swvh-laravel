<?php

use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Activities\Pages\CreateActivity;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Hirers\HirerResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Spaces\SpaceResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Activity;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('sends signed-out visitors to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('loads every admin screen', function (string $resource) {
    $this->actingAs(admin())->get($resource::getUrl())->assertOk();
})->with([
    ActivityResource::class,
    PageResource::class,
    SpaceResource::class,
    BookingResource::class,
    HirerResource::class,
    PaymentResource::class,
    EnquiryResource::class,
    UserResource::class,
]);

it('loads the dashboard and the create forms', function () {
    $this->actingAs(admin());

    $this->get('/admin')->assertOk()->assertSee('Regular activities');
    $this->get(ActivityResource::getUrl('create'))->assertOk();
    $this->get(PageResource::getUrl('create'))->assertOk();
    $this->get(BookingResource::getUrl('create'))->assertOk();
});

it('creates an activity, making the slug from the title', function () {
    $this->actingAs(admin());

    Livewire::test(CreateActivity::class)
        ->fillForm(['title' => 'Tai Chi', 'body' => 'Gentle exercise.', 'day' => 'Monday', 'time' => '10am', 'phone' => '0118   123 4567'])
        ->call('create')
        ->assertHasNoFormErrors();

    $activity = Activity::sole();
    expect($activity->slug)->toBe('tai-chi')
        ->and($activity->phone)->toBe('0118 123 4567');
});

it('rejects a bad phone number', function () {
    $this->actingAs(admin());

    Livewire::test(CreateActivity::class)
        ->fillForm(['title' => 'Tai Chi', 'body' => 'Gentle exercise.', 'phone' => 'call me'])
        ->call('create')
        ->assertHasFormErrors(['phone']);
});

it("won't let a page take a built-in page's address", function () {
    $this->actingAs(admin());

    Livewire::test(CreatePage::class)
        ->fillForm(['title' => 'Booking', 'slug' => 'booking', 'body' => 'Hi', 'template' => 'standard'])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(Page::count())->toBe(0);
});

it('deletes the old upload when an image is replaced, removed or the record deleted', function () {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $activity = Activity::factory()->create(['hero_image' => UploadedFile::fake()->image('old.jpg')->store('heroes', 'public')]);
    $old = $activity->hero_image;

    $activity->update(['hero_image' => $new = UploadedFile::fake()->image('new.jpg')->store('heroes', 'public')]);
    $disk->assertMissing($old);
    $disk->assertExists($new);

    $activity->update(['hero_image' => null]);
    $disk->assertMissing($new);

    $activity->update(['hero_image' => $last = UploadedFile::fake()->image('last.jpg')->store('heroes', 'public')]);
    $activity->delete();
    $disk->assertMissing($last);
});

it('shows an uploaded image in place of the seeded one', function () {
    Storage::fake('public');
    $activity = Activity::factory()->create(['hero_image_path' => '/images/activities/yoga-with-tash.jpg']);

    expect($activity->heroImageUrl())->toBe('/images/activities/yoga-with-tash.jpg');

    $activity->update(['hero_image' => 'heroes/abc.jpg']);
    expect($activity->heroImageUrl())->toEndWith('/storage/heroes/abc.jpg');
});

it("won't delete yourself or the last admin", function () {
    $me = admin();
    $this->actingAs($me);

    expect(UserResource::canDelete($me))->toBeFalse();

    $other = admin();
    expect(UserResource::canDelete($other))->toBeTrue();

    $other->delete();
    expect(UserResource::canDelete($me))->toBeFalse()
        ->and(User::count())->toBe(1);
});

it('creates an admin from the command line', function () {
    $this->artisan('swvh:admin', ['email' => 'New@Example.com'])
        ->expectsQuestion('Password for new@example.com', 'a-long-password-1')
        ->assertSuccessful();

    $user = User::sole();
    expect($user->email)->toBe('new@example.com')
        ->and(Hash::check('a-long-password-1', $user->password))->toBeTrue();
});
