<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrokerConsentResource\Pages;
use App\Models\BrokerConsent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrokerConsentResource extends Resource
{
    protected static ?string $model = BrokerConsent::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Trust & KYC';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('listing_id')
                ->relationship('listing', 'title')
                ->required()
                ->searchable(),
            Forms\Components\Select::make('broker_id')
                ->relationship('broker', 'full_name')
                ->required()
                ->searchable(),
            Forms\Components\Select::make('owner_id')
                ->relationship('owner', 'full_name')
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('deed_file_path')->disabled(),
            Forms\Components\TextInput::make('authorization_file_path')->disabled(),
            Forms\Components\Toggle::make('indemnity_accepted')->disabled(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->required(),
            Forms\Components\Textarea::make('review_notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('listing.title')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('broker.full_name')->label('Broker')->searchable(),
                Tables\Columns\TextColumn::make('owner.full_name')->label('Owner')->searchable(),
                Tables\Columns\IconColumn::make('indemnity_accepted')->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrokerConsents::route('/'),
            'create' => Pages\CreateBrokerConsent::route('/create'),
            'edit' => Pages\EditBrokerConsent::route('/{record}/edit'),
        ];
    }
}
