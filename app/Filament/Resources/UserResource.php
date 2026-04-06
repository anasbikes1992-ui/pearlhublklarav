<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Users & Auth';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('full_name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required(fn ($record) => $record === null)
                        ->hiddenOn('edit')
                        ->dehydrated(fn ($state) => !empty($state)),
                ]),

            Forms\Components\Section::make('Role & Status')
                ->schema([
                    Forms\Components\Select::make('role')
                        ->options([
                            User::ROLE_ADMIN => 'Admin',
                            User::ROLE_PROVIDER => 'Provider',
                            User::ROLE_CUSTOMER => 'Customer',
                            User::ROLE_DRIVER => 'Driver',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Inactive users cannot log in'),
                ]),

            Forms\Components\Section::make('Profile Information')
                ->relationship('profile')
                ->schema([
                    Forms\Components\TextInput::make('nic')
                        ->label('NIC/ID Number'),
                    Forms\Components\TextInput::make('city'),
                    Forms\Components\TextInput::make('district'),
                    Forms\Components\Toggle::make('is_kyc_verified')
                        ->label('KYC Verified'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'provider' => 'warning',
                        'customer' => 'success',
                        'driver' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\IconColumn::make('profile.is_kyc_verified')
                    ->boolean()
                    ->label('KYC'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_PROVIDER => 'Provider',
                        User::ROLE_CUSTOMER => 'Customer',
                        User::ROLE_DRIVER => 'Driver',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\Filter::make('kyc_verified')
                    ->query(fn (Builder $query): Builder => $query->whereHas('profile', fn ($q) => $q->where('is_kyc_verified', true)))
                    ->label('KYC Verified Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
