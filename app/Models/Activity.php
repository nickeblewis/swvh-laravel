<?php

namespace App\Models;

use App\Models\Concerns\HasHeroImage;
use App\Models\Concerns\Publishable;
use Carbon\CarbonImmutable;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A regular activity at the hall. `day` and `time` are free text; the next
 * session is worked out from them by App\Support\Schedule.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $body
 * @property string|null $description
 * @property string|null $day
 * @property string|null $time
 * @property string|null $price
 * @property string|null $age_range
 * @property string|null $contact
 * @property string|null $phone
 * @property string|null $external_url
 * @property string|null $hero_image
 * @property string|null $hero_image_path
 * @property string|null $hero_image_alt
 * @property string|null $source_url
 * @property CarbonImmutable|null $source_updated_on
 * @property bool $draft
 * @property int $position
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Guarded(['id'])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory, HasHeroImage, Publishable;

    protected function casts(): array
    {
        return [
            'source_updated_on' => 'date',
            'draft' => 'boolean',
            'position' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /** "Monday · 7:30 PM - 9:00 PM", for the admin list. */
    public function scheduleSummary(): string
    {
        return collect([$this->day, $this->time])->filter()->implode(' · ');
    }
}
