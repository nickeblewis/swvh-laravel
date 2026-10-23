<?php

namespace App\Filament\Resources\Spaces\Pages;

use App\Filament\Resources\Spaces\SpaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSpaces extends ManageRecords
{
    protected static string $resource = SpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
