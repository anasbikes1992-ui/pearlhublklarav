<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerticalFeeConfigResource\Pages;
use App\Models\VerticalFeeConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VerticalFeeConfigResource extends Resource
{
    protected static ?string $model = VerticalFeeConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Fee Configuration';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('vertical')
                ->options([
                    'property' => 'Property',
                    'stay' => 'Stays',
                    'vehicle' => 'Vehicles',
                    'event' => 'Events',
                    'sme' => 'SME',
                ])
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('listing_fee')
                ->numeric()
                ->prefix('LKR')
                ->default(0),
            Forms\Components\TextInput::make('commission_rate')
                ->numeric()
                ->suffix('%')
                ->helperText('Enter as decimal, e.g. 0.08 = 8%')
                ->default(0.08),
            Forms\Components\TextInput::make('vat_rate')
                ->numeric()
                ->suffix('%')
                ->helperText('VAT rate as decimal')
                ->default(0),
            Forms\Components\TextInput::make('tourism_tax_rate')
                ->numeric()
                ->suffix('%')
                ->helperText('Sri Lanka tourism tax')
                ->default(0),
            Forms\Components\TextInput::make('service_charge_rate')
                ->numeric()
                ->suffix('%')
                ->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vertical')->badge()->color('info'),
                Tables\Columns\TextColumn::make('listing_fee')->money('LKR'),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->formatStateUsing(fn ($state) => number_format($state * 100, 1) . '%'),
                Tables\Columns\TextColumn::make('vat_rate')
                    ->formatStateUsing(fn ($state) => number_format($state * 100, 1) . '%'),
                Tables\Columns\TextColumn::make('tourism_tax_rate')
                    ->label('Tourism Tax')
                    ->formatStateUsing(fn ($state) => number_format($state * 100, 1) . '%'),
                Tables\Columns\TextColumn::make('service_charge_rate')
                    ->label('Service Charge')
                    ->formatStateUsing(fn ($state) => number_format($state * 100, 1) . '%'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerticalFeeConfigs::route('/'),
            'create' => Pages\CreateVerticalFeeConfig::route('/create'),
            'edit' => Pages\EditVerticalFeeConfig::route('/{record}/edit'),
        ];
    }
}
