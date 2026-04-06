<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialPostResource\Pages;
use App\Models\SocialPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialPostResource extends Resource
{
    protected static ?string $model = SocialPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Post Content')
                ->schema([
                    Forms\Components\Select::make('author_id')
                        ->relationship('author', 'full_name')
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('listing_id')
                        ->relationship('listing', 'title')
                        ->searchable()
                        ->nullable(),
                    Forms\Components\Select::make('vertical_tag')
                        ->options([
                            'property' => 'Property',
                            'stay' => 'Stays',
                            'vehicle' => 'Vehicles',
                            'event' => 'Events',
                            'sme' => 'SME',
                            'general' => 'General',
                        ]),
                    Forms\Components\Textarea::make('content')
                        ->required()
                        ->maxLength(2000)
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('attachments')
                        ->label('Attachments (JSON)'),
                    Forms\Components\Toggle::make('is_pinned')
                        ->label('Pinned to Top'),
                    Forms\Components\Toggle::make('is_hidden')
                        ->label('Hidden from Feed'),
                ]),

            Forms\Components\Section::make('Metadata')
                ->schema([
                    Forms\Components\TextInput::make('likes_count')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('comments_count')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('shares_count')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('views_count')
                        ->numeric()
                        ->disabled(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('author.full_name')
                    ->label('Author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Linked Listing')
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('vertical_tag')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'property' => 'success',
                        'stay' => 'info',
                        'vehicle' => 'warning',
                        'event' => 'danger',
                        'sme' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_pinned')
                    ->boolean()
                    ->label('Pinned'),
                Tables\Columns\IconColumn::make('is_hidden')
                    ->boolean()
                    ->label('Hidden'),
                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes'),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Comments'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vertical_tag')
                    ->options([
                        'property' => 'Property',
                        'stay' => 'Stays',
                        'vehicle' => 'Vehicles',
                        'event' => 'Events',
                        'sme' => 'SME',
                        'general' => 'General',
                    ]),
                Tables\Filters\TernaryFilter::make('is_pinned')
                    ->label('Pinned Posts'),
                Tables\Filters\TernaryFilter::make('is_hidden')
                    ->label('Hidden Posts'),
                Tables\Filters\Filter::make('reported')
                    ->query(fn (Builder $query): Builder => $query->whereHas('reports'))
                    ->label('Reported Posts'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn ($record) => ! $record->is_hidden)
                    ->action(fn ($record) => $record->update(['is_hidden' => true])),
                Tables\Actions\Action::make('unhide')
                    ->label('Unhide')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn ($record) => $record->is_hidden)
                    ->action(fn ($record) => $record->update(['is_hidden' => false])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('hide')
                        ->label('Hide Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_hidden' => true])),
                    Tables\Actions\BulkAction::make('unhide')
                        ->label('Unhide Selected')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_hidden' => false])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // SocialCommentRelationManager could be added here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialPosts::route('/'),
            'create' => Pages\CreateSocialPost::route('/create'),
            'edit' => Pages\EditSocialPost::route('/{record}/edit'),
        ];
    }
}
