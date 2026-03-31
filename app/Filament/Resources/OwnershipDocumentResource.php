<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnershipDocumentResource\Pages;
use App\Models\OwnershipDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OwnershipDocumentResource extends Resource
{
    protected static ?string $model = OwnershipDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Trust & KYC';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('listing_id')
                ->relationship('listing', 'title')
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('owner_name')->required(),
            Forms\Components\TextInput::make('nic_or_company'),
            Forms\Components\Select::make('type')
                ->options([
                    'deed_title' => 'Deed Title',
                    'nic_copy' => 'NIC Copy',
                    'company_reg' => 'Company Registration',
                ])
                ->required(),
            Forms\Components\TextInput::make('file_path')->disabled(),
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
                Tables\Columns\TextColumn::make('owner_name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
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
            'index' => Pages\ListOwnershipDocuments::route('/'),
            'create' => Pages\CreateOwnershipDocument::route('/create'),
            'edit' => Pages\EditOwnershipDocument::route('/{record}/edit'),
        ];
    }
}
