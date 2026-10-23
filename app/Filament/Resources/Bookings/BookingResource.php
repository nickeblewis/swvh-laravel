<?php

namespace App\Filament\Resources\Bookings;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Booking;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Hall bookings. The public site still books through the Hall Master diary;
 * these are kept here ready for in-app booking.
 */
class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Hall';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Select::make('hirer_id')
                    ->relationship('hirer', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->unique('hirers', 'email'),
                        TextInput::make('phone')->tel(),
                    ])
                    ->required(),
                Select::make('status')->options(BookingStatus::class)->default(BookingStatus::Requested)->required(),
                DateTimePicker::make('starts_at')->label('Starts')->seconds(false)->required(),
                DateTimePicker::make('ends_at')->label('Ends')->seconds(false)->required()->after('starts_at'),
                Select::make('spaces')
                    ->relationship('spaces', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('position'))
                    ->multiple()
                    ->preload()
                    ->columnSpanFull(),
                Textarea::make('notes')->rows(3)->columnSpanFull(),
            ]),
            Section::make('Payment')
                ->relationship('payment')
                ->columns(2)
                ->collapsed(fn (?Booking $record): bool => $record?->payment === null)
                ->schema(PaymentResource::fields()),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('starts_at')->label('When')->dateTime('D j M Y, g:i A')->sortable()
                    ->description(fn (Booking $record): string => 'until '.$record->ends_at->format($record->ends_at->isSameDay($record->starts_at) ? 'g:i A' : 'D j M, g:i A')),
                TextColumn::make('title')->searchable()->weight('medium')
                    ->description(fn (Booking $record): string => $record->hirer->name),
                TextColumn::make('spaces.name')->badge()->color('gray'),
                TextColumn::make('status')->badge(),
                TextColumn::make('payment.status')->label('Payment')->badge()->placeholder('—'),
            ])
            ->filters([SelectFilter::make('status')->options(BookingStatus::class)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
