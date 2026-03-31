<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EscrowResource\Pages;
use App\Models\Escrow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EscrowResource extends Resource
{
    protected static ?string $model = Escrow::class;

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('booking_id')->required(),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->required(),
            Forms\Components\Select::make('status')->options([
                'held' => 'Held',
                'released' => 'Released',
                'cancelled' => 'Cancelled',
            ])->required(),
            Forms\Components\DateTimePicker::make('released_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_id')->searchable(),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEscrows::route('/'),
            'create' => Pages\CreateEscrow::route('/create'),
            'edit' => Pages\EditEscrow::route('/{record}/edit'),
        ];
    }
}
