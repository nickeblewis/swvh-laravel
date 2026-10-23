<?php

namespace App\Filament\Resources\Pages;

use App\Enums\PageTemplate;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\ContentFields;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Admin-created pages, served at `/{slug}`. The built-in pages (market,
 * booking, …) are Vue files and are not edited here.
 */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                ContentFields::title()->columnSpanFull(),
                ContentFields::slug('/', config('site.reserved_slugs')),
                Select::make('template')
                    ->options(PageTemplate::class)
                    ->default(PageTemplate::Standard)
                    ->required(),
                Textarea::make('description')
                    ->helperText('Shown under the title, and to search engines.')
                    ->rows(2)
                    ->columnSpanFull(),
                ContentFields::body(),
                ContentFields::sourceUrl(),
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
                TextColumn::make('title')->searchable()->weight('medium'),
                TextColumn::make('slug')->label('Address')->formatStateUsing(fn (string $state): string => "/{$state}"),
                TextColumn::make('template')->badge()->color('gray'),
                TextColumn::make('draft')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Draft' : 'Published')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'success'),
            ])
            ->filters([
                TernaryFilter::make('draft'),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (Page $record): string => "/{$record->slug}", shouldOpenInNewTab: true)
                    ->visible(fn (Page $record): bool => $record->isPublished()),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
