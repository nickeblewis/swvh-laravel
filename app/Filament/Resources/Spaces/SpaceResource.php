<?php

namespace App\Filament\Resources\Spaces;

use App\Filament\Resources\Spaces\Pages\ManageSpaces;
use App\Models\Space;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** The parts of the hall that can be hired. Bookable ones show as chips on the home page. */
class SpaceResource extends Resource
{
    protected static ?string $model = Space::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Hall';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                    if ($operation === 'create') {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')->required()->regex('/^[a-z0-9-]+$/')->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('capacity')->numeric()->integer()->minValue(1)->suffix('people'),
            TextInput::make('dimensions')->placeholder('L 14m (45ft) × W 7m (22.5ft)')->maxLength(255),
            Textarea::make('description')->rows(3)->columnSpanFull(),
            TextInput::make('position')->numeric()->integer()->default(0)->required()->helperText('Lower numbers come first.'),
            Toggle::make('bookable')->default(true)->helperText('Shown on the home page.')->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('name')->weight('medium')->searchable(),
                TextColumn::make('capacity')->placeholder('—'),
                TextColumn::make('dimensions')->placeholder('—'),
                IconColumn::make('bookable')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSpaces::route('/')];
    }
}
