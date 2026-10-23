<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageTemplate: string implements HasLabel
{
    case Standard = 'standard';
    case Contact = 'contact';

    public function getLabel(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Contact => 'Contact (adds the enquiry form)',
        };
    }
}
