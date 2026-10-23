<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\CreateActivity;
use App\Filament\Resources\Activities\Pages\EditActivity;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Support\ContentFields;
use App\Models\Activity;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Regular activities';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                ContentFields::title()->columnSpanFull(),
                ContentFields::slug('/regular-activities/'),
                ContentFields::sourceUrl(),
                Textarea::make('description')
                    ->helperText('A sentence for search engines. Uses the day and time if left empty.')
                    ->rows(2)
                    ->columnSpanFull(),
                ContentFields::body(),
            ]),
            Section::make('When and how much')->columns(2)->schema([
                TextInput::make('day')
                    ->helperText('e.g. "Monday, Thursday", "Saturdays", "1st Sunday of month" or "Last Friday of month".')
                    ->maxLength(255),
                TextInput::make('time')
                    ->helperText('e.g. "7:30 PM - 9:00 PM" or "10am". Used to work out the next session.')
                    ->maxLength(255),
                TextInput::make('price')->maxLength(255),
                TextInput::make('age_range')->label('Age range')->maxLength(255),
            ]),
            Section::make('Contact')->columns(3)->schema([
                TextInput::make('contact')->label('Contact email')->email()->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->regex('/^\+?[\d\s()-]{7,20}$/')
                    ->validationMessages(['regex' => 'Phone is invalid.'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? (string) preg_replace('/\s+/', ' ', trim($state)) : null),
                TextInput::make('external_url')->label('More information link')->url(),
                DatePicker::make('source_updated_on')->label('Source last updated')->native(false),
            ]),
            ContentFields::heroImage(),
            ContentFields::publishing(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query) => $query->orderBy('position')->orderBy('title'))
            ->columns([
                TextColumn::make('title')->searchable()->weight('medium')
                    ->description(fn (Activity $record): string => "/regular-activities/{$record->slug}"),
                TextColumn::make('schedule')
                    ->state(fn (Activity $record): string => $record->scheduleSummary())
                    ->placeholder('—'),
                TextColumn::make('draft')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Draft' : 'Published')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'success'),
                TextColumn::make('position')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('draft'),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (Activity $record): string => "/regular-activities/{$record->slug}", shouldOpenInNewTab: true)
                    ->visible(fn (Activity $record): bool => $record->isPublished()),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }
}
