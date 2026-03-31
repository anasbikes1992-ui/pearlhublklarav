<?php

namespace App\Filament\Resources\OwnershipDocumentResource\Pages;

use App\Filament\Resources\OwnershipDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOwnershipDocument extends EditRecord
{
    protected static string $resource = OwnershipDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
