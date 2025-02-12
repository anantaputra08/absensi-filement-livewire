<?php

namespace App\Filament\Resources\RfidCardResource\Pages;

use App\Filament\Resources\RfidCardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRfidCard extends CreateRecord
{
    protected static string $resource = RfidCardResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
