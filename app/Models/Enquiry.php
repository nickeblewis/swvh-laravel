<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

/**
 * A contact-form enquiry.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $message
 * @property CarbonImmutable|null $read_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Guarded(['id'])]
class Enquiry extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
