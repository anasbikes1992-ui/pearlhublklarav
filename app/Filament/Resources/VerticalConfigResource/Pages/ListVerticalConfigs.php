<?php

namespace App\Filament\Resources\VerticalConfigResource\Pages;

use App\Filament\Resources\VerticalConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVerticalConfigs extends ListRecords
{
    protected static string $resource = VerticalConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Vertical'),
        ];
    }
}
