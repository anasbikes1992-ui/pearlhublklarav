<?php

namespace App\Filament\Resources\VerticalConfigResource\Pages;

use App\Filament\Resources\VerticalConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVerticalConfig extends EditRecord
{
    protected static string $resource = VerticalConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
