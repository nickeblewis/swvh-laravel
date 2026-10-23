<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/** The note from the Nuxt dashboard: what lives in the code rather than here. */
class NotEditedHere extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.not-edited-here';
}
