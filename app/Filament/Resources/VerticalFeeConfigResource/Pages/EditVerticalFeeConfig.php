<?php

namespace App\Filament\Resources\VerticalFeeConfigResource\Pages;

use App\Filament\Resources\VerticalFeeConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVerticalFeeConfig extends EditRecord
{
    protected static string $resource = VerticalFeeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
