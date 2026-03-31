<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerificationAuditResource\Pages;
use App\Models\VerificationAudit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VerificationAuditResource extends Resource
{
    protected static ?string $model = VerificationAudit::class;

    protected static ?string $navigationGroup = 'Trust & KYC';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('listing_id')->required(),
            Forms\Components\TextInput::make('inspector_id')->required(),
            Forms\Components\Select::make('status')->options([
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'needs_changes' => 'Needs Changes',
            ])->required(),
            Forms\Components\Textarea::make('notes'),
            Forms\Components\DateTimePicker::make('inspected_at')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('listing_id')->searchable(),
                Tables\Columns\TextColumn::make('inspector_id')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('inspected_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerificationAudits::route('/'),
            'create' => Pages\CreateVerificationAudit::route('/create'),
            'edit' => Pages\EditVerificationAudit::route('/{record}/edit'),
        ];
    }
}
