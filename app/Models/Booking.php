<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $title
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property BookingStatus $status
 * @property string|null $notes
 * @property int $hirer_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Guarded(['id'])]
class Booking extends Model
{
    protected $attributes = [
        'status' => 'requested',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    /** @return BelongsTo<Hirer, $this> */
    public function hirer(): BelongsTo
    {
        return $this->belongsTo(Hirer::class);
    }

    /** @return BelongsToMany<Space, $this> */
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class)->withTimestamps();
    }

    /** @return HasOne<Payment, $this> */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
