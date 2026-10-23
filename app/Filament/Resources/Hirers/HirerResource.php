<?php

namespace App\Filament\Resources\Hirers;

use App\Filament\Resources\Hirers\Pages\ManageHirers;
use App\Models\Hirer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** People who hire the hall. Not yet used by the public site, which books through Hall Master. */
class HirerResource extends Resource
{
    protected static ?string $model = Hirer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Hall';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('phone')->tel()->maxLength(255),
            DateTimePicker::make('approved_at')->label('Approved')->seconds(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium')
                    ->description(fn (Hirer $record): ?string => $record->email),
                TextColumn::make('phone')->placeholder('—'),
                TextColumn::make('bookings_count')->counts('bookings')->label('Bookings'),
                TextColumn::make('approved_at')->label('Approved')->date('j M Y')->placeholder('Not yet'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHirers::route('/')];
    }
}
