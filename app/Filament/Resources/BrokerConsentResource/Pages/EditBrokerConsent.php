<?php

namespace App\Filament\Resources\BrokerConsentResource\Pages;

use App\Filament\Resources\BrokerConsentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrokerConsent extends EditRecord
{
    protected static string $resource = BrokerConsentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
