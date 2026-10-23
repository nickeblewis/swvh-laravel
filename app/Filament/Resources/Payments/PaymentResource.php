<?php

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\ManagePayments;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/** One payment per booking. Amounts are stored in pence and edited in pounds. */
class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Hall';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('booking_id')
                ->relationship('booking', 'title', modifyQueryUsing: fn ($query) => $query->orderByDesc('starts_at'))
                ->searchable()
                ->preload()
                ->unique(ignoreRecord: true)
                ->required(),
            ...self::fields(),
        ]);
    }

    /**
     * The fields for a payment, also used inline on the booking form.
     *
     * @return list<mixed>
     */
    public static function fields(): array
    {
        return [
            TextInput::make('amount_pence')
                ->label('Amount')
                ->prefix('£')
                ->numeric()
                ->minValue(0)
                ->step('0.01')
                ->required()
                ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : number_format($state / 100, 2, '.', ''))
                ->dehydrateStateUsing(fn (mixed $state): int => (int) round(((float) $state) * 100)),
            Select::make('status')->options(PaymentStatus::class)->default(PaymentStatus::Pending)->required(),
            DateTimePicker::make('paid_at')->label('Paid')->seconds(false),
            TextInput::make('provider')->placeholder('e.g. bank transfer')->maxLength(255),
            TextInput::make('provider_reference')->label('Reference')->maxLength(255),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('booking.title')->label('Booking')->searchable()->weight('medium')
                    ->description(fn (Payment $record): string => $record->booking->starts_at->format('j M Y')),
                TextColumn::make('amount_pence')->label('Amount')
                    ->formatStateUsing(fn (Payment $record): string => $record->formattedAmount()),
                TextColumn::make('status')->badge(),
                TextColumn::make('paid_at')->label('Paid')->date('j M Y')->placeholder('—'),
            ])
            ->filters([SelectFilter::make('status')->options(PaymentStatus::class)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePayments::route('/')];
    }
}
