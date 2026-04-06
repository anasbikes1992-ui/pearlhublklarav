<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Referral Information')
                ->schema([
                    Forms\Components\Select::make('referrer_id')
                        ->relationship('referrer', 'full_name')
                        ->required()
                        ->searchable()
                        ->disabledOn('edit'),
                    Forms\Components\Select::make('referred_id')
                        ->relationship('referred', 'full_name')
                        ->required()
                        ->searchable()
                        ->disabledOn('edit'),
                    Forms\Components\TextInput::make('code')
                        ->disabledOn('edit'),
                    Forms\Components\Select::make('status')
                        ->options([
                            Referral::STATUS_PENDING => 'Pending',
                            Referral::STATUS_QUALIFIED => 'Qualified',
                            Referral::STATUS_COMPLETED => 'Completed',
                            Referral::STATUS_PAID => 'Paid',
                            Referral::STATUS_EXPIRED => 'Expired',
                        ])
                        ->required(),
                    Forms\Components\Select::make('referral_type')
                        ->options([
                            Referral::TYPE_SIGNUP => 'Signup',
                            Referral::TYPE_BOOKING => 'Booking',
                            Referral::TYPE_LISTING => 'Listing',
                        ])
                        ->disabledOn('edit'),
                ]),

            Forms\Components\Section::make('Bonus Details')
                ->schema([
                    Forms\Components\TextInput::make('points_awarded')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('revenue_bonus_amount')
                        ->numeric()
                        ->prefix('LKR'),
                    Forms\Components\TextInput::make('bonus_currency')
                        ->default('LKR'),
                    Forms\Components\DateTimePicker::make('bonus_paid_at')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('qualified_at')
                        ->disabled(),
                ]),

            Forms\Components\Section::make('Qualification')
                ->schema([
                    Forms\Components\TextInput::make('qualified_action')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('expires_at'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('referrer.full_name')
                    ->label('Referrer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referred.full_name')
                    ->label('Referred User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Referral::STATUS_PAID => 'success',
                        Referral::STATUS_COMPLETED => 'info',
                        Referral::STATUS_QUALIFIED => 'warning',
                        Referral::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('referral_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label('Points'),
                Tables\Columns\TextColumn::make('revenue_bonus_amount')
                    ->money('LKR')
                    ->label('Bonus'),
                Tables\Columns\TextColumn::make('bonus_paid_at')
                    ->dateTime()
                    ->label('Paid At'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Referral::STATUS_PENDING => 'Pending',
                        Referral::STATUS_QUALIFIED => 'Qualified',
                        Referral::STATUS_COMPLETED => 'Completed',
                        Referral::STATUS_PAID => 'Paid',
                        Referral::STATUS_EXPIRED => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('referral_type')
                    ->options([
                        Referral::TYPE_SIGNUP => 'Signup',
                        Referral::TYPE_BOOKING => 'Booking',
                        Referral::TYPE_LISTING => 'Listing',
                    ]),
                Tables\Filters\Filter::make('unpaid')
                    ->query(fn ($query) => $query->unpaid())
                    ->label('Unpaid Bonuses'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pay_bonus')
                    ->label('Pay Bonus')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->isQualified() || $record->isCompleted())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(\App\Services\ReferralService::class)->payReferralBonus($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('pay_bonuses')
                        ->label('Pay Selected Bonuses')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(
                            fn ($record) => app(\App\Services\ReferralService::class)->payReferralBonus($record)
                        )),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }
}
