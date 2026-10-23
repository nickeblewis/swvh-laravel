<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A part of the hall that can be hired. Bookable spaces show as chips on the
 * home page.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $bookable
 * @property int $position
 * @property int|null $capacity
 * @property string|null $description
 * @property string|null $dimensions
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Guarded(['id'])]
class Space extends Model
{
    protected function casts(): array
    {
        return [
            'bookable' => 'boolean',
            'position' => 'integer',
            'capacity' => 'integer',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('bookable', true)->orderBy('position')->orderBy('name');
    }

    /** @return BelongsToMany<Booking, $this> */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class)->withTimestamps();
    }
}
