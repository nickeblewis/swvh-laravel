<?php

namespace App\Filament\Resources\Hirers\Pages;

use App\Filament\Resources\Hirers\HirerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHirers extends ManageRecords
{
    protected static string $resource = HirerResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
