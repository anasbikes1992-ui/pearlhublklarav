<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationGroup = 'Marketplace Moderation';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('provider_id')->required(),
            Forms\Components\Select::make('vertical')->options([
                'property' => 'Property',
                'stay' => 'Stay',
                'vehicle' => 'Vehicle',
                'event' => 'Event',
                'sme' => 'SME',
                'taxi' => 'Taxi',
            ])->required(),
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('description'),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\Select::make('status')->options([
                'draft' => 'Draft',
                'pending_verification' => 'Pending Verification',
                'published' => 'Published',
                'paused' => 'Paused',
                'archived' => 'Archived',
            ])->required(),
            Forms\Components\Toggle::make('is_hidden'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('vertical')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\IconColumn::make('is_hidden')->boolean(),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'pending_verification' => 'Pending Verification',
                    'published' => 'Published',
                    'paused' => 'Paused',
                    'archived' => 'Archived',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }
}
