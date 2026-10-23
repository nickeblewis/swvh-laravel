<?php

namespace App\Filament\Resources\Enquiries;

use App\Filament\Resources\Enquiries\Pages\ManageEnquiries;
use App\Models\Enquiry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/** Contact-form enquiries. Each one is also emailed (see App\Mail\EnquiryMail). */
class EnquiryResource extends Resource
{
    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Inbox';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Enquiry::whereNull('read_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i A'),
            TextEntry::make('subject')->columnSpanFull(),
            TextEntry::make('message')->columnSpanFull()->prose()->formatStateUsing(fn (string $state): string => nl2br(e($state)))->html(),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
                TextColumn::make('name')->searchable()
                    ->weight(fn (Enquiry $record): string => $record->read_at ? 'normal' : 'bold')
                    ->description(fn (Enquiry $record): string => $record->email),
                TextColumn::make('subject')->searchable()->wrap(),
                TextColumn::make('read_at')->label('Read')->since()->placeholder('Unread'),
            ])
            ->filters([
                TernaryFilter::make('read_at')->label('Read')->nullable(),
            ])
            ->recordActions([
                ViewAction::make()->after(fn (Enquiry $record) => $record->read_at ?? $record->update(['read_at' => now()])),
                Action::make('reply')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->url(fn (Enquiry $record): string => 'mailto:'.$record->email.'?subject='.rawurlencode("Re: {$record->subject}")),
                Action::make('markRead')
                    ->label('Mark as read')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Enquiry $record): bool => $record->read_at === null)
                    ->action(fn (Enquiry $record) => $record->update(['read_at' => now()])),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEnquiries::route('/')];
    }
}
