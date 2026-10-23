<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

/** Who can sign in to the admin. Every admin can do everything. */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Admin users';

    protected static ?string $modelLabel = 'admin user';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->dehydrateStateUsing(fn (string $state): string => strtolower(trim($state))),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->rule(Password::min(12))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText(fn (string $operation): string => $operation === 'edit'
                    ? 'Leave empty to keep the current password. At least 12 characters.'
                    : 'At least 12 characters.'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('email')
            ->columns([
                TextColumn::make('email')->searchable()->weight('medium')
                    ->description(fn (User $record): ?string => $record->id === auth()->id() ? 'You' : null),
                TextColumn::make('name')->searchable(),
                TextColumn::make('created_at')->label('Added')->date('j M Y'),
            ])
            ->recordActions([
                EditAction::make(),
                // You can't delete yourself or the last admin.
                DeleteAction::make()->visible(fn (User $record): bool => self::canBeDeleted($record)),
            ]);
    }

    public static function canBeDeleted(User $user): bool
    {
        return $user->id !== auth()->id() && User::count() > 1;
    }

    public static function canDelete(Model $record): bool
    {
        return $record instanceof User && self::canBeDeleted($record);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
