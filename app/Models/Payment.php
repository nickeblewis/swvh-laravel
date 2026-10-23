<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One payment per booking. Money is integer pence plus a currency code.
 *
 * @property int $id
 * @property int $booking_id
 * @property int $amount_pence
 * @property string $currency
 * @property PaymentStatus $status
 * @property CarbonImmutable|null $paid_at
 * @property string|null $provider
 * @property string|null $provider_reference
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Guarded(['id'])]
class Payment extends Model
{
    protected $attributes = [
        'currency' => 'GBP',
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'amount_pence' => 'integer',
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** "£12.50" */
    public function formattedAmount(): string
    {
        $symbol = $this->currency === 'GBP' ? '£' : "{$this->currency} ";

        return $symbol.number_format($this->amount_pence / 100, 2);
    }
}
