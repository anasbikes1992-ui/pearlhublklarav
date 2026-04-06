<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerticalConfigResource\Pages;
use App\Models\VerticalFeeConfig;
use App\Services\VerticalPolicy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class VerticalConfigResource extends Resource
{
    protected static ?string $model = VerticalFeeConfig::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Vertical Configurations';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Vertical Settings')
                ->schema([
                    Forms\Components\TextInput::make('vertical')
                        ->disabled()
                        ->label('Vertical'),
                    Forms\Components\TextInput::make('display_name')
                        ->required()
                        ->label('Display Name'),
                    Forms\Components\TextInput::make('icon')
                        ->label('Icon (Emoji)'),
                    Forms\Components\TextInput::make('color')
                        ->label('Theme Color'),
                ]),

            Forms\Components\Section::make('Commission & Fees')
                ->schema([
                    Forms\Components\TextInput::make('commission_rate')
                        ->numeric()
                        ->suffix('%')
                        ->step(0.01)
                        ->label('Commission Rate')
                        ->helperText('Percentage charged on each transaction'),
                    Forms\Components\TextInput::make('tax_rate')
                        ->numeric()
                        ->suffix('%')
                        ->step(0.01)
                        ->label('Tax Rate'),
                    Forms\Components\TextInput::make('service_charge_rate')
                        ->numeric()
                        ->suffix('%')
                        ->label('Service Charge'),
                ]),

            Forms\Components\Section::make('Booking Rules')
                ->schema([
                    Forms\Components\Select::make('flow_type')
                        ->options([
                            'booking' => 'Instant Booking',
                            'inquiry_only' => 'Inquiry Only',
                            'approval_required' => 'Approval Required',
                        ]),
                    Forms\Components\TextInput::make('cancellation_window_hours')
                        ->numeric()
                        ->suffix('hours')
                        ->label('Cancellation Window'),
                    Forms\Components\TextInput::make('buffer_hours')
                        ->numeric()
                        ->suffix('hours')
                        ->label('Buffer Hours'),
                    Forms\Components\Toggle::make('requires_escrow')
                        ->label('Requires Escrow'),
                ]),

            Forms\Components\Section::make('Provider Limits')
                ->schema([
                    Forms\Components\TextInput::make('product_limit')
                        ->numeric()
                        ->label('Product Limit per Provider')
                        ->helperText('Leave empty for unlimited'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vertical')
                    ->badge()
                    ->label('Vertical'),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon'),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->suffix('%')
                    ->label('Commission'),
                Tables\Columns\TextColumn::make('tax_rate')
                    ->suffix('%')
                    ->label('Tax'),
                Tables\Columns\IconColumn::make('requires_escrow')
                    ->boolean()
                    ->label('Escrow'),
                Tables\Columns\TextColumn::make('flow_type')
                    ->badge()
                    ->label('Flow'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('flow_type')
                    ->options([
                        'booking' => 'Instant Booking',
                        'inquiry_only' => 'Inquiry Only',
                        'approval_required' => 'Approval Required',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('requires_escrow')
                    ->label('Escrow Required'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('clear_cache')
                    ->label('Clear Cache')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(VerticalPolicy::class)->clearCache($record->vertical);
                        Notification::make()
                            ->title('Cache cleared')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('clear_all_cache')
                    ->label('Clear All Cache')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        app(VerticalPolicy::class)->clearAllCache();
                        Notification::make()
                            ->title('All vertical caches cleared')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerticalConfigs::route('/'),
            'edit' => Pages\EditVerticalConfig::route('/{record}/edit'),
        ];
    }
}
